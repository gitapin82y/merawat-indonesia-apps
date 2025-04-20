<?php

namespace Database\Seeders;

use App\Models\Adsense;
use Illuminate\Database\Seeder;

class AdsenseTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Adsense::create([
            'tiktok_pixel' => 'TIKTOK-PIXEL-123456789',
            'facebook_pixel' => 'FACEBOOK-PIXEL-987654321',
            'google_analytics_tag' => 'G-ANALYTICS-12345',
            'meta_token' => 'META-TOKEN-ABCDEFG123456',
            'meta_endpoint' => 'https://graph.facebook.com/v18.0/123456789/events',
            'google_ads_id' => 'AW-1234567890',
            'google_ads_label' => 'abcdefghijklm',
            'tiktok_token' => 'TIKTOK-TOKEN-ABCDEFG123456',
            'tiktok_endpoint' => 'https://business-api.tiktok.com/open_api/v1.3/pixel/track/'
        ]);
    }
}