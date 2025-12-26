<?php

namespace App\Http\Controllers;

use App\Models\KabarTerbaru;
use Illuminate\Http\Request;
use App\Models\Campaign;
use App\Models\Admin;
use App\Models\Category;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Mail\CampaignUpdateMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Models\Donation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;



class KabarTerbaruController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if($user->role !== 'super_admin'){
            return redirect()->back();
        }

        Carbon::setLocale('id');
    
        if ($request->ajax()) {
            $query = KabarTerbaru::with(['campaign', 'campaign.category', 'campaign.admin'])
            ->select('campaign_id', DB::raw('MAX(id) as max_id'))
            ->groupBy('campaign_id')
            ->get()
            ->map(function ($item) {
                // Ambil kabar terbaru terbaru dari setiap campaign
                return KabarTerbaru::with(['campaign', 'campaign.category', 'campaign.admin'])
                    ->where('id', $item->max_id)
                    ->first();
            });

            $campaignCounts = KabarTerbaru::select('campaign_id', DB::raw('count(*) as total'))
                   ->groupBy('campaign_id')
                   ->pluck('total', 'campaign_id')
                   ->toArray();
    
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('title', function($row) {
                    return $row->campaign?->title ?? 'N/A'; // Pastikan title diambil dari campaign
                })
                ->addColumn('category', function($row) {
                    return $row->campaign?->category?->name ?? 'N/A'; // Pastikan category tidak null
                })
                ->addColumn('created_at', function($row) {
                    return $row->created_at 
                    ? Carbon::parse($row->created_at)->timezone('Asia/Jakarta')->format('d M Y')
                    : '-';
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
                         <a href="'.route('kabar-terbaru.edit', $row->campaign->id).'" class="btn text-white btn-info btn-sm">Lihat Kabar Terbaru</a>
                            <button onclick="deleteKabarTerbaru('.$row->campaign->id.')" class="btn btn-warning btn-sm"><i class="fas fa-times text-white"></i></button>
                        </div>
                    ';
                })
                ->addColumn('total', function($row) use ($campaignCounts) {
                    $count = $campaignCounts[$row->campaign_id] ?? 0;
                    return '<span class="badge bg-primary text-white">'.$count.'</span>';
                })                
                ->rawColumns(['total','category', 'status', 'admin', 'action'])
                ->make(true);
        }
    
        return view('super_admin.kabar_terbaru.index');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'campaign_id' => 'required|exists:campaigns,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
    
        DB::beginTransaction();
        try {
            $kabarTerbaru = KabarTerbaru::create($request->all());
            $campaign = Campaign::findOrFail($request->campaign_id);
            $this->notifyDonors($kabarTerbaru, $campaign);
            DB::commit();
            return redirect('admin/kampanye/' . $kabarTerbaru->campaign->slug . '/kabar-terbaru')
                ->with('success', 'Kabar Terbaru berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menambahkan kabar terbaru: ' . $e->getMessage());
        }
    }

        /**
     * Notify all donors of this campaign about the new update
     *
     * @param  \App\Models\KabarTerbaru  $kabarTerbaru
     * @param  \App\Models\Campaign  $campaign
     * @return void
     */
    private function notifyDonors(KabarTerbaru $kabarTerbaru, Campaign $campaign)
    {
        try {
            // Get all unique donors who have made a donation to this campaign
            // Only consider successful donations (adjust the status as needed)
            $donations = Donation::where('campaign_id', $campaign->id)
                               ->where('status', 'sukses')
                               ->get();
            
            \Log::info('Notifying donors for campaign: ' . $campaign->id . ' - ' . $campaign->title);
            \Log::info('Found ' . $donations->count() . ' donations with status "success"');
            
            // Group donors by email to avoid duplicate notifications
            $uniqueDonors = [];
            foreach ($donations as $donation) {
                // Use the email as the key to avoid duplicates
                $uniqueDonors[$donation->email] = [
                    'name' => $donation->name,
                    'email' => $donation->email
                ];
            }
            
            \Log::info('Sending notifications to ' . count($uniqueDonors) . ' unique donors');
            
            // Send notification to each donor
            foreach ($uniqueDonors as $donor) {
                $this->sendUpdateEmail($donor, $kabarTerbaru, $campaign);
            }
            
            \Log::info('Notification process completed');
        } catch (\Exception $e) {
            \Log::error('Error in notifyDonors: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            // Don't rethrow the exception - we don't want to fail the entire transaction
            // just because notifications couldn't be sent
        }
    }

        public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $kabar = KabarTerbaru::findOrFail($id);
        $kabar->update([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return redirect()->back()->with('success', 'Kabar terbaru berhasil diperbarui');
    }
    
    /**
     * Send an email to a donor about the campaign update
     *
     * @param array $donor
     * @param \App\Models\KabarTerbaru $kabarTerbaru
     * @param \App\Models\Campaign $campaign
     */
    private function sendUpdateEmail($donor, $kabarTerbaru, $campaign)
    {
        try {
            \Log::info('Sending email to: ' . $donor['email']);
            
            if (!filter_var($donor['email'], FILTER_VALIDATE_EMAIL)) {
            \Log::warning('Invalid email skipped: ' . $donor['email']);
            return;
            }

            Mail::to($donor['email'])
                ->queue(new CampaignUpdateMail($donor, $campaign, $kabarTerbaru));
                
            \Log::info('Email queued successfully for: ' . $donor['email']);
        } catch (\Exception $e) {
            \Log::error('Failed to send email to ' . $donor['email'] . ': ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
        }
    }

    public function buatKabarTerbaru($slug)
    {
        $campaign = Campaign::where('slug',$slug)->first();
        return view('admin.kampanye.buat-kabar', [
            'idKampanye' => $campaign->id,
            'slug' => $slug
        ]);
    }

    public function kabarTerbaru($slug)
    {
        $campaign = Campaign::where('slug',$slug)->first();
        $kabarTerbaru = KabarTerbaru::where('campaign_id',$campaign->id)->get();
        return view('admin.kampanye.kabar-terbaru', [
            'kabarTerbaru' => $kabarTerbaru,
            'slug' => $slug,
        ]);
    }


    public function edit($id)
    {
        $kabarTerbaru = KabarTerbaru::whereHas('campaign', function($query) use ($id) {
            $query->where('id', $id);
        })->get();
        return view('super_admin.kabar_terbaru.form', compact('kabarTerbaru'));
    }
    
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            KabarTerbaru::where('campaign_id',$id)->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Kabar Terbaru berhasil dihapus']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menghapus admin: ' . $e->getMessage()], 500);
        }
    }
}