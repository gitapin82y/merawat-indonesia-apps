<?php

namespace Database\Seeders;

use App\Models\DonationSource;
use Illuminate\Database\Seeder;

class DonationSourcesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sources = [
            [
                'source_type' => 'google_ads',
                'campaign_name' => 'Google Campaign 1',
                'utm_source' => 'google',
                'utm_medium' => 'cpc',
                'utm_campaign' => 'donation-drive-2025',
                'total_donations' => 0,
                'total_amount' => 0
            ],
            [
                'source_type' => 'google_ads',
                'campaign_name' => 'Google Campaign 2',
                'utm_source' => 'google',
                'utm_medium' => 'display',
                'utm_campaign' => 'ramadan-2025',
                'total_donations' => 0,
                'total_amount' => 0
            ],
            [
                'source_type' => 'facebook',
                'campaign_name' => 'Facebook Campaign 1',
                'utm_source' => 'facebook',
                'utm_medium' => 'social',
                'utm_campaign' => 'donation-drive-2025',
                'total_donations' => 0,
                'total_amount' => 0
            ],
            [
                'source_type' => 'facebook',
                'campaign_name' => 'Facebook Campaign 2',
                'utm_source' => 'facebook',
                'utm_medium' => 'social',
                'utm_campaign' => 'ramadan-2025',
                'total_donations' => 0,
                'total_amount' => 0
            ],
            [
                'source_type' => 'tiktok',
                'campaign_name' => 'TikTok Campaign 1',
                'utm_source' => 'tiktok',
                'utm_medium' => 'social',
                'utm_campaign' => 'donation-drive-2025',
                'total_donations' => 0,
                'total_amount' => 0
            ],
            [
                'source_type' => 'tiktok',
                'campaign_name' => 'TikTok Campaign 2',
                'utm_source' => 'tiktok',
                'utm_medium' => 'social',
                'utm_campaign' => 'ramadan-2025',
                'total_donations' => 0,
                'total_amount' => 0
            ],
            [
                'source_type' => 'direct',
                'campaign_name' => null,
                'utm_source' => null,
                'utm_medium' => null,
                'utm_campaign' => null,
                'total_donations' => 0,
                'total_amount' => 0
            ],
            [
                'source_type' => 'instagram',
                'campaign_name' => 'Instagram Campaign',
                'utm_source' => 'instagram',
                'utm_medium' => 'social',
                'utm_campaign' => 'donation-drive-2025',
                'total_donations' => 0,
                'total_amount' => 0
            ],
            [
                'source_type' => 'youtube',
                'campaign_name' => 'YouTube Campaign',
                'utm_source' => 'youtube',
                'utm_medium' => 'video',
                'utm_campaign' => 'donation-drive-2025',
                'total_donations' => 0,
                'total_amount' => 0
            ],
            [
                'source_type' => 'email',
                'campaign_name' => 'Email Newsletter',
                'utm_source' => 'email',
                'utm_medium' => 'email',
                'utm_campaign' => 'monthly-newsletter',
                'total_donations' => 0,
                'total_amount' => 0
            ]
        ];

        foreach ($sources as $source) {
            DonationSource::create($source);
        }
    }
}