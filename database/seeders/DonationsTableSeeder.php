<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\Donation;
use App\Models\DonationSource;
use App\Models\ManualPaymentMethod;
use App\Models\User;
use Illuminate\Database\Seeder;

class DonationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $campaigns = Campaign::where('status', 'aktif')
                              ->orWhere('status', 'selesai')
                              ->get();
                              
        if ($campaigns->isEmpty()) {
            $this->command->info('No active or completed campaigns found. Please run CampaignsTableSeeder first.');
            return;
        }
        
        $users = User::where('role', 'donatur')->get();
        $manualPaymentMethods = ManualPaymentMethod::where('is_active', true)->get();
        $donationSources = DonationSource::all();
        
        $paymentMethods = [
            'payment_gateway' => ['credit_card', 'bank_transfer', 'gopay', 'ovo', 'dana', 'shopeepay'],
            'manual' => $manualPaymentMethods->pluck('name')->toArray()
        ];
        
        $doas = [
            'Semoga bantuan ini bermanfaat untuk saudara-saudara kita yang membutuhkan.',
            'Semoga Allah memberikan kemudahan bagi semua yang terkena musibah.',
            'Ikut membantu sesama, semoga berkah.',
            'Semoga bantuan kecil ini dapat membantu saudara kita yang terkena musibah.',
            'Mari bersama membantu sesama. Semoga bermanfaat.',
            'Semoga cepat pulih dan kembali normal.',
            'Sedikit membantu dari saya, semoga berkah.',
            'Ikut berdonasi, semoga bisa meringankan beban saudara kita.',
            null, // Some donations might not have doa
        ];
        
        foreach ($campaigns as $campaign) {
            // Calculate how many donations needed based on campaign's total_donatur
            $totalDonatursNeeded = $campaign->total_donatur;
            
            // Actual donations to create for this campaign
            $donationsToCreate = min($totalDonatursNeeded, 30); // Cap at 30 per campaign for seeding
            
            $totalDonationAmount = 0;
            
            for ($i = 0; $i < $donationsToCreate; $i++) {
                $isAnonymous = (bool)rand(0, 1);
                $isRegisteredUser = (bool)rand(0, 1) && $users->isNotEmpty();
                $paymentType = array_rand($paymentMethods);
                $paymentMethod = $paymentMethods[$paymentType][array_rand($paymentMethods[$paymentType])];
                
                $donationAmount = rand(10, 100) * 10000; // Random amount between 100k - 1M
                $totalDonationAmount += $donationAmount;
                
                // Create donation
                $donation = new Donation();
                $donation->campaign_id = $campaign->id;
                
                // User related fields
                if ($isRegisteredUser) {
                    $user = $users->random();
                    $donation->user_id = $user->id;
                    $donation->name = $isAnonymous ? 'Sahabat Baik' : $user->name;
                    $donation->email = $user->email;
                    $donation->phone = $user->phone;
                } else {
                    $donation->user_id = null;
                    $donation->name = $isAnonymous ? 'Sahabat Baik' : 'Donatur ' . ($i + 1);
                    $donation->email = 'donatur' . ($i + 1) . '@example.com';
                    $donation->phone = '08' . rand(100000000, 999999999);
                }
                
                // Donation details
                $donation->is_anonymous = $isAnonymous;
                $donation->doa = $doas[array_rand($doas)];
                $donation->amount = $donationAmount;
                $donation->payment_type = $paymentType;
                $donation->payment_method = $paymentMethod;
                $donation->status = rand(1, 10) <= 8 ? 'sukses' : (rand(0, 1) ? 'pending' : 'gagal');
                $donation->snap_token = 'tok_' . md5(uniqid($i, true));
                
                // Source tracking
                if (rand(0, 1) && $donationSources->isNotEmpty()) {
                    $source = $donationSources->random();
                    $donation->donation_source_id = $source->id;
                    $donation->utm_source = $source->utm_source;
                    $donation->utm_medium = $source->utm_medium;
                    $donation->utm_campaign = $source->utm_campaign;
                }
                
                // If manual payment, assign payment method
                if ($paymentType === 'manual' && $manualPaymentMethods->isNotEmpty()) {
                    $manualMethod = $manualPaymentMethods->where('name', $paymentMethod)->first();
                    if ($manualMethod) {
                        $donation->manual_payment_method_id = $manualMethod->id;
                        
                        // Add payment proof for successful manual payments
                        if ($donation->status === 'sukses') {
                            $donation->payment_proof = 'proof_' . $i . '.jpg';
                        }
                    }
                }
                
                $donation->save();
            }
            
            // Update campaign donation amount if there's a mismatch
            if ($campaign->jumlah_donasi != $totalDonationAmount && $campaign->status === 'aktif') {
                $campaign->update([
                    'jumlah_donasi' => $totalDonationAmount,
                    'current_donation' => $totalDonationAmount,
                    'total_donatur' => $donationsToCreate
                ]);
            }
        }
        
        // Update donation sources counts and amounts
        foreach ($donationSources as $source) {
            $sourceStats = Donation::where('donation_source_id', $source->id)
                                   ->where('status', 'sukses')
                                   ->selectRaw('COUNT(*) as total_count, SUM(amount) as total_sum')
                                   ->first();
                                   
            if ($sourceStats && $sourceStats->total_count > 0) {
                $source->update([
                    'total_donations' => $sourceStats->total_count,
                    'total_amount' => $sourceStats->total_sum
                ]);
            }
        }
    }
}