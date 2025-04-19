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
            $query = PrioritasCampaign::with(['campaign', 'campaign.category', 'campaign.admin'])
                    ->orderBy('prioritas', 'asc')
                    ->get();
    
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('title', function($row) {
                    return $row->campaign?->title ?? 'N/A'; 
                })
                ->addColumn('category', function($row) {
                    return $row->campaign?->category?->name ?? 'N/A';
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
        // Get campaigns that are not already prioritized
        $usedCampaignIds = PrioritasCampaign::pluck('campaign_id')->toArray();
        $campaigns = Campaign::whereNotIn('id', $usedCampaignIds)->get();
        
        // Get list of used priorities
        $usedPriorities = PrioritasCampaign::pluck('prioritas')->toArray();
        
        return view('super_admin.prioritas_kampanye.form', compact('campaigns', 'usedPriorities'));
    }    

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'campaign_id' => 'required|exists:campaigns,id|unique:prioritas_campaigns,campaign_id',
            'prioritas' => 'required|numeric|unique:prioritas_campaigns,prioritas',
        ], [
            'campaign_id.unique' => 'Kampanye ini sudah ada dalam daftar prioritas.',
            'prioritas.unique' => 'Nomor prioritas ini sudah digunakan, silakan pilih nomor lain.'
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
    
        DB::beginTransaction();
        try {
            // Create new prioritas campaign
            PrioritasCampaign::create([
                'campaign_id' => $request->campaign_id,
                'prioritas' => $request->prioritas
            ]);
    
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
        // Get campaigns that are not already prioritized (except the current one)
        $usedCampaignIds = PrioritasCampaign::where('id', '!=', $prioritasKampanye->id)
            ->pluck('campaign_id')
            ->toArray();
        
        $campaigns = Campaign::whereNotIn('id', $usedCampaignIds)
            ->orWhere('id', $prioritasKampanye->campaign_id)
            ->get();
        
        // Get list of used priorities (except the current one)
        $usedPriorities = PrioritasCampaign::where('id', '!=', $prioritasKampanye->id)
            ->pluck('prioritas')
            ->toArray();
        
        return view('super_admin.prioritas_kampanye.form', compact('campaigns', 'prioritasKampanye', 'usedPriorities'));
    }

    public function update(Request $request, PrioritasCampaign $prioritasKampanye)
    {
        $validator = Validator::make($request->all(), [
            'campaign_id' => 'required|exists:campaigns,id|unique:prioritas_campaigns,campaign_id,'.$prioritasKampanye->id,
            'prioritas' => 'required|numeric|unique:prioritas_campaigns,prioritas,'.$prioritasKampanye->id,
        ], [
            'campaign_id.unique' => 'Kampanye ini sudah ada dalam daftar prioritas.',
            'prioritas.unique' => 'Nomor prioritas ini sudah digunakan, silakan pilih nomor lain.'
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
    
        DB::beginTransaction();
        try {
            $prioritasKampanye->update([
                'campaign_id' => $request->campaign_id,
                'prioritas' => $request->prioritas
            ]);
    
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

    public function destroy(PrioritasCampaign $prioritasKampanye)
    {
        DB::beginTransaction();
        try {
            $prioritasKampanye->delete();

            DB::commit();
            return response()->json([
                'status' => 'success', 
                'message' => 'Kampanye berhasil dihapus dari daftar prioritas'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error', 
                'message' => 'Gagal menghapus prioritas kampanye: ' . $e->getMessage()
            ], 500);
        }
    }
}