<?php

namespace App\Http\Controllers;

use App\Models\KabarTerbaru;
use Illuminate\Http\Request;
use App\Models\Campaign;
use App\Models\Admin;
use App\Models\Category;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class KabarTerbaruController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if($user->role !== 'super_admin'){
            return redirect()->back();
        }

        Carbon::setLocale('id');
    
        if ($request->ajax()) {
            $query = KabarTerbaru::with(['campaign', 'campaign.category', 'campaign.admin'])->get();

            $campaignCounts = KabarTerbaru::select('campaign_id', DB::raw('count(*) as total'))
                   ->groupBy('campaign_id')
                   ->pluck('total', 'campaign_id')
                   ->toArray();
    
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('title', function($row) {
                    return $row->campaign?->title ?? 'N/A'; // Pastikan title diambil dari campaign
                })
                ->addColumn('category', function($row) {
                    return $row->campaign?->category?->name ?? 'N/A'; // Pastikan category tidak null
                })
                ->addColumn('created_at', function($row) {
                    return $row->created_at 
                    ? Carbon::parse($row->created_at)->timezone('Asia/Jakarta')->format('d M Y')
                    : '-';
                })
                ->addColumn('admin', function($row) {
                    if ($row->campaign?->admin) {
                        $statusColor = [
                            'menunggu' => 'warning',
                            'disetujui' => 'success',
                            'ditolak' => 'danger'
                        ];
                        $adminStatus = $row->campaign->admin->status ?? 'menunggu';
                        $color = $statusColor[$adminStatus] ?? 'secondary';
    
                        return $row->campaign->admin->name . 
                               '<br> <span class="badge bg-'.$color.' text-white">'.$adminStatus.'</span>';
                    }
                    return 'N/A';
                })
                ->addColumn('action', function($row) {
                    $title = $row->campaign?->title ?? 'N/A';
                    return '
                        <div class="btn-group" role="group">
                         <a href="'.route('kabar-terbaru.edit', $row->campaign->id).'" class="btn text-white btn-info btn-sm">Lihat Kabar Terbaru</a>
                            <button onclick="deleteKabarTerbaru('.$row->id.')" class="btn btn-warning btn-sm"><i class="fas fa-times text-white"></i></button>
                        </div>
                    ';
                })
                ->addColumn('total', function($row) use ($campaignCounts) {
                    $count = $campaignCounts[$row->campaign_id] ?? 0;
                    return '<span class="badge bg-primary text-white">'.$count.'</span>';
                })                
                ->rawColumns(['total','category', 'status', 'admin', 'action'])
                ->make(true);
        }
    
        return view('super_admin.kabar_terbaru.index');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'campaign_id' => 'required|exists:campaigns,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
    
        DB::beginTransaction();
        try {
            $kabarTerbaru = KabarTerbaru::create($request->all());
            DB::commit();
            return redirect()->back()
                ->with('success', 'Kabar Terbaru berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menambahkan kabar terbaru: ' . $e->getMessage());
        }
    }

    public function buatKabarTerbaru($slug)
    {
        $campaign = Campaign::where('slug',$slug)->first();
        return view('admin.kampanye.buat-kabar', [
            'idKampanye' => $campaign->id,
        ]);
    }

    public function kabarTerbaru($slug)
    {
        $campaign = Campaign::where('slug',$slug)->first();
        $kabarTerbaru = KabarTerbaru::where('campaign_id',$campaign->id)->get();
        return view('admin.kampanye.kabar-terbaru', [
            'kabarTerbaru' => $kabarTerbaru,
            'slug' => $slug,
        ]);
    }


    public function edit($id)
    {
        $kabarTerbaru = KabarTerbaru::whereHas('campaign', function($query) use ($id) {
            $query->where('id', $id);
        })->get();
        return view('super_admin.kabar_terbaru.form', compact('kabarTerbaru'));
    }
    
    public function destroy(KabarTerbaru $kabarTerbaru, $id)
    {
        DB::beginTransaction();
        try {
            KabarTerbaru::where('id',$id)->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Kabar Terbaru berhasil dihapus']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menghapus admin: ' . $e->getMessage()], 500);
        }
    }
}