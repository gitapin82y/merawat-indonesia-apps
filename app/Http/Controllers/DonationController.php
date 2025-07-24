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
use App\Models\TripayPaymentMethod;

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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

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
         $this->apiKey = env('TRIPAY_API_KEY', '738ktbs6qGsDWCz6y43l97GYETsEblbMNVaSD4QN');
         $this->privateKey = env('TRIPAY_PRIVATE_KEY', 'DjCiT-tVIsX-9AFLb-WuW0F-o4P4E');
         $this->merchantCode = env('TRIPAY_MERCHANT_CODE', 'T31062');
         $this->apiUrl = env('TRIPAY_API_URL', 'https://tripay.co.id/api/');
        $this->notificationService = $notificationService;
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
                    'donor_name' => $donation->is_anonymous ? 'Sahabat Baik' : $donation->name,
                    'amount' => $donation->amount,
                    'campaign_title' => $donation->campaign->title
                ];
                
                $notif = $this->notificationService->createNotification(
                    $admin,
                    'Donasi Baru Diterima',
                    'Kampanye Anda "' . $donation->campaign->title . '" telah menerima donasi sebesar Rp ' . number_format($donation->amount) . ' dari ' . ($donation->is_anonymous ? 'Sahabat Baik' : $donation->name) . '.',
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
        
        $channels = $this->getPaymentChannels();
        $manualMethods = ManualPaymentMethod::where('is_active', true)->get();

        return view('donatur.donasi.index', compact('campaign', 'channels', 'manualMethods'));
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
        // Update donasi dengan reference
        $donation->snap_token = $transaction['data']['reference'];
        $donation->save();
        
        // PERUBAHAN: Tidak redirect, langsung ke halaman status dengan token
        $statusToken = $this->createStatusToken($donation->id);
        return redirect()->route('donations.status', [
            'id' => $donation->id,
            'status_token' => $statusToken
        ]);
    } else {
        // Jika gagal, tampilkan error
        return redirect()->back()->with('error', 'Gagal membuat transaksi pembayaran: ' . ($transaction['message'] ?? 'Terjadi kesalahan sistem'));
    }
}

    public function processDonation(Request $request)
    {

        // Validasi input
        $rules = [
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
            'contact_agree' => 'nullable',
            'payment_type' => 'required|string|in:payment_gateway,manual',
            'selected_payment_method' => 'required|string',
        ];

        if ($request->payment_type === 'manual') {
            $rules['selected_payment_method'] = 'required|exists:manual_payment_methods,id';
        }

        $validated = $request->validate($rules);



        
        $campaign = Campaign::findOrFail($request->campaign_id);

            // Tangkap UTM parameters
    $utmSource = $request->utm_source ?? session('utm_source');
    $utmMedium = $request->utm_medium ?? session('utm_medium');
    $utmCampaign = $request->utm_campaign ?? session('utm_campaign');
    $referralCode = session('referral_code');
    
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



        $uniqueCode = rand(100, 999);
      
        // Buat donasi baru dengan status pending
        $donation = Donation::create([
            'campaign_id' => $request->campaign_id,
            'user_id' => auth()->id(), // Jika user login
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'doa' => $request->doa,
            'is_anonymous' => $request->has('is_anonymous'),
             'is_contactable' => $request->has('contact_agree'),
            'amount' => $request->amount,
            'payment_type' => $request->payment_type,
            'payment_method' => null,
            'status' => 'pending',
            'unique_code' => $uniqueCode,
            'snap_token' => Str::random(32), // Placeholder untuk snap_token
            'donation_source_id' => $donationSource->id,
            'utm_source' => $utmSource,
            'referral_code' => $referralCode,
            'utm_medium' => $utmMedium,
            'utm_campaign' => $utmCampaign,
        ]);
        
        if ($request->payment_type === 'payment_gateway') {
            $donation->payment_method = $request->selected_payment_method;
            $donation->save();

            $transaction = $this->createTransaction($donation, $campaign);

            if (isset($transaction['success']) && $transaction['success'] && isset($transaction['data'])) {
                $donation->snap_token = $transaction['data']['reference'];
                $donation->save();

                $statusToken = $this->createStatusToken($donation->id);
                return redirect()->route('donations.status', [
                    'id' => $donation->id,
                    'status_token' => $statusToken
                ]);
            }

            return redirect()->back()->with('error', 'Gagal membuat transaksi pembayaran');
        } else {
            // Pembayaran manual

            $manualMethod = ManualPaymentMethod::find($request->selected_payment_method);
            $donation->payment_method = $manualMethod ? $manualMethod->name : 'manual';
            if (Schema::hasColumn('donations', 'manual_payment_method_id')) {
                $donation->manual_payment_method_id = $request->selected_payment_method;
            }

            $originalAmount = $donation->amount;
            $donation->amount = $originalAmount + $donation->unique_code;
            $donation->save();

            return redirect()->route('donations.status', ['id' => $donation->id]);
        }
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

        $manual_method = 'manual';

        if($request->selected_payment_method){
            $method = ManualPaymentMethod::find($request->selected_payment_method);
            $manual_method = $method->name;
        }
        
        // Update metode pembayaran donasi
        $donation->payment_type = $request->payment_type;
        $donation->payment_method = $manual_method;
        if (Schema::hasColumn('donations', 'manual_payment_method_id')) {
            $donation->manual_payment_method_id = $request->selected_payment_method;
        }
        $donation->save();

        $user = User::where('email', 'suport@merawatindonesia.com')->first();
        if ($user) {
            $notificationData = [
                'donation_id' => $donation->id,
                'amount' => $donation->amount,
                'campaign_title' => $donation->campaign->title
            ];
            
            $notif = $this->notificationService->createNotification(
                $user,
                'Request Verifikasi Payment Manual', 
                'Memerlukan persetujuan donasi dengan jumlah Rp ' . number_format($donation->amount) . ' untuk "' . $donation->campaign->title . '".',
                'request_verifikasi',
                $notificationData
            );
            
            $this->notificationService->sendEmail($notif);
        }

        
        return redirect()->route('donations.status', ['id' => $donation->id]);
    }

    protected function getPaymentChannels()
    {
        try {
            // Log::info('API URL from config: ' . ($this->apiUrl ?? 'NULL'));
        // Log::info('API Key from config: ' . (empty($this->apiKey) ? 'EMPTY' : substr($this->apiKey, 0, 5) . '...'));
            // Pastikan URL berakhir dengan slash
            $apiUrl = rtrim($this->apiUrl, '/') . '/';
            $endpoint = 'merchant/payment-channel';
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey
            ])->get($apiUrl . $endpoint);

            // Log full URL dan response
            // Log::info('Tripay Request URL: ' . $apiUrl . $endpoint);
            // Log::info('Tripay Response: ' . $response->body());
            
            if ($response->successful() && isset($response['success']) && $response['success']) {
                // Get active payment methods from database
                $activeMethods = TripayPaymentMethod::where('is_active', true)
                    ->pluck('code')
                    ->toArray();
                
                // If no methods have been configured yet, return all methods
                if (empty($activeMethods)) {
                    // This ensures all methods are shown by default before admin configures them
                    return $response['data'];
                }
                
                // Filter payment methods to only show active ones
                $filteredMethods = collect($response['data'])
                    ->filter(function ($method) use ($activeMethods) {
                        return in_array($method['code'], $activeMethods);
                    })
                    ->values()
                    ->all();
                
                return $filteredMethods;
            }
            
            Log::error('Tripay payment channels error: ' . $response->body());
            return [];
        } catch (\Exception $e) {
            Log::error('Error getting payment channels: ' . $e->getMessage());
            return [];
        }
    }

    protected function createTransaction($donation, $campaign)
{
    $statusToken = $this->createStatusToken($donation->id);
    
    $merchantRef = 'DON-' . $donation->id . '-' . time();
    $amount = (int)$donation->amount;
    $donaturName = $donation->is_anonymous ? 'Sahabat Baik' : $donation->name;
    
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
        // 'callback_url' => route('tripay.callback'),
        'return_url' => route('donations.status', [
            'id' => $donation->id, 
            'status_token' => $statusToken
        ]),
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

// Helper method to process fundraising commission
private function processFundraisingCommission($donation)
{
    $fundraising = Fundraising::where('code_link', $donation->referral_code)->first();
    
    if ($fundraising) {
        $commissionSetting = Commission::first();
        $commissionPercent = $commissionSetting->amount ?? 0;
        
        // Calculate commission
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

private function processSuccessfulPayment($donation)
{
    // Update status donasi
    $donation->status = 'sukses';
    $donation->updated_at = now();
    $donation->save();
    
    // Update campaign statistics
    $campaign = Campaign::find($donation->campaign_id);
    if ($campaign) {
        $campaign->increment('jumlah_donasi', $donation->amount);
        $campaign->increment('current_donation', $donation->amount);
        $campaign->increment('total_donatur', 1);
    }
    
    // Update donation source if exists
    if ($donation->donation_source_id) {
        $source = DonationSource::find($donation->donation_source_id);
        if ($source) {
            $source->increment('total_donations', 1);
            $source->increment('total_amount', $donation->amount);
        }
    }
    
    // Process fundraising commission if exists
    if ($donation->referral_code) {
        $this->processFundraisingCommission($donation);
    }
    
    // Kirim notifikasi dan track konversi
    try {
        Mail::to($donation->email)->send(new DonationSuccessMail($donation));
        // Log::info('Donation success email sent to donor: ' . $donation->email);
    } catch (\Exception $e) {
        Log::error('Failed to send donation success email to donor: ' . $e->getMessage());
    }

    try {
        $campaign = Campaign::with('admin')->find($donation->campaign_id);
        if ($campaign && $campaign->admin && $campaign->admin->email) {
            Mail::to($campaign->admin->email)->send(new CampaignDonationMail($donation));
            // Log::info('Campaign donation email sent to admin: ' . $campaign->admin->email);
        } else {
            Log::warning('Admin email not found for campaign ID: ' . $donation->campaign_id);
        }
    } catch (\Exception $e) {
        Log::error('Failed to send campaign donation email to admin: ' . $e->getMessage());
    }

    try {
        $this->trackServerSideConversion($donation);
        $this->clearDonationSessions();
        Log::info('Conversion tracking completed', ['donation_id' => $donation->id]);
    } catch (\Exception $e) {
        Log::error('Error when sending notifications or tracking conversion', [
            'donation_id' => $donation->id,
            'error' => $e->getMessage()
        ]);
    }
    
    return true;
}

public function status(Request $request, $id)
{
    $donation = Donation::with(['campaign', 'manualPaymentMethod'])->where('id', $id)->firstOrFail();
    $campaign = $donation->campaign;
    $paymentDetail = null;
    $isNewPayment = false;

    if ($request->has('status_token')) {
        $tokenValid = $this->checkStatusToken($id, $request->status_token);
        if ($tokenValid) {
            $isNewPayment = true;
        }
    }
    
    // Jika pembayaran gateway dan status masih pending, cek status di Tripay
    if ($donation->payment_type == 'payment_gateway' && $donation->snap_token && $donation->status == 'pending') {
        $apiUrl = rtrim($this->apiUrl, '/') . '/';
        $endpoint = 'transaction/detail';
        
        $reference = $donation->snap_token;
        $signature = hash_hmac('sha256', $this->merchantCode . $reference, $this->privateKey);
        
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey
            ])->get($apiUrl . $endpoint, [
                'reference' => $reference,
                'signature' => $signature
            ]);
            
            $responseData = $response->json();
            // Log::info('Tripay status check response', ['donation_id' => $id, 'response' => $responseData]);
            
            if (isset($responseData['success']) && $responseData['success'] === true && isset($responseData['data'])) {
                $transaction = $responseData['data'];
                
                // Set payment detail untuk tampilan dengan format yang lebih user-friendly
                $paymentDetail = [
                    'merchant_ref' => $transaction['merchant_ref'] ?? '',
                    'reference' => $transaction['reference'] ?? '',
                    'payment_method' => $transaction['payment_name'] ?? $donation->payment_method,
                    'payment_instructions' => $this->formatPaymentInstructions($transaction['instructions'] ?? []),
                    'virtual_account' => $transaction['pay_code'] ?? null,
                    'qr_string' => $transaction['qr_string'] ?? null,
                    'qr_url' => $transaction['qr_url'] ?? null,
                    'checkout_url' => $transaction['checkout_url'] ?? null,
                    'payment_amount' => $transaction['amount'] ?? $donation->amount,
                    'expired_time' => $transaction['expired_time'] ?? null,
                    'status' => $transaction['status'] ?? 'PENDING'
                ];
                
                // Proses pembayaran sukses dengan transaction dan lock
                if ($transaction['status'] === 'PAID' && $donation->status !== 'sukses') {
                    DB::beginTransaction();
                    try {
                        $freshDonation = Donation::lockForUpdate()->find($donation->id);
                        
                        if ($freshDonation && $freshDonation->status !== 'sukses') {
                            $this->processSuccessfulPayment($freshDonation);
                            // Log::info('Payment successfully processed via status page', ['donation_id' => $donation->id]);
                        }
                        
                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error('Error processing payment in status page', [
                            'donation_id' => $donation->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                } 
                else if (in_array($transaction['status'], ['EXPIRED', 'FAILED', 'REFUND']) && $donation->status !== 'gagal') {
                    $donation->status = 'gagal';
                    $donation->save();
                    
                    // Log::info('Payment marked as failed via status page', ['donation_id' => $donation->id]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error checking transaction status', [
                'donation_id' => $donation->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    // Untuk pembayaran manual
    if ($donation->payment_type == 'manual' && $donation->manual_payment_method_id) {
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
    
    // Ambil data donasi lagi untuk mendapatkan status terbaru
    $donation = Donation::with(['campaign', 'manualPaymentMethod'])->find($id);
    
    return view('donatur.donasi.status', compact('donation', 'campaign', 'paymentDetail', 'isNewPayment'));
}

// Tambahkan method helper untuk format instruksi
private function formatPaymentInstructions($instructions)
{
    if (empty($instructions)) {
        return [];
    }
    
    $formatted = [];
    foreach ($instructions as $instruction) {
        $formatted[] = [
            'title' => $instruction['title'] ?? '',
            'steps' => $instruction['steps'] ?? []
        ];
    }
    
    return $formatted;
}

public function pollPendingTransactions()
{
    try {
        // Ambil semua donasi pending dalam waktu 48 jam terakhir
        $pendingDonations = Donation::where('status', 'pending')
        ->where('payment_type', 'payment_gateway')
        ->where('created_at', '>', now()->subHours(24))
        ->orderBy('created_at', 'desc')
        ->limit(50)
        ->get();
        
        // Log::info('Running payment polling', ['total_pending' => $pendingDonations->count()]);
        
        $counter = 0;
        
        foreach ($pendingDonations as $donation) {
            try {
                // Cek status di Tripay
                $apiUrl = rtrim($this->apiUrl, '/') . '/';
                $endpoint = 'transaction/detail';
                
                $reference = $donation->snap_token;
                $signature = hash_hmac('sha256', $this->merchantCode . $reference, $this->privateKey);
                
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey
                ])->get($apiUrl . $endpoint, [
                    'reference' => $reference,
                    'signature' => $signature
                ]);
                
                $responseData = $response->json();
                
                if (isset($responseData['success']) && $responseData['success'] === true && isset($responseData['data'])) {
                    $transaction = $responseData['data'];
                    
                    if ($transaction['status'] === 'PAID' && $donation->status !== 'sukses') {
                        // Gunakan transaction dan lock untuk mencegah race condition
                        DB::beginTransaction();
                        
                        // Lock donasi untuk mencegah race condition
                        $freshDonation = Donation::lockForUpdate()->find($donation->id);
                        
                        if ($freshDonation && $freshDonation->status !== 'sukses') {
                            $this->processSuccessfulPayment($freshDonation);
                            $counter++;
                            
                            // Log::info('Donation updated by polling', ['donation_id' => $donation->id]);
                        }
                        
                        DB::commit();
                    } else if (in_array($transaction['status'], ['EXPIRED', 'FAILED', 'REFUND']) && $donation->status !== 'gagal') {
                        $donation->status = 'gagal';
                        $donation->save();
                        // Log::info('Donation marked as failed by polling', ['donation_id' => $donation->id]);
                    }
                }
            } catch (\Exception $e) {
                if (isset($dbTransaction)) {
                    DB::rollBack();
                }
                
                Log::error('Error polling transaction', [
                    'donation_id' => $donation->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => "Successfully updated $counter donations"
        ]);
    } catch (\Exception $e) {
        Log::error('Error in poll pending transactions', ['error' => $e->getMessage()]);
        
        return response()->json([
            'success' => false,
            'message' => 'Error processing pending donations: ' . $e->getMessage()
        ], 500);
    }
}


private function createStatusToken($donationId)
{
    // Buat token unik
    $token = md5($donationId . time() . Str::random(10));
    
    // Simpan token di cache selama 30 menit
    Cache::put('donation_token_' . $donationId, $token, now()->addMinutes(30));
    
    return $token;
}

private function checkStatusToken($donationId, $token)
{
    $savedToken = Cache::get('donation_token_' . $donationId);
    
    if ($savedToken && $savedToken === $token) {
        // Token valid, hapus dari cache untuk mencegah penggunaan kembali
        Cache::forget('donation_token_' . $donationId);
        return true;
    }
    
    return false;
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

                $freshDonation = Donation::lockForUpdate()->find($donation->id);
                        
                if ($freshDonation && $freshDonation->status !== 'sukses') {
                    $this->processSuccessfulPayment($freshDonation);
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
                        'note' => $transaction['note'] ?? null,
                        'checkout_url' => $transaction['checkout_url'] ?? null
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
        
        // Facebook Conversion API - Track "Donate" event
        if ($adsense->meta_token && $adsense->meta_endpoint) {
            $userData = [
                'em' => hash('sha256', strtolower($donation->email)),
                'ph' => hash('sha256', preg_replace('/[^0-9]/', '', $donation->phone)),
                'client_user_agent' => request()->userAgent(),
                'client_ip_address' => request()->ip(),
            ];
            
            $data = [
                'data' => [
                    [
                        'event_name' => 'Donate',
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
            Log::info('Facebook Conversion API event sent', ['donation_id' => $donation->id]);
        }

        // Alternative: Menggunakan environment variables langsung
        if (env('FB_PIXEL_ID') && env('FB_ACCESS_TOKEN')) {
            $fbData = [
                'data' => [[
                    'event_name' => 'Donate',
                    'event_time' => time(),
                    'action_source' => 'website',
                    'event_source_url' => url()->current(),
                    'user_data' => [
                        'em' => hash('sha256', strtolower($donation->email)),
                        'ph' => hash('sha256', preg_replace('/[^0-9]/', '', $donation->phone)),
                        'client_user_agent' => request()->userAgent(),
                        'client_ip_address' => request()->ip(),
                    ],
                    'custom_data' => [
                        'currency' => 'IDR',
                        'value' => $donation->amount,
                        'content_name' => $donation->campaign->title,
                        'content_type' => 'donation',
                        'content_ids' => [$donation->campaign_id],
                    ],
                ]]
            ];

            Http::withToken(env('FB_ACCESS_TOKEN'))
                ->post("https://graph.facebook.com/v18.0/" . env('FB_PIXEL_ID') . "/events", $fbData);
            
            Log::info('Facebook Conversion API (direct) event sent', ['donation_id' => $donation->id]);
        }
        
        // TikTok Events API - Track "Donate" event  
        if ($adsense->tiktok_token && $adsense->tiktok_endpoint && $adsense->tiktok_pixel) {
            $data = [
                'pixel_code' => $adsense->tiktok_pixel,
                'event' => 'Donate',
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
        $query = Donation::with('campaign')->where('status','sukses');
        
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

        $totalAmount = $query->sum('amount');
        
        return DataTables::of($query->latest())
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
            ->with('totalAmount', $totalAmount)
            ->make(true);
    }
    
    return view('super_admin.donasi_kampanye.index', compact('campaigns'));
}

public function ceklis(Request $request)
{
    Carbon::setLocale('id');
     $campaigns = Campaign::orderBy('title')->get();
    if ($request->ajax()) {
        // Start with base query
       $query = Donation::with(['campaign', 'user']);
        
         // Apply payment method filter if provided
        if ($request->has('payment_type') && $request->payment_type) {
            $query->where('payment_type', $request->payment_type);
        }
        
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by contact agreement
        if ($request->is_contactable !== null ) {
            $query->where('is_contactable', $request->is_contactable);
        }
        
        
        // Apply campaign filter if provided
        if ($request->has('campaign_id') && $request->campaign_id) {
            $query->where('campaign_id', $request->campaign_id);
        }
        
        // IMPORTANT: Use the query builder version of DataTables, not the collection version
        return DataTables::of($query->latest())
            ->addIndexColumn()
             ->addColumn('campaign_title', function ($row) {
                return $row->campaign ? $row->campaign->title : 'N/A';
            })
            ->addColumn('method', function ($row) {
                if($row->payment_type == "manual"){
                    $payment = "Manual";
                }else{
                    $payment = "Payment Gateway";
                }
                return $payment . ' ('. $row->payment_method . ')';
            })
            ->addColumn('is_contactable', function($row){
                return $row->is_contactable ? 'Bersedia' : 'Tidak Bersedia';
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
                if ($row->status == 'pending' && $row->payment_type == 'manual') {
                        if ($row->payment_proof) {
                        $actionBtn .= '
                    <a href="'.asset('storage/'.$row->payment_proof).'" target="_blank" class="btn btn-info text-white btn-sm">
                        <i class="fas fa-file"></i>
                    </a>';
                                        } else {
                        $actionBtn .= '
                    <button onclick="noPaymentProofAlert()" class="btn btn-info text-white btn-sm">
                        <i class="fas fa-file"></i>
                    </button>';
                    }
                    $actionBtn .= '
                        <button onclick="updateStatus('.$row->id.', \'sukses\')" class="btn btn-primary btn-sm">
                            <i class="fas fa-check"></i>
                        </button>
                        <button onclick="updateStatus('.$row->id.', \'gagal\')" class="btn btn-warning text-white btn-sm">
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
    
    return view('super_admin.ceklis_donasi.index', compact('campaigns'));
}

public function exportCeklis(Request $request)
{
    $query = Donation::with(['campaign','user']);

    if ($request->has('payment_type') && $request->payment_type) {
        $query->where('payment_type', $request->payment_type);
    }
    if ($request->has('status') && $request->status) {
        $query->where('status', $request->status);
    }
    if ($request->has('campaign_id') && $request->campaign_id) {
        $query->where('campaign_id', $request->campaign_id);
    }
      if ($request->is_contactable !== null) {
            $query->where('is_contactable', $request->is_contactable);
    }
    if ($request->has('search') && $request->search) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('name','like',"%$search%")
              ->orWhere('email','like',"%$search%")
              ->orWhere('phone','like',"%$search%")
              ->orWhereHas('campaign', function($c) use ($search){
                  $c->where('title','like',"%$search%");
              });
        });
    }

    $donations = $query->latest()->get();

    $filename = 'ceklis_donasi_'.now()->format('Ymd_His').'.csv';

    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename='.$filename,
    ];

     $columns = ['Nama','Email','Phone','Bersedia Dihubungi','Kampanye','Total Donasi','Metode','Tanggal','Status'];

    $callback = function() use ($donations, $columns) {
        $handle = fopen('php://output', 'w');
        fputcsv($handle, $columns);
        foreach ($donations as $d) {
            $method = ($d->payment_type == 'manual' ? 'Manual' : 'Payment Gateway') .' ('. $d->payment_method .')';
            fputcsv($handle, [
                $d->name,
                $d->email,
                $d->phone,
                $d->is_contactable ? 'Bersedia' : 'Tidak Bersedia',
                optional($d->campaign)->title,
                $d->amount,
                $method,
                optional($d->created_at)->timezone('Asia/Jakarta')->format('d M Y'),
                $d->status,
            ]);
        }
        fclose($handle);
    };

    return response()->stream($callback, 200, $headers);
}

    public function updateStatus(Request $request)
    {
        $donation = Donation::find($request->id);
        
        if (!$donation) {
            return response()->json(['success' => false, 'message' => 'Donasi tidak ditemukan']);
        }

        if($request->status == 'sukses' && $donation->payment_type == 'manual'){

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
                
                if ($donation->referral_code) {
                    $fundraising = Fundraising::where('code_link', $donation->referral_code)->first();
                    
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

                try {
                    Mail::to($donation->email)->send(new DonationSuccessMail($donation));
                    // Log::info('Donation success email sent to donor: ' . $donation->email);
                } catch (\Exception $e) {
                    Log::error('Failed to send donation success email to donor: ' . $e->getMessage());
                }

                try {
                    $campaign = Campaign::with('admin')->find($donation->campaign_id);
                    if ($campaign && $campaign->admin && $campaign->admin->email) {
                        Mail::to($campaign->admin->email)->send(new CampaignDonationMail($donation));
                        // Log::info('Campaign donation email sent to admin: ' . $campaign->admin->email);
                    } else {
                        Log::warning('Admin email not found for campaign ID: ' . $donation->campaign_id);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to send campaign donation email to admin: ' . $e->getMessage());
                }

                $this->clearDonationSessions();
        }

        $donation->updated_at = now();
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

    private function clearDonationSessions()
{
    // Hapus session fundraising
    session()->forget('referral_code');
    
    // Hapus semua session UTM
    session()->forget('utm_source');
    session()->forget('utm_medium');
    session()->forget('utm_campaign');
    
    return true;
}


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


