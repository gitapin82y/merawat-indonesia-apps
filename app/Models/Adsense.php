<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Adsense extends Model
{
    use HasFactory;

    protected $table = 'adsense';

    protected $fillable = [
         'tiktok_pixel', 'facebook_pixel', 'facebook_pixel_second', 'google_analytics_tag',
        'meta_token', 'meta_endpoint', 'google_ads_id',
        'google_ads_label', 'tiktok_token', 'tiktok_endpoint'
    ];
}
