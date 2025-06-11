<?php

namespace App\Http\Controllers;

use App\Models\FundraisingWithdrawal;
use App\Models\Fundraising;
use App\Models\User;
use App\Mail\FundraisingWithdrawalMail;
use App\Mail\FundraisingStatusMail; // Renamed to avoid conflict
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class FundraisingWithdrawalController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Carbon::setLocale('id');
        if ($request->ajax()) {
            $query = FundraisingWithdrawal::with(['fundraising','user'])->orderBy('created_at', 'desc')->get();
            
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
                            <a href="'.route('pencairan-fundraising.approve', $row->id).'" class="btn btn-success btn-sm action-btn" title="Setujui">
                                <i class="fas fa-check"></i>
                            </a>
                            <a href="'.route('pencairan-fundraising.reject', $row->id).'" class="btn btn-warning text-white btn-sm action-btn" title="Tolak">
                                <i class="fas fa-times"></i>
                            </a>';
                    }

                    $actionBtn .= '
                        <a href="'.$whatsappUrl.'" target="_blank" class="btn btn-success btn-sm">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                          <a href="'.route('pencairan-fundraising.edit', $row->id).'" class="btn btn-info btn-sm"><i class="fa-solid fa-eye text-white"></i></a>
                        '; // Tutup div.btn-group

                    if ($row->bukti_pencairan) {
                        $actionBtn .= '
                            <a href="'.asset('storage/'.$row->bukti_pencairan).'" target="_blank" class="btn btn-primary text-white btn-sm">
                                <i class="fas fa-file"></i>
                            </a>';
                    }

                    $actionBtn .= '
                   <button onclick="deletePencairanFundraising('.$row->id.')" class="btn btn-danger btn-sm">
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
     * Show approve form
     */
    public function approve($id)
    {
        $withdrawal = FundraisingWithdrawal::findOrFail($id);
        
        // Check if status is already set
        if ($withdrawal->status != 'menunggu') {
            return redirect()->route('pencairan-fundraising.index')
                ->with('error', 'Status pencairan dana sudah diubah sebelumnya.');
        }
        
        return view('super_admin.pencairan_fundraising.approve', compact('withdrawal'));
    }
    
    /**
     * Show reject form
     */
    public function reject($id)
    {
        $withdrawal = FundraisingWithdrawal::findOrFail($id);
        
        // Check if status is already set
        if ($withdrawal->status != 'menunggu') {
            return redirect()->route('pencairan-fundraising.index')
                ->with('error', 'Status pencairan dana sudah diubah sebelumnya.');
        }
        
        return view('super_admin.pencairan_fundraising.reject', compact('withdrawal'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:100000',
            'payment_method' => 'required|string',
            'account_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->with('error', 'Validation error: ' . $validator->errors()->first())
                ->withInput();
        }

        $user = Auth::user();
        
        // Verifikasi jumlah komisi yang tersedia
        $fundraisings = Fundraising::where('user_id', $user->id)->get();
        $totalCommission = $fundraisings->sum('commission');
        
        if ($totalCommission < $request->amount) {
            return redirect()->back()->with('error', 'Jumlah yang diminta melebihi jumlah komisi yang tersedia.');
        }

        DB::beginTransaction();
        try {
            // Buat entri pencairan dana
            $withdrawal = FundraisingWithdrawal::create([
                'fundraising_id' => $fundraisings->first()->id, // Ambil fundraising pertama atau bisa di-update sesuai kebutuhan
                'user_id' => $user->id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'account_name' => $request->account_name,
                'account_number' => $request->account_number,
                'status' => 'menunggu',
            ]);
            
            $user = User::where('email', 'merawatindonesia2@gmail.com')->first();
            
            // Jika spesifik ke satu email
            Mail::to('merawatindonesia2@gmail.com')->send(new FundraisingWithdrawalMail($withdrawal));
            
            // Create system notification for admin
            $this->notificationService->createNotification(
                $user,
                'Permintaan Pencairan Fundraising Baru',
                'Permintaan pencairan dana fundraising baru dari ' . $user->name . ' sebesar Rp ' . number_format($request->amount, 0, ',', '.'),
                'fundraising_withdraw',
                ['withdrawal_id' => $withdrawal->id]
            );

            DB::commit();
            
            return redirect()->back()->with('success', 'Permintaan pencairan dana berhasil diajukan! Kami akan memproses dalam 1x24 jam.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
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

    /**
     * Update status of withdrawal request and send notifications
     */
    public function updateStatus(Request $request)
    {
        $fundraisingWithdrawal = FundraisingWithdrawal::find($request->id);
        
        if (!$fundraisingWithdrawal) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Pencairan Fundraising tidak ditemukan']);
            }
            return redirect()->route('pencairan-fundraising.index')
                ->with('error', 'Pencairan Fundraising tidak ditemukan');
        }

        $oldStatus = $fundraisingWithdrawal->status;
        $fundraisingWithdrawal->status = $request->status;

        // If approved and uploading bukti pencairan
        if ($request->hasFile('bukti_pencairan') && $request->status == 'disetujui') {
            $file = $request->file('bukti_pencairan');
            $path = $file->store('bukti_pencairan', 'public');
            $fundraisingWithdrawal->bukti_pencairan = $path;
        } else if ($request->status == 'disetujui' && !$fundraisingWithdrawal->bukti_pencairan && !$request->wantsJson()) {
            return redirect()->back()
                ->with('error', 'Bukti pencairan dana diperlukan untuk menyetujui pencairan dana.')
                ->withInput();
        }else if ($request->status == 'disetujui' && $fundraisingWithdrawal->bukti_pencairan) {
            // Jika sudah ada bukti pencairan sebelumnya
            $buktiPath = $fundraisingWithdrawal->bukti_pencairan;
        }
        
        // If rejected, save the reason
        if ($request->status == 'ditolak' && $request->has('rejection_reason')) {
            $fundraisingWithdrawal->rejection_reason = $request->rejection_reason;
        } else if ($request->status == 'ditolak' && !$request->has('rejection_reason') && !$request->wantsJson()) {
            return redirect()->back()
                ->with('error', 'Alasan penolakan diperlukan.')
                ->withInput();
        }

            if ($request->status == 'disetujui') {
                // Update fundraising commission
                $fundraising = $fundraisingWithdrawal->fundraising;
                
                // Pastikan commission tidak menjadi negatif
                if ($fundraising->commission < $fundraisingWithdrawal->amount) {
                    // Jika amount lebih besar dari commission yang tersedia
                    if ($request->wantsJson()) {
                        return response()->json(['success' => false, 'message' => 'Jumlah penarikan melebihi komisi yang tersedia']);
                    }
                    
                    return redirect()->back()
                        ->with('error', 'Jumlah penarikan (Rp ' . number_format($fundraisingWithdrawal->amount, 0, ',', '.') . ') melebihi komisi yang tersedia (Rp ' . number_format($fundraising->commission, 0, ',', '.') . ').')
                        ->withInput();
                }
                
                $fundraising->commission -= $fundraisingWithdrawal->amount;
                $fundraising->save();
            }

        $fundraisingWithdrawal->save();
        
        // Send notifications to user based on status
        if ($oldStatus != $request->status && $request->status != 'menunggu') {
            $user = $fundraisingWithdrawal->user;
            $amount = number_format($fundraisingWithdrawal->amount, 0, ',', '.');
            
            // Create notification title and message based on status
            $title = '';
            $message = '';
            $additionalData = [
                'withdrawal_id' => $fundraisingWithdrawal->id, 
                'status' => $request->status
            ];
            
            if ($request->status == 'disetujui') {
                $title = 'Permintaan Pencairan Dana Disetujui';
                $message = "Permintaan pencairan dana sebesar Rp {$amount} telah disetujui. Dana akan ditransfer ke rekening Anda dalam waktu 1x24 jam.";

                $buktiPath = $fundraisingWithdrawal->bukti_pencairan;

                if ($buktiPath) {
                    $additionalData['bukti_pencairan'] = $buktiPath;
            
                    // Kirim notifikasi dengan gambar
                    $this->notificationService->createNotification(
                        $user,
                        $title,
                        $message,
                        'fundraising_withdraw_update',
                        $additionalData,
                        $buktiPath // Tambahkan path gambar
                    );
                    
                    
                    // Kirim email dengan gambar bukti pencairan
                    $emailData = [
                        'withdrawal' => $fundraisingWithdrawal,
                        'bukti_pencairan_url' => url('storage/' . $buktiPath)
                    ];

                    Mail::to($user->email)->send(new FundraisingStatusMail($fundraisingWithdrawal, $emailData));
                } else {
                    // Kirim notifikasi tanpa gambar
                    $this->notificationService->createNotification(
                        $user,
                        $title,
                        $message,
                        'fundraising_withdraw_update',
                        $additionalData
                    );
                    
                    // Kirim email tanpa gambar
                    Mail::to($user->email)->send(new FundraisingStatusMail($fundraisingWithdrawal));
                }

            } elseif ($request->status == 'ditolak') {
                $title = 'Permintaan Pencairan Dana Ditolak';
                $message = "Permintaan pencairan dana sebesar Rp {$amount} ditolak.";
                
                if ($fundraisingWithdrawal->rejection_reason) {
                    $message .= " Alasan: " . $fundraisingWithdrawal->rejection_reason;
                    $additionalData['rejection_reason'] = $fundraisingWithdrawal->rejection_reason;
                } else {
                    $message .= " Silakan hubungi admin untuk informasi lebih lanjut.";
                }

                $this->notificationService->createNotification(
                    $user,
                    $title,
                    $message,
                    'fundraising_withdraw_update',
                    $additionalData
                );
                
                // Kirim email tanpa gambar
                Mail::to($user->email)->send(new FundraisingStatusMail($fundraisingWithdrawal));
            }
        }

        // Handle different response types based on request
        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Status Pencairan Fundraising berhasil diperbarui']);
        }
        
        // Redirect with success message if not an AJAX request
        return redirect()->route('pencairan-fundraising.index')
            ->with('success', 'Status Pencairan Fundraising berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FundraisingWithdrawal $pencairanFundraising)
    {
        DB::beginTransaction();
        try {

            $pencairanFundraising->delete();
    
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Pencairan Fundraising berhasil dihapus']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Gagal menghapus pencairan fundraising: ' . $e->getMessage()], 500);
        }
    }
}