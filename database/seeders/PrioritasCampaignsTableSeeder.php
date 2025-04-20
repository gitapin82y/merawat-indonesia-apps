<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\PrioritasCampaign;
use Illuminate\Database\Seeder;

class PrioritasCampaignsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $activeCampaigns = Campaign::where('status', 'aktif')->limit(5)->get();
        
        foreach ($activeCampaigns as $index => $campaign) {
            PrioritasCampaign::create([
                'campaign_id' => $campaign->id,
                'prioritas' => $index + 1 // Priority 1 is highest
            ]);
        }
    }
}