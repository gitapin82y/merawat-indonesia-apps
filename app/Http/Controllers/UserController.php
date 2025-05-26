<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Admin;
use App\Models\Campaign;
use App\Models\Banner;
use App\Models\Donation;
use App\Models\Category;
use App\Models\PrioritasCampaign;
use App\Models\UrgentCampaign;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{


    public function allMenu(Request $request){
        $categories = Category::all();
        return view('donatur.menu-lainnya', compact('categories'));
    }
    public function home(Request $request)
    {
        Campaign::checkAndUpdateExpiredCampaigns();
        $donaturLeaderboard = User::whereHas('donations', function($query) {
            $query->where('status', 'sukses');
        })
        ->withSum(['donations' => function($query){
            $query->where('status', 'sukses');
        }], 'amount')
        ->orderByDesc('donations_sum_amount')
        ->limit(10)
        ->get()
        ->map(function($user) {
            return [
                'name' => $user->name,
                'avatar' => $user->avatar_url,
                'total_donation' => $user->donations_sum_amount,
                'total_donation_formatted' => 'Rp ' . number_format($user->donations_sum_amount, 0, ',', '.')
            ];
        });

        $today = Carbon::now();
        $oneWeekFromNow = $today->copy()->addWeek();

        // Gunakan paginate untuk pagination biasa
        $campaigns = Campaign::where('status', 'aktif')->paginate(6); // Perhatikan jumlah item per halaman

        // Filter kampanye yang tinggal 1 minggu lagi
         $weekendCampaigns = Campaign::where('status', 'aktif')
            ->whereNotNull('deadline')
            ->whereDate('deadline', '>=', $today->toDateString())
            ->whereDate('deadline', '<=', $oneWeekFromNow->toDateString())
            ->limit(10)
            ->get();

        $prioritasCampaigns = prioritasCampaign::with(['campaign'])
        ->orderBy('prioritas', 'asc')
        ->get();

        $urgentCampaigns = UrgentCampaign::with(['campaign'])
        ->orderBy('prioritas', 'asc')
        ->get();


        $banners = Banner::get();

        // Untuk permintaan AJAX, hanya kirim data kampanye dan status pagination
        if ($request->ajax()) {
            return response()->json([
                'campaigns' => view('partials.campaigns', compact('campaigns'))->render(),
                'hasMorePages' => $campaigns->hasMorePages(),
                'nextPageUrl' => $campaigns->nextPageUrl(),
            ]);
        }
        $categories = Category::all();
        $categoriesCount = $categories->count();

        return view('donatur.home', compact('categories', 'categoriesCount','donaturLeaderboard', 'campaigns', 'weekendCampaigns','prioritasCampaigns','urgentCampaigns','banners'));
    }

    public function eksplore(Request $request)
    {
        Campaign::checkAndUpdateExpiredCampaigns();
        $donaturLeaderboard = User::whereHas('donations', function($query) {
            $query->where('status', 'sukses');
        })
        ->withSum(['donations' => function($query){
            $query->where('status', 'sukses');
        }], 'amount')
        ->orderByDesc('donations_sum_amount')
        ->limit(10)
        ->get()
        ->map(function($user) {
            return [
                'name' => $user->name,
                'avatar' => $user->avatar_url,
                'total_donation' => $user->donations_sum_amount,
                'total_donation_formatted' => 'Rp ' . number_format($user->donations_sum_amount, 0, ',', '.')
            ];
        });

        $today = Carbon::now();
        $oneWeekFromNow = $today->copy()->addWeek();

        // Gunakan paginate untuk pagination biasa
        $campaigns = Campaign::where('status', 'aktif')->paginate(8); // Perhatikan jumlah item per halaman

        // Filter kampanye yang tinggal 1 minggu lagi
         $weekendCampaigns = Campaign::where('status', 'aktif')
            ->whereNotNull('deadline')
            ->whereDate('deadline', '>=', $today->toDateString())
            ->whereDate('deadline', '<=', $oneWeekFromNow->toDateString())
            ->limit(10)
            ->get();

        $prioritasCampaigns = prioritasCampaign::with(['campaign'])
        ->orderBy('prioritas', 'asc')
        ->get();

        // Untuk permintaan AJAX, hanya kirim data kampanye dan status pagination
        if ($request->ajax()) {
            return response()->json([
                'campaigns' => view('partials.campaigns', compact('campaigns'))->render(),
                'hasMorePages' => $campaigns->hasMorePages(),
                'nextPageUrl' => $campaigns->nextPageUrl(),
            ]);
        }

        $categories = Category::all();
        $categoriesCount = $categories->count();

        return view('donatur.eksplore-kampanye', compact('donaturLeaderboard', 'campaigns', 'weekendCampaigns','prioritasCampaigns','categories','categoriesCount'));
    }

    public function result(Request $request)
{
    $query = Campaign::with('category')->where('status', 'aktif');
    $hasFilters = false;

    // Filter berdasarkan kategori (multiple)
    if ($request->has('category')) {
        $categories = $request->input('category');
        $hasFilters = true;
        
        // Jika kategori adalah array
        if (is_array($categories)) {
            $query->whereHas('category', function ($q) use ($categories) {
                $q->whereIn('name', $categories);
            });
        } else {
            // Jika kategori adalah string tunggal
            $query->whereHas('category', function ($q) use ($categories) {
                $q->where('name', $categories);
            });
        }
    }

    // Filter berdasarkan judul
    if ($request->has('title') && !empty($request->title)) {
        $hasFilters = true;
        $query->where('title', 'like', '%' . $request->title . '%');
    }

    // Filter khusus
    if ($request->has('filter')) {
        $filters = $request->input('filter');
        $hasFilters = true;
        
        // Jika filter multiple
        if (is_array($filters)) {
            if (in_array('terbaru', $filters)) {
                $query->orderBy('created_at', 'desc');
            }
            
            if (in_array('populer', $filters)) {
                $query->orderBy('jumlah_donasi', 'desc');
            }
            
            if (in_array('hampir_tercapai', $filters)) {
                $query->orderByRaw('(jumlah_donasi / jumlah_target_donasi) DESC');
            }
        } else {
            // Jika filter tunggal
            switch ($filters) {
                case 'terbaru':
                    $query->orderBy('created_at', 'desc');
                    break;

                case 'populer':
                    $query->orderBy('jumlah_donasi', 'desc');
                    break;

                case 'hampir_tercapai':
                    $query->orderByRaw('(jumlah_donasi / jumlah_target_donasi) DESC');
                    break;

                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }
        }
    } else {
        // Default urutan jika tidak ada filter
        $query->orderBy('created_at', 'desc');
    }

    // Paginate hasil
    $campaigns = $query->paginate(8);
    
    // Tambahkan parameter filter ke pagination links
    $campaigns->appends($request->all());

    // Variabel untuk menampilkan status pencarian
    $notFound = $hasFilters && $campaigns->total() == 0;

    if ($request->ajax()) {
        return response()->json([
            'campaigns' => view('partials.campaigns', compact('campaigns'))->render(),
            'hasMorePages' => $campaigns->hasMorePages(),
            'nextPageUrl' => $campaigns->nextPageUrl(),
            'notFound' => $notFound
        ]);
    }

    // Pass semua filter yang aktif ke view untuk menampilkan status filter
    $activeFilters = [
        'categories' => $request->input('category', []),
        'filters' => $request->input('filter', []),
        'title' => $request->input('title', '')
    ];

    return view('donatur.eksplore-result', compact('campaigns', 'activeFilters', 'notFound'));
}

    public function index(Request $request)
    {
        Carbon::setLocale('id');
        if ($request->ajax()) {
            $query = User::where('role','!=','super_admin')->get();
            
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('action', function($row) {
                    $actionBtn = '
                        <div class="btn-group" role="group">
                        <a href="'.url('profile-donatur/'.$row->name).'"  class="btn btn-info btn-sm" target="_blank"><i class="fa-solid fa-eye text-white"></i></a>
                            <a href="'.route('user.edit', $row->id).'" class="btn btn-primary btn-sm"><i class="fa-solid fa-pen"></i></a>
                            <button onclick="deleteUser('.$row->id.')" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    ';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                 ->make(true);
        }
        
        return view('super_admin.user.index');
    }

    public function create()
    {
        $users = User::get();
        return view('super_admin.user.form', compact('users'));
    }

    public function leaderboard()
    {
        $donaturLeaderboard = User::whereHas('donations', function($query)  {
                $query->where('status', 'sukses');
            })
            ->withSum(['donations' => function($query)  {
                $query->where('status', 'sukses');
            }], 'amount')
            ->orderByDesc('donations_sum_amount')
            ->limit(10)
            ->get()
            ->map(function($user) {
                return [
                    'name' => $user->name,
                    'avatar' => $user->avatar_url,
                    'total_donation' => $user->donations_sum_amount,
                    'total_donation_formatted' => 'Rp ' . number_format($user->donations_sum_amount, 0, ',', '.')
                ];
            });

        // Leaderboard Admin Penggalang Dana (Berdasarkan Total Donatur)
        $adminLeaderboard = Admin::with(['user', 'campaigns.donations' => function($query) {
            $query->where('status', 'sukses');
        }])
        ->get()
        ->map(function($admin) {
            $totalDonaturSukses = $admin->campaigns->sum(function($campaign) {
                return $campaign->donations->count();
            });
            
            return [
                'name' => $admin->name,
                'avatar' => $admin->avatar_url,
                'total_donatur' => $totalDonaturSukses,
                'total_campaigns' => $admin->campaigns->count()
            ];
        })
        ->sortByDesc('total_donatur')
        ->take(10)
        ->values();

        return view('donatur.leaderboard', [
            'donaturLeaderboard' => $donaturLeaderboard,
            'adminLeaderboard' => $adminLeaderboard,
        ]);
    }

    public function profileDonatur(Request $request)
{
    $perPage = 4;
    $user = Auth::user();
    
    // Load basic user info without eager loading large collections
    $userBasic = User::findOrFail($user->id);
    
    // Calculate totals for header statistics
    $totalDonasi = number_format($userBasic->donations()->where('status','sukses')->sum('amount'), 0, ',', '.');
    $jumlahDukungan = $userBasic->donations()->where('status','sukses')->distinct('campaign_id')->count('campaign_id');
    
    // Handle pagination for each tab
    if ($request->ajax()) {
        if ($request->has('tab')) {
            switch ($request->tab) {
                case 'donations':
                    $donations = $user->donations()
                    ->where('status','sukses')
                        ->orderBy('created_at', 'desc')
                        ->paginate($perPage, ['*'], 'donations_page');
                    
                    return response()->json([
                        'html' => view('partials.profile.donations', compact('donations'))->render(),
                        'hasMorePages' => $donations->hasMorePages(),
                        'nextPageUrl' => $donations->nextPageUrl() . '&tab=donations',
                    ]);
                
                case 'saved':
                    $savedCampaigns = $user->savedCampaigns()
                        ->orderBy('user_campaign_save.created_at', 'desc')
                        ->paginate($perPage, ['*'], 'saved_page');
                    
                    return response()->json([
                        'html' => view('partials.profile.saved-campaigns', compact('savedCampaigns'))->render(),
                        'hasMorePages' => $savedCampaigns->hasMorePages(),
                        'nextPageUrl' => $savedCampaigns->nextPageUrl() . '&tab=saved',
                    ]);
                
                case 'supported':
                    $supportedCampaigns = $user->donations()
                        ->with('campaign')
                        ->where('status','sukses')
                        ->whereNotNull('campaign_id')
                        ->select('campaign_id')
                        ->distinct()
                        ->orderBy('created_at', 'desc')
                        ->paginate($perPage, ['*'], 'supported_page');
                    
                    return response()->json([
                        'html' => view('partials.profile.supported-campaigns', compact('supportedCampaigns'))->render(),
                        'hasMorePages' => $supportedCampaigns->hasMorePages(),
                        'nextPageUrl' => $supportedCampaigns->nextPageUrl() . '&tab=supported',
                    ]);
            }
        }
        
        return response()->json(['error' => 'Invalid request'], 400);
    }
    
    // For initial page load, get first page of each tab
    $donations = $user->donations()
    ->where('status','sukses')
        ->orderBy('created_at', 'desc')
        ->paginate($perPage, ['*'], 'donations_page');
    
    $savedCampaigns = $user->savedCampaigns()
        ->orderBy('user_campaign_save.created_at', 'desc')
        ->paginate($perPage, ['*'], 'saved_page');
    
   // For supported campaigns, get the latest donation for each campaign
    $latestDonationsByCampaign = $user->donations()
        ->where('status','sukses')
        ->whereNotNull('campaign_id')
        ->select('campaign_id', DB::raw('MAX(id) as max_id'))
        ->groupBy('campaign_id')
        ->get()
    ->pluck('max_id');

    $supportedCampaigns = Donation::with('campaign')
        ->whereIn('id', $latestDonationsByCampaign)
        ->orderBy('created_at', 'desc')
    ->paginate($perPage, ['*'], 'supported_page');
    
    return view('donatur.profile', compact(
        'user', 
        'totalDonasi', 
        'jumlahDukungan',
        'donations',
        'savedCampaigns',
        'supportedCampaigns'
    ));
}

    public function profileDonaturLeaderboard(Request $request, $name){
        $perPage = 4;
        $user = User::with(['donations.campaign'])->where('name',$name)->first();
        $totalDonasi = number_format($user->donations()->where('status','sukses')->sum('amount'), 0, ',', '.');

        $jumlahDukungan = $user->donations()->where('status','sukses')->distinct('campaign_id')->count('campaign_id');

        if ($request->ajax()) {
            if ($request->has('tab')) {
                switch ($request->tab) {
                    case 'donations':
                        $donations = $user->donations()
                        ->where('status','sukses')
                            ->orderBy('created_at', 'desc')
                            ->paginate($perPage, ['*'], 'donations_page');
                        
                        return response()->json([
                            'html' => view('partials.profile.donations', compact('donations'))->render(),
                            'hasMorePages' => $donations->hasMorePages(),
                            'nextPageUrl' => $donations->nextPageUrl() . '&tab=donations',
                        ]);
                    
                    case 'supported':
                        $supportedCampaigns = $user->donations()
                            ->with('campaign')
                            ->where('status','sukses')
                            ->whereNotNull('campaign_id')
                            ->select('campaign_id')
                            ->distinct()
                            ->orderBy('created_at', 'desc')
                            ->paginate($perPage, ['*'], 'supported_page');
                        
                        return response()->json([
                            'html' => view('partials.profile.supported-campaigns', compact('supportedCampaigns'))->render(),
                            'hasMorePages' => $supportedCampaigns->hasMorePages(),
                            'nextPageUrl' => $supportedCampaigns->nextPageUrl() . '&tab=supported',
                        ]);
                }
            }
            
            return response()->json(['error' => 'Invalid request'], 400);
        }

        $donations = $user->donations()
        ->where('status','sukses')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'donations_page');

        $latestDonationsByCampaign = $user->donations()
        ->where('status','sukses')
        ->whereNotNull('campaign_id')
        ->select('campaign_id', DB::raw('MAX(id) as max_id'))
        ->groupBy('campaign_id')
        ->get()
    ->pluck('max_id');

    $supportedCampaigns = Donation::with('campaign')
        ->whereIn('id', $latestDonationsByCampaign)
        ->orderBy('created_at', 'desc')
    ->paginate($perPage, ['*'], 'supported_page');



        return view('donatur.profile-donatur', compact('user', 'totalDonasi', 'jumlahDukungan','donations','supportedCampaigns'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|regex:/^08[1-9][0-9]{7,10}$/|min:10|max:13',
            'password' => 'required|string|min:6',
            'email' => 'required|email|unique:users',
            'avatar' => 'nullable|image|max:2048',
            'bio' => 'required|string',
            'role' => 'required|string',
            'thumbnail' => 'nullable|image|max:2048',
            'social' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $userData = $request->except(['avatar', 'thumbnail', 'social']);
                // Proses social media: pastikan jika tidak diisi, set menjadi null
        $socialData = $request->input('social', []);
        
        $socialData = array_filter($socialData, function ($item) {
            return !empty($item); // Hanya sisakan yang tidak kosong
        });

        // Simpan social media dalam bentuk array (bukan JSON-encoded string)
        $userData['social'] = $socialData;  // Tidak perlu json_encode lagi

            // Handle file uploads
            if ($request->hasFile('avatar')) {
                $userData['avatar'] = $request->file('avatar')->store('admin_avatar', 'public');
            }
            
            if ($request->hasFile('thumbnail')) {
                $userData['thumbnail'] = $request->file('thumbnail')->store('admin_thumbnail', 'public');
            }

            $user = User::create($userData);

            DB::commit();
            return redirect()->route('user.index')
                ->with('success', 'Admin berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menambahkan User: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function edit(User $user)
    {
        return view('super_admin.user.form', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        // Validasi berdasarkan role
        $rules = [
            'name' => 'required|string|max:255',
            'phone' => 'required|regex:/^08[1-9][0-9]{7,10}$/|min:10|max:13',
            'avatar' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'password' => 'nullable|string|min:6',
            'thumbnail' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'social' => 'nullable|array',
            'bio' => 'nullable|max:255',
        ];
    
        if ($user->role === 'super_admin') {
            $rules['address'] = 'nullable|string';
            $rules['email'] = 'required|email';
        } else {
            $rules['address'] = 'nullable';
            $rules['email'] = 'nullable|email';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }




        DB::beginTransaction();
        try {
            $userData = $request->except(['avatar', 'thumbnail', 'social']);
            
                 // Proses social media: pastikan jika tidak diisi, set menjadi null
        $socialData = $request->input('social', []);
        
        $socialData = array_filter($socialData, function ($item) {
            return !empty($item); // Hanya sisakan yang tidak kosong
        });

        // Simpan social media dalam bentuk array (bukan JSON-encoded string)
        $userData['social'] = $socialData;  // Tidak perlu json_encode lagi

            
            // Handle file uploads
            if ($request->hasFile('avatar')) {
                if ($user->avatar && $user->avatar != 'default/default-avatar.png') {
                    Storage::disk('public')->delete($user->avatar);
                }
        
                $userData['avatar'] = $request->file('avatar')->store('admin_avatar', 'public');
            }
            
            if ($request->hasFile('thumbnail')) {
                // Delete old thumbnail
                if ($user->thumbnail) {
                    Storage::disk('public')->delete($user->thumbnail);
                }
                $userData['thumbnail'] = $request->file('thumbnail')->store('admin_thumbnail', 'public');
            }

            $user->update($userData);

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

    public function show(User $user)
    {
        $user->social = json_decode($user->social, true) ?? [];
        return view('admin.show', compact('admin'));
    }

    public function destroy(User $user)
    {
        DB::beginTransaction();
        try {
            if ($user->avatar && $user->avatar != 'default/default-avatar.png') {
                Storage::disk('public')->delete($user->avatar);
            }

            
            if ($user->thumbnail) {
                Storage::disk('public')->delete($user->thumbnail);
            }

            $user->delete();

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'User berhasil dihapus']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Gagal menghapus User: ' . $e->getMessage()], 500);
        }
    }
}
