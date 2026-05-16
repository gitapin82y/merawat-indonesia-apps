<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Donation;
use App\Models\Fundraising;
use App\Models\Commission;

class RepairFundraisingData extends Command
{
    protected $signature   = 'fundraising:repair';
    protected $description = 'Repair fundraising data yang tidak tercatat karena bug processFundraisingCommission';

    public function handle()
    {
        $commissionPercent = Commission::first()->amount ?? 0;
        $fixed  = 0;
        $skip   = 0;

        $this->info("Commission rate: {$commissionPercent}%");
        $this->info("Mencari donasi sukses dengan referral_code...\n");

        $donations = Donation::where('status', 'sukses')
            ->whereNotNull('referral_code')
            ->get();

        $this->info("Ditemukan {$donations->count()} donasi dengan referral_code.\n");

        foreach ($donations as $donation) {
            $fundraising = Fundraising::where('code_link', $donation->referral_code)->first();

            if (!$fundraising) {
                $this->warn("Skip #{$donation->id} — fundraising tidak ditemukan untuk code: {$donation->referral_code}");
                $skip++;
                continue;
            }

            $existing = json_decode($fundraising->donations ?? '[]', true) ?: [];
            $recorded = array_column($existing, 'donation_id');

            if (in_array($donation->id, $recorded)) {
                $this->line("Skip #{$donation->id} — sudah tercatat");
                $skip++;
                continue;
            }

            $commission = ($donation->amount * $commissionPercent) / 100;

            $fundraising->total_donatur += 1;
            $fundraising->jumlah_donasi += $donation->amount;
            $fundraising->commission    += $commission;

            $existing[] = [
                'donation_id' => $donation->id,
                'amount'      => $donation->amount,
                'commission'  => $commission,
                'user_name'   => null,
                'user_email'  => null,
                'created_at'  => $donation->updated_at->format('Y-m-d H:i:s'),
            ];
            $fundraising->donations = json_encode($existing);
            $fundraising->save();

            $fixed++;
            $this->info("Fixed #{$donation->id} — amount: {$donation->amount}, commission: {$commission}");
        }

        $this->info("\n=============================");
        $this->info("Total diperbaiki : {$fixed} donasi");
        $this->info("Total dilewati   : {$skip} donasi");
        $this->info("=============================");

        return Command::SUCCESS;
    }
}