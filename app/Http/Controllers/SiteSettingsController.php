<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SiteSettingsController extends Controller
{
    /**
     * Update social media settings.
     */
    public function updateSocialMedia(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'social_media' => 'required|array',
            'social_media.facebook' => 'nullable|url',
            'social_media.instagram' => 'nullable|url',
            'social_media.youtube' => 'nullable|url',
            'social_media.tiktok' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Filter out empty values
        $socialMediaData = array_filter($request->social_media, function($value) {
            return !empty($value);
        });

        SiteSetting::updateOrCreateValue('social_media', json_encode($socialMediaData));

        return response()->json([
            'status' => 'success',
            'message' => 'Pengaturan media sosial berhasil diperbarui',
            'data' => $socialMediaData
        ]);
    }

    /**
     * Get social media settings.
     */
    public function getSocialMedia()
    {
        $socialMedia = SiteSetting::getSocialMedia();
        
        return response()->json([
            'status' => 'success',
            'data' => $socialMedia
        ]);
    }
}