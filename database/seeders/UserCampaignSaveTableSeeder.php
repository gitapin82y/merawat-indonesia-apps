<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserCampaignSaveTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('role', 'donatur')->get();
        $campaigns = Campaign::where('status', 'aktif')->get();
        
        if ($users->isEmpty() || $campaigns->isEmpty()) {
            $this->command->info('No users or active campaigns found. Please run UsersTableSeeder and CampaignsTableSeeder first.');
            return;
        }
        
        // Each user saves 1-5 random campaigns
        foreach ($users as $user) {
            $saveCount = rand(1, 5);
            $campaignsToSave = $campaigns->random(min($saveCount, $campaigns->count()));
            
            foreach ($campaignsToSave as $campaign) {
                // Check if the user has already saved this campaign
                $exists = DB::table('user_campaign_save')
                    ->where('user_id', $user->id)
                    ->where('campaign_id', $campaign->id)
                    ->exists();
                    
                if (!$exists) {
                    DB::table('user_campaign_save')->insert([
                        'user_id' => $user->id,
                        'campaign_id' => $campaign->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }
    }
}