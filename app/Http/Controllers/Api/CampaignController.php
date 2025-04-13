<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class CampaignController extends Controller
{
    public function index(Request $request)
    {
        $query = Campaign::with(['admin', 'category']);

        // Filtering
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // Search
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting
        if ($request->has('sort')) {
            $sortField = $request->input('sort', 'created_at');
            $sortDirection = $request->input('direction', 'desc');
            $query->orderBy($sortField, $sortDirection);
        }

        // Pagination
        $campaigns = $query->paginate($request->input('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $campaigns
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'admin_id' => 'required|exists:admins,id',
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:aktif,selesai,ditolak,validasi,berakhir',
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'document_rab' => 'required|file|mimes:pdf,doc,docx|max:5120',
            'deadline' => 'nullable|date',
            'jumlah_target_donasi' => 'nullable|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 400);
        }

        $campaignData = $request->except(['photo', 'document_rab']);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('campaign_photos', 'public');
            $campaignData['photo'] = $photoPath;
        }

        // Handle document upload
        if ($request->hasFile('document_rab')) {
            $documentPath = $request->file('document_rab')->store('campaign_documents', 'public');
            $campaignData['document_rab'] = $documentPath;
        }

        $campaign = Campaign::create($campaignData);

        return response()->json([
            'status' => 'success',
            'message' => 'Campaign created successfully',
            'data' => $campaign
        ], 201);
    }

    public function show($id)
    {
        $campaign = Campaign::with([
            'admin', 
            'category', 
            'donations', 
            'fundraisings', 
            'kabarTerbaru', 
            'kabarPencairan', 
            'campaignWithdrawals'
        ])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $campaign
        ]);
    }

    public function update(Request $request, $id)
    {
        $campaign = Campaign::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'category_id' => 'sometimes|exists:categories,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'status' => 'sometimes|in:aktif,selesai,ditolak,validasi,berakhir',
            'photo' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'document_rab' => 'sometimes|file|mimes:pdf,doc,docx|max:5120',
            'deadline' => 'nullable|date',
            'jumlah_target_donasi' => 'nullable|numeric',
            'bukti_pencairan_dana' => 'sometimes|file|mimes:jpeg,png,jpg,pdf|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 400);
        }

        $campaignData = $request->except(['photo', 'document_rab', 'bukti_pencairan_dana']);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo
            if ($campaign->photo) {
                Storage::disk('public')->delete($campaign->photo);
            }
            $photoPath = $request->file('photo')->store('campaign_photos', 'public');
            $campaignData['photo'] = $photoPath;
        }

        // Handle document upload
        if ($request->hasFile('document_rab')) {
            // Delete old document
            if ($campaign->document_rab) {
                Storage::disk('public')->delete($campaign->document_rab);
            }
            $documentPath = $request->file('document_rab')->store('campaign_documents', 'public');
            $campaignData['document_rab'] = $documentPath;
        }

        // Handle bukti pencairan dana
        if ($request->hasFile('bukti_pencairan_dana')) {
            // Delete old bukti
            if ($campaign->bukti_pencairan_dana) {
                Storage::disk('public')->delete($campaign->bukti_pencairan_dana);
            }
            $pencairanPath = $request->file('bukti_pencairan_dana')->store('pencairan_dana', 'public');
            $campaignData['bukti_pencairan_dana'] = $pencairanPath;
        }

        $campaign->update($campaignData);

        return response()->json([
            'status' => 'success',
            'message' => 'Campaign updated successfully',
            'data' => $campaign
        ]);
    }

    public function destroy($id)
    {
        $campaign = Campaign::findOrFail($id);

        // Delete associated files
        if ($campaign->photo) {
            Storage::disk('public')->delete($campaign->photo);
        }
        if ($campaign->document_rab) {
            Storage::disk('public')->delete($campaign->document_rab);
        }
        if ($campaign->bukti_pencairan_dana) {
            Storage::disk('public')->delete($campaign->bukti_pencairan_dana);
        }

        $campaign->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Campaign deleted successfully'
        ]);
    }

    // Additional methods
    public function getCampaignDonations($id)
    {
        $campaign = Campaign::findOrFail($id);
        $donations = $campaign->donations()->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $donations
        ]);
    }

    public function getCampaignFundraisings($id)
    {
        $campaign = Campaign::findOrFail($id);
        $fundraisings = $campaign->fundraisings()->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $fundraisings
        ]);
    }
}