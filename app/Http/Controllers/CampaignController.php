<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Donation;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;


class CampaignController extends Controller
{
    public function index(Request $request)
    {
        Carbon::setLocale('id');
        if ($request->ajax()) {
            $query = Campaign::with(['admin','category'])->get();
            
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('action', function($row) {
                    $actionBtn = '
                        <div class="btn-group" role="group">
                        <a href="/kampanye/'.$row->title.'" class="btn btn-info btn-sm"><i class="fa-solid fa-eye text-white"></i></a>
                            <a href="'.route('kampanye.edit', $row->id).'" class="btn btn-primary btn-sm"><i class="fa-solid fa-pen"></i></a>
                            <button onclick="deleteCampaign('.$row->id.')" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    ';
                    return $actionBtn;
                })
                ->addColumn('category', function($row) {
                    return $row->category->name ?? 'N/A'; // Menampilkan nama kategori
                })
                ->addColumn('status', function($row) {
                    $statusColor = [
                        'aktif' => 'success',
                        'selesai' => 'primary',
                        'ditolak' => 'danger',
                        'validasi' => 'warning',
                        'berakhir' => 'secondary'
                    ];
                    return '<span class="badge bg-'.$statusColor[$row->status].' text-white">'.$row->status.'</span>';
                })
                ->addColumn('admin', function($row) {
                    $statusColor = [
                        'menunggu' => 'warning',
                        'disetujui' => 'success', 
                        'ditolak' => 'danger'
                    ];
                    return $row->admin->name . '<br> <span class="badge bg-'.$statusColor[$row->admin->status].' text-white">'.$row->admin->status.'</span>';
                })
                ->rawColumns(['category','status','admin','action'])
                 ->make(true);
        }
        
        return view('super_admin.kampanye.index');
    }

    public function create()
    {
        $admins = Admin::get();
        $categories = Category::get();
        return view('super_admin.kampanye.form', compact('categories','admins'));
    }    

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'admin_id' => 'required|exists:admins,id',
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'photo' => 'required|image|max:2048',
            'deadline' => 'nullable|date',
            'jumlah_target_donasi' => 'nullable|numeric',
            'document_rab' => 'required|mimes:pdf,doc,docx,xls,xlsx|max:5120',
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
    
        DB::beginTransaction();
        try {
            $campaignData = $request->except(['photo', 'document_rab']);
            
            // Handle file uploads
            if ($request->hasFile('photo')) {
                $campaignData['photo'] = $request->file('photo')->store('campaign_photos', 'public');
            }

            if ($request->hasFile('document_rab')) {
                $campaignData['document_rab'] = $request->file('document_rab')->store('campaign_documents', 'public');
            }
    
            $campaign = Campaign::create($campaignData);
            DB::commit();
            return redirect()->back()
                ->with('success', 'Kampanye berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menambahkan kampanye: ' . $e->getMessage());
        }
    }

    public function upload(Request $request)
    {
 try {
            // Log the request
            Log::info('Summernote image upload request received');
            
            if (!$request->hasFile('file')) {
                Log::error('No file found in the request');
                return response()->json(['error' => 'No file uploaded'], 400);
            }
            
            // Validate file type and size
            $validated = $request->validate([
                'file' => 'required|image|max:5120', // 5MB max
            ]);
            
            $file = $request->file('file');
            Log::info('File received: ' . $file->getClientOriginalName());
            
            // Generate unique filename
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            
            // Store the file
            $path = $file->storeAs('summernote/images', $filename, 'public');
            Log::info('File stored at: ' . $path);
            
            return response()->json([
                'location' => asset('storage/' . $path)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Summernote image upload error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    
    }
    
    public function edit(Campaign $kampanye)
    {
        $admins = Admin::get();
        $categories = Category::get();
        return view('super_admin.kampanye.form', compact('categories','kampanye','admins'));
    }

    public function update(Request $request, Campaign $kampanye)
    {
        $user = auth()->user(); // Ambil user yang sedang login
        $role = $user->role; // Misalnya role ada di dalam field 'role'

        $rules = [
            'admin_id' => 'required|exists:admins,id',
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'photo' => 'nullable|image|max:2048',
            'deadline' => 'nullable|date',
            'jumlah_target_donasi' => 'nullable|numeric',
            'document_rab' => 'nullable|mimes:pdf,doc,docx,xls,xlsx|max:5120',
        ];

        if ($role === 'super_admin') {
            $rules['document_rab'] = 'required|mimes:pdf,doc,docx,xls,xlsx|max:5120';
            $rules['category_id'] = 'required|exists:categories,id';
        } else {
            $rules['document_rab'] = 'nullable|mimes:pdf,doc,docx,xls,xlsx|max:5120';
            $rules['category_id'] = 'nullable|exists:categories,id';
        }
    
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
    
        DB::beginTransaction();
        try {
            $kampanyeData = $request->except(['photo', 'document_rab']);
            
            if ($request->hasFile('photo')) {
                if ($kampanye->photo) {
                    Storage::disk('public')->delete($kampanye->photo);
                }
                $kampanyeData['photo'] = $request->file('photo')->store('campaign_photos', 'public');
            }
        
            if ($request->hasFile('document_rab')) {
                if ($kampanye->document_rab) {
                    Storage::disk('public')->delete($kampanye->document_rab);
                }
                $kampanyeData['document_rab'] = $request->file('document_rab')->store('campaign_documents', 'public');
            } 
            $kampanye->update($kampanyeData);

            DB::commit();
            if ($role === 'super_admin') {
                return redirect()->back()
                ->with('success', 'Kampanye berhasil diperbarui');
            } else {
                return redirect('/admin/kampanye/'. $kampanye->title)->with('success', 'Kampanye berhasil diperbarui');
            }
        } catch (\Exception $e) {

            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal memperbarui kampanye: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function donaturKampanye(Request $request, Campaign $kampanye,$title)
    {
        $campaign = Campaign::with([
            'kabarTerbaru', 
            'kabarPencairan' => function ($query) {
                $query->where('status', 'disetujui');
            },
            'admin', 
            'donations' => function ($query) {
                $query->where('status', 'sukses')
                      ->orderBy('created_at', 'desc');  // Menambahkan pengurutan berdasarkan waktu terbaru
            }
        ])->where('title', $title)->first();

    $totalDonaturs = $campaign->donations->where('status', 'sukses')->count();
    
    // Hitung jumlah total kampanye
    $totalKampanye = $campaign->where('status', 'aktif')->count();

    $comments = Donation::where('campaign_id', $campaign->id)
    ->where('status', 'sukses')
    ->whereNotNull('doa')
    ->orderBy('created_at', 'desc')  // Menambahkan pengurutan berdasarkan waktu terbaru
    ->paginate(6);

    if ($request->ajax()) {
        return response()->json([
            'comments' => view('partials.comments', compact('comments'))->render(),
            'hasMorePages' => $comments->hasMorePages(),
            'nextPageUrl' => $comments->nextPageUrl(),
        ]);
    }

        return view('donatur.detail-kampanye', [
            'campaign' => $campaign,
            'comments' => $comments,
            'totalDonaturs' => $totalDonaturs,
            'totalKampanye' => $totalKampanye,
        ]);
    }

    public function show(Campaign $kampanye,$title)
    {
        $campaign = Campaign::with([
            'kabarTerbaru', 
            'kabarPencairan' => function ($query) {
                $query->where('status', 'disetujui');
            },
            'admin', 
            'donations' => function ($query) {
                $query->where('status', 'sukses')
                      ->orderBy('created_at', 'desc');  // Menambahkan pengurutan berdasarkan waktu terbaru
            }
        ])->where('title', $title)->first();

        return view('admin.kampanye.detail-kampanye', [
            'campaign' => $campaign,
        ]);
    }

    // form edit admin
    public function editKampanye($title)
    {

        $kampanye = Campaign::where('title',$title)->first();

        return view('admin.kampanye.edit-kampanye', [
            'kampanye' => $kampanye,
        ]);
    }

    public function destroy(Campaign $kampanye)
    {
        DB::beginTransaction();
        try {
            // Hapus file avatar dan thumbnail jika ada
            if ($kampanye->document_rab) {
                Storage::disk('public')->delete($kampanye->document_rab);
            }
            
            if ($kampanye->photo) {
                Storage::disk('public')->delete($kampanye->photo);
            }

            $kampanye->delete();

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil dihapus'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menghapus admin: ' . $e->getMessage()], 500);
        }
    }

    public function changeStatus(Campaign $campaign, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:menunggu,disetujui,ditolak'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $campaign->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Status admin berhasil diubah',
            'new_status' => $campaign->status
        ]);
    }

    public function toggleSave(Request $request)
    {
        // Pastikan user sudah login
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda harus login terlebih dahulu.'
            ], 401);
        }
    
        $campaignId = $request->input('campaign_id');
        $user = Auth::user();
        $campaign = Campaign::findOrFail($campaignId);
    
        // Cek apakah kampanye sudah disimpan
        $isSaved = $user->savedCampaigns()->where('campaign_id', $campaignId)->exists();
    
        if ($isSaved) {
            // Jika sudah disimpan, maka hapus
            $user->savedCampaigns()->detach($campaignId);
            $status = 'unsaved';
        } else {
            // Jika belum disimpan, maka simpan
            $user->savedCampaigns()->attach($campaignId);
            $status = 'saved';
        }
    
        return response()->json([
            'status' => $status,
            'message' => $status == 'saved' 
                ? 'Kampanye berhasil disimpan!' 
                : 'Kampanye berhasil dihapus dari daftar simpanan!'
        ]);
    }
}