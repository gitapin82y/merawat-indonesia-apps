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

use App\Models\Adsense;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class MootaWebhookController extends Controller
{
    protected MootaService $mootaService;

    public function __construct(MootaService $mootaService)
    {
        $this->mootaService = $mootaService;
    }

    public function handle(Request $request)
    {
        $rawBody   = $request->getContent();
        $signature = $request->header('Signature', '');

        Log::info('Moota Webhook Received', [
            'user_agent'       => $request->header('User-Agent'),
            'x_moota_user'     => $request->header('X-MOOTA-USER'),
            'x_moota_webhook'  => $request->header('X-MOOTA-WEBHOOK'),
            'signature_exists' => !empty($signature),
            'body_length'      => strlen($rawBody),
        ]);

        if (!$this->verifySignature($rawBody, $signature)) {
            Log::warning('Moota Webhook: Signature tidak valid.');
            return response()->json(['message' => 'Invalid signature'], 200);
        }

        $mutations = json_decode($rawBody, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($mutations)) {
            Log::error('Moota Webhook: Body bukan JSON array valid.');
            return response()->json(['message' => 'Invalid JSON'], 200);
        }

        if (isset($mutations['mutation_id'])) {
            $mutations = [$mutations];
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

    protected function processMutation(array $mutation): string
    {
        $mutationId  = $mutation['mutation_id'] ?? $mutation['token'] ?? null;
        $type        = strtoupper($mutation['type'] ?? '');
        $amount      = isset($mutation['amount']) ? (int) $mutation['amount'] : 0;
        $description = $mutation['description'] ?? '';

        if (!$mutationId || !$amount || !$type) {
            Log::warning('Moota: Field wajib tidak lengkap.', compact('mutationId', 'type', 'amount'));
            return 'skipped';
        }

        if ($type !== 'CR') {
            Log::info('Moota: Bukan CR, skip.', compact('type', 'mutationId', 'amount'));
            return 'skipped';
        }

        // Idempotency check
        if (Donation::where('snap_token', 'moota_' . $mutationId)->exists()) {
            Log::info('Moota: Mutasi sudah diproses (idempotent).', compact('mutationId'));
            return 'skipped';
        }

        Log::info('Moota: Proses mutasi CR', [
            'mutation_id' => $mutationId,
            'amount'      => $amount,
            'description' => $description,
        ]);

        // Cari donasi pending yang cocok berdasarkan amount
        // amount di DB sudah = total (amount + unique_code) sejak create
        $donation = Donation::where('status', 'pending')
            ->where('payment_method', 'like', 'moota%')
            ->where('amount', $amount)
            ->orderBy('created_at', 'asc')
            ->first();

        if (!$donation) {
            Log::info('Moota: Tidak ada donasi pending yang cocok.', [
                'incoming_amount' => $amount,
                'mutation_id'     => $mutationId,
            ]);
            return 'skipped';
        }

        // ── DB Transaction dengan commit yang benar ───────────────────
        DB::beginTransaction();
        try {
            $freshDonation = Donation::lockForUpdate()->find($donation->id);

            if (!$freshDonation || $freshDonation->status === 'sukses') {
                DB::rollBack();
                Log::info('Moota: Donasi sudah sukses saat di-lock.', ['donation_id' => $donation->id]);
                return 'skipped';
            }

            // Update status donasi
            $freshDonation->status     = 'sukses';
            $freshDonation->snap_token = 'moota_' . $mutationId;
            $freshDonation->updated_at = now();
            $freshDonation->save();

            // Update statistik kampanye
            $campaign = Campaign::find($freshDonation->campaign_id);
            if ($campaign) {
                $campaign->increment('jumlah_donasi',    $freshDonation->amount);
                $campaign->increment('current_donation', $freshDonation->amount);
                $campaign->increment('total_donatur',    1);
            }

            // Update donation source
            if ($freshDonation->donation_source_id) {
                $source = DonationSource::find($freshDonation->donation_source_id);
                if ($source) {
                    $source->increment('total_donations', 1);
                    $source->increment('total_amount',    $freshDonation->amount);
                }
            }

            // Komisi fundraising — gunakan amount dari freshDonation langsung
            // FIX: method hanya butuh 1 parameter (Donation), tidak perlu $totalAmount
            if ($freshDonation->referral_code) {
                $this->processFundraisingCommission($freshDonation);
            }

            // FIX UTAMA: DB::commit() WAJIB dipanggil agar perubahan tersimpan!
            DB::commit();

            Log::info('Moota: Donasi sukses ✓', [
                'donation_id' => $freshDonation->id,
                'amount'      => $freshDonation->amount,
                'mutation_id' => $mutationId,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Moota: Gagal proses mutasi.', [
                'mutation_id' => $mutationId,
                'donation_id' => $donation->id,
                'error'       => $e->getMessage(),
                'trace'       => $e->getTraceAsString(),
            ]);
            return 'skipped';
        }

        // Email di luar DB transaction
        $this->sendNotifications($freshDonation);
        $this->trackServerSideConversion($freshDonation);

        return 'processed';
    }

    protected function trackServerSideConversion(Donation $donation): void
{
    // Guard: cegah duplikasi tracking
    $cacheKey = 'conversion_tracked_' . $donation->id;
    if (Cache::has($cacheKey)) {
        Log::info('Moota: Conversion sudah di-track, skip.', ['donation_id' => $donation->id]);
        return;
    }
    Cache::put($cacheKey, true, now()->addDays(7));

    try {
        $adsense = Adsense::first();
        if (!$adsense) return;

        // ── Facebook Conversion API ───────────────────────────────
        if ($adsense->meta_token && $adsense->facebook_pixel) {
            $userData = [
                'em'                => hash('sha256', strtolower(trim($donation->email ?? ''))),
                'client_ip_address' => request()->ip(),
                'client_user_agent' => request()->userAgent(),
            ];
            if ($donation->phone) {
                $userData['ph'] = hash('sha256', preg_replace('/[^0-9]/', '', $donation->phone));
            }

            $response = Http::withToken($adsense->meta_token)
                ->post("https://graph.facebook.com/v20.0/{$adsense->facebook_pixel}/events", [
                    'data' => [[
                        'event_name'       => 'Donate',
                        'event_time'       => time(),
                        'event_id'         => 'moota_' . $donation->id, // dedup key
                        'user_data'        => $userData,
                        'custom_data'      => [
                            'currency'         => 'IDR',
                            'value'            => (float) $donation->amount,
                            'content_name'     => $donation->campaign->title ?? 'Donation',
                            'content_type'     => 'donation',
                            'content_ids'      => [(string) $donation->campaign_id],
                        ],
                        'event_source_url' => url('/donations/' . $donation->id . '/status'),
                        'action_source'    => 'website',
                    ]],
                ]);

            Log::info('Moota: Facebook CAPI sent', [
                'donation_id' => $donation->id,
                'status'      => $response->status(),
            ]);
        }

        // ── TikTok Events API ─────────────────────────────────────
        if ($adsense->tiktok_token && $adsense->tiktok_pixel && $adsense->tiktok_endpoint) {
            $tiktokUser = array_filter([
                'email'        => hash('sha256', strtolower(trim($donation->email ?? ''))),
                'phone_number' => $donation->phone
                    ? hash('sha256', preg_replace('/[^0-9]/', '', $donation->phone))
                    : null,
            ]);

            $response = Http::withHeaders([
                'Access-Token' => $adsense->tiktok_token,
                'Content-Type' => 'application/json',
            ])->post($adsense->tiktok_endpoint, [
                'pixel_code' => $adsense->tiktok_pixel,
                'event'      => 'CompletePayment',
                'event_id'   => 'moota_' . $donation->id, // dedup dengan browser pixel
                'timestamp'  => now()->toIso8601String(),
                'context'    => [
                    'user'       => $tiktokUser,
                    'page'       => ['url' => url('/donations/' . $donation->id . '/status')],
                    'ip'         => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ],
                'properties' => [
                    'currency'     => 'IDR',
                    'value'        => (float) $donation->amount,
                    'content_id'   => (string) ($donation->campaign_id ?? $donation->id),
                    'content_type' => 'product',
                    'content_name' => $donation->campaign->title ?? 'Donation',
                    'quantity'     => 1,
                ],
            ]);

            Log::info('Moota: TikTok Events API sent', [
                'donation_id' => $donation->id,
                'status'      => $response->status(),
                'response'    => $response->json(),
            ]);
        }

    } catch (\Exception $e) {
        Log::error('Moota: Gagal track server-side conversion', [
            'donation_id' => $donation->id,
            'error'       => $e->getMessage(),
        ]);
    }
}

    /**
     * FIX: Hanya 1 parameter — gunakan $donation->amount langsung
     * (amount sudah = total sejak create, tidak perlu $totalAmount terpisah)
     */
    protected function processFundraisingCommission(Donation $donation): void
    {
        try {
            $fundraising = Fundraising::where('code_link', $donation->referral_code)->first();
            if (!$fundraising) return;

            $commissionSetting = Commission::first();
            $commissionPercent = $commissionSetting->amount ?? 0;
            $commission        = ($donation->amount * $commissionPercent) / 100;

            $fundraising->total_donatur += 1;
            $fundraising->jumlah_donasi += $donation->amount;
            $fundraising->commission    += $commission;

            $donations   = json_decode($fundraising->donations, true) ?: [];
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

        } catch (\Exception $e) {
            Log::error('Moota: Gagal proses komisi fundraising: ' . $e->getMessage());
        }
    }

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