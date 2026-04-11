<?php

namespace App\Http\Controllers;

use App\Models\MootaBank;
use App\Services\MootaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MootaBankController extends Controller
{
    protected MootaService $mootaService;

    public function __construct(MootaService $mootaService)
    {
        $this->mootaService = $mootaService;
    }

    /**
     * Ambil semua bank Moota (untuk render di modal dashboard super admin via AJAX)
     */
    public function index()
    {
        $banks = MootaBank::orderBy('bank_type')->get()->map(function ($bank) {
            return [
                'id'             => $bank->id,
                'bank_id'        => $bank->bank_id,
                'bank_type'      => $bank->bank_type,
                'account_number' => $bank->account_number,
                'account_name'   => $bank->account_name,
                'label'          => $bank->bank_label,
                'balance'        => $bank->balance,
                'is_active'      => $bank->is_active,
                'moota_active'   => $bank->moota_active,
                'last_synced_at' => $bank->last_synced_at?->format('d M Y H:i'),
            ];
        });

        return response()->json($banks);
    }

    /**
     * Sync bank dari Moota API ke database lokal
     */
    public function sync()
    {
        try {
            $result = $this->mootaService->syncBanks();

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => "Berhasil menyinkronkan {$result['synced']} bank dari Moota.",
                    'synced'  => $result['synced'],
                ]);
            }

            return response()->json([
                'success' => false,
                'error'   => $result['message'] ?? 'Gagal sinkronisasi bank dari Moota.',
            ], 500);
        } catch (\Exception $e) {
            Log::error('MootaBankController sync error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Toggle aktif/nonaktif bank Moota untuk ditampilkan ke donatur
     */
    public function toggleStatus(Request $request)
    {
        $request->validate([
            'bank_id'   => 'required|string|exists:moota_banks,bank_id',
            'is_active' => 'required|in:0,1',
        ]);

        try {
            $bank = MootaBank::where('bank_id', $request->bank_id)->firstOrFail();
            $bank->is_active = (bool) $request->is_active;
            $bank->save();

            return response()->json([
                'success'   => true,
                'message'   => 'Status bank berhasil diperbarui.',
                'is_active' => $bank->is_active,
            ]);
        } catch (\Exception $e) {
            Log::error('MootaBankController toggleStatus error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}