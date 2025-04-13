<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index()
{
    return response()->json(Category::all());
}

public function store(Request $request)
{
    $request->validate([
        'name' => 'required|unique:categories,name',
        'icon' => 'required|image|mimes:png,jpg,jpeg,svg|max:2048'
    ]);

    $iconPath = $request->file('icon') ? $request->file('icon')->store('icons', 'public') : null;

    Category::create([
        'name' => $request->name,
        'icon' => $iconPath
    ]);

    return response()->json(['message' => 'Kategori berhasil ditambahkan!']);
}

public function update(Request $request, $id)
{
    $category = Category::findOrFail($id);

    $request->validate([
        'name' => 'required|unique:categories,name,' . $category->id,
        'icon' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048'
    ]);

    if ($request->hasFile('icon')) {
        Storage::disk('public')->delete($category->icon);
        $category->icon = $request->file('icon')->store('icons', 'public');
    }

    $category->name = $request->name;
    $category->save();

    return response()->json(['message' => 'Kategori berhasil diperbarui!']);
}

public function destroy($id)
{
    $category = Category::findOrFail($id);

    // Hapus file icon jika ada
    if ($category->icon) {
        Storage::disk('public')->delete($category->icon);
    }

    // Hapus kategori dari database
    $category->delete();

    return response()->json(['message' => 'Kategori berhasil dihapus!'], 200);
}
}