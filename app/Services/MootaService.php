<?php

namespace App\Services;

use App\Models\MootaBank;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MootaService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://app.moota.co/api/v2';

    public function __construct()
    {
        $this->apiKey = config('moota.api_key');
    }

    /**
     * Header standar untuk setiap request ke Moota API
     */
    protected function headers(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ];
    }

    /**
     * Ambil daftar semua bank yang terdaftar di Moota
     */
    public function getBanks(): array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->get("{$this->baseUrl}/bank");

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Moota getBanks response', ['data' => $data]);
                return ['success' => true, 'data' => $data];
            }

            Log::error('Moota getBanks failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mengambil data bank dari Moota: ' . $response->body(),
            ];
        } catch (\Exception $e) {
            Log::error('Moota getBanks exception: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Sync bank aktif dari Moota ke database lokal
     * Hanya simpan yang is_active = true di sisi Moota
     */
    public function syncBanks(): array
    {
        $result = $this->getBanks();

        if (!$result['success']) {
            return $result;
        }

        $banks      = $result['data'];
        $syncedIds  = [];
        $synced     = 0;

        // Moota API v2 mengembalikan array langsung atau dalam key 'data'
        if (isset($banks['data'])) {
            $banks = $banks['data'];
        }

        foreach ($banks as $bank) {
            $bankId        = $bank['bank_id'] ?? $bank['token'] ?? null;
            $accountNumber = $bank['account_number'] ?? '';
            $accountName   = $bank['atas_nama'] ?? '';
            $bankType      = $bank['bank_type'] ?? '';
            $label         = $bank['label'] ?? strtoupper($bankType);
            $balance       = $bank['balance'] ?? 0;
            $mootaActive   = $bank['is_active'] ?? true;

            if (!$bankId || !$accountNumber) {
                continue;
            }

            // Ambil status is_active yang sudah diset admin, jangan override
            $existing  = MootaBank::where('bank_id', $bankId)->first();
            $isActive  = $existing ? $existing->is_active : true; // default aktif saat pertama sync

            MootaBank::updateOrCreate(
                ['bank_id' => $bankId],
                [
                    'bank_type'      => $bankType,
                    'account_number' => $accountNumber,
                    'account_name'   => $accountName,
                    'label'          => $label,
                    'balance'        => $balance,
                    'moota_active'   => $mootaActive,
                    'is_active'      => $isActive,
                    'last_synced_at' => now(),
                ]
            );

            $syncedIds[] = $bankId;
            $synced++;
        }

        // Non-aktifkan bank yang sudah tidak ada di Moota
        if (!empty($syncedIds)) {
            MootaBank::whereNotIn('bank_id', $syncedIds)->update(['moota_active' => false]);
        }

        Log::info("Moota syncBanks: {$synced} banks synced.");

        return ['success' => true, 'synced' => $synced, 'bank_ids' => $syncedIds];
    }

    /**
     * Ambil mutasi dari bank tertentu berdasarkan jumlah & tanggal
     */
    public function getMutations(string $bankId, array $filters = []): array
    {
        try {
            $params   = array_merge(['page' => 1, 'per_page' => 50], $filters);
            $response = Http::withHeaders($this->headers())
                ->get("{$this->baseUrl}/mutation", $params);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            Log::error('Moota getMutations failed', [
                'bank_id' => $bankId,
                'status'  => $response->status(),
                'body'    => $response->body(),
            ]);

            return ['success' => false, 'message' => $response->body()];
        } catch (\Exception $e) {
            Log::error('Moota getMutations exception: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Verifikasi signature webhook dari Moota
     * Moota mengirim header 'Signature' yang merupakan HMAC SHA256 dari body
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $secretToken = config('moota.webhook_secret');

        if (empty($secretToken)) {
            // Jika secret belum diset, skip verifikasi (log warning)
            Log::warning('Moota webhook secret tidak dikonfigurasi, verifikasi signature dilewati.');
            return true;
        }

        $expected = hash_hmac('sha256', $payload, $secretToken);

        return hash_equals($expected, $signature);
    }

    /**
     * Register webhook ke Moota untuk menerima notifikasi mutasi
     */
    public function registerWebhook(string $url, string $bankId = '', string $secretToken = ''): array
    {
        try {
            $payload = [
                'url'              => $url,
                'bank_account_id'  => $bankId, // kosong = semua bank
                'kinds'            => 'credit', // hanya uang masuk
                'secret_token'     => $secretToken,
                'start_unique_code'=> 0,
                'end_unique_code'  => 999,
            ];

            $response = Http::withHeaders($this->headers())
                ->post("{$this->baseUrl}/integration/webhook", $payload);

            if ($response->successful()) {
                Log::info('Moota webhook registered', ['response' => $response->json()]);
                return ['success' => true, 'data' => $response->json()];
            }

            Log::error('Moota registerWebhook failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return ['success' => false, 'message' => $response->body()];
        } catch (\Exception $e) {
            Log::error('Moota registerWebhook exception: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}