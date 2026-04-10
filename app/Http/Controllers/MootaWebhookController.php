<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\Campaign;
use App\Models\DonationSource;
use App\Models\Fundraising;
use App\Models\Commission;
use App\Services\MootaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\DonationSuccessMail;
use App\Mail\CampaignDonationMail;

class MootaWebhookController extends Controller
{
    protected MootaService $mootaService;

    public function __construct(MootaService $mootaService)
    {
        $this->mootaService = $mootaService;
    }

    /**
     * Handle webhook push dari Moota.
     *
     * Moota SELALU mengirim array of mutations:
     * [{ "amount": 50819, "type": "CR", "mutation_id": "xxx", "bank_id": "yyy", ... }]
     *
     * Signature: hash_hmac('sha256', $raw_json_body, $secret_token)
     */
    public function handle(Request $request)
    {
        $rawBody   = $request->getContent();
        $signature = $request->header('Signature', '');

        Log::info('Moota Webhook Received', [
            'user_agent'      => $request->header('User-Agent'),
            'x_moota_user'    => $request->header('X-MOOTA-USER'),
            'x_moota_webhook' => $request->header('X-MOOTA-WEBHOOK'),
            'signature_exists'=> !empty($signature),
            'body_length'     => strlen($rawBody),
        ]);

        // Verifikasi signature
        if (!$this->verifySignature($rawBody, $signature)) {
            Log::warning('Moota Webhook: Signature tidak valid.');
            // Tetap return 200 agar Moota tidak retry terus-menerus
            return response()->json(['message' => 'Invalid signature'], 200);
        }

        // Parse JSON body
        $mutations = json_decode($rawBody, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($mutations)) {
            Log::error('Moota Webhook: Body bukan JSON array valid.', [
                'body_preview' => substr($rawBody, 0, 300),
            ]);
            return response()->json(['message' => 'Invalid JSON'], 200);
        }

        // Jika Moota kirim single object (tidak sesuai docs), wrap ke array
        if (isset($mutations['mutation_id'])) {
            $mutations = [$mutations];
            Log::warning('Moota Webhook: Menerima single object, di-wrap ke array.');
        }

        $processed = 0;
        $skipped   = 0;

        foreach ($mutations as $mutation) {
            $result = $this->processMutation($mutation);
            $result === 'processed' ? $processed++ : $skipped++;
        }

        Log::info("Moota Webhook selesai: {$processed} diproses, {$skipped} dilewati.");

        return response()->json([
            'message'   => 'OK',
            'processed' => $processed,
            'skipped'   => $skipped,
        ], 200);
    }

    /**
     * Verifikasi signature Moota.
     * Algoritma: hash_hmac('sha256', $raw_json_body_string, $secret_token)
     */
    protected function verifySignature(string $rawBody, string $signature): bool
    {
        $secret = config('moota.webhook_secret', '');

        if (empty($secret)) {
            Log::warning('Moota: MOOTA_WEBHOOK_SECRET belum diset — verifikasi dilewati.');
            return true;
        }

        if (empty($signature)) {
            Log::warning('Moota: Header Signature kosong.');
            return false;
        }

        $expected = hash_hmac('sha256', $rawBody, $secret);
        $valid    = hash_equals($expected, $signature);

        if (!$valid) {
            Log::debug('Moota Signature mismatch', [
                'expected' => $expected,
                'received' => $signature,
            ]);
        }

        return $valid;
    }

    /**
     * Proses satu mutasi dari payload Moota.
     *
     * LOGIKA AMOUNT:
     * - Moota mengirim `amount` = jumlah FULL yang masuk ke rekening
     *   contoh: donatur input Rp 50.000, unique_code = 819
     *           donatur transfer Rp 50.819
     *           rekening terima Rp 50.819
     *           Moota push `amount: 50819`
     *
     * - Kita cocokkan: (donation.amount + donation.unique_code) == mutation.amount
     * - Saat sukses: update donation.amount = mutation.amount (50819)
     *                update donation.unique_code = 0
     *   → Donasi tercatat PENUH sesuai uang masuk, tidak ada yang hilang.
     *
     * Return: 'processed' | 'skipped'
     */
    protected function processMutation(array $mutation): string
    {
        // Validasi field wajib
        $mutationId  = $mutation['mutation_id'] ?? $mutation['token'] ?? null;
        $type        = strtoupper($mutation['type'] ?? '');
        $amount      = isset($mutation['amount']) ? (int) $mutation['amount'] : 0;
        $description = $mutation['description'] ?? '';

        if (!$mutationId || !$amount || !$type) {
            Log::warning('Moota: Field wajib tidak lengkap.', compact('mutationId', 'type', 'amount'));
            return 'skipped';
        }

        // Hanya proses CR (Credit / Uang Masuk)
        if ($type !== 'CR') {
            Log::info('Moota: Bukan CR, skip.', compact('type', 'mutationId', 'amount'));
            return 'skipped';
        }

        // Idempotency: cek apakah mutation_id ini sudah pernah diproses
        // snap_token diisi 'moota_{mutation_id}' setelah sukses
        if (Donation::where('snap_token', 'moota_' . $mutationId)->exists()) {
            Log::info('Moota: Mutasi sudah diproses sebelumnya (idempotent).', compact('mutationId'));
            return 'skipped';
        }

        Log::info('Moota: Proses mutasi CR', [
            'mutation_id' => $mutationId,
            'amount'      => $amount,
            'description' => $description,
        ]);

        // Cari donasi pending Moota yang cocok
        // Cocokkan: (amount + unique_code) == incoming amount dari Moota
        // payment_method format: "moota:bank_id" atau legacy "moota"
        $donation = Donation::where('status', 'pending')
            ->where('payment_method', 'like', 'moota%')
            ->where('amount', $amount)  // langsung cocokkan amount (sudah = total)
            ->orderBy('created_at', 'asc')
            ->first();


        if (!$donation) {
            Log::info('Moota: Tidak ada donasi pending yang cocok.', [
                'incoming_amount' => $amount,
                'mutation_id'     => $mutationId,
            ]);
            return 'skipped';
        }

        // ── Hitung total yang akan dicatat sebagai donasi ──
        // Pilihan: Amount + unique_code (sesuai uang masuk nyata)
        $totalAmount = $donation->amount + $donation->unique_code;
        // Verifikasi konsistensi (harusnya sama dengan $amount dari Moota)
        if ($totalAmount !== $amount) {
            Log::warning('Moota: Mismatch amount antara DB dan Moota.', [
                'db_total'    => $totalAmount,
                'moota_amount'=> $amount,
                'donation_id' => $donation->id,
            ]);
            // Gunakan amount dari Moota sebagai sumber kebenaran
            $totalAmount = $amount;
        }

        // Proses dengan DB transaction + row lock
        DB::beginTransaction();
        try {
            $freshDonation = Donation::lockForUpdate()->find($donation->id);

            // Double-check setelah lock
            if (!$freshDonation || $freshDonation->status === 'sukses') {
                DB::rollBack();
                Log::info('Moota: Donasi sudah sukses saat di-lock.', ['donation_id' => $donation->id]);
                return 'skipped';
            }

            // ──────────────────────────────────────────────────────────────
            // UPDATE DONASI:
            // amount  = total uang yang masuk (amount + unique_code)
            //           contoh: 50.000 + 819 = 50.819 → tercatat Rp 50.819
            // unique_code = 0 (sudah tidak relevan, sudah masuk ke amount)
            // snap_token  = 'moota_{mutation_id}' sebagai idempotency key
            // ──────────────────────────────────────────────────────────────
            $freshDonation->status      = 'sukses';
        $freshDonation->snap_token  = 'moota_' . $mutationId;
        $freshDonation->updated_at  = now();
        $freshDonation->save();
 
        // Dan campaign increment pakai $freshDonation->amount langsung:
        $campaign = Campaign::find($freshDonation->campaign_id);
        if ($campaign) {
            $campaign->increment('jumlah_donasi',    $freshDonation->amount);
            $campaign->increment('current_donation', $freshDonation->amount);
            $campaign->increment('total_donatur',    1);
        }
 
        // donation source juga pakai $freshDonation->amount:
        if ($freshDonation->donation_source_id) {
            $source = DonationSource::find($freshDonation->donation_source_id);
            if ($source) {
                $source->increment('total_donations', 1);
                $source->increment('total_amount',    $freshDonation->amount);
            }
        }
 
        // processFundraisingCommission juga cukup dengan $freshDonation:
        if ($freshDonation->referral_code) {
            $this->processFundraisingCommission($freshDonation);
        }
        

            Log::info('Moota: Donasi sukses ✓', [
                'donation_id'  => $freshDonation->id,
                'original_amt' => $donation->amount,
                'unique_code'  => $donation->unique_code,
                'total_recorded'=> $totalAmount,
                'mutation_id'  => $mutationId,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Moota: Gagal proses mutasi.', [
                'mutation_id' => $mutationId,
                'donation_id' => $donation->id,
                'error'       => $e->getMessage(),
            ]);
            return 'skipped';
        }

        // Email di luar DB transaction
        $this->sendNotifications($freshDonation);

        return 'processed';
    }

    /**
     * Proses komisi fundraising.
     * Menerima $totalAmount eksplisit agar konsisten
     * dengan amount yang sudah diupdate (amount + unique_code).
     */
    protected function processFundraisingCommission(Donation $donation, int $totalAmount): void
    {
        try {
            $fundraising = Fundraising::where('code_link', $donation->referral_code)->first();
            if (!$fundraising) return;

            $commissionSetting = Commission::first();
            $commissionPercent = $commissionSetting->amount ?? 0;
            $commission        = ($totalAmount * $commissionPercent) / 100;

            $fundraising->total_donatur += 1;
            $fundraising->jumlah_donasi += $totalAmount;
            $fundraising->commission    += $commission;

            $donations   = json_decode($fundraising->donations, true) ?: [];
            $donations[] = [
                'donation_id' => $donation->id,
                'amount'      => $totalAmount,
                'commission'  => $commission,
                'user_name'   => $donation->user?->name,
                'user_email'  => $donation->user?->email,
                'created_at'  => now()->format('Y-m-d H:i:s'),
            ];
            $fundraising->donations = json_encode($donations);
            $fundraising->save();

        } catch (\Exception $e) {
            Log::error('Moota: Gagal proses komisi fundraising: ' . $e->getMessage());
        }
    }

    /**
     * Kirim email sukses ke donatur dan admin kampanye.
     */
    protected function sendNotifications(Donation $donation): void
    {
        try {
            Mail::to($donation->email)->queue(new DonationSuccessMail($donation));
        } catch (\Exception $e) {
            Log::error('Moota: Gagal kirim email donatur: ' . $e->getMessage());
        }

        try {
            $campaign = Campaign::with('admin')->find($donation->campaign_id);
            if ($campaign && $campaign->admin && $campaign->admin->email) {
                Mail::to($campaign->admin->email)->queue(new CampaignDonationMail($donation));
            }
        } catch (\Exception $e) {
            Log::error('Moota: Gagal kirim email admin: ' . $e->getMessage());
        }
    }
}