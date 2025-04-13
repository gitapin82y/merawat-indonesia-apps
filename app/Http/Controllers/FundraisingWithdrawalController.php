<?php

namespace App\Http\Controllers;

use App\Models\FundraisingWithdrawal;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class FundraisingWithdrawalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Carbon::setLocale('id');
        if ($request->ajax()) {
            $query = FundraisingWithdrawal::with(['fundraising','user'])->get();
            
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('name', function ($row) {
                    return $row->user->name;
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
                    $whatsappUrl = "https://wa.me/". $row->user->phone;
                    
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
                          <a href="'.route('pencairan-fundraising.edit', $row->id).'" class="btn btn-info btn-sm"><i class="fa-solid fa-eye text-white"></i></a>
                        <button onclick="deletePencairanFundraising('.$row->id.')" class="btn btn-danger btn-sm">
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
        
        return view('super_admin.pencairan_fundraising.index');
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
    public function show(FundraisingWithdrawal $fundraisingWithdrawal)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FundraisingWithdrawal $pencairanFundraising)
    {
        return view('super_admin.pencairan_fundraising.detail', compact('pencairanFundraising'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FundraisingWithdrawal $fundraisingWithdrawal)
    {
        //
    }


    public function updateStatus(Request $request)
    {
        $fundraisingWithdrawal = FundraisingWithdrawal::find($request->id);
        
        if (!$fundraisingWithdrawal) {
            return response()->json(['success' => false, 'message' => 'Pencairan Fundraising tidak ditemukan']);
        }

        $fundraisingWithdrawal->status = $request->status;
        $fundraisingWithdrawal->save();

        return response()->json(['success' => true, 'message' => 'Status Pencairan Fundraising berhasil diperbarui']);
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FundraisingWithdrawal $pencairanFundraising)
    {
        DB::beginTransaction();
        try {
            // Menghapus data donasi
            $pencairanFundraising->delete();
    
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Pencairan Fundraising berhasil dihapus']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Gagal menghapus pencairan fundraising: ' . $e->getMessage()], 500);
        }
    }
}
