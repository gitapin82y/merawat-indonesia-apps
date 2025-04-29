<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Campaign;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Mail\AdminApplicationMail;

use Illuminate\Support\Facades\Mail;
use App\Mail\AdminStatusMail;
use App\Services\NotificationService;

class AdminController extends Controller
{
    // Tambahkan property di class
protected $notificationService;

// Tambahkan di constructor
public function __construct(NotificationService $notificationService)
{
    $this->notificationService = $notificationService;
}


    public function index(Request $request)
    {
        $user = Auth::user();
        if($user->role !== 'super_admin'){
            return redirect('galang-dana');
        }
        Carbon::setLocale('id');
        if ($request->ajax()) {
            $query = Admin::with('user')->withCount('campaigns') // Hitung jumlah campaign
            ->withSum('campaigns', 'total_donatur') // Hitung total donasi dari semua campaign admin
            ->get();
            
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('action', function($row) {
                    $actionBtn = '<div class="btn-group" role="group">';
                    if($row->status == 'menunggu') {
                        $actionBtn .= '
                            <button onclick="changeStatus('.$row->id.', \'disetujui\')" class="btn btn-success btn-sm action-btn" title="Approve"><i class="fa-solid fa-check"></i></button>
                            <button onclick="changeStatus('.$row->id.', \'ditolak\')" class="btn btn-warning text-white btn-sm action-btn" title="Reject"><i class="fa-solid fa-times"></i></button>';
                    }
                    $whatsappUrl = "https://wa.me/". $row->phone;
    $actionBtn .= '
            <a href="'.$whatsappUrl.'" target="_blank" class="btn btn-success btn-sm">
                <i class="fab fa-whatsapp"></i>
            </a>
            <a href="/galang-dana/'.$row->name.'" class="btn btn-info btn-sm" target="_blank"><i class="fa-solid fa-eye text-white"></i></a>
            <a href="'.route('admin.edit', $row->id).'" class="btn btn-primary btn-sm"><i class="fa-solid fa-pen"></i></a>
            <button onclick="deleteAdmin('.$row->id.')" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>';
    
  
    
    $actionBtn .= '</div>';
    return $actionBtn;
                })
                ->addColumn('name', function($row) {
                    $statusColor = [
                        'menunggu' => 'warning',
                        'disetujui' => 'success', 
                        'ditolak' => 'danger'
                    ];
                    return $row->name . '<br> <span class="badge bg-'.$statusColor[$row->status].' text-white">'.$row->status.'</span>';
                })
                ->addColumn('total_statistik', function($row) {
                    return $row->campaigns_count . ' Kampanye ' . number_format($row->campaigns_sum_total_donatur, 0, ',', '.') . " Donatur";
                })
                ->addColumn('log_activity', function($row) {
                    return $row->log_activity 
                    ? Carbon::parse($row->log_activity)->timezone('Asia/Jakarta')->diffForHumans()
                    : '-';
                })
                ->rawColumns(['name','total_statistik', 'log_activity', 'action'])
                 ->make(true);
        }
        
        return view('super_admin.admin.index');
    }

    public function create()
    {
        $users = User::whereDoesntHave('admin')->get();
        return view('super_admin.admin.form', compact('users'));
    }

    public function store(Request $request)
    {
        $user = auth()->user(); // Ambil user yang sedang login
        $role = $user->role; // Misalnya role ada di dalam field 'role'

        $checkadmin = Admin::where('user_id',$user->id)->first();

        if($checkadmin && $role == 'donatur'){
            return redirect()->back()->with('toast', [
                'type' => 'error', 
                'message' => 'Sebelumnya anda telah mendaftar, tunggu validasi admin'
            ]);
        }
        // Sebelum menambahkan admin baru pastikan sudah register user biasa atau membuat akun user biasa, setelah itu ubah role/peran menjadi admin, dan baru anda bisa menambahkan admin/yayasan dengan memilih user atas nama akun yang bar usaja dibuat dengan peran akun admin
    
        // Validasi berdasarkan role
        $rules = [
            'name' => 'required|string|max:255',
            'phone' => 'required|regex:/^08[1-9][0-9]{7,10}$/|min:10|max:13',
            'email' => 'required|email|unique:admins,email',
            'leader_name' => 'required|string|max:255',
            'address' => 'required|string',
            'social' => 'nullable|array',
        ];
    
        if ($role === 'super_admin') {
            $rules['legality'] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048';
            $rules['thumbnail'] = 'nullable|file|mimes:jpg,jpeg,png|max:2048';
            $rules['avatar'] = 'nullable|file|mimes:jpg,jpeg,png|max:2048';
        } else {
            $rules['legality'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:2048';
            $rules['thumbnail'] = 'required|file|mimes:jpg,jpeg,png|max:2048';
            $rules['avatar'] = 'required|file|mimes:jpg,jpeg,png|max:2048';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $adminData = $request->except(['legality','avatar', 'thumbnail', 'social']);
            
            // Handle social media as JSON
            $adminData['social'] = json_encode($request->input('social', []));

            // Handle file uploads
            if ($request->hasFile('avatar')) {
                $adminData['avatar'] = $request->file('avatar')->store('admin_avatar', 'public');
            }
            
            if ($request->hasFile('thumbnail')) {
                $adminData['thumbnail'] = $request->file('thumbnail')->store('admin_thumbnail', 'public');
            }

            if ($request->hasFile('legality')) {
                $adminData['legality'] = $request->file('legality')->store('admin_legality', 'public');
            }
            if($role == 'super_admin'){
                $adminData['user_id'] = $request->user_id;
                if($request->status == 'disetujui'){
                    User::where('id', $request->user_id)->update(['role' => 'yayasan']);
                }
            }else{
                $adminData['user_id'] = $user->id;
            }
            $admin = Admin::create($adminData);

            DB::commit();

            if ($role === 'super_admin') {    
                return redirect()->back()
                ->with('success', 'Admin berhasil ditambahkan');
            } else {          
                // Kirim email ke alamat email tetap
                Mail::to('apin82y@gmail.com')->send(new AdminApplicationMail($admin, $user));
                    return redirect()->back()
                    ->with('success', 'Berhasil Mendaftar, Data sedang divalidasi');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal : ' . $e->getMessage())
                ->withInput();
        }
    }

    public function galangDana()
    {
        $user = Auth::user();
        if(Auth::check() && $user->role == "yayasan"){
            $admin = $user->admin;
            if(!$admin){
               return view('donatur.galang-dana.index');
            }
            $campaign = Campaign::with([
                'donations', 
                'kabarTerbaru', 
                'campaignWithdrawals'
            ])->where('admin_id', $admin->id)->get();

             // Hitung jumlah donatur (total donasi yang sudah dikonfirmasi)
    $totalDonaturs = $campaign->sum(function($campaign) {
        return $campaign->donations->where('status', 'sukses')->count();
    });
    
    // Hitung jumlah kabar terbaru
    $totalKabarTerbaru = $campaign->sum(function($campaign) {
        return $campaign->kabarTerbaru->count();
    });
    
    // Hitung jumlah pencairan dana
    // $totalPencairanDana = $campaign->sum(function($campaign) {
    //     return $campaign->campaignWithdrawals->where('status', 'disetujui')->count();
    // });

    $totalKampanye = $campaign->where('status', 'aktif')->count();
    
    // Hitung total doa
    $totalDoa = $campaign->sum(function($campaign) {
        return $campaign->donations->whereNotNull('doa')->count();
    });
    
    // Hitung total donasi terkumpul
    $totalDonasiTerkumpul = $campaign->sum(function($campaign) {
        return $campaign->donations->where('status', 'sukses')->sum('amount');
    });
    
    // Hitung total pencairan dana
    $totalPencairanDanaRupiah = $campaign->sum(function($campaign) {
        return $campaign->campaignWithdrawals->where('status', 'disetujui')->sum('amount');
    });
    

            // Prepare campaigns grouped by status
            $campaignsByStatus = [
                'semua' => $campaign,
                'aktif' => $campaign->where('status', 'aktif'),
                'berakhir' => $campaign->where('status', 'berakhir'),
                'validasi' => $campaign->where('status', 'validasi'),
                'ditolak' => $campaign->where('status', 'ditolak')
            ];
            return view('admin.profile', compact(
        'admin',
        'campaignsByStatus',
        'campaign', 
        'totalDonaturs', 
        'totalKabarTerbaru', 
        'totalKampanye', 
        'totalDoa',
        'totalDonasiTerkumpul',
        'totalPencairanDanaRupiah'
    ));
        }else{
            return view('donatur.galang-dana.index');
        }
    }

    public function profileGalangDana($name)
    {
        $admin = Admin::where('name',$name)->first();

            $campaign = Campaign::with([
                'donations', 
                'kabarTerbaru', 
                'campaignWithdrawals'
            ])->where('admin_id', $admin->id)->get();

             // Hitung jumlah donatur (total donasi yang sudah dikonfirmasi)
    $totalDonaturs = $campaign->sum(function($campaign) {
        return $campaign->donations->where('status', 'sukses')->count();
    });

    // Hitung jumlah kabar terbaru
    $totalKabarTerbaru = $campaign->sum(function($campaign) {
        return $campaign->kabarTerbaru->count();
    });
    
    // Hitung jumlah total kampanye
    $totalKampanye = $campaign->where('status', 'aktif')->count();
    
    // Hitung total doa
    $totalDoa = $campaign->sum(function($campaign) {
        return $campaign->donations->whereNotNull('doa')->count();
    });
    
    // Hitung total donasi terkumpul
    $totalDonasiTerkumpul = $campaign->sum(function($campaign) {
        return $campaign->donations->where('status', 'sukses')->sum('amount');
    });
    
    // Hitung total pencairan dana
    $totalPencairanDanaRupiah = $campaign->sum(function($campaign) {
        return $campaign->campaignWithdrawals->where('status', 'disetujui')->sum('amount');
    });
    

            // Prepare campaigns grouped by status
            $campaignsByStatus = [
                'semua' => $campaign,
                'aktif' => $campaign->where('status', 'aktif'),
                'berakhir' => $campaign->where('status', 'berakhir'),
                'validasi' => $campaign->where('status', 'validasi'),
                'ditolak' => $campaign->where('status', 'ditolak')
            ];
            return view('donatur.profile-galang-dana', compact(
        'admin',
        'campaignsByStatus',
        'campaign', 
        'totalDonaturs', 
        'totalKabarTerbaru', 
        'totalKampanye', 
        'totalDoa',
        'totalDonasiTerkumpul',
        'totalPencairanDanaRupiah'
    ));
    }

    public function edit(Admin $admin)
    {
        $users = User::whereDoesntHave('admin')->orWhere('id', $admin->user_id)->get();
        return view('super_admin.admin.form', compact('admin', 'users'));
    }

    public function update(Request $request, Admin $admin)
    {

        $user = auth()->user(); // Ambil user yang sedang login
        $role = $user->role; // Misalnya role ada di dalam field 'role'

        // Validasi berdasarkan role
        $rules = [
            'user_id' => 'required|exists:users,id|unique:admins,user_id,'.$admin->id,
            'name' => 'required|string|max:255',
            'phone' => 'required|regex:/^08[1-9][0-9]{7,10}$/|min:10|max:13',
            'leader_name' => 'required|string|max:255',
            'legality' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'avatar' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'thumbnail' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'social' => 'nullable|array',
        ];
    
        if ($role === 'super_admin') {
            $rules['address'] = 'required|string';
            $rules['status'] = 'required|in:menunggu,disetujui,ditolak';
            $rules['email'] = 'required|email|unique:admins,email,'.$admin->id;
            $rules['bio'] = 'nullable|max:255';
        } else {
            $rules['address'] = 'nullable';
            $rules['status'] = 'nullable';
            $rules['bio'] = 'nullable|max:255';
            $rules['email'] = 'nullable';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $adminData = $request->except(['legality','avatar', 'thumbnail', 'social']);
            
        // Proses social media: pastikan jika tidak diisi, set menjadi null
        $socialData = $request->input('social', []);
        
        $socialData = array_filter($socialData, function ($item) {
            return !empty($item); // Hanya sisakan yang tidak kosong
        });

        // Simpan social media dalam bentuk array (bukan JSON-encoded string)
        $adminData['social'] = $socialData;  // Tidak perlu json_encode lagi


            // Handle file uploads
            if ($request->hasFile('avatar')) {
                // Delete old avatar
                if ($admin->avatar && $admin->avatar != 'default/default-avatar.png') {
                    Storage::disk('public')->delete($admin->avatar);
                }
                $adminData['avatar'] = $request->file('avatar')->store('admin_avatar', 'public');
            }
            
            if ($request->hasFile('thumbnail')) {
                // Delete old thumbnail
                if ($admin->thumbnail) {
                    Storage::disk('public')->delete($admin->thumbnail);
                }
                $adminData['thumbnail'] = $request->file('thumbnail')->store('admin_thumbnail', 'public');
            }

            if ($request->hasFile('legality')) {
                // Delete old legality
                if ($admin->legality) {
                    Storage::disk('public')->delete($admin->legality);
                }
                $adminData['legality'] = $request->file('legality')->store('admin_legality', 'public');
            }

            if (isset($adminData['status']) && $adminData['status'] === 'disetujui') {
                $user = User::findOrFail($user->id);
                $user->role = 'yayasan';
                $user->save();
            }
            
            if (isset($adminData['status']) && $adminData['status'] === 'ditolak') {
                $user = User::findOrFail($user->id);
                $user->role = 'donatur';
                $user->save();
            }
            

            $admin->update($adminData);

            DB::commit();
            return redirect()->back()
                ->with('success', 'Data berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal memperbarui data: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(Admin $admin)
    {
        $admin->social = json_decode($admin->social, true) ?? [];
        return view('admin.show', compact('admin'));
    }

    public function destroy(Admin $admin)
    {
        DB::beginTransaction();
        try {
            if ($admin->avatar && $admin->avatar != 'default/default-avatar.png') {
                Storage::disk('public')->delete($admin->avatar);
            }
            
            if ($admin->thumbnail) {
                Storage::disk('public')->delete($admin->thumbnail);
            }

            $admin->delete();

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Admin berhasil dihapus']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Gagal menghapus admin: ' . $e->getMessage()], 500);
        }
    }

    public function changeStatus(Admin $admin, Request $request)
{
    $validator = Validator::make($request->all(), [
        'status' => 'required|in:menunggu,disetujui,ditolak'
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 400);
    }

    // Simpan status lama untuk pengecekan
    $oldStatus = $admin->status;
    
    DB::beginTransaction();
    try {
        // Update status admin dan log_activity
        $admin->update([
            'status' => $request->status,
            'log_activity' => now() // Update log_activity dengan waktu saat ini
        ]);
        
        // Update role user jika status berubah
        if ($admin->user_id) {
            $user = User::find($admin->user_id);
            if ($user) {
                if ($request->status === 'disetujui') {
                    $user->role = 'yayasan';
                    $user->save();
                } elseif ($request->status === 'ditolak') {
                    $user->role = 'donatur';
                    $user->save();
                }
            }
        }
        
        // Kirim email notifikasi jika status berubah ke disetujui atau ditolak
        if (($request->status === 'disetujui' || $request->status === 'ditolak') && $oldStatus !== $request->status) {
            try {
                // Kirim email
                Mail::to($admin->email)->send(new AdminStatusMail($admin, $request->status));
                
                // Buat notifikasi sistem jika admin memiliki user_id
                if ($admin->user_id) {
                    $user = User::find($admin->user_id);
                    if ($user) {
                        $status = $request->status === 'disetujui' ? 'disetujui' : 'ditolak';
                        $message = 'Pendaftaran admin "' . $admin->name . '" telah ' . $status . '.';
                        
                        $this->notificationService->createNotification(
                            $user,
                            'Status Admin ' . ucfirst($status),
                            $message,
                            'admin_status_update',
                            ['admin_id' => $admin->id, 'status' => $request->status]
                        );
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Gagal mengirim notifikasi status admin: ' . $e->getMessage());
                // Continue even if notification fails
            }
        }
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => 'Status admin berhasil diubah menjadi ' . $request->status,
            'new_status' => $request->status
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Gagal mengubah status: ' . $e->getMessage()
        ], 500);
    }
}

}