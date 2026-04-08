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
     * Handle webhook push dari Moota
     *
     * Moota mengirim array mutasi dalam satu request:
     * [
     *   {
     *     "bank_id": "bpPkB9RxWB2",
     *     "account_number": "...",
     *     "date": "2026-04-08 10:00:00",
     *     "description": "TRSF dari Budi 083...",
     *     "amount": "150123",        <- amount + unique_code
     *     "type": "CR",              <- CR = kredit/masuk, DB = debit/keluar
     *     "balance": "...",
     *     "mutation_id": "abcdef",
     *     "note": "",
     *     "created_at": "..."
     *   },
     *   ...
     * ]
     */
    public function handle(Request $request)
    {
        Log::info('Moota Webhook Received', [
            'body'    => $request->all(),
            'headers' => $request->headers->all(),
        ]);

        // Verifikasi signature jika sudah dikonfigurasi
        $signature = $request->header('Signature') ?? $request->header('X-Signature') ?? '';
        $payload   = $request->getContent();

        if (!$this->mootaService->verifyWebhookSignature($payload, $signature)) {
            Log::warning('Moota Webhook: Invalid signature');
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        // Moota bisa kirim single object atau array
        $mutations = $request->all();
        if (isset($mutations['bank_id'])) {
            // Single mutation object
            $mutations = [$mutations];
        }

        $processed = 0;
        $skipped   = 0;

        foreach ($mutations as $mutation) {
            $result = $this->processMutation($mutation);
            if ($result === 'processed') $processed++;
            else $skipped++;
        }

        Log::info("Moota Webhook: {$processed} processed, {$skipped} skipped.");

        return response()->json([
            'message'   => 'OK',
            'processed' => $processed,
            'skipped'   => $skipped,
        ], 200);
    }

    /**
     * Proses satu mutasi dari Moota
     */
    protected function processMutation(array $mutation): string
    {
        // Hanya proses uang MASUK (CR = Credit)
        $type = strtoupper($mutation['type'] ?? '');
        if ($type !== 'CR') {
            Log::info('Moota: Mutasi bukan kredit, skip.', ['type' => $type, 'mutation_id' => $mutation['mutation_id'] ?? '']);
            return 'skipped';
        }

        $incomingAmount = (int) $mutation['amount'];
        $mutationId     = $mutation['mutation_id'] ?? $mutation['token'] ?? null;
        $bankId         = $mutation['bank_id'] ?? null;
        $description    = $mutation['description'] ?? '';

        if (!$incomingAmount || !$mutationId) {
            Log::warning('Moota: Data mutasi tidak lengkap', $mutation);
            return 'skipped';
        }

        // Cari donasi pending yang amount+unique_code cocok dengan incomingAmount
        // Strategi: cari semua donasi pending dengan payment_method = 'moota', 
        // lalu cocokkan (amount + unique_code) == incomingAmount
        $donation = Donation::where('status', 'pending')
            ->where('payment_method', 'moota')
            ->whereRaw('(amount + unique_code) = ?', [$incomingAmount])
            ->orderBy('created_at', 'asc') // FIFO jika ada kesamaan jumlah
            ->first();

        if (!$donation) {
            Log::info('Moota: Tidak ada donasi pending yang cocok', [
                'incoming_amount' => $incomingAmount,
                'mutation_id'     => $mutationId,
                'description'     => $description,
            ]);
            return 'skipped';
        }

        // Cek apakah mutasi ini sudah pernah diproses (idempotency)
        // Kita simpan mutation_id di field snap_token saat diproses
        $alreadyProcessed = Donation::where('snap_token', 'moota_' . $mutationId)->exists();
        if ($alreadyProcessed) {
            Log::info('Moota: Mutasi sudah diproses sebelumnya', ['mutation_id' => $mutationId]);
            return 'skipped';
        }

        // Proses pembayaran sukses
        DB::beginTransaction();
        try {
            $freshDonation = Donation::lockForUpdate()->find($donation->id);

            if (!$freshDonation || $freshDonation->status === 'sukses') {
                DB::rollBack();
                return 'skipped';
            }

            // Tandai donasi sebagai sukses
            $freshDonation->status     = 'sukses';
            $freshDonation->snap_token = 'moota_' . $mutationId; // simpan mutation_id untuk idempotency
            $freshDonation->updated_at = now();
            $freshDonation->save();

            // Update statistik kampanye
            $campaign = Campaign::find($freshDonation->campaign_id);
            if ($campaign) {
                $campaign->increment('jumlah_donasi', $freshDonation->amount);
                $campaign->increment('current_donation', $freshDonation->amount);
                $campaign->increment('total_donatur', 1);
            }

            // Update donation source
            if ($freshDonation->donation_source_id) {
                $source = DonationSource::find($freshDonation->donation_source_id);
                if ($source) {
                    $source->increment('total_donations', 1);
                    $source->increment('total_amount', $freshDonation->amount);
                }
            }

            // Proses komisi fundraising jika ada referral
            if ($freshDonation->referral_code) {
                $this->processFundraisingCommission($freshDonation);
            }

            DB::commit();

            Log::info('Moota: Donasi berhasil diverifikasi otomatis', [
                'donation_id' => $freshDonation->id,
                'amount'      => $freshDonation->amount,
                'mutation_id' => $mutationId,
            ]);

            // Kirim email notifikasi (di luar transaksi DB)
            $this->sendNotifications($freshDonation);

            return 'processed';
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Moota: Error memproses mutasi', [
                'mutation_id'  => $mutationId,
                'donation_id'  => $donation->id,
                'error'        => $e->getMessage(),
            ]);
            return 'skipped';
        }
    }

    /**
     * Proses komisi fundraising
     */
    protected function processFundraisingCommission(Donation $donation): void
    {
        $fundraising = Fundraising::where('code_link', $donation->referral_code)->first();
        if (!$fundraising) return;

        $commissionSetting = Commission::first();
        $commissionPercent = $commissionSetting->amount ?? 0;
        $commission        = ($donation->amount * $commissionPercent) / 100;

        $fundraising->total_donatur  += 1;
        $fundraising->jumlah_donasi  += $donation->amount;
        $fundraising->commission     += $commission;

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
    }

    /**
     * Kirim email sukses ke donatur dan admin kampanye
     */
    protected function sendNotifications(Donation $donation): void
    {
        try {
            Mail::to($donation->email)->queue(new DonationSuccessMail($donation));
        } catch (\Exception $e) {
            Log::error('Moota: Gagal kirim email ke donatur: ' . $e->getMessage());
        }

        try {
            $campaign = Campaign::with('admin')->find($donation->campaign_id);
            if ($campaign && $campaign->admin && $campaign->admin->email) {
                Mail::to($campaign->admin->email)->queue(new CampaignDonationMail($donation));
            }
        } catch (\Exception $e) {
            Log::error('Moota: Gagal kirim email ke admin kampanye: ' . $e->getMessage());
        }
    }
}