<?php

namespace App\Http\Controllers;

use App\Models\CampaignWithdrawal;
use App\Models\Campaign;
use App\Models\KabarPencairan;
use App\Models\User;
use App\Mail\CampaignWithdrawalMail;
use App\Mail\CampaignStatusMail;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class CampaignWithdrawalController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
   
    public function index(Request $request)
    {
        Carbon::setLocale('id');
        if ($request->ajax()) {
            $query = CampaignWithdrawal::with(['campaign','admin'])->orderBy('created_at', 'desc')->get();
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('name', function ($row) {
                    return $row->campaign->title;
                })    
                ->addColumn('payment_method', function ($row) {
                    return $row->payment_method. " a/n ".$row->account_name;
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
                            <a href="'.route('pencairan-kampanye.approve', $row->id).'" class="btn btn-success btn-sm action-btn" title="Setujui">
                                <i class="fas fa-check"></i>
                            </a>
                            <a href="'.route('pencairan-kampanye.reject', $row->id).'" class="btn btn-warning text-white btn-sm action-btn" title="Tolak">
                                <i class="fas fa-times"></i>
                            </a>';
                    }

                    $actionBtn .= '
                        <a href="'.$whatsappUrl.'" target="_blank" class="btn btn-success btn-sm">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="'.route('pencairan-kampanye.edit', $row->id).'" class="btn btn-info btn-sm"><i class="fa-solid fa-eye text-white"></i></a>'; 

                    if ($row->bukti_pencairan) {
                        $actionBtn .= '
                            <a href="'.asset('storage/'.$row->bukti_pencairan).'" target="_blank" class="btn btn-primary text-white btn-sm">
                                <i class="fas fa-file"></i>
                            </a>';
                    }

                    $actionBtn .= '<button onclick="deletePencairanKampanye('.$row->id.')" class="btn btn-danger btn-sm">
                            <i class="fa-solid fa-trash"></i>
                        </button>';



                    $actionBtn .= '</div>';
                
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

public function create()
{
    // Get campaigns yang ada saldo donasi
    $campaigns = Campaign::with('admin')
        ->whereRaw('current_donation > 0')
        ->whereIn('status', ['aktif', 'selesai'])
        ->orderBy('created_at', 'desc')
        ->get();
    
    return view('super_admin.pencairan_kampanye.create', compact('campaigns'));
}

  /**
     * Store a newly created resource - Super Admin dapat langsung approve
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'admin_id' => 'required|exists:admins,id',
            'campaign_id' => 'required|exists:campaigns,id',
            'payment_method' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'document_rab' => 'required|mimes:pdf,doc,docx,xls,xlsx|max:5120',
            'bukti_pencairan' => 'required|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $campaign = Campaign::findOrFail($request->campaign_id);

        // Validasi amount tidak melebihi saldo
        if ($request->amount > $campaign->current_donation_real) {
            return redirect()->back()
                ->with('error', 'Jumlah pencairan tidak boleh melebihi saldo kampanye (Rp ' . number_format($campaign->current_donation_real, 0, ',', '.') . ')')
                ->withInput();
        }
    
        DB::beginTransaction();
        try {
            // Upload document RAB
            $docRabPath = $request->file('document_rab')->store('campaign_documents', 'public');
            
            // Upload bukti pencairan
            $buktiPath = $request->file('bukti_pencairan')->store('bukti_pencairan', 'public');

            // Create withdrawal dengan status langsung disetujui
            $withdrawal = CampaignWithdrawal::create([
                'campaign_id' => $request->campaign_id,
                'admin_id' => $request->admin_id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'account_number' => $request->account_number,
                'account_name' => $request->account_name,
                'document_rab' => $docRabPath,
                'bukti_pencairan' => $buktiPath,
                'status' => 'disetujui', // Langsung disetujui oleh super admin
            ]);

            // Update campaign - kurangi current_donation dan tambah jumlah_pencairan_dana
            $campaign->current_donation -= $request->amount;
            $campaign->jumlah_pencairan_dana += $request->amount;
            $campaign->save();

            // Create kabar pencairan dengan status disetujui
            KabarPencairan::create([
                'campaign_id' => $request->campaign_id,
                'title' => 'Pencairan Dana Rp ' . number_format($request->amount, 0, ',', '.'),
                'description' => 'Ke Rekening Bank ' . strtoupper($request->payment_method) . ' *** **** **** **** ' . substr($request->account_number, -4) . ' a/n ' . $request->account_name,
                'total_amount' => $request->amount,
                'document_rab' => $docRabPath,
                'status' => 'disetujui', // Langsung disetujui
            ]);

            // Kirim notifikasi ke admin yayasan
            $admin = $campaign->admin;
            if ($admin && $admin->user) {
                $formattedAmount = number_format($request->amount, 0, ',', '.');
                
                $this->notificationService->createNotification(
                    $admin->user,
                    'Pencairan Dana Kampanye Disetujui',
                    'Pencairan dana kampanye "' . $campaign->title . '" sebesar Rp ' . $formattedAmount . ' telah disetujui oleh super admin.',
                    'campaign_withdraw_approved',
                    [
                        'campaign_id' => $campaign->id,
                        'campaign_title' => $campaign->title,
                        'withdrawal_id' => $withdrawal->id,
                        'amount' => $request->amount,
                        'bukti_pencairan' => $buktiPath
                    ]
                );
                
                // Kirim email notifikasi
                Mail::to($admin->email)->queue(new CampaignStatusMail($withdrawal, [
                    'status' => 'disetujui',
                    'bukti_pencairan' => $buktiPath
                ]));
            }

            DB::commit();
            return redirect()->route('pencairan-kampanye.index')
                ->with('success', 'Pencairan dana berhasil ditambahkan dan disetujui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menambahkan pencairan dana: ' . $e->getMessage())
                ->withInput();
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(CampaignWithdrawal $campaignWithdrawal)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CampaignWithdrawal $pencairanKampanye)
    {
        return view('super_admin.pencairan_kampanye.detail', compact('pencairanKampanye'));
    }

    public function updateStatus(Request $request)
{
    $kampanyeWithdrawal = CampaignWithdrawal::find($request->id);
    
    if (!$kampanyeWithdrawal) {
        if ($request->wantsJson()) {
            return response()->json(['success' => false, 'message' => 'Pencairan Kampanye tidak ditemukan']);
        }
        return redirect()->route('pencairan-kampanye.index')
            ->with('error', 'Pencairan Kampanye tidak ditemukan');
    }

    $oldStatus = $kampanyeWithdrawal->status;
    $kampanyeWithdrawal->status = $request->status;
    
    // Variabel untuk menyimpan path bukti pencairan
    $buktiPath = null;

    // If approved and uploading bukti pencairan
    if ($request->hasFile('bukti_pencairan') && $request->status == 'disetujui') {
        $file = $request->file('bukti_pencairan');
        $buktiPath = $file->store('bukti_pencairan', 'public');
        $kampanyeWithdrawal->bukti_pencairan = $buktiPath;
    } else if ($request->status == 'disetujui' && !$kampanyeWithdrawal->bukti_pencairan && !$request->wantsJson()) {
        return redirect()->back()
            ->with('error', 'Bukti pencairan dana diperlukan untuk menyetujui pencairan dana.')
            ->withInput();
    } else if ($request->status == 'disetujui' && $kampanyeWithdrawal->bukti_pencairan) {
        // Jika sudah ada bukti pencairan sebelumnya
        $buktiPath = $kampanyeWithdrawal->bukti_pencairan;
    }
    
    // If rejected, save the reason
    if ($request->status == 'ditolak' && $request->has('rejection_reason')) {
        $kampanyeWithdrawal->rejection_reason = $request->rejection_reason;
    } else if ($request->status == 'ditolak' && !$request->has('rejection_reason') && !$request->wantsJson()) {
        return redirect()->back()
            ->with('error', 'Alasan penolakan diperlukan.')
            ->withInput();
    }

    // If approved, update campaign donation and record approval details
    if ($request->status == 'disetujui') {        
        // Update campaign donation
        $campaign = Campaign::where('id', $kampanyeWithdrawal->campaign_id)->first();

        
        $campaign->current_donation -= $kampanyeWithdrawal->amount;
        $campaign->jumlah_pencairan_dana += $kampanyeWithdrawal->amount;
        $campaign->save();

        // Update kabar pencairan status
        $kabarPencairan = KabarPencairan::where('document_rab', $kampanyeWithdrawal->document_rab)->first();
        if ($kabarPencairan) {
            $kabarPencairan->status = 'disetujui';
            $kabarPencairan->save();
        }
    }

    $kampanyeWithdrawal->save();
    
    // Send notifications to admin based on status
    if ($oldStatus != $request->status && $request->status != 'menunggu') {
        // Get the user related to admin
        $adminUser = $kampanyeWithdrawal->admin->user;
        $amount = number_format($kampanyeWithdrawal->amount, 0, ',', '.');
        
        // Create notification title and message based on status
        $title = '';
        $message = '';
        $additionalData = [
            'withdrawal_id' => $kampanyeWithdrawal->id, 
            'status' => $request->status,
            'campaign_id' => $kampanyeWithdrawal->campaign_id,
            'campaign_title' => $kampanyeWithdrawal->campaign->title
        ];
        
        if ($request->status == 'disetujui') {
            $title = 'Pencairan Dana Kampanye Disetujui';
            $message = "Pencairan dana kampanye '{$kampanyeWithdrawal->campaign->title}' sebesar Rp {$amount} telah disetujui. Dana akan ditransfer ke rekening Anda dalam waktu 1x24 jam.";
            
            if ($buktiPath) {
                $additionalData['bukti_pencairan'] = $buktiPath;
                
                // Kirim notifikasi dengan gambar
                $this->notificationService->createNotification(
                    $adminUser,
                    $title,
                    $message,
                    'campaign_withdraw_update',
                    $additionalData,
                    $buktiPath // Tambahkan path gambar
                );
                
                // Kirim email dengan gambar bukti pencairan
                $emailData = [
                    'withdrawal' => $kampanyeWithdrawal,
                    'bukti_pencairan_url' => url('storage/' . $buktiPath)
                ];
                Mail::to($adminUser->email)->queue(new CampaignStatusMail($kampanyeWithdrawal, $emailData));
            } else {
                // Kirim notifikasi tanpa gambar
                $this->notificationService->createNotification(
                    $adminUser,
                    $title,
                    $message,
                    'campaign_withdraw_update',
                    $additionalData
                );
                
                // Kirim email tanpa gambar
                Mail::to($adminUser->email)->queue(new CampaignStatusMail($kampanyeWithdrawal));
            }
        } elseif ($request->status == 'ditolak') {
            $title = 'Pencairan Dana Kampanye Ditolak';
            $message = "Pencairan dana kampanye '{$kampanyeWithdrawal->campaign->title}' sebesar Rp {$amount} ditolak.";
            
            if ($kampanyeWithdrawal->rejection_reason) {
                $message .= " Alasan: " . $kampanyeWithdrawal->rejection_reason;
                $additionalData['rejection_reason'] = $kampanyeWithdrawal->rejection_reason;
            } else {
                $message .= " Silakan hubungi admin untuk informasi lebih lanjut.";
            }
            
            // Kirim notifikasi penolakan
            $this->notificationService->createNotification(
                $adminUser,
                $title,
                $message,
                'campaign_withdraw_update',
                $additionalData
            );
            
            // Kirim email penolakan
            Mail::to($adminUser->email)->queue(new CampaignStatusMail($kampanyeWithdrawal));
        }
    }

    // Handle different response types based on request
    if ($request->wantsJson()) {
        return response()->json(['success' => true, 'message' => 'Status Pencairan Kampanye berhasil diperbarui']);
    }
    
    // Redirect with success message if not an AJAX request
    return redirect()->route('pencairan-kampanye.index')
        ->with('success', 'Status Pencairan Kampanye berhasil diperbarui');
}


    /**
     * Update the specified resource in storage.
     */
    // public function updateStatus(Request $request)
    // {
    //     $kampanyeWithdrawal = CampaignWithdrawal::find($request->id);
        
    //     if (!$kampanyeWithdrawal) {
    //         return response()->json(['success' => false, 'message' => 'Pencairan Kampanye tidak ditemukan']);
    //     }

    //     if($request->status == "disetujui"){
    //         $campaign = Campaign::where('id',$kampanyeWithdrawal->campaign_id)->first();
    //         $campaign->current_donation -= $kampanyeWithdrawal->amount;
    //         $campaign->jumlah_pencairan_dana += $kampanyeWithdrawal->amount;
    //         $campaign->save();

    //         $kabarPencairan = KabarPencairan::where('document_rab',$kampanyeWithdrawal->document_rab)->first();
    //         $kabarPencairan->status = 'disetujui';
    //         $kabarPencairan->save();
    //     }

    //     $kampanyeWithdrawal->status = $request->status;
    //     $kampanyeWithdrawal->save();

    //     return response()->json(['success' => true, 'message' => 'Status Pencairan Kampanye berhasil diperbarui']);
    // }
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

     /**
     * Show approve form
     */
    public function approve($id)
    {
        $withdrawal = CampaignWithdrawal::findOrFail($id);
        
        // Check if status is already set
        if ($withdrawal->status != 'menunggu') {
            return redirect()->route('pencairan-kampanye.index')
                ->with('error', 'Status pencairan dana sudah diubah sebelumnya.');
        }
        
        return view('super_admin.pencairan_kampanye.approve', compact('withdrawal'));
    }
    
    /**
     * Show reject form
     */
    public function reject($id)
    {
        $withdrawal = CampaignWithdrawal::findOrFail($id);
        
        // Check if status is already set
        if ($withdrawal->status != 'menunggu') {
            return redirect()->route('pencairan-kampanye.index')
                ->with('error', 'Status pencairan dana sudah diubah sebelumnya.');
        }
        
        return view('super_admin.pencairan_kampanye.reject', compact('withdrawal'));
    }
    
}
