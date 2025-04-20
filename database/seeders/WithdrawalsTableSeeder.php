<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\Admin;
use App\Models\CampaignWithdrawal;
use App\Models\Fundraising;
use App\Models\FundraisingWithdrawal;
use App\Models\KabarPencairan;
use Illuminate\Database\Seeder;

class WithdrawalsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed campaign withdrawals
        $this->seedCampaignWithdrawals();
        
        // Seed fundraising withdrawals
        $this->seedFundraisingWithdrawals();
    }
    
    private function seedCampaignWithdrawals()
    {
        $campaigns = Campaign::where('status', 'aktif')
                            ->orWhere('status', 'selesai')
                            ->where('jumlah_donasi', '>', 0)
                            ->get();
                            
        $statuses = ['menunggu', 'disetujui', 'ditolak'];
        $paymentMethods = ['Bank BCA', 'Bank Mandiri', 'Bank BNI', 'Bank BRI'];
        
        foreach ($campaigns as $campaign) {
            $admin = Admin::find($campaign->admin_id);
            
            if (!$admin) {
                continue;
            }
            
            // How many withdrawals for this campaign
            $withdrawalCount = rand(0, 3);
            
            $totalWithdrawn = 0;
            
            for ($i = 0; $i < $withdrawalCount; $i++) {
                // Determine amount (10-50% of available funds each time)
                $availableFunds = $campaign->jumlah_donasi - $totalWithdrawn;
                
                if ($availableFunds <= 0) {
                    continue;
                }
                
                $withdrawalPercent = rand(10, 50) / 100;
                $amount = round($availableFunds * $withdrawalPercent, -3); // Round to nearest thousand
                
                // Minimum withdrawal 500k
                if ($amount < 500000) {
                    $amount = min($availableFunds, 500000);
                }
                
                $status = $statuses[array_rand($statuses)];
                
                // Only disetujui status counts toward total_pencairan_dana
                if ($status === 'disetujui') {
                    $totalWithdrawn += $amount;
                }
                
                // For approved withdrawals, create bukti_pencairan
                $buktiPencairan = null;
                if ($status === 'disetujui') {
                    $buktiPencairan = 'bukti_pencairan_' . $campaign->id . '_' . ($i + 1) . '.jpg';
                }
                
                // Rejection reason for rejected withdrawals
                $rejectionReason = null;
                if ($status === 'ditolak') {
                    $reasons = [
                        'Dokumen RAB tidak lengkap',
                        'Data rekening tidak valid',
                        'Perlu klarifikasi tujuan penggunaan dana',
                        'Mohon lampirkan dokumen pendukung tambahan'
                    ];
                    $rejectionReason = $reasons[array_rand($reasons)];
                }
                
                // Create the withdrawal
                $withdrawal = CampaignWithdrawal::create([
                    'campaign_id' => $campaign->id,
                    'admin_id' => $admin->id,
                    'amount' => $amount,
                    'status' => $status,
                    'document_rab' => 'rab_withdrawal_' . $campaign->id . '_' . ($i + 1) . '.pdf',
                    'bukti_pencairan' => $buktiPencairan,
                    'account_name' => $admin->name,
                    'account_number' => '123456' . rand(1000, 9999),
                    'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                    'rejection_reason' => $rejectionReason
                ]);
                
                // For approved withdrawals, create kabar_pencairan
                if ($status === 'disetujui') {
                    KabarPencairan::create([
                        'campaign_id' => $campaign->id,
                        'title' => 'Pencairan Dana Tahap ' . ($i + 1),
                        'description' => 'Dana sebesar Rp ' . number_format($amount, 0, ',', '.') . ' telah dicairkan untuk kebutuhan ' . ['operasional', 'pembelian material', 'bantuan langsung', 'logistik'][array_rand(['operasional', 'pembelian material', 'bantuan langsung', 'logistik'])],
                        'total_amount' => $amount,
                        'document_rab' => 'rab_withdrawal_' . $campaign->id . '_' . ($i + 1) . '.pdf',
                        'status' => 'disetujui'
                    ]);
                }
            }
            
            // Update campaign withdrawal statistics
            if ($totalWithdrawn > 0) {
                $campaign->update([
                    'total_pencairan_dana' => $withdrawalCount,
                    'jumlah_pencairan_dana' => $totalWithdrawn
                ]);
            }
        }
    }
    
    private function seedFundraisingWithdrawals()
    {
        $fundraisings = Fundraising::where('jumlah_donasi', '>', 0)->get();
        $statuses = ['menunggu', 'disetujui', 'ditolak'];
        $paymentMethods = ['Bank BCA', 'Bank Mandiri', 'Bank BNI', 'Bank BRI'];
        
        foreach ($fundraisings as $fundraising) {
            // Check if there's any commission
            if ($fundraising->commission <= 0) {
                continue;
            }
            
            // 50% chance to create a withdrawal
            if (rand(0, 1) === 0) {
                continue;
            }
            
            $status = $statuses[array_rand($statuses)];
            
            // For approved withdrawals, create bukti_pencairan
            $buktiPencairan = null;
            if ($status === 'disetujui') {
                $buktiPencairan = 'bukti_pencairan_fundraising_' . $fundraising->id . '.jpg';
            }
            
            // Rejection reason for rejected withdrawals
            $rejectionReason = null;
            if ($status === 'ditolak') {
                $reasons = [
                    'Data rekening tidak valid',
                    'Mohon verifikasi identitas terlebih dahulu',
                    'Minimum penarikan belum tercapai',
                    'Mohon lengkapi profil pengguna'
                ];
                $rejectionReason = $reasons[array_rand($reasons)];
            }
            
            // Create the withdrawal
            FundraisingWithdrawal::create([
                'fundraising_id' => $fundraising->id,
                'user_id' => $fundraising->user_id,
                'amount' => $fundraising->commission,
                'status' => $status,
                'bukti_pencairan' => $buktiPencairan,
                'account_name' => 'User ' . $fundraising->user_id,
                'account_number' => '987654' . rand(1000, 9999),
                'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                'rejection_reason' => $rejectionReason
            ]);
        }
    }
}