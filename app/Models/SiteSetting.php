<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Get setting by key
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getValue($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        
        return $setting ? $setting->value : $default;
    }

    /**
     * Update or create setting
     * 
     * @param string $key
     * @param mixed $value
     * @return SiteSetting
     */
    public static function updateOrCreateValue($key, $value)
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Get social media links
     * 
     * @return array
     */
    public static function getSocialMedia()
    {
        $social = self::getValue('social_media');
        return $social ? json_decode($social, true) : [];
    }
}