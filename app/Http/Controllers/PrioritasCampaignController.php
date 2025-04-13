<?php

namespace App\Http\Controllers;

use App\Models\PrioritasCampaign;
use Illuminate\Http\Request;
use App\Models\Campaign;
use App\Models\Admin;
use App\Models\Category;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PrioritasCampaignController extends Controller
{
    public function index(Request $request)
    {
        Carbon::setLocale('id');
    
        if ($request->ajax()) {
            $query = PrioritasCampaign::with(['campaign', 'campaign.category', 'campaign.admin'])->get();
    
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('title', function($row) {
                    return $row->campaign?->title ?? 'N/A'; // Pastikan title diambil dari campaign
                })
                ->addColumn('category', function($row) {
                    return $row->campaign?->category?->name ?? 'N/A'; // Pastikan category tidak null
                })
                ->addColumn('total_donatur', function($row) {
                    return $row->campaign?->total_donatur.' Donatur';
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
                            <a href="/kampanye/'.$title.'" class="btn btn-info btn-sm"><i class="fa-solid fa-eye text-white"></i></a>
                            <a href="'.route('prioritas-kampanye.edit', $row->id).'" class="btn btn-primary btn-sm"><i class="fa-solid fa-pen"></i></a>
                            <button onclick="deletePrioritas('.$row->id.')" class="btn btn-warning btn-sm"><i class="fa-solid text-white fa-xmark"></i></button>
                        </div>
                    ';
                })
                ->addColumn('prioritas', function($row) {
                    return '<span class="badge bg-primary text-white">'.$row->prioritas.'</span>';
                })
                ->rawColumns(['prioritas','category', 'status', 'admin', 'action'])
                ->make(true);
        }
    
        return view('super_admin.prioritas_kampanye.index');
    }
    
    public function create()
    {
        $campaigns = Campaign::get();
        return view('super_admin.prioritas_kampanye.form', compact('campaigns'));
    }    

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'campaign_id' => 'required|exists:campaigns,id',
            'prioritas' => 'required|numeric',
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
    
        DB::beginTransaction();
        try {

            $prioritasKampanye = Campaign::create($request->all());
    
            DB::commit();
            return redirect()->route('prioritas-kampanye.index')
                ->with('success', 'Prioritas Kampanye berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menambahkan prioritas kampanye: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    public function edit(PrioritasCampaign $prioritasKampanye)
    {
        $campaigns = Campaign::get();
        return view('super_admin.prioritas_kampanye.form', compact('campaigns','prioritasKampanye'));
    }

    public function update(Request $request, PrioritasCampaign $prioritasKampanye)
    {
        $validator = Validator::make($request->all(), [
            'campaign_id' => 'required|exists:campaigns,id',
            'prioritas' => 'required|numeric',
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
    
        DB::beginTransaction();
        try {
           
            $prioritasKampanye->update($request->all());
    
            DB::commit();
            return redirect()->route('prioritas-kampanye.index')
                ->with('success', 'Prioritas Kampanye berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal memperbarui prioritas kampanye: ' . $e->getMessage())
                ->withInput();
        }
    }

    // public function show(PrioritasCampaign $prioritasKampanye)
    // {
    //     return view('admin.show', compact('admin'));
    // }

    public function destroy(PrioritasCampaign $prioritasKampanye)
    {
        DB::beginTransaction();
        try {
            $prioritasKampanye->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Campaign berhasil dihapus']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menghapus admin: ' . $e->getMessage()], 500);
        }
    }
}