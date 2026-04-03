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
use Illuminate\Support\Facades\Mail;
use App\Mail\CampaignStatusMail;
use App\Mail\CampaignStatusUpdateMail;
use App\Mail\NewCampaignNotificationMail;
use App\Services\NotificationService;
use App\Models\Commission;


class CampaignController extends Controller
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
            $query = Campaign::with(['admin','category'])->orderBy('created_at', 'desc')->get();
            
            return DataTables::of($query)
                ->addIndexColumn()
                // ->addColumn('action', function($row) {
                //     $actionBtn = '<div class="btn-group" role="group">';
                    
                //     // Add approval/rejection buttons for campaigns in validation status
                //     if($row->status === 'validasi') {
                //         $actionBtn .= '
                //             <button onclick="changeStatus('.$row->id.', \'disetujui\')" class="btn btn-success btn-sm action-btn" title="Approve"><i class="fa-solid fa-check"></i></button>
                //             <button onclick="changeStatus('.$row->id.', \'ditolak\')" class="btn btn-warning text-white btn-sm action-btn" title="Reject"><i class="fa-solid fa-times"></i></button>';
                //     }
                    
                //     $actionBtn .= '
                //         <a href="/kampanye/'.$row->slug.'" class="btn btn-info btn-sm"><i class="fa-solid fa-eye text-white"></i></a>
                //         <a href="'.route('kampanye.edit', $row->id).'" class="btn btn-primary btn-sm"><i class="fa-solid fa-pen"></i></a>
                //         <button onclick="deleteCampaign('.$row->id.')" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
                //     </div>';
                    
                //     return $actionBtn;
                // })

                ->addColumn('action', function($row) {
    $actionBtn = '<div class="btn-group" role="group">';
    
    // Tombol approve/reject untuk status validasi
    if($row->status === 'validasi') {
        $actionBtn .= '
            <button onclick="changeStatus('.$row->id.', \'disetujui\')" class="btn btn-success btn-sm action-btn" title="Approve"><i class="fa-solid fa-check"></i></button>
            <button onclick="changeStatus('.$row->id.', \'ditolak\')" class="btn btn-warning text-white btn-sm action-btn" title="Reject"><i class="fa-solid fa-times"></i></button>';
    }

    // Tombol toggle hide/unhide dari halaman home
    $isHidden = $row->is_hidden_from_home;
    $toggleColor = $isHidden ? 'btn-secondary' : 'btn-success';
    $toggleTitle = $isHidden ? 'Hidden dari Home' : 'Tampil di Home';
    // $toggleIcon = $isHidden ? 'fa-eye-slash' : 'fa-eye';
    $toggleIcon = $isHidden ? 'fa-toggle-off' : 'fa-toggle-on';
    $actionBtn .= '
        <button onclick="toggleHomeVisibility('.$row->id.', this)" 
            class="btn '.$toggleColor.' btn-sm" 
            data-hidden="'.($isHidden ? '1' : '0').'"
            title="'.$toggleTitle.'">
            <i class="fa-solid '.$toggleIcon.'"></i>
        </button>';

    // Tombol lainnya yang sudah ada
    $actionBtn .= '
        <a href="/kampanye/'.$row->slug.'" class="btn btn-info btn-sm"><i class="fa-solid fa-eye text-white"></i></a>
        <a href="'.route('kampanye.edit', $row->id).'" class="btn btn-primary btn-sm"><i class="fa-solid fa-pen"></i></a>
        <button onclick="deleteCampaign('.$row->id.')" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
    </div>';
    
    return $actionBtn;
})
                ->addColumn('category', function($row) {
                    return $row->category->name ?? 'N/A'; 
                })
                ->addColumn('status', function($row) {
                    $statusColor = [
                        'aktif' => 'success',
                        'selesai' => 'info',
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
            'status' => 'nullable|string|max:2048',
            'deadline' => 'nullable|date',
            'jumlah_target_donasi' => 'nullable|numeric',
            'document_rab' => 'required|mimes:pdf,doc,docx,xls,xlsx|max:5120',
            'slug' => 'nullable|alpha_dash|max:255|unique:campaigns,slug',
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
    
        DB::beginTransaction();
        try {
            $campaignData = $request->except(['photo', 'document_rab']);


                    // Check if custom slug was provided
        if (empty($request->slug)) {
            // Generate slug from title
            $slug = Str::slug($request->title);
            $originalSlug = $slug;
            $count = 1;

            while (Campaign::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }
        } else {
            // Use custom slug
            $slug = Str::slug($request->slug);
            
            // Check if slug is already taken
            if (Campaign::where('slug', $slug)->exists()) {
                return redirect()->back()
                    ->withErrors(['slug' => 'Slug tersebut sudah digunakan. Silakan pilih slug lain.'])
                    ->withInput();
            }
        }

        $campaignData['slug'] = $slug;

            
            // Handle file uploads
            if ($request->hasFile('photo')) {
                $campaignData['photo'] = $request->file('photo')->store('campaign_photos', 'public');
            }

            if ($request->hasFile('document_rab')) {
                $campaignData['document_rab'] = $request->file('document_rab')->store('campaign_documents', 'public');
            }
    
            $campaign = Campaign::create($campaignData);
            DB::commit();
        if (Auth::check()) {
            if(Auth::user()->role == 'super_admin'){
                try {
                    if ($campaign->admin) {
                        Mail::to($campaign->admin->email)->queue(new CampaignStatusUpdateMail($campaign, 'validasi'));
                        
                        // Create system notification for admin
                        $this->notificationService->createNotification(
                            $campaign->admin->user,
                            'Kampanye Berhasil Dibuat',
                            'Kampanye "' . $campaign->title . '" telah berhasil dibuat dan sedang menunggu validasi.',
                            'campaign_status_update',
                            ['campaign_id' => $campaign->id, 'status' => 'validasi']
                        );
                    }
                } catch (\Exception $e) {
                    Log::error('Gagal mengirim notifikasi kampanye baru: ' . $e->getMessage());
                }
            }else{
                try {
                    Mail::to('merawatindonesia2@gmail.com')->queue(new NewCampaignNotificationMail($campaign));
                    
                    // Send notification to campaign admin
                    if ($campaign->admin) {
                        Mail::to($campaign->admin->email)->queue(new CampaignStatusUpdateMail($campaign, 'validasi'));
                        
                        // Create system notification for admin
                        $this->notificationService->createNotification(
                            $campaign->admin->user,
                            'Kampanye Sedang Divalidasi',
                            'Kampanye "' . $campaign->title . '" telah diajukan dan sedang menunggu validasi.',
                            'campaign_status_update',
                            ['campaign_id' => $campaign->id, 'status' => 'validasi']
                        );
                    }
                } catch (\Exception $e) {
                    Log::error('Gagal mengirim notifikasi pengajuan kampanye: ' . $e->getMessage());
                }

                
                return redirect()->back()
                ->with('success', 'Kampanye berhasil diajukan');
            }
        } else {
            // Default message if user is not authenticated (optional)
            return redirect()->back()
                ->with('success', 'Kampanye berhasil diajukan');
        }

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal mengajukan kampanye: ' . $e->getMessage());
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
    
    public function edit($id)
{
    $kampanye = Campaign::findOrFail($id);
    $admins = Admin::get();
    $categories = Category::get();
    return view('super_admin.kampanye.form', compact('categories','kampanye','admins'));
}

    public function update(Request $request, $id)
    {
        $kampanye = Campaign::findOrFail($id);
        $user = auth()->user(); // Ambil user yang sedang login
        $role = $user->role; // Misalnya role ada di dalam field 'role'
        $oldStatus = $kampanye->status;
        $rules = [
            'admin_id' => 'required|exists:admins,id',
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'photo' => 'nullable|image|max:2048',
            'status' => 'nullable|string|max:2048',
            'deadline' => 'nullable|date',
            'jumlah_target_donasi' => 'nullable|numeric',
            'document_rab' => 'nullable|mimes:pdf,doc,docx,xls,xlsx|max:5120',
            'slug' => 'nullable|alpha_dash|max:255|unique:campaigns,slug,' . $id, 
        ];

        if ($role === 'super_admin') {
            $rules['document_rab'] = 'nullable|mimes:pdf,doc,docx,xls,xlsx|max:5120';
            $rules['category_id'] = 'nullable|exists:categories,id';
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

           // Handle custom slug
        if (empty($request->slug)) {
            // If title changed, regenerate slug
            if ($request->title !== $kampanye->title) {
                $slug = Str::slug($request->title);
                $originalSlug = $slug;
                $count = 1;
                
                // Make sure slug is unique
                while (Campaign::where('slug', $slug)->where('id', '!=', $kampanye->id)->exists()) {
                    $slug = $originalSlug . '-' . $count++;
                }
                
                $kampanyeData['slug'] = $slug;
            }
        } else {
            // Use custom slug
            $slug = Str::slug($request->slug);
            
            // Check if slug is already taken by another campaign
            if (Campaign::where('slug', $slug)->where('id', '!=', $kampanye->id)->exists()) {
                return redirect()->back()
                    ->withErrors(['slug' => 'Slug tersebut sudah digunakan. Silakan pilih slug lain.'])
                    ->withInput();
            }
            
            $kampanyeData['slug'] = $slug;
        }
            
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

              // Check if status changed and send notifications
              if (isset($kampanyeData['status']) && $kampanyeData['status'] !== $oldStatus) {
                // Reload campaign with relationships
                $kampanye->load(['admin', 'category']);
                
                try {
                    // Send email to campaign admin
                    if ($kampanye->admin) {
                        Mail::to($kampanye->admin->email)->queue(new CampaignStatusUpdateMail($kampanye, $kampanyeData['status']));
                        
                        // Create system notification
                        $statusMessage = '';
            if ($kampanyeData['status'] === 'aktif') {
                $statusMessage = 'disetujui';
            } elseif ($kampanyeData['status'] === 'ditolak') {
                $statusMessage = 'ditolak';
            } elseif ($kampanyeData['status'] === 'validasi') {
                $statusMessage = 'sedang divalidasi';
            } else {
                $statusMessage = $kampanyeData['status'];
            }
            
            $this->notificationService->createNotification(
                $kampanye->admin->user,
                'Status Kampanye Berubah',
                'Kampanye "' . $kampanye->title . '" telah ' . $statusMessage . '.',
                'campaign_status_update',
                ['campaign_id' => $kampanye->id, 'status' => $kampanyeData['status']]
            );
        }
    } catch (\Exception $e) {
        Log::error('Gagal mengirim notifikasi perubahan status kampanye: ' . $e->getMessage());
    }
}

            DB::commit();
            if ($role === 'super_admin') {
                return redirect()->back()
                ->with('success', 'Kampanye berhasil diperbarui');
            } else {
                return redirect('/admin/kampanye/'. $kampanye->slug)->with('success', 'Kampanye berhasil diperbarui');
            }
        } catch (\Exception $e) {

            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal memperbarui kampanye: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function donaturKampanye(Request $request, $slug)
    {
        session()->forget('referral_code');
        
        $perPage = 4; // Set the number of items per page
        
        $campaign = Campaign::where('slug', $slug)->first();
        
        // Get paginated data for each tab
        $kabarTerbaru = $campaign->kabarTerbaru()->paginate($perPage, ['*'], 'kabar_page');
        
        $donations = $campaign->donations()
            ->where('status', 'sukses')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'donatur_page');
        
        $kabarPencairan = $campaign->kabarPencairan()
            ->where('status', 'disetujui')
            ->paginate($perPage, ['*'], 'pencairan_page');
        
        $comments = Donation::where('campaign_id', $campaign->id)
            ->where('status', 'sukses')
            ->whereNotNull('doa')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'comments_page');
        
        $totalDonaturs = $campaign->donations->where('status', 'sukses')->count();

        $totalCampaign = Campaign::where('admin_id', $campaign->admin_id)->get();
        $totalKampanye = $totalCampaign->where('status', 'aktif')->count();
        
        // Get guest identifier from cookie
        $guestIdentifier = $request->cookie('guest_identifier');
        
        // Handle AJAX requests for each tab
        if ($request->ajax()) {
            if ($request->has('load_tab')) {
                switch ($request->load_tab) {
                    case 'kabar-terbaru':
                        return response()->json([
                            'html' => view('partials.kabar-terbaru', compact('kabarTerbaru'))->render(),
                            'hasMorePages' => $kabarTerbaru->hasMorePages(),
                            'nextPageUrl' => $kabarTerbaru->nextPageUrl() . '&load_tab=kabar-terbaru',
                        ]);
                    
                    case 'donatur':
                        return response()->json([
                            'html' => view('partials.donatur', compact('donations'))->render(),
                            'hasMorePages' => $donations->hasMorePages(),
                            'nextPageUrl' => $donations->nextPageUrl() . '&load_tab=donatur',
                        ]);
                    
                    case 'kabar-pencairan':
                        return response()->json([
                            'html' => view('partials.kabar-pencairan', compact('kabarPencairan'))->render(),
                            'hasMorePages' => $kabarPencairan->hasMorePages(),
                            'nextPageUrl' => $kabarPencairan->nextPageUrl() . '&load_tab=kabar-pencairan',
                        ]);
                    
                    case 'comments':
                        return response()->json([
                            'html' => view('partials.comments', compact('comments', 'guestIdentifier'))->render(),
                            'hasMorePages' => $comments->hasMorePages(),
                            'nextPageUrl' => $comments->nextPageUrl() . '&load_tab=comments',
                        ]);
                }
            }
        }

        $commission = Commission::first();
        
        return view('donatur.detail-kampanye', [
            'campaign' => $campaign,
            'kabarTerbaru' => $kabarTerbaru,
            'donations' => $donations,
            'kabarPencairan' => $kabarPencairan,
            'comments' => $comments,
            'guestIdentifier' => $guestIdentifier,
            'totalDonaturs' => $totalDonaturs,
            'totalKampanye' => $totalKampanye,
            'commission' => $commission->amount,
        ]);
    }

    public function show(Request $request, $slug)
    {
        session()->forget('referral_code');
        $perPage = 4; // Set the number of items per page
        
        $campaign = Campaign::where('slug', $slug)->first();
        
        // Get paginated data for each tab
        $kabarTerbaru = $campaign->kabarTerbaru()->paginate($perPage, ['*'], 'kabar_page');
        
        $donations = $campaign->donations()
            ->where('status', 'sukses')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'donatur_page');
        
        $kabarPencairan = $campaign->kabarPencairan()
            ->where('status', 'disetujui')
            ->paginate($perPage, ['*'], 'pencairan_page');
        
        $comments = Donation::where('campaign_id', $campaign->id)
            ->where('status', 'sukses')
            ->whereNotNull('doa')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'comments_page');
        
        $totalDonaturs = $campaign->donations->where('status', 'sukses')->count();
        $totalKampanye = Campaign::where('status', 'aktif')->count();
        
        // Get guest identifier from cookie
        $guestIdentifier = $request->cookie('guest_identifier');
        
        // Handle AJAX requests for each tab
        if ($request->ajax()) {
            if ($request->has('load_tab')) {
                switch ($request->load_tab) {
                    case 'kabar-terbaru':
                        return response()->json([
                            'html' => view('partials.kabar-terbaru', compact('kabarTerbaru'))->render(),
                            'hasMorePages' => $kabarTerbaru->hasMorePages(),
                            'nextPageUrl' => $kabarTerbaru->nextPageUrl() . '&load_tab=kabar-terbaru',
                        ]);
                    
                    case 'donatur':
                        return response()->json([
                            'html' => view('partials.donatur', compact('donations'))->render(),
                            'hasMorePages' => $donations->hasMorePages(),
                            'nextPageUrl' => $donations->nextPageUrl() . '&load_tab=donatur',
                        ]);
                    
                    case 'kabar-pencairan':
                        return response()->json([
                            'html' => view('partials.kabar-pencairan', compact('kabarPencairan'))->render(),
                            'hasMorePages' => $kabarPencairan->hasMorePages(),
                            'nextPageUrl' => $kabarPencairan->nextPageUrl() . '&load_tab=kabar-pencairan',
                        ]);
                    
                    case 'comments':
                        return response()->json([
                            'html' => view('partials.comments', compact('comments', 'guestIdentifier'))->render(),
                            'hasMorePages' => $comments->hasMorePages(),
                            'nextPageUrl' => $comments->nextPageUrl() . '&load_tab=comments',
                        ]);
                }
            }
        }
        
        $viewName = (Auth::check() && Auth::user()->role === 'yayasan' && $campaign->admin_id == Auth::user()->admin->id)
        ? 'admin.kampanye.detail-kampanye'
        : 'donatur.detail-kampanye';

        return view($viewName, [
            'campaign' => $campaign,
            'kabarTerbaru' => $kabarTerbaru,
            'donations' => $donations,
            'kabarPencairan' => $kabarPencairan,
            'comments' => $comments,
            'guestIdentifier' => $guestIdentifier,
            'totalDonaturs' => $totalDonaturs,
            'totalKampanye' => $totalKampanye,
        ]);
    }


    // form edit admin
    public function editKampanye($slug)
    {

        $kampanye = Campaign::where('slug',$slug)->first();

        return view('admin.kampanye.edit-kampanye', [
            'kampanye' => $kampanye,
        ]);
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $kampanye = Campaign::findOrFail($id);

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

    // Update metode changeStatus
    public function changeStatus(Request $request, $campaignId)
    {
        $campaign = Campaign::find($campaignId);
    
        // Check if campaign exists
        if (!$campaign) {
            return response()->json([
                'success' => false,
                'message' => 'Kampanye tidak ditemukan'
            ], 404);
        }
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:disetujui,ditolak,validasi,aktif,selesai'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Simpan status lama untuk pengecekan
        $oldStatus = $campaign->status;
        
        DB::beginTransaction();
        try {
            // Khusus untuk approval, ubah status menjadi 'aktif'
            $newStatus = $request->status;
            if ($request->status === 'disetujui') {
                $newStatus = 'aktif';
            }
            
            // Update status kampanye
            $campaign->update(['status' => $newStatus]);
            
            // Load relasi admin jika belum dimuat
            if (!$campaign->relationLoaded('admin')) {
                $campaign->load(['admin', 'category']);
            }
            
            // Kirim email notifikasi jika status berubah
            if ($oldStatus !== $newStatus && $campaign->admin) {
                try {
                    // Kirim email ke admin kampanye menggunakan CampaignStatusUpdateMail bukan CampaignStatusMail
                    Mail::to($campaign->admin->email)->queue(new CampaignStatusUpdateMail($campaign, $request->status));
                    
                    // Buat notifikasi sistem untuk admin
                    $statusMessage = '';
                    if ($request->status === 'disetujui') {
                        $statusMessage = 'disetujui dan aktif';
                    } elseif ($request->status === 'ditolak') {
                        $statusMessage = 'ditolak';
                    } elseif ($request->status === 'validasi') {
                        $statusMessage = 'sedang divalidasi';
                    } elseif ($request->status === 'selesai') {
                        $statusMessage = 'selesai';
                    } else {
                        $statusMessage = $newStatus;
                    }
                    
                    $this->notificationService->createNotification(
                        $campaign->admin->user, // Make sure this is correct - admin->user not just admin
                        'Status Kampanye Berubah',
                        'Kampanye "' . $campaign->title . '" telah ' . $statusMessage . '.',
                        'campaign_status_update',
                        ['campaign_id' => $campaign->id, 'status' => $newStatus]
                    );
                    
                    // Log untuk debugging
                    Log::info('Email notifikasi status kampanye berhasil dikirim ke ' . $campaign->admin->email);
                } catch (\Exception $e) {
                    // Log error
                    Log::error('Gagal mengirim email notifikasi status kampanye: ' . $e->getMessage());
                    // Continue process even if email fails
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Status kampanye berhasil diubah menjadi ' . ($statusMessage ?? $newStatus),
                'new_status' => $newStatus
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error changing campaign status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status: ' . $e->getMessage()
            ], 500);
        }
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

    public function toggleHomeVisibility($id)
{
    $campaign = Campaign::findOrFail($id);
    $campaign->is_hidden_from_home = !$campaign->is_hidden_from_home;
    $campaign->save();

    return response()->json([
        'status' => 'success',
        'is_hidden' => $campaign->is_hidden_from_home,
        'message' => $campaign->is_hidden_from_home
            ? 'Kampanye disembunyikan dari halaman utama'
            : 'Kampanye ditampilkan di halaman utama'
    ]);
}
}
