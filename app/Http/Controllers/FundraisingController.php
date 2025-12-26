<?php

namespace App\Http\Controllers;

use App\Models\Fundraising;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\Commission;
use App\Models\FundraisingWithdrawal;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Mail;
use App\Mail\FundraisingWithdrawalMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;


class FundraisingController extends Controller
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
            $query = Campaign::with('fundraisings')->has('fundraisings')->get();
            return DataTables::of($query)
                ->addIndexColumn()
                 ->addColumn('campaign', function ($row) {
                    return $row->title;
                })          
                    ->addColumn('total_fundraiser', function ($row) {
                    return '<span class="badge bg-primary text-white">'.$row->fundraisings->count().'</span>';
                })  
                ->addColumn('jumlah_donasi', function ($row) {
                    $total = $row->fundraisings->sum('jumlah_donasi');
                    return 'Rp ' . number_format($total, 0, ',', '.');
                })
                ->addColumn('total_komisi', function ($row) {
                    $total = $row->fundraisings->sum('commission');
                    return 'Rp ' . number_format($total, 0, ',', '.');
                })
                 ->addColumn('action', function ($row) {
                    $detailUrl = route('fundraising.campaign.detail', $row->slug);
                    $deleteUrl = route('fundraising.campaign.destroy', $row->slug);
                    $btn = '<div class="btn-group" role="group">';
                    $btn .= '<a href="'.$detailUrl.'" class="btn btn-info btn-sm"><i class="fa-solid fa-eye text-white"></i></a>';
                    $btn .= '<button onclick="deleteCampaignFundraising(\''.$row->slug.'\')" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['campaign','total_fundraiser','jumlah_donasi','total_komisi','action'])
                ->make(true);
             
        }
        
        return view('super_admin.fundraising.index');
    }
  public function destroyByCampaign(Campaign $campaign)
    {
        DB::beginTransaction();
        try {
            $campaign->fundraisings()->delete();

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Data fundraising kampanye berhasil dihapus']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Gagal menghapus data fundraising kampanye: ' . $e->getMessage()], 500);
        }
    }
      public function campaignDetail(Campaign $campaign)
    {
        $campaign->load(['fundraisings.user']);

        return view('super_admin.fundraising.campaign-detail', compact('campaign'));
    }

    public function fundraising(Request $request)
{
    $user = Auth::user();
    Log::info('User mengakses fundraising', ['user_id' => $user->id]);

    // Cek apakah user punya fundraising
    $hasAnyFundraising = Fundraising::where('user_id', $user->id)->exists();

    // Ambil komisi
    $commissionModel = Commission::first();
    $commission = $commissionModel ? $commissionModel->amount : 5;

    if (!$hasAnyFundraising) {
        return view('donatur.fundraishing.index', compact('commission', 'hasAnyFundraising'));
    }

    // Ambil filter dari request
    $filterType = $request->get('filter_type', 'all');
    $date = $request->get('date');
    $month = $request->get('month');
    $start_date = $request->get('start_date');
    $end_date = $request->get('end_date');

    $allFundraisings = Fundraising::where('user_id', $user->id)
        ->with(['campaign', 'fundraisingWithdrawals'])
        ->get();

    $isFiltered = false;
    $fundraisings = collect();
    $totalCommission = 0;

    foreach ($allFundraisings as $fundraising) {
        $donationIds = $fundraising->donations;

        // Decode JSON jika perlu
        if (!is_array($donationIds)) {
            $decoded = json_decode($donationIds, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $donationIds = $decoded;
            } else {
                continue; // skip jika invalid
            }
        }

        // Pastikan array 1 dimensi
        $donationIds = Arr::flatten($donationIds);

        if (empty($donationIds)) {
            if ($filterType === 'all') {
                $fundraisings->push($fundraising);
                $totalCommission += $fundraising->commission ?? 0;
            }
            continue;
        }

        // Query donasi sukses
        $donationsQuery = Donation::whereIn('id', $donationIds)
            ->where('status', 'sukses');

        // Filter tanggal
        try {
            switch ($filterType) {
                case 'daily':
                    if ($date) {
                        $donationsQuery->whereDate('created_at', $date);
                        $isFiltered = true;
                    }
                    break;

                case 'monthly':
                    if ($month) {
                        $parsedMonth = Carbon::parse($month);
                        $donationsQuery->whereYear('created_at', $parsedMonth->year)
                                       ->whereMonth('created_at', $parsedMonth->month);
                        $isFiltered = true;
                    }
                    break;

                case 'range':
                    if ($start_date && $end_date) {
                        $donationsQuery->whereBetween('created_at', [
                            Carbon::parse($start_date)->startOfDay(),
                            Carbon::parse($end_date)->endOfDay(),
                        ]);
                        $isFiltered = true;
                    }
                    break;
            }
        } catch (\Exception $e) {
            Log::error('Format tanggal filter fundraising salah', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            continue;
        }

        $filteredDonations = $donationsQuery->get();

        if ($isFiltered) {
            if ($filteredDonations->isNotEmpty()) {
                $filteredTotalDonasi = $filteredDonations->sum('amount');
                $filteredTotalDonatur = $filteredDonations->count();
                $filteredCommission = ($filteredTotalDonasi * $commission) / 100;

                $filteredFundraising = $fundraising->replicate();
                $filteredFundraising->jumlah_donasi = $filteredTotalDonasi;
                $filteredFundraising->total_donatur = $filteredTotalDonatur;
                $filteredFundraising->commission = $filteredCommission;
                $filteredFundraising->filtered_donations = $filteredDonations;

                $fundraisings->push($filteredFundraising);
                $totalCommission += $filteredCommission;
            }
        } else {
            $fundraisings->push($fundraising);
            $totalCommission += $fundraising->commission ?? 0;
        }
    }

    $filterData = [
        'filter_type' => $filterType,
        'date' => $date,
        'month' => $month,
        'start_date' => $start_date,
        'end_date' => $end_date,
        'is_filtered' => $isFiltered,
        'has_results' => $fundraisings->isNotEmpty()
    ];

    return view('donatur.fundraishing.index', compact(
        'fundraisings',
        'totalCommission',
        'commission',
        'filterData',
        'hasAnyFundraising'
    ));
}

// Method untuk AJAX filter jika diperlukan
public function getFilteredData(Request $request)
{
    $user = Auth::user();
    
    $filterType = $request->get('filter_type', 'all');
    $date = $request->get('date');
    $month = $request->get('month');
    $start_date = $request->get('start_date');
    $end_date = $request->get('end_date');
    
    // Query dasar fundraising user
    $allFundraisings = Fundraising::where('user_id', $user->id)
        ->with(['campaign'])
        ->get();
    
    $isFiltered = false;
    $fundraisings = collect();
    $totalCommission = 0;


    
    // Proses filter berdasarkan tanggal donasi dari kolom donations array
    foreach ($allFundraisings as $fundraising) {
        // Jika tidak ada donations array, skip
        if (empty($fundraising->donations) || !is_array($fundraising->donations)) {
            if ($filterType == 'all') {
                $fundraisings->push([
                    'id' => $fundraising->id,
                    'campaign' => $fundraising->campaign,
                    'code_link' => $fundraising->code_link,
                    'jumlah_donasi' => $fundraising->jumlah_donasi,
                    'total_donatur' => $fundraising->total_donatur,
                    'commission' => $fundraising->commission
                ]);
                $totalCommission += $fundraising->commission;
            }
            continue;
        }
        
        // Ambil donasi berdasarkan ID yang ada di array donations
        $donationIds = $fundraising->donations;
        $donationsQuery = Donation::whereIn('id', $donationIds)->where('status', 'sukses');
        
        // Terapkan filter tanggal
        switch($filterType) {
            case 'daily':
                if($date) {
                    $donationsQuery->whereDate('created_at', $date);
                    $isFiltered = true;
                }
                break;
                
            case 'monthly':
                if($month) {
                    $donationsQuery->whereYear('created_at', Carbon::parse($month)->year)
                                  ->whereMonth('created_at', Carbon::parse($month)->month);
                    $isFiltered = true;
                }
                break;
                
            case 'range':
                if($start_date && $end_date) {
                    $donationsQuery->whereBetween('created_at', [
                        Carbon::parse($start_date)->startOfDay(),
                        Carbon::parse($end_date)->endOfDay()
                    ]);
                    $isFiltered = true;
                }
                break;
        }
        
        $filteredDonations = $donationsQuery->get();
        
        // Jika filter aktif
        if ($isFiltered) {
            if ($filteredDonations->isNotEmpty()) {
                // Hitung ulang data berdasarkan donasi yang terfilter
                $filteredTotalDonasi = $filteredDonations->sum('amount');
                $filteredTotalDonatur = $filteredDonations->count();
                
                // Hitung commission untuk periode yang dipilih
                $commissionRate = Commission::first()->amount ?? 5;
                $filteredCommission = ($filteredTotalDonasi * $commissionRate) / 100;
                
                $fundraisings->push([
                    'id' => $fundraising->id,
                    'campaign' => $fundraising->campaign,
                    'code_link' => $fundraising->code_link,
                    'jumlah_donasi' => $filteredTotalDonasi,
                    'total_donatur' => $filteredTotalDonatur,
                    'commission' => $filteredCommission
                ]);
                
                $totalCommission += $filteredCommission;
            }
        } else {
            // Jika tidak ada filter, gunakan data asli
            $fundraisings->push([
                'id' => $fundraising->id,
                'campaign' => $fundraising->campaign,
                'code_link' => $fundraising->code_link,
                'jumlah_donasi' => $fundraising->jumlah_donasi,
                'total_donatur' => $fundraising->total_donatur,
                'commission' => $fundraising->commission
            ]);
            $totalCommission += $fundraising->commission;
        }
    }
    
    return response()->json([
        'success' => true,
        'data' => $fundraisings,
        'totalCommission' => $totalCommission,
        'formattedTotal' => number_format($totalCommission),
        'hasResults' => $fundraisings->isNotEmpty()
    ]);
}

    public function join($slug)
    {
        $user = Auth::user();
        $campaign = Campaign::where('slug',$slug)->first();
        
        // Cek apakah pengguna sudah terdaftar di kampanye ini
        $existingFundraising = Fundraising::where('user_id', $user->id)
            ->where('campaign_id', $campaign->id)
            ->first();
            
        if ($existingFundraising) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda sudah terdaftar sebagai fundraiser untuk kampanye ini',
                'redirect' => url('/profile/fundraising')
            ]);
        }
        
        // Buat kode unik untuk link referral
        $codeLink = Str::random(10);
        
        // Buat record fundraising baru
        $fundraising = Fundraising::create([
            'campaign_id' => $campaign->id,
            'user_id' => $user->id,
            'code_link' => $codeLink,
            'total_donatur' => 0,
            'donations' => json_encode([]),
            'jumlah_donasi' => 0,
            'commission' => 0
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil bergabung sebagai fundraiser kampanye',
            'redirect' => url('/profile/fundraising')
        ]);
    }

    public function withdrawFunds(Request $request)
    {
        // Validasi input
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
        $fundraisings = Fundraising::where('user_id', $user->id)->get();
        $totalCommission = $fundraisings->sum('commission');

        if ($request->amount <= 0) {
            return redirect()->back()->with('error', 'Jumlah penarikan harus lebih dari 0.');
        }

        if ($totalCommission < $request->amount) {
            return redirect()->back()->with('error', 'Jumlah yang diminta melebihi jumlah komisi yang tersedia.');
        }
        
        if ($totalCommission < 100000) {
            return redirect()->back()->with('error', 'Minimal pencairan dana adalah Rp 100.000');
        }

        DB::beginTransaction();
    try {
        // Cari fundraising dengan komisi terbesar untuk dijadikan sebagai fundraising_id
        $primaryFundraising = $fundraisings->sortByDesc('commission')->first();
        
        $admin = User::where('email', 'merawatindonesia2@gmail.com')->first();

        // Buat entri pencairan dana
        $withdrawal = FundraisingWithdrawal::create([
            'fundraising_id' => $primaryFundraising->id,
            'user_id' => $user->id,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'account_name' => $request->account_name,
            'account_number' => $request->account_number,
            'status' => 'menunggu',
        ]);
        
            
        $this->notificationService->createNotification(
            $admin,
            'Permintaan Pencairan Fundraising Baru',
            'Permintaan pencairan dana fundraising baru dari ' . $user->name . ' sebesar Rp ' . number_format($request->amount, 0, ',', '.'),
            'fundraising_withdraw',
            ['withdrawal_id' => $withdrawal->id]
        );

        Mail::to("merawatindonesia2@gmail.com")->queue(new FundraisingWithdrawalMail($withdrawal));

        DB::commit();
        
        return redirect()->back()->with('success', 'Permintaan pencairan dana berhasil diajukan! Kami akan memproses dalam 1x24 jam.');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
    }
    }

    public function showCampaignWithReferral(Request $request,Campaign $kampanye,$slug,$code)
    {

        $fundraising = Fundraising::where('code_link', $code)->firstOrFail();      
        session(['referral_code' => $code]);
        

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
    
        $totalKampanye = $campaign->where('status', 'aktif')->count();

           // Get guest identifier from cookie
           $guestIdentifier = $request->cookie('guest_identifier');

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
            'request' => request(), 
            'fundraising' => $fundraising,
            'commission' => $commission->amount,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Fundraising $fundraising)
    {
        //
    }

    public function edit(Fundraising $fundraising)
    {
        return view('super_admin.fundraising.detail', compact('fundraising'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Fundraising $fundraising)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Fundraising $fundraising)
    {
        DB::beginTransaction();
        try {
            // Menghapus data donasi
            $fundraising->delete();
    
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Fundraising berhasil dihapus']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Gagal menghapus fundraising: ' . $e->getMessage()], 500);
        }
    }
}
