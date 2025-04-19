<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\Campaign;
use App\Models\Fundraising;
use App\Models\Commission;
use App\Models\ManualPaymentMethod;
use App\Models\DonationSource;
use App\Models\Adsense;
use App\Models\User;
use App\Models\Admin;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Mail;
use App\Mail\DonationSuccessMail;
use App\Mail\CampaignDonationMail;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class DonationController extends Controller
{
    protected $apiKey;
    protected $privateKey;
    protected $merchantCode;
    protected $apiUrl;
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        // Konfigurasi Tripay
        $this->apiKey = env('TRIPAY_API_KEY');
        $this->privateKey = env('TRIPAY_PRIVATE_KEY');
        $this->merchantCode = env('TRIPAY_MERCHANT_CODE');
        $this->apiUrl = env('TRIPAY_API_URL', 'https://tripay.co.id/api-sandbox/');
        $this->notificationService = $notificationService;
    }

    private function updateDonationToSuccess($donation)
    {
        // Begin transaction
        DB::beginTransaction();
        
        try {
            // Update donation status
            $donation->status = 'sukses';
            $donation->updated_at = now();
            $donation->save();
            
            // Update campaign statistics
            $campaign = $donation->campaign;
            $campaign->jumlah_donasi += $donation->amount;
            $campaign->current_donation += $donation->amount;
            $campaign->total_donatur += 1;
            $campaign->save();
            
            // Track server-side conversion
            $this->trackServerSideConversion($donation);
            
            // Update donation source statistics
            if ($donation->donation_source_id) {
                $source = DonationSource::find($donation->donation_source_id);
                if ($source) {
                    $source->total_donations += 1;
                    $source->total_amount += $donation->amount;
                    $source->save();
                }
            }
            
            // Proses fundraising jika ada
            // ...kode fundraising yang sudah ada...
            
            // Kirim notifikasi email
            $this->sendDonationNotifications($donation);
            
            // Commit transaction
            DB::commit();
            
            return true;
        } catch (\Exception $e) {
            // Rollback in case of error
            DB::rollBack();
            Log::error('Error updating donation to success: ' . $e->getMessage());
            
            return false;
        }
    }
    
    /**
     * Kirim notifikasi email ke donatur dan pemilik kampanye
     */
    private function sendDonationNotifications($donation)
    {
        try {
            // Load relations yang dibutuhkan
            $donation->load(['campaign', 'campaign.admin']);
            
            // 1. Notifikasi untuk donatur
            if ($donation->email) {
                // Buat notifikasi di sistem
                if ($donation->user_id) {
                    $user = User::find($donation->user_id);
                    if ($user) {
                        $notificationData = [
                            'donation_id' => $donation->id,
                            'amount' => $donation->amount,
                            'campaign_title' => $donation->campaign->title
                        ];
                        
                        $notif = $this->notificationService->createNotification(
                            $user,
                            'Donasi Berhasil', 
                            'Terima kasih, donasi Anda sebesar Rp ' . number_format($donation->amount) . ' untuk "' . $donation->campaign->title . '" telah berhasil.',
                            'donation_success',
                            $notificationData
                        );
                        
                        // Kirim email
                        $this->notificationService->sendEmail($notif);
                    }
                } else {
                    // Jika tidak login, kirim email langsung
                    Mail::to($donation->email)->send(new DonationSuccessMail($donation));
                }
            }
            
            // 2. Notifikasi untuk pemilik kampanye
            if ($donation->campaign->admin && $donation->campaign->admin->email) {
                $admin = $donation->campaign->admin;
                
                $notificationData = [
                    'donation_id' => $donation->id,
                    'donor_name' => $donation->is_anonymous ? 'Orang Baik' : $donation->name,
                    'amount' => $donation->amount,
                    'campaign_title' => $donation->campaign->title
                ];
                
                $notif = $this->notificationService->createNotification(
                    $admin,
                    'Donasi Baru Diterima',
                    'Kampanye Anda "' . $donation->campaign->title . '" telah menerima donasi sebesar Rp ' . number_format($donation->amount) . ' dari ' . ($donation->is_anonymous ? 'Orang Baik' : $donation->name) . '.',
                    'new_donation',
                    $notificationData
                );
                
                // Kirim email
                $this->notificationService->sendEmail($notif);
            }
            
            return true;
        } catch (\Exception $e) {
            Log::error('Error sending donation notifications: ' . $e->getMessage());
            return false;
        }
    }


    public function showDonationForm($slug)
    {
        $campaign = Campaign::where('slug',$slug)->first();
        
        return view('donatur.donasi.index', compact('campaign'));
    }

    public function selectPaymentMethod($id)
    {
        $donation = Donation::with('campaign')->findOrFail($id);
        $campaign = $donation->campaign;
        
        // Ambil daftar channel pembayaran dari Tripay
        $channels = $this->getPaymentChannels();
        
        // Ambil daftar metode pembayaran manual
        $manualMethods = ManualPaymentMethod::where('is_active', true)->get();
        
        return view('donatur.donasi.payment-method', compact('donation', 'campaign', 'channels', 'manualMethods'));
    }

    public function processPayment(Request $request)
    {
        $validated = $request->validate([
            'donation_id' => 'required|exists:donations,id',
            'payment_type' => 'required|string|in:payment_gateway',
            'selected_payment_method' => 'required|string'
        ]);
        
        $donation = Donation::with('campaign')->findOrFail($request->donation_id);
        $campaign = $donation->campaign;
        
        // Update metode pembayaran donasi
        $donation->payment_type = $request->payment_type;
        $donation->payment_method = $request->selected_payment_method;
        $donation->save();
        
        // Buat transaksi di Tripay
        $transaction = $this->createTransaction($donation, $campaign);
        
        if (isset($transaction['success']) && $transaction['success'] && isset($transaction['data'])) {
            // Update donasi dengan reference dan checkout URL
            $donation->snap_token = $transaction['data']['reference'];
            $donation->save();
            
            // Redirect ke halaman pembayaran Tripay
            return redirect($transaction['data']['checkout_url']);
        } else {
            // Jika gagal, tampilkan error
            return redirect()->back()->with('error', 'Gagal membuat transaksi pembayaran: ' . ($transaction['message'] ?? 'Terjadi kesalahan sistem'));
        }
    }

    public function processDonation(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'campaign_id' => 'required|exists:campaigns,id',
            'amount' => 'required|numeric|min:10000',
            'name' => 'required|string|max:255',
            'phone' => 'required|string',
            'email' => 'required',
            'is_anonymous' => 'nullable',
            'doa' => 'nullable|string',
            'utm_source' => 'nullable|string',
        'utm_medium' => 'nullable|string',
        'utm_campaign' => 'nullable|string',
        ]);
        
        $campaign = Campaign::findOrFail($request->campaign_id);

            // Tangkap UTM parameters
    $utmSource = $request->utm_source ?? session('utm_source');
    $utmMedium = $request->utm_medium ?? session('utm_medium');
    $utmCampaign = $request->utm_campaign ?? session('utm_campaign');
    
    // Tentukan sumber donasi
    $sourceType = 'direct'; // Default
    if ($utmSource) {
        if (strpos($utmSource, 'google') !== false) $sourceType = 'google_ads';
        elseif (strpos($utmSource, 'facebook') !== false || strpos($utmSource, 'fb') !== false || strpos($utmSource, 'meta') !== false) $sourceType = 'facebook';
        elseif (strpos($utmSource, 'tiktok') !== false) $sourceType = 'tiktok';
    }
    
    // Cari atau buat sumber donasi
    $donationSource = DonationSource::firstOrCreate(
        ['source_type' => $sourceType, 'utm_source' => $utmSource, 'utm_medium' => $utmMedium, 'utm_campaign' => $utmCampaign],
        ['campaign_name' => $utmCampaign]
    );
      
        // Buat donasi baru dengan status pending
        $donation = Donation::create([
            'campaign_id' => $request->campaign_id,
            'user_id' => auth()->id(), // Jika user login
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'doa' => $request->doa,
            'is_anonymous' => $request->has('is_anonymous'),
            'amount' => $request->amount,
            'payment_type' => null, // Akan diupdate setelah memilih metode pembayaran
            'payment_method' => null, // Akan diupdate setelah memilih metode pembayaran
            'status' => 'pending',
            'snap_token' => Str::random(32), // Placeholder untuk snap_token
            'donation_source_id' => $donationSource->id,
            'utm_source' => $utmSource,
            'utm_medium' => $utmMedium,
            'utm_campaign' => $utmCampaign,
        ]);
        
        return redirect()->route('donations.select-payment-method', $donation->id);
    }

    public function markExpired($id)
{
    try {
        $donation = Donation::findOrFail($id);
        
        // Only update if donation is still pending
        if ($donation->status === 'pending') {
            $donation->status = 'gagal';
            $donation->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Donation marked as expired'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Donation is not in pending status'
        ]);
    } catch (\Exception $e) {
        Log::error('Error marking donation as expired: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Error updating donation status'
        ], 500);
    }
}


    // public function processDonation(Request $request)
    // {

    //     if($request->has('is_anonymous') && $request['is_anonymous'] == "on"){
    //         $request['is_anonymous'] = true;
    //     }else{
    //         $request['is_anonymous'] = false;
    //     }

    //     // Validasi input
    //     $validated = $request->validate([
    //         'campaign_id' => 'required|exists:campaigns,id',
    //         'amount' => 'required|numeric|min:10000',
    //         'name' => 'required|string|max:255',
    //         'phone' => 'required|string',
    //         'email' => 'required',
    //         'is_anonymous' => 'nullable',
    //         'doa' => 'nullable|string',
    //         'payment_method' => 'required|string'
    //     ]);
        
    //     $campaign = Campaign::findOrFail($request->campaign_id);
        
    //     // Cek apakah ada referral code di session
    //     $referralCode = session('referral_code');
    //     $fundraisingId = null;
        
    //     // Jika ada referral code, dapatkan fundraisingId
    //     if ($referralCode) {
    //         $fundraising = Fundraising::where('code_link', $referralCode)->first();
    //         if ($fundraising) {
    //             $fundraisingId = $fundraising->id;
    //         }
    //     }

        
    //     // Buat donasi baru dengan status pending
    //     $donation = Donation::create([
    //         'campaign_id' => $request->campaign_id,
    //         'user_id' => auth()->id(), // Jika user login
    //         'name' => $request->name,
    //         'phone' => $request->phone,
    //         'email' => $request->email,
    //         'doa' => $request->doa,
    //         'is_anonymous' => $request->has('is_anonymous'),
    //         'amount' => $request->amount,
    //         'payment_type' => 'payment_gateway',
    //         'payment_method' => $request->payment_method,
    //         'status' => 'pending',
    //         'snap_token' => Str::random(32) // Placeholder untuk snap_token
    //     ]);


        
    //     // Buat transaksi di Tripay
    //     $transaction = $this->createTransaction($donation, $campaign);
        
    //     if (isset($transaction['success']) && $transaction['success'] && isset($transaction['data'])) {
    //         // Update donasi dengan reference dan checkout URL
    //         $donation->snap_token = $transaction['data']['reference'];
    //         $donation->save();
            
    //         // Redirect ke halaman pembayaran Tripay
    //         return redirect($transaction['data']['checkout_url']);
    //     } else {
    //         // Jika gagal, hapus donasi dan tampilkan error
    //         $donation->delete();
    //         return redirect()->back()->with('error', 'Gagal membuat transaksi pembayaran: ' . ($transaction['message'] ?? 'Terjadi kesalahan sistem'));
    //     }
    // }
    public function processManualPayment(Request $request)
    {
        $validated = $request->validate([
            'donation_id' => 'required|exists:donations,id',
            'payment_type' => 'required|string|in:manual',
            'selected_payment_method' => 'required|exists:manual_payment_methods,id',
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
        
        $donation = Donation::with('campaign')->findOrFail($request->donation_id);
        
        // Simpan bukti pembayaran
        if ($request->hasFile('payment_proof')) {
            $path = $request->file('payment_proof')->store('payment_proofs', 'public');
            $donation->payment_proof = $path;
        }
        
        // Update metode pembayaran donasi
        $donation->payment_type = $request->payment_type;
        $donation->payment_method = 'manual';
        $donation->manual_payment_method_id = $request->selected_payment_method;
        $donation->save();
        
        return redirect()->route('donations.status', ['id' => $donation->id]);
    }

    protected function getPaymentChannels()
    {
        try {
            // Pastikan URL berakhir dengan slash
            $apiUrl = rtrim($this->apiUrl, '/') . '/';
            $endpoint = 'merchant/payment-channel';
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey
            ])->get($apiUrl . $endpoint);
    
            // Log full URL dan response
            Log::info('Tripay Request URL: ' . $apiUrl . $endpoint);
            Log::info('Tripay Response: ' . $response->body());
            
            if ($response->successful() && isset($response['success']) && $response['success']) {
                return $response['data'];
            }
            
            Log::error('Tripay payment channels error: ' . $response->body());
            return [];
        } catch (\Exception $e) {
            Log::error('Error getting payment channels: ' . $e->getMessage());
            return [];
        }
    }

    // protected function getPaymentChannels()
    // {
    //     try {
    //         // Pastikan URL berakhir dengan slash
    //         $apiUrl = rtrim($this->apiUrl, '/') . '/';
    //         $endpoint = 'merchant/payment-channel';
            
    //         $response = Http::withHeaders([
    //             'Authorization' => 'Bearer ' . $this->apiKey
    //         ])->get($apiUrl . $endpoint);
    
    //         // Log full URL dan response
    //         Log::info('Tripay Request URL: ' . $apiUrl . $endpoint);
    //         Log::info('Tripay Response: ' . $response->body());
            
    //         if ($response->successful() && isset($response['success']) && $response['success']) {
    //             return $response['data'];
    //         }
            
    //         Log::error('Tripay payment channels error: ' . $response->body());
    //         return [];
    //     } catch (\Exception $e) {
    //         Log::error('Error getting payment channels: ' . $e->getMessage());
    //         return [];
    //     }
    // }



    protected function createTransaction($donation, $campaign)
    {
        $merchantRef = 'DON-' . $donation->id . '-' . time();
        $amount = (int)$donation->amount;
        $donaturName = $donation->is_anonymous ? 'Orang Baik' : $donation->name;
        
        $data = [
            'method' => $donation->payment_method,
            'merchant_ref' => $merchantRef,
            'amount' => $amount,
            'customer_name' => $donaturName,
            'customer_email' => $donation->email,
            'customer_phone' => $donation->phone,
            'order_items' => [
                [
                    'name' => 'Donasi untuk ' . $campaign->title,
                    'price' => $amount,
                    'quantity' => 1
                ]
            ],
            'callback_url' => route('tripay.callback'),
            'return_url' => route('donations.status', ['id' => $donation->id]),
            'expired_time' => (time() + (24 * 60 * 60)), // 24 jam
            'signature' => hash_hmac('sha256', $this->merchantCode . $merchantRef . $amount, $this->privateKey)
        ];

        try {
            $apiUrl = rtrim($this->apiUrl, '/') . '/';
            $endpoint = 'transaction/create';
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey
            ])->post($apiUrl . $endpoint, $data);
            
            return $response->json();
        } catch (\Exception $e) {
            Log::error('Error creating transaction: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    public function callback(Request $request)
    {
        // Log request untuk debugging
        Log::info('Tripay Callback received: ' . $request->getContent());
        
        // Ambil data callback dari Tripay
        $callbackSignature = $request->header('X-Callback-Signature');
        $json = $request->getContent();
        
        // Verifikasi signature untuk keamanan
        $signature = hash_hmac('sha256', $json, $this->privateKey);
        
        if ($signature !== $callbackSignature) {
            Log::warning('Invalid Tripay callback signature');
            return response()->json([
                'success' => false,
                'message' => 'Invalid signature'
            ], 400);
        }
        
        $data = json_decode($json, true);
        
        // Validasi data
        if (!isset($data['reference'])) {
            Log::warning('No reference found in Tripay callback');
            return response()->json([
                'success' => false,
                'message' => 'No reference found'
            ], 400);
        }
        
        // Cari donasi berdasarkan reference
        $donation = Donation::where('snap_token', $data['reference'])->first();
        
        if (!$donation) {
            Log::warning('Donation not found for reference: ' . $data['reference']);
            return response()->json([
                'success' => false,
                'message' => 'Donation not found'
            ], 404);
        }
        
        Log::info('Processing Tripay callback for donation ID: ' . $donation->id . ', status: ' . $data['status']);
        
        // Begin transaction
        DB::beginTransaction();
        
        try {
            // Update status berdasarkan callback
            if ($data['status'] == 'PAID' && $donation->status !== 'sukses') {
                // Update donation status
                $donation->status = 'sukses';
                $donation->updated_at = now();
                $donation->save();

                $this->trackServerSideConversion($donation);
                
                // Update campaign statistics
                $campaign = $donation->campaign;
                $campaign->jumlah_donasi += $donation->amount;
                $campaign->current_donation += $donation->amount;
                $campaign->total_donatur += 1;
                $campaign->save();

                 // Update donation source statistics
        if ($donation->donation_source_id) {
            $source = DonationSource::find($donation->donation_source_id);
            if ($source) {
                $source->total_donations += 1;
                $source->total_amount += $donation->amount;
                $source->save();
            }
        }
                
                // Check if there's referral code in session
                $referralCode = session('referral_code');
                
                if ($referralCode) {
                    $fundraising = Fundraising::where('code_link', $referralCode)->first();
                    
                    if ($fundraising) {
                        $commissionSetting = Commission::first();
                        $commissionPercent = $commissionSetting->amount ?? 0;
                        
                        // Calculate commission based on percentage from database
                        $commission = ($donation->amount * $commissionPercent) / 100;
                        
                        // Update fundraising data
                        $fundraising->total_donatur += 1;
                        $fundraising->jumlah_donasi += $donation->amount;
                        $fundraising->commission += $commission;
                        
                        // Update donations array
                        $donations = json_decode($fundraising->donations, true) ?: [];
                        $donations[] = [
                            'donation_id' => $donation->id,
                            'amount' => $donation->amount,
                            'commission' => $commission,
                            'user_name' => $donation->user ? $donation->user->name : null,
                            'user_email' => $donation->user ? $donation->user->email : null,
                            'created_at' => now()->format('Y-m-d H:i:s')
                        ];
                        $fundraising->donations = json_encode($donations);
                        
                        $fundraising->save();
                    }
                }
                
                Log::info('Donation marked as success via callback: ' . $donation->id);
            } else if (in_array($data['status'], ['EXPIRED', 'FAILED', 'REFUND']) && $donation->status !== 'gagal') {
                // Jika gagal atau kadaluarsa
                $donation->status = 'gagal';
                $donation->save();
                Log::info('Donation marked as failed via callback: ' . $donation->id);
            }
            
            // Commit transaction
            DB::commit();
        } catch (\Exception $e) {
            // Rollback in case of error
            DB::rollBack();
            Log::error('Error processing Tripay callback: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error processing callback: ' . $e->getMessage()
            ], 500);
        }
        
        // Selalu kembalikan sukses untuk callback Tripay
        return response()->json(['success' => true]);
    }

    public function status(Request $request, $id)
{
    $donation = Donation::with(['campaign', 'manualPaymentMethod'])->where('id', $id)->firstOrFail();
    $campaign = $donation->campaign;
    $paymentDetail = null;
    
    // Jika pembayaran gateway dan memiliki snap_token, cek status di Tripay
    if ($donation->payment_type == 'payment_gateway' && $donation->snap_token) {
        // Pastikan URL berakhir dengan slash
        $apiUrl = rtrim($this->apiUrl, '/') . '/';
        $endpoint = 'transaction/detail';
        
        $reference = $donation->snap_token; // Gunakan snap_token dari donasi
        $signature = hash_hmac('sha256', $this->merchantCode . $reference, $this->privateKey);
        
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey
            ])->get($apiUrl . $endpoint, [
                'reference' => $reference,
                'signature' => $signature
            ]);
            
            $responseData = $response->json();
            
            if (isset($responseData['success']) && $responseData['success'] === true && isset($responseData['data'])) {
                $transaction = $responseData['data'];
                
                // Simpan detail pembayaran untuk ditampilkan di view
                $paymentDetail = [
                    'merchant_ref' => $transaction['merchant_ref'] ?? '',
                    'reference' => $transaction['reference'] ?? '',
                    'payment_method' => $transaction['payment_name'] ?? $donation->payment_method,
                    'payment_instructions' => $transaction['instructions'] ?? [],
                    'checkout_url' => $transaction['checkout_url'] ?? null
                ];
                
                // Update status donasi jika perlu
                $transaction = $responseData['data'];

                $status = 'PENDING';
                
                
                if ($transaction['status'] === 'PAID') {
                    $status = 'PAID';

                    $campaign = $donation->campaign;
                    $campaign->jumlah_donasi += $donation->amount;
                    $campaign->current_donation += $donation->amount;
                    $campaign->total_donatur += 1;
                    $campaign->save();

                    $this->trackServerSideConversion($donation);

                     // Update donation source statistics
                    if ($donation->donation_source_id) {
                        $source = DonationSource::find($donation->donation_source_id);
                        if ($source) {
                            $source->total_donations += 1;
                            $source->total_amount += $donation->amount;
                            $source->save();
                        }
                    }

                        
                    // Cek apakah ada referral code di session
                    $referralCode = session('referral_code');
                    $fundraisingId = null;

                    
                    if ($referralCode) {
                        $fundraising = Fundraising::where('code_link', $referralCode)->first();
                        
                        if ($fundraising) {

                            $commissionSetting = Commission::first(); // atau ->find($id) kalau kamu pakai banyak record
                            $commissionPercent = $commissionSetting->amount ?? 0;

                            // Hitung komisi sesuai persentase dari database
                            $commission = ($donation->amount * $commissionPercent) / 100;
                            
                            
                            // Update data fundraising
                            $fundraising->total_donatur += 1;
                            $fundraising->jumlah_donasi += $donation->amount;
                            $fundraising->commission += $commission;
                            
                            // Update array donations
                            $donations = json_decode($fundraising->donations, true) ?: [];
                            $donations[] = [
                                'donation_id' => $donation->id,
                                'amount' => $donation->amount,
                                'commission' => $commission,
                                'user_name' => $user->name ?? null,
                                'user_email' => $user->email ?? null,
                                'created_at' => now()->format('Y-m-d H:i:s')
                            ];
                            $fundraising->donations = json_encode($donations);
                            
                            $fundraising->save();
                        }
                    }
                    
                    if ($donation) {
                        $donation->status = 'sukses';
                        $donation->updated_at = now();
                        $donation->save();
                    }
                } else if (in_array($transaction['status'], ['EXPIRED', 'FAILED', 'REFUND'])) {
                    $status = 'EXPIRED';
                
                    if ($donation) {
                        $donation->status = 'gagal';
                        $donation->save();
                    }
                }
            } else if ($donation->payment_type == 'manual' && $donation->manual_payment_method_id) {
                $manualMethod = $donation->manualPaymentMethod;
                
                if ($manualMethod) {
                    $paymentDetail = [
                        'payment_method' => 'Manual - ' . $manualMethod->name,
                        'manual_account_name' => $manualMethod->account_name,
                        'manual_account_number' => $manualMethod->account_number,
                        'manual_instructions' => $manualMethod->instructions,
                        'payment_proof' => $donation->payment_proof ? asset('storage/' . $donation->payment_proof) : null
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('Error checking transaction status: ' . $e->getMessage());
        }
    }
    
    return view('donatur.donasi.status', compact('donation', 'campaign', 'paymentDetail'));
}

public function checkStatus($reference)
{
    try {
        // Cari donasi berdasarkan reference/snap_token
        $donation = Donation::where('snap_token', $reference)->first();
        
        if (!$donation) {
            return response()->json([
                'success' => false,
                'message' => 'Donasi tidak ditemukan'
            ], 404);
        }
        
        // Jika pembayaran manual, cek status langsung dari database
        if ($donation->payment_type == 'manual') {
            return response()->json([
                'success' => true,
                'data' => [
                    'status' => $donation->status,
                    'payment_method' => 'Manual - ' . 
                        ($donation->manualPaymentMethod ? $donation->manualPaymentMethod->name : 'Transfer'),
                    'amount' => $donation->amount,
                    'payment_proof' => $donation->payment_proof ? 
                        asset('storage/' . $donation->payment_proof) : null,
                    'updated_at' => $donation->updated_at->format('Y-m-d H:i:s')
                ]
            ]);
        }
        
        // Jika pembayaran gateway, cek status di Tripay
        // Pastikan URL berakhir dengan slash
        $apiUrl = rtrim($this->apiUrl, '/') . '/';
        $endpoint = 'transaction/detail';
        
        $signature = hash_hmac('sha256', $this->merchantCode . $reference, $this->privateKey);
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey
        ])->get($apiUrl . $endpoint, [
            'reference' => $reference,
            'signature' => $signature
        ]);

        $responseData = $response->json();


        if (isset($responseData['success']) && $responseData['success'] === true) {

            
            if ($responseData['success'] === true) {
                // Ambil data transaksi dari respons
                $transaction = $responseData['data'];

                $status = 'PENDING';
                
                
                if ($transaction['status'] === 'PAID') {
                    $status = 'PAID';

                    $campaign = $donation->campaign;
                    $campaign->jumlah_donasi += $donation->amount;
                    $campaign->current_donation += $donation->amount;
                    $campaign->total_donatur += 1;
                    $campaign->save();

                    $this->trackServerSideConversion($donation);


                     // Update donation source statistics
        if ($donation->donation_source_id) {
            $source = DonationSource::find($donation->donation_source_id);
            if ($source) {
                $source->total_donations += 1;
                $source->total_amount += $donation->amount;
                $source->save();
            }
        }
                    
                    $donation = Donation::where('snap_token', $reference)->first();
                    if ($donation) {
                        $donation->status = 'sukses';
                        $donation->updated_at = now();
                        $donation->save();
                    }

                } else if (in_array($transaction['status'], ['EXPIRED', 'FAILED', 'REFUND'])) {
                    $status = 'EXPIRED';
                    
                    $donation = Donation::where('snap_token', $reference)->first();
                    if ($donation) {
                        $donation->status = 'gagal';
                        $donation->save();
                    }
                }
                
                return response()->json([
                    'success' => true,
                    'data' => [
                        'status' => $status,
                        'reference' => $reference,
                        'payment_method' => $transaction['payment_method'],
                        'amount' => $transaction['amount'],
                        'paid_at' => $transaction['paid_at'] ?? null,
                        'note' => $transaction['note'] ?? null
                    ]
                ]);
                
            }
            
            return response()->json([
                'success' => false,
                'message' => $responseData['message'] ?? 'Transaksi tidak ditemukan'
            ]);
        }
        
        // Jika API mengembalikan error
        return response()->json([
            'success' => false,
            'message' => 'Gagal mendapatkan status pembayaran: ' . ($responseData['message'] ?? 'Transaksi tidak ditemukan')
        ]);
    } catch (\Exception $e) {
        Log::error('Error checking transaction status: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
        ]);
    }
}

private function trackServerSideConversion($donation)
{
    try {
        $adsense = Adsense::first();
        if (!$adsense) return;
        
        // Facebook Conversion API
        if ($adsense->meta_token && $adsense->meta_endpoint) {
            $userData = [
                'em' => hash('sha256', strtolower($donation->email)),
                'ph' => hash('sha256', preg_replace('/[^0-9]/', '', $donation->phone)),
            ];
            
            $data = [
                'data' => [
                    [
                        'event_name' => 'Purchase',
                        'event_time' => time(),
                        'user_data' => $userData,
                        'custom_data' => [
                            'currency' => 'IDR',
                            'value' => $donation->amount,
                            'content_name' => $donation->campaign->title,
                            'content_type' => 'donation',
                            'content_ids' => [$donation->id],
                            'campaign_id' => $donation->campaign_id,
                        ],
                        'event_source_url' => url('/donations/' . $donation->id . '/status'),
                        'action_source' => 'website'
                    ]
                ],
                'access_token' => $adsense->meta_token
            ];
            
            Http::post($adsense->meta_endpoint, $data);
        }
        
        // TikTok Events API
        if ($adsense->tiktok_token && $adsense->tiktok_endpoint && $adsense->tiktok_pixel) {
            $data = [
                'pixel_code' => $adsense->tiktok_pixel,
                'event' => 'CompletePayment',
                'timestamp' => time(),
                'properties' => [
                    'currency' => 'IDR',
                    'value' => $donation->amount,
                    'content_id' => $donation->id,
                    'content_type' => 'donation'
                ],
                'context' => [
                    'user' => [
                        'email' => hash('sha256', strtolower($donation->email)),
                        'phone' => hash('sha256', preg_replace('/[^0-9]/', '', $donation->phone))
                    ],
                    'page' => [
                        'url' => url('/donations/' . $donation->id . '/status')
                    ]
                ]
            ];
            
            Http::withHeaders([
                'Access-Token' => $adsense->tiktok_token
            ])->post($adsense->tiktok_endpoint, $data);
        }
    } catch (\Exception $e) {
        Log::error('Error tracking conversion: ' . $e->getMessage());
    }
}

public function index(Request $request)
{
    Carbon::setLocale('id');
    
    // Get all campaigns for dropdown filter
    $campaigns = Campaign::select('id', 'title')->get();
    
    if ($request->ajax()) {
        // Start with base query
        $query = Donation::with('campaign');
        
        // Apply campaign filter if provided
        if ($request->has('campaign_id') && $request->campaign_id) {
            $query->where('campaign_id', $request->campaign_id);
        }
        
        // Apply date range filter if provided
        if ($request->has('start_date') && $request->has('end_date') && $request->start_date && $request->end_date) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('campaign_title', function ($row) {
                return $row->campaign ? $row->campaign->title : '-';
            })    
            ->addColumn('created_at', function($row) {
                return $row->created_at 
                ? Carbon::parse($row->created_at)->timezone('Asia/Jakarta')->format('d M Y')
                : '-';
            })
            ->addColumn('action', function($row) {
                $actionBtn = '
                    <div class="btn-group" role="group">
                    <a href="/galang-dana/'.$row->name.'" class="btn btn-info btn-sm"><i class="fa-solid fa-eye text-white"></i></a>
                        <a href="'.route('admin.edit', $row->id).'" class="btn btn-primary btn-sm"><i class="fa-solid fa-pen"></i></a>
                        <button onclick="deleteAdmin('.$row->id.')" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
                    </div>
                ';
                return $actionBtn;
            })
            ->rawColumns(['campaign_title','created_at','action'])
            ->make(true);
    }
    
    return view('super_admin.donasi_kampanye.index', compact('campaigns'));
}

public function ceklis(Request $request)
{
    Carbon::setLocale('id');
    if ($request->ajax()) {
        // Start with base query
        $query = Donation::query();
        
        // Apply payment method filter if provided
        if ($request->has('payment_type') && $request->payment_type) {
            $query->where('payment_type', $request->payment_type);
        }
        
        // IMPORTANT: Use the query builder version of DataTables, not the collection version
        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('method', function ($row) {
                if($row->payment_type == "manual"){
                    $payment = "Manual";
                }else{
                    $payment = "Payment Gateway";
                }
                return $payment . ' ('. $row->payment_method . ')';
            })    
            ->addColumn('created_at', function($row) {
                return $row->created_at 
                ? Carbon::parse($row->created_at)->timezone('Asia/Jakarta')->format('d M Y')
                : '-';
            })
            ->addColumn('amount', function($row) {
                return 'Rp ' . number_format($row->amount, 0, ',', '.');
            })  
            ->addColumn('status', function($row) {
                $statusColor = [
                    'pending' => 'warning',
                    'sukses' => 'success', 
                    'gagal' => 'danger'
                ];
                return '<span class="badge bg-'.$statusColor[$row->status].' text-white">'.ucfirst($row->status).'</span>';
            })
            ->addColumn('action', function($row) {
                $whatsappUrl = "https://wa.me/". $row->phone;
                
                $actionBtn = '<div class="btn-group" role="group">';
            
                // Jika status masih pending, tampilkan tombol ceklis & silang lebih dulu
                if ($row->status == 'pending') {
                    $actionBtn .= '
                        <button onclick="updateStatus('.$row->id.', \'sukses\')" class="btn btn-primary btn-sm">
                            <i class="fas fa-check"></i>
                        </button>
                        <button onclick="updateStatus('.$row->id.', \'gagal\')" class="btn btn-danger btn-sm">
                            <i class="fas fa-times"></i>
                        </button>';
                }
            
                // Tambahkan tombol WhatsApp & Hapus setelahnya
                $actionBtn .= '
                    <a href="'.$whatsappUrl.'" target="_blank" class="btn btn-success btn-sm">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                    <button onclick="deleteDonasi('.$row->id.')" class="btn btn-danger btn-sm">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>'; // Tutup div.btn-group
            
                return $actionBtn;
            })               
            ->rawColumns(['amount','status','method','created_at','action'])
            // Remove this line that's causing the error:
            // ->orderColumn('DT_RowIndex', false)
            ->make(true);
    }
    
    return view('super_admin.ceklis_donasi.index');
}

    public function updateStatus(Request $request)
    {
        $donation = Donation::find($request->id);
        
        if (!$donation) {
            return response()->json(['success' => false, 'message' => 'Donasi tidak ditemukan']);
        }

        $donation->status = $request->status;
        $donation->save();

        return response()->json(['success' => true, 'message' => 'Status donasi berhasil diperbarui']);
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }


    /**
     * Display the specified resource.
     */
    // public function show(Request $request, $slug)
    // {
    //     Carbon::setLocale('id');
    
    //     if ($request->ajax()) {
    //         $query = Donation::with('campaign')
    //             ->whereHas('campaign', function ($q) use ($slug) {
    //                 $q->where('slug', $slug);
    //             })
    //             ->get();
    
    //         return DataTables::of($query)
    //             ->addIndexColumn()
    //             ->addColumn('campaign_title', function ($row) {
    //                 return $row->campaign ? $row->campaign->title : '-';
    //             })    
    //             ->addColumn('created_at', function ($row) {
    //                 return $row->created_at 
    //                     ? Carbon::parse($row->created_at)->timezone('Asia/Jakarta')->format('d M Y')
    //                     : '-';
    //             })
    //             ->rawColumns(['campaign_title', 'created_at'])
    //             ->make(true);
    //     }
    
    //     return view('super_admin.donasi_kampanye.show', compact('title'));
    // }    


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Donation $donation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Donation $donation)
    {
        //
    }

    public function destroy($id)
{
    DB::beginTransaction();
    try {
        // Cari data donasi berdasarkan ID
        $donation = Donation::findOrFail($id);

        // Menghapus data donasi
        $donation->delete();

        DB::commit();
        return response()->json(['status' => 'success', 'message' => 'Donasi berhasil dihapus']);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['status' => 'error', 'message' => 'Gagal menghapus donasi: ' . $e->getMessage()], 500);
    }
}



}


