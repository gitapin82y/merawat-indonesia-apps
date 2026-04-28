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
use App\Services\EspayService;
use Illuminate\Support\Facades\Mail;
use App\Mail\DonationSuccessMail;
use App\Mail\CampaignDonationMail;
use App\Models\EspayPaymentMethod;
use App\Models\MootaBank;

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
      protected $notificationService;
    protected $espayService; // NEW: Espay Service

    public function __construct(NotificationService $notificationService, EspayService $espayService)
    {
        $this->notificationService = $notificationService;
        $this->espayService = $espayService; // NEW: Inject Espay Service
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
                    Mail::to($donation->email)->queue(new DonationSuccessMail($donation));
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

    protected function getMootaBanks(): array
{
    try {
        return MootaBank::active()
            ->orderBy('bank_type')
            ->get()
            ->map(function ($bank) {
                return [
                    'bank_id'        => $bank->bank_id,
                    'bank_type'      => $bank->bank_type,
                    'account_number' => $bank->account_number,
                    'account_name'   => $bank->account_name,
                    'label'          => $bank->bank_label,
                    'gateway'        => 'moota', // penanda sumber gateway
                ];
            })
            ->toArray();
    } catch (\Exception $e) {
        Log::error('Error getting Moota banks: ' . $e->getMessage());
        return [];
    }
}

 public function showDonationForm($slug)
    {
        $campaign = Campaign::where('slug',$slug)->first();
            $channels    = $this->getPaymentChannels();   // Espay - tidak berubah
    $mootaBanks  = $this->getMootaBanks();        // Moota - BARU
    $manualMethods = ManualPaymentMethod::where('is_active', true)->get();
 
    return view('donatur.donasi.index', compact('campaign', 'channels', 'mootaBanks', 'manualMethods'));
    }

   public function selectPaymentMethod($id)
    {
          $donation    = Donation::with('campaign')->findOrFail($id);
    $campaign    = $donation->campaign;
    $channels    = $this->getPaymentChannels();   // Espay - tidak berubah
    $mootaBanks  = $this->getMootaBanks();        // Moota - BARU
    $manualMethods = ManualPaymentMethod::where('is_active', true)->get();
 
    return view('donatur.donasi.payment-method', compact('donation', 'campaign', 'channels', 'mootaBanks', 'manualMethods'));
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
    
           // CHANGED: Buat transaksi di Espay (bukan Tripay)
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
        Log::info('processDonation dipanggil', [
            'payment_type'     => $request->payment_type,
            'payment_method'   => $request->selected_payment_method,
            'selected_gateway' => $request->selected_gateway,
            'moota_bank_id'    => $request->selected_moota_bank_id,
            'amount'           => $request->amount,
        ]);
 
        $rules = [
            'campaign_id'             => 'required|exists:campaigns,id',
            'amount'                  => 'required|numeric|min:10000',
            'name'                    => 'required|string|max:255',
            'phone'                   => 'required|string',
            'email'                   => 'required',
            'is_anonymous'            => 'nullable',
            'doa'                     => 'nullable|string',
            'utm_source'              => 'nullable|string',
            'utm_medium'              => 'nullable|string',
            'utm_campaign'            => 'nullable|string',
            'contact_agree'           => 'nullable',
            'payment_type'            => 'required|string|in:payment_gateway,manual',
            'selected_payment_method' => 'required|string',
        ];
 
        if ($request->payment_type === 'manual') {
            $rules['selected_payment_method'] = 'required|exists:manual_payment_methods,id';
        }
 
        $request->validate($rules);
 
        $campaign = Campaign::findOrFail($request->campaign_id);
 
        $utmSource    = $request->utm_source   ?? session('utm_source');
        $utmMedium    = $request->utm_medium   ?? session('utm_medium');
        $utmCampaign  = $request->utm_campaign ?? session('utm_campaign');
        $referralCode = session('referral_code');
 
        $sourceType = 'direct';
        if ($utmSource) {
            if (str_contains($utmSource, 'google'))                                                                     $sourceType = 'google_ads';
            elseif (str_contains($utmSource, 'facebook') || str_contains($utmSource, 'fb') || str_contains($utmSource, 'meta')) $sourceType = 'facebook';
            elseif (str_contains($utmSource, 'tiktok'))                                                                 $sourceType = 'tiktok';
        }
 
        $donationSource = DonationSource::firstOrCreate(
            ['source_type' => $sourceType, 'utm_source' => $utmSource, 'utm_medium' => $utmMedium, 'utm_campaign' => $utmCampaign],
            ['campaign_name' => $utmCampaign]
        );
 
        // ── Generate unique code ──────────────────────────────────────
        $uniqueCode = rand(100, 999);
 
        // ── Tentukan gateway sebelum create ──────────────────────────
        $selectedGateway = $request->input('selected_gateway', 'espay');
        $isEspay  = ($request->payment_type === 'payment_gateway' && $selectedGateway === 'espay');
        $isMoota  = ($request->payment_type === 'payment_gateway' && $selectedGateway === 'moota');
        $isManual = ($request->payment_type === 'manual');
 
        // ── Hitung amount final ───────────────────────────────────────
        // ESPAY : amount bersih, unique_code = 0 (Espay tidak pakai kode unik)
        // MOOTA : amount = amount + unique_code, unique_code = 0
        // MANUAL: amount = amount + unique_code, unique_code = 0
        $amountBersih = (int) $request->amount;
        if ($isEspay) {
            $finalAmount     = $amountBersih;
            $finalUniqueCode = 0;            // Espay tidak pakai kode unik
        } else {
            // Moota & Manual: amount langsung total uang yang ditransfer
            $finalAmount     = $amountBersih + $uniqueCode;
            $finalUniqueCode = 0;            // disimpan 0 karena sudah masuk ke amount
        }
 
        // ── Buat record donasi ────────────────────────────────────────
        $donation = Donation::create([
            'campaign_id'        => $request->campaign_id,
            'user_id'            => auth()->id(),
            'name'               => $request->name,
            'phone'              => $request->phone,
            'email'              => $request->email,
            'doa'                => $request->doa,
            'is_anonymous'       => $request->has('is_anonymous'),
            'is_contactable'     => $request->has('contact_agree'),
            'amount'             => $finalAmount,       // total (sudah include kode unik untuk moota/manual)
            'unique_code'        => $finalUniqueCode,   // 0 untuk moota/manual, 0 untuk espay
            'payment_type'       => $request->payment_type,
            'payment_method'     => null,
            'status'             => 'pending',
            'snap_token'         => Str::random(32),
            'donation_source_id' => $donationSource->id,
            'utm_source'         => $utmSource,
            'referral_code'      => $referralCode,
            'utm_medium'         => $utmMedium,
            'utm_campaign'       => $utmCampaign,
        ]);
 
        // ── MOOTA ─────────────────────────────────────────────────────
        if ($isMoota) {
            $mootaBankId = $request->input('selected_moota_bank_id', '');
            $donation->payment_method = 'moota:' . $mootaBankId;
            $donation->save();
 
            Log::info('Moota donation created', [
                'donation_id'    => $donation->id,
                'payment_method' => $donation->payment_method,
                'amount'         => $donation->amount,    // sudah 50.819
                'unique_code'    => $donation->unique_code, // 0
                'kode_unik_raw'  => $uniqueCode,          // 819 (hanya untuk log)
            ]);
 
            $statusToken = $this->createStatusToken($donation->id);
            return redirect()->route('donations.status', [
                'id'           => $donation->id,
                'status_token' => $statusToken,
            ]);
        }
 
        // ── ESPAY ─────────────────────────────────────────────────────
        if ($isEspay) {
            $donation->payment_method = $request->selected_payment_method;
             $preToken = 'DON-' . $donation->id . '-' . time();
    $donation->snap_token = $preToken;
            $donation->save();
 
            $transaction = $this->createTransaction($donation, $campaign);
 
            if (isset($transaction['success']) && $transaction['success'] && isset($transaction['data'])) {
                $donation->snap_token = $transaction['data']['reference'];
                $donation->save();
 
                $statusToken = $this->createStatusToken($donation->id);
                return redirect()->route('donations.status', [
                    'id'           => $donation->id,
                    'status_token' => $statusToken,
                ]);
            }
 
            return redirect()->back()->with('error', 'Gagal membuat transaksi pembayaran: ' . ($transaction['message'] ?? 'Terjadi kesalahan sistem'));
        }
           // ── MANUAL ────────────────────────────────────────────────────
        $manualMethod = ManualPaymentMethod::find($request->selected_payment_method);
        $donation->payment_method = $manualMethod ? $manualMethod->name : 'manual';
        if (Schema::hasColumn('donations', 'manual_payment_method_id')) {
            $donation->manual_payment_method_id = $request->selected_payment_method;
        }
        $donation->save();
 
        return redirect()->route('donations.status', ['id' => $donation->id]);
    }

     public function checkStatusById($id)
    {
        try {
            $donation = Donation::find($id);
 
            if (!$donation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Donasi tidak ditemukan'
                ], 404);
            }
 
            $status = 'PENDING';
            if ($donation->status === 'sukses') $status = 'PAID';
            if ($donation->status === 'gagal')  $status = 'EXPIRED';
 
            return response()->json([
                'success' => true,
                'data'    => [
                    'status'  => $status,
                    'amount'  => $donation->amount,
                ]
            ]);
 
        } catch (\Exception $e) {
            Log::error('checkStatusById error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem'
            ]);
        }
    }

 
    // ============================================================
 
    public function status(Request $request, $id)
    {
        $donation    = Donation::with(['campaign', 'manualPaymentMethod'])->findOrFail($id);
        $campaign    = $donation->campaign;
        $paymentDetail = null;
        $isNewPayment  = false;
 
        if ($request->has('status_token')) {
            $isNewPayment = $this->checkStatusToken($id, $request->status_token);
        }
 
        // ══════════════════════════════════════════════════════════════
        // CASE 1: MOOTA — Transfer Bank Otomatis (pending)
        // Cek dengan str_starts_with karena format: "moota:bank_id"
        // ══════════════════════════════════════════════════════════════
         if ($donation->payment_type == 'payment_gateway' &&
            str_starts_with($donation->payment_method ?? '', 'moota') &&
            $donation->status == 'pending')
        {
            $parts  = explode(':', $donation->payment_method, 2);
            $bankId = $parts[1] ?? null;
 
            $mootaBank = null;
            if ($bankId) {
                $mootaBank = MootaBank::where('bank_id', $bankId)->first();
            }
            if (!$mootaBank) {
                $mootaBank = MootaBank::active()->orderBy('bank_type')->first();
            }
 
            $paymentDetail = [
                'type'           => 'moota_transfer',
                'bank_name'      => $mootaBank ? $mootaBank->bank_label    : 'BCA',
                'account_number' => $mootaBank ? $mootaBank->account_number : '-',
                'account_name'   => $mootaBank ? $mootaBank->account_name   : '-',
                // amount sudah = total (50.819), tidak perlu + unique_code lagi
                'total_amount'   => $donation->amount,
                'unique_code'    => 0, // tidak relevan, sudah masuk ke amount
            ];
 
            return view('donatur.donasi.status', compact('donation', 'campaign', 'paymentDetail', 'isNewPayment'));
        }
 
        // ══════════════════════════════════════════════════════════════
        // CASE 2: ESPAY — Payment Gateway (pending)
        // ══════════════════════════════════════════════════════════════
        if ($donation->payment_type == 'payment_gateway' &&
            $donation->snap_token &&
            $donation->status == 'pending')
        {
            // Hanya cek status ke Espay jika BUKAN redirect baru
            if (!$isNewPayment) {
                try {
                    $statusCheck = $this->espayService->checkPaymentStatus($donation->snap_token);
 
                    if (isset($statusCheck['success']) && $statusCheck['success'] && isset($statusCheck['data'])) {
                        $statusCode = $statusCheck['data']['transactionStatusCode'] ?? '';
 
                        if ($statusCode === '00' && $donation->status !== 'sukses') {
                            DB::beginTransaction();
                            try {
                                $fresh = Donation::lockForUpdate()->find($donation->id);
                                if ($fresh && $fresh->status !== 'sukses') {
                                    $this->processSuccessfulPayment($fresh);
                                }
                                DB::commit();
                                $donation = Donation::with(['campaign', 'manualPaymentMethod'])->find($id);
                            } catch (\Exception $e) {
                                DB::rollBack();
                                Log::error('Error processing Espay payment on status page', [
                                    'donation_id' => $donation->id,
                                    'error'       => $e->getMessage(),
                                ]);
                            }
                        } elseif (in_array($statusCode, ['02', '03']) && $donation->status !== 'gagal') {
                            $donation->status = 'gagal';
                            $donation->save();
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Error checking Espay status on status page', [
                        'donation_id' => $donation->id,
                        'error'       => $e->getMessage(),
                    ]);
                }
            }
 
            $paymentDetail = [
                'payment_method' => $donation->payment_method,
                'payment_amount' => $donation->amount,
                'checkout_url'   => $donation->checkout_url ?? null,
                    'qr_image'       => $donation->qr_image ?? null,    // TAMBAH
    'qr_content'     => $donation->qr_content ?? null,  // TAMBAH
                'status'         => 'PENDING',
            ];
        }
 
        // ══════════════════════════════════════════════════════════════
        // CASE 3: PEMBAYARAN MANUAL
        // ══════════════════════════════════════════════════════════════
        if ($donation->payment_type == 'manual' && $donation->manual_payment_method_id) {
            $manualMethod = $donation->manualPaymentMethod;
 
            if ($manualMethod) {
                $paymentDetail = [
                    'payment_method'        => 'Manual - ' . $manualMethod->name,
                    'manual_account_name'   => $manualMethod->account_name,
                    'manual_account_number' => $manualMethod->account_number,
                    'manual_instructions'   => $manualMethod->instructions,
                    'payment_proof'         => $donation->payment_proof
                        ? asset('storage/' . $donation->payment_proof)
                        : null,
                ];
            }
        }
 
        // Reload untuk status terbaru sebelum render
        $donation = Donation::with(['campaign', 'manualPaymentMethod'])->find($id);
 
        return view('donatur.donasi.status', compact('donation', 'campaign', 'paymentDetail', 'isNewPayment'));
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

        $user = User::where('email', 'merawatindonesia2@gmail.com')->first();
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

  /**
     * CHANGED: Get payment channels from Espay (bukan Tripay)
     */
    protected function getPaymentChannels()
    {
        try {
            // Get active payment methods from database
            $activeMethods = EspayPaymentMethod::where('is_active', true)
                ->get()
                ->groupBy('category');
            
            // Format untuk view (mirip dengan format Tripay)
            $formattedChannels = [];
            
            foreach ($activeMethods as $category => $methods) {
                foreach ($methods as $method) {
                    $formattedChannels[] = [
                        'code' => $method->code,
                        'name' => $method->name,
                        'category' => $category,
                        'pay_method' => $method->pay_method,
                        'pay_option' => $method->pay_option,
                        'fee_amount' => $method->fee_amount,
                        'fee_type' => $method->fee_type,
                        'icon_url' => $method->icon_url,
                    ];
                }
            }
            
            return $formattedChannels;
        } catch (\Exception $e) {
            Log::error('Error getting payment channels: ' . $e->getMessage());
            return [];
        }
    }

protected function createTransaction($donation, $campaign)
{
      Log::info('createTransaction dipanggil', [
        'donation_id'    => $donation->id,
        'payment_method' => $donation->payment_method,
    ]);
    try {
        $paymentMethod = EspayPaymentMethod::where('code', $donation->payment_method)->first();

        if (!$paymentMethod) {
            return ['success' => false, 'message' => 'Payment method not found'];
        }


$isQris = $paymentMethod->category === 'qris' ||
          str_contains(strtoupper($paymentMethod->pay_option ?? ''), 'QR');

$result = $isQris
    ? $this->espayService->createQrisPayment($donation, $campaign, $paymentMethod)
    : $this->espayService->createPaymentHostToHost($donation, $campaign, $paymentMethod);

if (isset($result['success']) && $result['success']) {
    $donation->snap_token   = $result['data']['reference'];
    $donation->checkout_url = $result['data']['checkout_url'] ?? null;
    $donation->qr_image     = $result['data']['qr_image'] ?? null;   // TAMBAH
    $donation->qr_content   = $result['data']['qr_content'] ?? null; // TAMBAH
    $donation->save();
}

        return $result;

    } catch (\Exception $e) {
        Log::error('Error creating Espay transaction: ' . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
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
        Mail::to($donation->email)->queue(new DonationSuccessMail($donation));
        // Log::info('Donation success email sent to donor: ' . $donation->email);
    } catch (\Exception $e) {
        Log::error('Failed to send donation success email to donor: ' . $e->getMessage());
    }

    try {
        $campaign = Campaign::with('admin')->find($donation->campaign_id);
        if ($campaign && $campaign->admin && $campaign->admin->email) {
            Mail::to($campaign->admin->email)->queue(new CampaignDonationMail($donation));
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
    } catch (\Exception $e) {
        Log::error('Error when sending notifications or tracking conversion', [
            'donation_id' => $donation->id,
            'error' => $e->getMessage()
        ]);
    }
    
    return true;
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

/**
     * NEW: Map Espay transaction status code to readable status
     */
    private function mapEspayStatus($statusCode)
    {
        $statusMap = [
            '00' => 'PAID',       // Success
            '01' => 'PENDING',    // Pending
            '02' => 'FAILED',     // Failed
            '03' => 'EXPIRED',    // Expired
        ];
        
        return $statusMap[$statusCode] ?? 'PENDING';
    }

 public function pollPendingTransactions()
    {
        try {
            // EXCLUDE: donasi Moota (payment_method LIKE 'moota%')
            // karena Moota pakai webhook push, bukan polling
            $pendingDonations = Donation::where('status', 'pending')
                ->where('payment_type', 'payment_gateway')
                ->where('payment_method', 'not like', 'moota%') // ← FIX
                ->where('created_at', '>', now()->subHours(24))
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();
 
            $counter = 0;
 
            foreach ($pendingDonations as $donation) {
                try {
                    $statusCheck = $this->espayService->checkPaymentStatus($donation->snap_token);
 
                    if (isset($statusCheck['success']) && $statusCheck['success'] === true && isset($statusCheck['data'])) {
                        $transaction = $statusCheck['data'];
 
                        if (isset($transaction['transactionStatusCode']) &&
                            $transaction['transactionStatusCode'] === '00' &&
                            $donation->status !== 'sukses') {
                            DB::beginTransaction();
                            $freshDonation = Donation::lockForUpdate()->find($donation->id);
                            if ($freshDonation && $freshDonation->status !== 'sukses') {
                                $this->processSuccessfulPayment($freshDonation);
                                $counter++;
                            }
                            DB::commit();
                        } elseif (isset($transaction['transactionStatusCode']) &&
                                  in_array($transaction['transactionStatusCode'], ['02', '03']) &&
                                  $donation->status !== 'gagal') {
                            $donation->status = 'gagal';
                            $donation->save();
                        }
                    }
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Error polling transaction', [
                        'donation_id' => $donation->id,
                        'error'       => $e->getMessage()
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
            $donation = Donation::where('snap_token', $reference)
                ->with('manualPaymentMethod')
                ->first();
 
            // Fallback: snap_token mungkin sudah berubah ke "moota_{mutation_id}"
            // tapi halaman masih pakai snap_token lama
            // Cari berdasarkan snap_token lama yang disimpan di... tidak bisa.
            // Solusinya: gunakan checkStatusById() untuk Moota (lihat blade fix)
 
            if (!$donation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Donasi tidak ditemukan'
                ], 404);
            }
 
            // ── MOOTA: cek dari DB ────────────────────────────────────
            if (str_starts_with($donation->payment_method ?? '', 'moota')) {
                $status = 'PENDING';
                if ($donation->status === 'sukses') $status = 'PAID';
                if ($donation->status === 'gagal')  $status = 'EXPIRED';
 
                return response()->json([
                    'success' => true,
                    'data'    => [
                        'status'         => $status,
                        'payment_method' => $donation->payment_method,
                        'amount'         => $donation->amount,
                    ]
                ]);
            }
 
            // ── MANUAL: cek dari DB ───────────────────────────────────
            if ($donation->payment_type === 'manual') {
                $status = 'PENDING';
                if ($donation->status === 'sukses') $status = 'PAID';
                if ($donation->status === 'gagal')  $status = 'EXPIRED';
 
                return response()->json([
                    'success' => true,
                    'data'    => [
                        'status'         => $status,
                        'payment_method' => 'Manual - ' . ($donation->manualPaymentMethod?->name ?? 'Transfer'),
                        'amount'         => $donation->amount,
                        'updated_at'     => $donation->updated_at->format('Y-m-d H:i:s'),
                    ]
                ]);
            }
 
            // ── ESPAY: hit API ────────────────────────────────────────
            $statusCheck = $this->espayService->checkPaymentStatus($reference);
 
            if (isset($statusCheck['success']) && $statusCheck['success'] === true && isset($statusCheck['data'])) {
                $transaction = $statusCheck['data'];
                $status = 'PENDING';
 
                if (isset($transaction['transactionStatusCode']) && $transaction['transactionStatusCode'] === '00') {
                    $status = 'PAID';
                    $freshDonation = Donation::lockForUpdate()->find($donation->id);
                    if ($freshDonation && $freshDonation->status !== 'sukses') {
                        DB::beginTransaction();
                        try {
                            $this->processSuccessfulPayment($freshDonation);
                            DB::commit();
                        } catch (\Exception $e) {
                            DB::rollBack();
                            Log::error('checkStatus: error processSuccessfulPayment', ['error' => $e->getMessage()]);
                        }
                    }
                } elseif (isset($transaction['transactionStatusCode']) &&
                          in_array($transaction['transactionStatusCode'], ['02', '03'])) {
                    $status = 'EXPIRED';
                    if ($donation->status !== 'gagal') {
                        $donation->status = 'gagal';
                        $donation->save();
                    }
                }
 
                return response()->json([
                    'success' => true,
                    'data'    => [
                        'status'         => $status,
                        'reference'      => $reference,
                        'payment_method' => $donation->payment_method,
                        'amount'         => $donation->amount,
                    ]
                ]);
            }
 
            return response()->json([
                'success' => false,
                'message' => $statusCheck['message'] ?? 'Failed to check payment status'
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
        if (!$adsense) {
            Log::warning('No adsense configuration found');
            return;
        }
        
        // Facebook Conversion API - Track "Donate" event
        if ($adsense->meta_token && $adsense->facebook_pixel) {
            
            // Prepare user data dengan validasi
            $userData = [
                'em' => hash('sha256', strtolower(trim($donation->email))),
                'client_user_agent' => request()->userAgent(),
                'client_ip_address' => request()->ip(),
            ];
            
            // Tambahkan phone jika ada
            if ($donation->phone) {
                $userData['ph'] = hash('sha256', preg_replace('/[^0-9]/', '', $donation->phone));
            }
            
            $eventData = [
                'data' => [
                    [
                        'event_name' => 'Donate',
                        'event_time' => time(),
                        'user_data' => $userData,
                        'custom_data' => [
                            'currency' => 'IDR',
                            'value' => (float) $donation->amount,
                            'content_name' => $donation->campaign->title ?? 'Donation',
                            'content_type' => 'donation',
                            'content_ids' => [(string) $donation->campaign_id],
                            'content_category' => $donation->campaign->category->name ?? 'donation',
                        ],
                        'event_source_url' => url('/donations/' . $donation->id . '/status'),
                        'action_source' => 'website'
                    ]
                ],
            ];
            
            // Gunakan endpoint yang benar dengan pixel dari database
            $response = Http::withToken($adsense->meta_token)
                ->post("https://graph.facebook.com/v20.0/{$adsense->facebook_pixel}/events", $eventData);
            
            Log::info('Facebook Conversion API Response', [
                'donation_id' => $donation->id,
                'pixel_id' => $adsense->facebook_pixel,
                'status' => $response->status(),
                'response' => $response->json(),
                'event_data' => $eventData
            ]);
        }
        
        // TikTok Events API - Track "Donate" event  
        if ($adsense->tiktok_token && $adsense->tiktok_endpoint && $adsense->tiktok_pixel) {
            $tiktokData = [
                'pixel_code' => $adsense->tiktok_pixel,
                'event' => 'Donate',
                'timestamp' => time(),
                'properties' => [
                    'currency' => 'IDR',
                    'value' => (float) $donation->amount,
                    'content_id' => (string) $donation->id,
                    'content_type' => 'donation',
                    'content_name' => $donation->campaign->title ?? 'Donation',
                ],
                'context' => [
                    'user' => [
                        'email' => hash('sha256', strtolower(trim($donation->email))),
                    ],
                    'page' => [
                        'url' => url('/donations/' . $donation->id . '/status')
                    ],
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]
            ];
            
            // Tambahkan phone jika ada
            if ($donation->phone) {
                $tiktokData['context']['user']['phone'] = hash('sha256', preg_replace('/[^0-9]/', '', $donation->phone));
            }
            
            $response = Http::withHeaders([
                'Access-Token' => $adsense->tiktok_token,
                'Content-Type' => 'application/json'
            ])->post($adsense->tiktok_endpoint, $tiktokData);
            
            Log::info('TikTok Conversion API Response', [
                'donation_id' => $donation->id,
                'status' => $response->status(),
                'response' => $response->json()
            ]);
        }
        
    } catch (\Exception $e) {
        Log::error('Error tracking server-side conversion', [
            'donation_id' => $donation->id ?? null,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
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
            
            ->addColumn('is_contactable', function($row){
                return $row->is_contactable ? 'Bersedia' : 'Tidak Bersedia';
            })    
            ->addColumn('created_at', function($row) {
                return $row->created_at
                ? Carbon::parse($row->created_at)->timezone('Asia/Jakarta')->format('d M Y')
                : '-';
            })
           
->addColumn('amount', function($row) {
    // Semua kondisi: tampilkan amount langsung
    // Untuk moota & manual: sudah = total (50.819) sejak create
    // Untuk espay: amount bersih (50.000)
    return 'Rp ' . number_format($row->amount, 0, ',', '.');
})
 
// addColumn('method') — tetap sama seperti patch sebelumnya:
->addColumn('method', function($row) {
    $paymentMethod = $row->payment_method ?? '';
 
    if (str_starts_with($paymentMethod, 'moota')) {
        $parts  = explode(':', $paymentMethod, 2);
        $bankId = $parts[1] ?? null;
 
        $bankLabel = 'Moota';
        if ($bankId) {
            $mootaBank = \App\Models\MootaBank::where('bank_id', $bankId)
                ->select('bank_type', 'account_name')
                ->first();
            if ($mootaBank) {
                $bankLabel = strtoupper($mootaBank->bank_type);
            }
        }
        return 'Payment Gateway (Moota: ' . $bankLabel . ')';
    }
 
    if ($row->payment_type === 'manual') {
        return 'Manual (' . ($paymentMethod ?: 'Transfer') . ')';
    }
 
    return 'Payment Gateway (' . $paymentMethod . ')';
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

    // Tampilkan tombol file, approve, reject untuk semua donasi manual
    if ($row->payment_type == 'manual') {
        // Tombol file bukti pembayaran
        if ($row->payment_proof) {
                $proofUrl = asset('storage/'.$row->payment_proof);
                $actionBtn .= '
            <button onclick="lihatBukti(\''.addslashes($proofUrl).'\')" class="btn btn-info text-white btn-sm" title="Lihat Bukti">
                <i class="fas fa-file"></i>
            </button>';
        } else {
            $actionBtn .= '
        <button onclick="noPaymentProofAlert()" class="btn btn-info text-white btn-sm" title="Belum ada bukti">
            <i class="fas fa-file"></i>
        </button>';
        }

        // Tombol approve (ceklis) — selalu tampil kecuali sudah sukses
        if ($row->status != 'sukses') {
            $actionBtn .= '
        <button onclick="updateStatus('.$row->id.', \'sukses\')" class="btn btn-primary btn-sm" title="Approve">
            <i class="fas fa-check"></i>
        </button>';
        }

        // Tombol reject (silang) — selalu tampil kecuali sudah gagal
        if ($row->status != 'gagal') {
            $actionBtn .= '
        <button onclick="updateStatus('.$row->id.', \'gagal\')" class="btn btn-warning text-white btn-sm" title="Reject">
            <i class="fas fa-times"></i>
        </button>';
        }

        // Tombol unchecklist (kembalikan ke pending) — tampil jika sudah sukses atau gagal
        if (in_array($row->status, ['sukses', 'gagal'])) {
            $actionBtn .= '
        <button onclick="updateStatus('.$row->id.', \'pending\')" class="btn btn-secondary btn-sm" title="Kembalikan ke Pending">
            <i class="fas fa-undo"></i>
        </button>';
        }
    }elseif ($row->payment_type == 'payment_gateway' && str_starts_with($row->payment_method ?? '', 'moota')) {

        if ($row->status != 'sukses') {
            $actionBtn .= '
                <button onclick="updateStatus('.$row->id.', \'sukses\')" class="btn btn-primary btn-sm" title="Approve">
                    <i class="fas fa-check"></i>
                </button>';
        }
        if ($row->status != 'gagal') {
            $actionBtn .= '
                <button onclick="updateStatus('.$row->id.', \'gagal\')" class="btn btn-warning text-white btn-sm" title="Reject">
                    <i class="fas fa-times"></i>
                </button>';
        }
        if (in_array($row->status, ['sukses', 'gagal'])) {
            $actionBtn .= '
                <button onclick="updateStatus('.$row->id.', \'pending\')" class="btn btn-secondary btn-sm" title="Kembalikan ke Pending">
                    <i class="fas fa-undo"></i>
                </button>';
        }
    }

    // Tambahkan tombol WhatsApp & Hapus setelahnya
    $actionBtn .= '
        <a href="'.$whatsappUrl.'" target="_blank" class="btn btn-success btn-sm">
            <i class="fab fa-whatsapp"></i>
        </a>
        <button onclick="deleteDonasi('.$row->id.')" class="btn btn-danger btn-sm">
            <i class="fa-solid fa-trash"></i>
        </button>
    </div>';

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
 
        $oldStatus = $donation->status;
        $newStatus = $request->status;
 
        if ($oldStatus === $newStatus) {
            return response()->json(['success' => true, 'message' => 'Status tidak berubah']);
        }
 
        DB::beginTransaction();
        try {
 
            // ── REVERSE: sukses → pending/gagal ──────────────────────
            if ($oldStatus === 'sukses' && $donation->payment_type === 'manual') {
                // amount sudah = total (50.819) sejak create, langsung gunakan
                $campaign = $donation->campaign;
                $campaign->jumlah_donasi    = max(0, $campaign->jumlah_donasi    - $donation->amount);
                $campaign->current_donation = max(0, $campaign->current_donation - $donation->amount);
                $campaign->total_donatur    = max(0, $campaign->total_donatur    - 1);
                $campaign->save();
 
                if ($donation->donation_source_id) {
                    $source = DonationSource::find($donation->donation_source_id);
                    if ($source) {
                        $source->total_donations = max(0, $source->total_donations - 1);
                        $source->total_amount    = max(0, $source->total_amount    - $donation->amount);
                        $source->save();
                    }
                }
 
                if ($donation->referral_code) {
                    $fundraising = Fundraising::where('code_link', $donation->referral_code)->first();
                    if ($fundraising) {
                        $commissionSetting = Commission::first();
                        $commissionPercent = $commissionSetting->amount ?? 0;
                        $commission = ($donation->amount * $commissionPercent) / 100;
 
                        $fundraising->total_donatur = max(0, $fundraising->total_donatur - 1);
                        $fundraising->jumlah_donasi = max(0, $fundraising->jumlah_donasi - $donation->amount);
                        $fundraising->commission    = max(0, $fundraising->commission    - $commission);
 
                        $donations = json_decode($fundraising->donations, true) ?: [];
                        $donations = array_filter($donations, fn($d) => $d['donation_id'] != $donation->id);
                        $fundraising->donations = json_encode(array_values($donations));
                        $fundraising->save();
                    }
                }
            }
 
            // ── FORWARD: → sukses (admin approve manual) ─────────────
            if ($newStatus === 'sukses' && $donation->payment_type === 'manual') {
                // amount sudah = total (50.819) sejak create
                // TIDAK perlu update amount di sini — sudah benar!
                $this->trackServerSideConversion($donation);
 
                $campaign = $donation->campaign;
                $campaign->jumlah_donasi    += $donation->amount;
                $campaign->current_donation += $donation->amount;
                $campaign->total_donatur    += 1;
                $campaign->save();
 
                if ($donation->donation_source_id) {
                    $source = DonationSource::find($donation->donation_source_id);
                    if ($source) {
                        $source->total_donations += 1;
                        $source->total_amount    += $donation->amount;
                        $source->save();
                    }
                }
 
                if ($donation->referral_code) {
                    $fundraising = Fundraising::where('code_link', $donation->referral_code)->first();
                    if ($fundraising) {
                        $commissionSetting = Commission::first();
                        $commissionPercent = $commissionSetting->amount ?? 0;
                        $commission = ($donation->amount * $commissionPercent) / 100;
 
                        $fundraising->total_donatur += 1;
                        $fundraising->jumlah_donasi += $donation->amount;
                        $fundraising->commission    += $commission;
 
                        $donations = json_decode($fundraising->donations, true) ?: [];
                        $donations[] = [
                            'donation_id' => $donation->id,
                            'amount'      => $donation->amount,
                            'commission'  => $commission,
                            'user_name'   => $donation->user?->name,
                            'user_email'  => $donation->user?->email,
                            'created_at'  => now()->format('Y-m-d H:i:s'),
                        ];
                        $fundraising->donations = json_encode($donations);
                        $fundraising->save();
                    }
                }
 
                try {
                    Mail::to($donation->email)->queue(new DonationSuccessMail($donation));
                } catch (\Exception $e) {
                    Log::error('Failed to send donation success email: ' . $e->getMessage());
                }
 
                try {
                    $camp = Campaign::with('admin')->find($donation->campaign_id);
                    if ($camp && $camp->admin && $camp->admin->email) {
                        Mail::to($camp->admin->email)->queue(new CampaignDonationMail($donation));
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to send campaign donation email: ' . $e->getMessage());
                }
 
                $this->clearDonationSessions();
            }
 
            $donation->updated_at = now();
            $donation->status     = $newStatus;
            $donation->save();
 
            DB::commit();
 
            return response()->json(['success' => true, 'message' => 'Status donasi berhasil diperbarui']);
 
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating donation status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status: ' . $e->getMessage(),
            ], 500);
        }
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


