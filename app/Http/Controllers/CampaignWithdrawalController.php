<?php

namespace App\Http\Controllers;

use App\Models\CampaignWithdrawal;
use App\Models\Campaign;
use App\Models\KabarPencairan;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class CampaignWithdrawalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Carbon::setLocale('id');
        if ($request->ajax()) {
            $query = CampaignWithdrawal::with(['campaign','admin'])->get();
            
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('name', function ($row) {
                    return $row->admin->name;
                })    
                ->addColumn('amount', function($row) {
                    return 'Rp ' . number_format($row->amount, 0, ',', '.');
                })  
                ->addColumn('status', function($row) {
                    $statusColor = [
                        'menunggu' => 'warning',
                        'disetujui' => 'success', 
                        'ditolak' => 'danger'
                    ];
                    return '<span class="badge bg-'.$statusColor[$row->status].' text-white">'.$row->status.'</span>';
                })              
                ->addColumn('action', function($row) {
                    $whatsappUrl = "https://wa.me/". $row->admin->phone;
                    
                    $actionBtn = '<div class="btn-group" role="group">';
                    
                    if ($row->status == 'menunggu') {
                        $actionBtn .= '
                            <button onclick="updateStatus('.$row->id.', \'disetujui\')" class="btn btn-success btn-sm">
                                <i class="fas fa-check"></i>
                            </button>
                            <button onclick="updateStatus('.$row->id.', \'ditolak\')" class="btn btn-warning text-white btn-sm">
                                <i class="fas fa-times"></i>
                            </button>';
                    }

                    $actionBtn .= '
                        <a href="'.$whatsappUrl.'" target="_blank" class="btn btn-success btn-sm">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                          <a href="'.route('pencairan-kampanye.edit', $row->id).'" class="btn btn-info btn-sm"><i class="fa-solid fa-eye text-white"></i></a>
                        <button onclick="deletePencairanKampanye('.$row->id.')" class="btn btn-danger btn-sm">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>'; // Tutup div.btn-group
                
                    return $actionBtn;
                })        
                ->addColumn('created_at', function ($row) {
                    return $row->created_at 
                        ? Carbon::parse($row->created_at)->timezone('Asia/Jakarta')->format('d M Y')
                        : '-';
                })    
                ->rawColumns(['name','amount','status','created_at','action'])
                 ->make(true);
        }
        
        return view('super_admin.pencairan_kampanye.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(CampaignWithdrawal $campaignWithdrawal)
    {
        //
    }

    public function edit(CampaignWithdrawal $pencairanKampanye)
    {
        return view('super_admin.pencairan_kampanye.detail', compact('pencairanKampanye'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateStatus(Request $request)
    {
        $kampanyeWithdrawal = CampaignWithdrawal::find($request->id);
        
        if (!$kampanyeWithdrawal) {
            return response()->json(['success' => false, 'message' => 'Pencairan Kampanye tidak ditemukan']);
        }

        if($request->status == "disetujui"){
            $campaign = Campaign::where('id',$kampanyeWithdrawal->campaign_id)->first();
            $campaign->current_donation -= $kampanyeWithdrawal->amount;
            $campaign->jumlah_pencairan_dana += $kampanyeWithdrawal->amount;
            $campaign->save();

            $kabarPencairan = KabarPencairan::where('document_rab',$kampanyeWithdrawal->document_rab)->first();
            $kabarPencairan->status = 'disetujui';
            $kabarPencairan->save();
        }

        $kampanyeWithdrawal->status = $request->status;
        $kampanyeWithdrawal->save();

        return response()->json(['success' => true, 'message' => 'Status Pencairan Kampanye berhasil diperbarui']);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CampaignWithdrawal $pencairanKampanye)
    {
        DB::beginTransaction();
        try {
            // Menghapus data donasi
            $pencairanKampanye->delete();
    
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Pencairan Kampanye berhasil dihapus']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Gagal menghapus pencairan kampanye: ' . $e->getMessage()], 500);
        }
    }
}
