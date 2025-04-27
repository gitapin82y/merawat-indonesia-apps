<?php

namespace App\Http\Controllers;

use App\Models\Fundraising;
use App\Models\Campaign;
use App\Models\Donation;
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
            $query = Fundraising::with(['campaign','user'])->get();
            
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('name', function ($row) {
                    return $row->user->name;
                })    
                ->addColumn('email', function($row) {
                    return $row->user->email;
                })
                ->addColumn('commission', function($row) {
                    $jumlahKomisi = $row->jumlah_donasi * ($row->commission / 100); // Menghitung jumlah komisi
                    return $row->commission . '% | Rp ' . number_format($jumlahKomisi, 0, ',', '.');
                })                
                ->addColumn('total_donatur', function($row) {
                    return '<span class="badge bg-primary text-white">'.$row->total_donatur.'</span>';
                })
                ->addColumn('jumlah_donasi', function($row) {
                    return 'Rp ' . number_format($row->jumlah_donasi, 0, ',', '.');
                })                
                ->addColumn('action', function($row) {
                    $whatsappUrl = "https://wa.me/". $row->user->phone;
                    
                    $actionBtn = '<div class="btn-group" role="group">';
                    $actionBtn .= '
                        <a href="'.$whatsappUrl.'" target="_blank" class="btn btn-success btn-sm">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                          <a href="'.route('fundraising.edit', $row->id).'" class="btn btn-info btn-sm"><i class="fa-solid fa-eye text-white"></i></a>
                        <button onclick="deleteFundraising('.$row->id.')" class="btn btn-danger btn-sm">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>'; // Tutup div.btn-group
                
                    return $actionBtn;
                })            
                ->rawColumns(['name','email','commission','total_donatur','jumlah_donasi','action'])
                 ->make(true);
        }
        
        return view('super_admin.fundraising.index');
    }

    public function fundraising()
    {
        $user = Auth::user();
        $fundraisings = Fundraising::where('user_id', $user->id)
            ->with('campaign')
            ->get();
        
        $totalCommission = $fundraisings->sum('commission');
        return view('donatur.fundraishing.index', compact('fundraisings', 'totalCommission'));
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
        
        $admin = User::where('role', 'super_admin')->first();

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

        Mail::to("apin82y@gmail.com")->send(new FundraisingWithdrawalMail($withdrawal));

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
