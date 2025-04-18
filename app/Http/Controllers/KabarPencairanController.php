<?php

namespace App\Http\Controllers;

use App\Models\KabarPencairan;
use App\Models\Campaign;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\CampaignWithdrawal;
use App\Mail\CampaignWithdrawalMail;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class KabarPencairanController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'admin_id' => 'required|exists:admins,id',
            'campaign_id' => 'required|exists:campaigns,id',
            'payment_method' => 'required|string|max:255',
            'bukti_pencairan' => 'nullable|string|max:255',
            'account_number' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'amount' => 'nullable|numeric',
            'document_rab' => 'required|mimes:pdf,doc,docx,xls,xlsx|max:5120',
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $campaign = Campaign::with(['kabarPencairan' => function ($query) {
            $query->where('status', 'disetujui');
        }])->where('id', $request->campaign_id)->first();

        if($request->amount >= $campaign->current_donation){
            return redirect()->back()
            ->with('error', 'Jumlah Pencairan Dana Tidak Boleh Melebihi Total Donasi Kampanye Anda');
        }
    
        DB::beginTransaction();
        try {
            $withdrawal = $request->except(['document_rab']);
            
            if ($request->hasFile('document_rab')) {
                $withdrawal['document_rab'] = $request->file('document_rab')->store('campaign_documents', 'public');
                
                // Buat kabar pencairan
                $kabarPencairan = KabarPencairan::create([
                    "campaign_id" => $withdrawal['campaign_id'],
                    "title" => "Pencairan Dana Rp ". number_format($request->amount, 0, ',', '.'),
                    "description" => "Ke Rekening Bank ".strtoupper($request->payment_method)." *** **** **** **** " . substr($request->account_number, -4) . " a/n ".$request->account_name,
                    "total_amount" => $withdrawal['amount'],
                    "document_rab" => $withdrawal['document_rab'],
                ]);
            }
    
            // Buat pencairan dana kampanye
            $campaignWithdrawal = CampaignWithdrawal::create($withdrawal);
            
            // Ambil data admin yang mengajukan
            $admin = Auth::user()->admin;
            
            // Ambil data super admin
            $superAdmin = User::where('role', 'super_admin')->first();
            
            // Format angka untuk tampilan
            $formattedAmount = number_format($request->amount, 0, ',', '.');
       
            $this->notificationService->createNotification(
                $superAdmin,
                'Permintaan Pencairan Dana Kampanye Baru',
                'Admin yayasan '. $admin->name .' mengajukan pencairan dana kampanye "'. $campaign->title .'" sebesar Rp '. $formattedAmount,
                'campaign_withdrawal_request',
                [
                    'campaign_id' => $campaign->id,
                    'campaign_title' => $campaign->title,
                    'withdrawal_id' => $campaignWithdrawal->id,
                    'amount' => $request->amount,
                    'admin_id' => $admin->id,
                    'admin_name' => $admin->name
                ]
            );
            
            // Kirim email ke admin
            Mail::to("apin82y@gmail.com")->send(new CampaignWithdrawalMail($campaignWithdrawal));

            DB::commit();
            return redirect()->back()
                ->with('success', 'Pencairan dana berhasil diajukan! Permintaan Anda akan diproses dalam 1x24 jam kerja.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menambahkan pencairan dana: ' . $e->getMessage());
        }
    }


    // public function store(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'admin_id' => 'required|exists:admins,id',
    //         'campaign_id' => 'required|exists:campaigns,id',
    //         'payment_method' => 'required|string|max:255',
    //         'bukti_pencairan' => 'nullable|string|max:255',
    //         'account_number' => 'required|string|max:255',
    //         'account_name' => 'required|string|max:255',
    //         'amount' => 'nullable|numeric',
    //         'document_rab' => 'required|mimes:pdf,doc,docx,xls,xlsx|max:5120',
    //     ]);
    
    //     if ($validator->fails()) {
    //         return redirect()->back()
    //             ->withErrors($validator)
    //             ->withInput();
    //     }

    //     $campaign = Campaign::with(['kabarPencairan' => function ($query) {
    //         $query->where('status', 'disetujui');
    //     }])->where('id', $request->campaign_id)->first();

    //     if($request->amount >= $campaign->current_donation){
    //         return redirect()->back()
    //         ->with('error', 'Jumlah Pencairan Dana Tidak Boleh Melebihi Total Donasi Kampanye Anda');
    //     }
    
    //     DB::beginTransaction();
    //     try {
    //         $withdrawal = $request->except(['document_rab']);
            
    //         if ($request->hasFile('document_rab')) {
    //             $withdrawal['document_rab'] = $request->file('document_rab')->store('campaign_documents', 'public');
    //             KabarPencairan::create([
    //                 "campaign_id" => $withdrawal['campaign_id'],
    //                 "title" => "Pencairan Dana Rp ". number_format($request->amount, 0, ',', '.'),
    //                 "description" => "Ke Rekening Bank ".strtoupper($request->payment_method)." *** **** **** **** " . substr($request->account_number, -4) . " a/n ".$request->account_name,
    //                 "total_amount" => $withdrawal['amount'],
    //                 "document_rab" => $withdrawal['document_rab'],
    //             ]);
    //         }
    
    //         $campaign = CampaignWithdrawal::create($withdrawal);

    //         DB::commit();
    //         return redirect()->back()
    //             ->with('success', 'Pencairan dana berhasil ditambahkan');
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return redirect()->back()
    //             ->with('error', 'Gagal menambahkan pencairan dana: ' . $e->getMessage());
    //     }
    // }

    public function kabarPencairan($slug)
    {
        $campaign = Campaign::where('slug',$slug)->first();
        $kabarPencairan = KabarPencairan::where('campaign_id',$campaign->id)->get();
        return view('admin.kampanye.kabar-pencairan', [
            'kabarPencairan' => $kabarPencairan,
            'slug' => $slug,
        ]);
    }

    public function buatKabarPencairan($slug)
    {
        $campaign = Campaign::where('slug',$slug)->first();
        return view('admin.kampanye.pencairan-dana', [
            'idKampanye' => $campaign->id,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }
    /**
     * Display the specified resource.
     */
    public function show(KabarPencairan $kabarPencairan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(KabarPencairan $kabarPencairan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, KabarPencairan $kabarPencairan)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(KabarPencairan $kabarPencairan)
    {
        //
    }
}
