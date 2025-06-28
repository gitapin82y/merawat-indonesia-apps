<?php

namespace App\Http\Controllers;

use App\Models\Adsense;
use Illuminate\Http\Request;

class AdsenseController extends Controller
{
    public function index(){
            $adsense = Adsense::first();
            return view('super_admin.dashboard', compact('adsense'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'tiktok_pixel' => 'nullable|string|max:255',
                'facebook_pixel' => 'nullable|string|max:255',
                 'facebook_pixel_second' => 'nullable|string|max:255',
                'google_analytics_tag' => 'nullable|string|max:255',
                'meta_token' => 'nullable|string|max:255',
                'meta_endpoint' => 'nullable|string|max:255',
                'google_ads_id' => 'nullable|string|max:255',
                'google_ads_label' => 'nullable|string|max:255',
                'tiktok_token' => 'nullable|string|max:255',
                'tiktok_endpoint' => 'nullable|string|max:255',
            ]);

            Adsense::create($validated);

            return response()->json(['message' => 'Data berhasil disimpan.'], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan, coba lagi.'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'tiktok_pixel' => 'nullable|string|max:255',
                'facebook_pixel' => 'nullable|string|max:255',
                 'facebook_pixel_second' => 'nullable|string|max:255',
                'google_analytics_tag' => 'nullable|string|max:255',
                'meta_token' => 'nullable|string|max:255',
                'meta_endpoint' => 'nullable|string|max:255',
                'google_ads_id' => 'nullable|string|max:255',
                'google_ads_label' => 'nullable|string|max:255',
                'tiktok_token' => 'nullable|string|max:255',
                'tiktok_endpoint' => 'nullable|string|max:255',
            ]);

            $adsense = Adsense::findOrFail($id);
            $adsense->update($validated);
        

            return response()->json(['message' => 'Data berhasil diperbarui.'], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan :'. $e], 500);
        }
    }
}