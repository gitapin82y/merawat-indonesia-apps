<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if ($user->role !== 'super_admin') {
            return redirect('galang-dana');
        }

        if ($request->ajax()) {
            $query = Article::get();
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    return '<div class="btn-group" role="group">'
                        .'<a href="'.route('artikel.edit', $row->id).'" class="btn btn-primary btn-sm"><i class="fa-solid fa-pen"></i></a>'
                        .'<button onclick="deleteArticle('.$row->id.')" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>'
                        .'</div>';
                })
                ->addColumn('created_at',function ($row){
                    return $row->created_at->format('d M Y');
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('super_admin.artikel.index');
    }

    public function create()
    {
        return view('super_admin.artikel.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('articles', 'public');
            $validated['image'] = $path;
        }
        $validated['slug'] = Str::slug($validated['title']);

        Article::create($validated);

        return redirect('super-admin/artikel')->with('success', 'Artikel berhasil dibuat');
    }

    public function edit($id)
    {
        $article = Article::findOrFail($id);
        return view('super_admin.artikel.form', compact('article'));
    }

    public function update(Request $request, $id)
    {
        $article = Article::findOrFail($id);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('articles', 'public');
            $validated['image'] = $path;
        }
        $validated['slug'] = Str::slug($validated['title']);

        $article->update($validated);

        return redirect('super-admin/artikel')->with('success', 'Artikel berhasil diperbarui');
    }

    public function destroy($id)
    {
        $article = Article::findOrFail($id);
        $article->delete();
        return response()->json(['success' => true]);
    }

    public function userIndex()
    {
        $articles = Article::orderBy('created_at', 'desc')->paginate(10);
        return view('donatur.artikel.index', compact('articles'));
    }

    public function show($slug)
    {
        $article = Article::where('slug', $slug)->firstOrFail();
        return view('donatur.artikel.show', compact('article'));
    }
}