<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\Commission;
use App\Models\Donation;
use App\Models\Fundraising;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FundraisingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $commissionRate = Commission::first()->amount ?? 5; // Default to 5% if no commission set
        $users = User::where('role', 'donatur')->get();
        $campaigns = Campaign::where('status', 'aktif')->orWhere('status', 'selesai')->get();
        
        if ($users->isEmpty() || $campaigns->isEmpty()) {
            $this->command->info('No users or active campaigns found for fundraising. Please run UsersTableSeeder and CampaignsTableSeeder first.');
            return;
        }
        
        // Create 20 fundraising entries across different campaigns
        for ($i = 0; $i < 20; $i++) {
            $user = $users->random();
            $campaign = $campaigns->random();
            
            // Create a unique code link for this fundraiser
            $codeLink = Str::random(8);
            
            // Generate between 0 and 5 donations for this fundraiser
            $donationCount = rand(0, 5);
            $totalDonation = 0;
            $donationIds = [];
            
            for ($j = 0; $j < $donationCount; $j++) {
                $donationAmount = rand(5, 50) * 10000; // 50k to 500k
                $totalDonation += $donationAmount;
                
                // Create a donation linked to this fundraiser
                $donation = Donation::create([
                    'campaign_id' => $campaign->id,
                    'user_id' => rand(0, 1) ? $users->random()->id : null,
                    'name' => 'Donatur via ' . $user->name,
                    'phone' => '08' . rand(100000000, 999999999),
                    'email' => 'fundraising_donor_' . rand(1000, 9999) . '@example.com',
                    'snap_token' => 'tok_fundraising_' . md5(uniqid($j, true)),
                    'amount' => $donationAmount,
                    'payment_type' => rand(0, 1) ? 'payment_gateway' : 'manual',
                    'payment_method' => rand(0, 1) ? 'bank_transfer' : 'gopay',
                    'status' => 'sukses',
                    'doa' => 'Semoga bermanfaat via ' . $user->name,
                    'is_anonymous' => (bool)rand(0, 1)
                ]);
                
                $donationIds[] = $donation->id;
            }
            
            // Calculate commission amount
            $commissionAmount = ($totalDonation * $commissionRate) / 100;
            
            // Create the fundraising record
            Fundraising::create([
                'campaign_id' => $campaign->id,
                'user_id' => $user->id,
                'code_link' => $codeLink,
                'total_donatur' => $donationCount,
                'donations' => json_encode($donationIds),
                'jumlah_donasi' => $totalDonation,
                'commission' => $commissionAmount
            ]);
        }
    }
}