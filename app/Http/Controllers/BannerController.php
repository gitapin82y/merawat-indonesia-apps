<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index()
{
    return response()->json(Banner::all());
}

public function store(Request $request)
{
    $request->validate([
        'photo' => 'required|image|mimes:png,jpg,jpeg,svg|max:2048'
    ]);

    $photoPath = $request->file('photo') ? $request->file('photo')->store('banners', 'public') : null;

    Banner::create([
        'name' => $request->name,
        'photo' => $photoPath
    ]);

    return response()->json(['message' => 'Banner berhasil ditambahkan!']);
}


public function destroy($id)
{
    $banner = Banner::findOrFail($id);

    // Hapus file photo jika ada
    if ($banner->photo) {
        Storage::disk('public')->delete($banner->photo);
    }

    // Hapus Banner dari database
    $banner->delete();

    return response()->json(['message' => 'Banner berhasil dihapus!'], 200);
}
}
