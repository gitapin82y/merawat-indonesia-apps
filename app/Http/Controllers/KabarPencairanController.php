<?php

namespace App\Http\Controllers;

use App\Models\KabarPencairan;
use App\Models\Campaign;
use Illuminate\Http\Request;
use App\Models\CampaignWithdrawal;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class KabarPencairanController extends Controller
{
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
                KabarPencairan::create([
                    "campaign_id" => $withdrawal['campaign_id'],
                    "title" => "Pencairan Dana Rp ". number_format($request->amount, 0, ',', '.'),
                    "description" => "Ke Rekening Bank ".strtoupper($request->payment_method)." *** **** **** **** " . substr($request->account_number, -4) . " a/n ".$request->account_name,
                    "total_amount" => $withdrawal['amount'],
                    "document_rab" => $withdrawal['document_rab'],
                ]);
            }
    
            $campaign = CampaignWithdrawal::create($withdrawal);

            DB::commit();
            return redirect()->back()
                ->with('success', 'Pencairan dana berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menambahkan pencairan dana: ' . $e->getMessage());
        }
    }

    public function kabarPencairan($title)
    {
        $campaign = Campaign::where('title',$title)->first();
        $kabarPencairan = KabarPencairan::where('campaign_id',$campaign->id)->get();
        return view('admin.kampanye.kabar-pencairan', [
            'kabarPencairan' => $kabarPencairan,
            'title' => $title,
        ]);
    }

    public function buatKabarPencairan($title)
    {
        $campaign = Campaign::where('title',$title)->first();
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
