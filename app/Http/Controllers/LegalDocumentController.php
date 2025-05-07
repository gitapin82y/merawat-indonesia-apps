<?php

namespace App\Http\Controllers;

use App\Models\LegalDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class LegalDocumentController extends Controller
{
    /**
     * Display a listing of legal documents.
     */
    public function index()
    {
        $privacyPolicy = LegalDocument::getByType(LegalDocument::PRIVACY_POLICY);
        $termsOfService = LegalDocument::getByType(LegalDocument::TERMS_OF_SERVICE);
        
        // Convert timestamps to Carbon instances if they aren't already
        if ($privacyPolicy && $privacyPolicy->last_updated && !($privacyPolicy->last_updated instanceof Carbon)) {
            $privacyPolicy->last_updated = Carbon::parse($privacyPolicy->last_updated);
        }
        
        if ($termsOfService && $termsOfService->last_updated && !($termsOfService->last_updated instanceof Carbon)) {
            $termsOfService->last_updated = Carbon::parse($termsOfService->last_updated);
        }
        
        return view('super_admin.legal_documents.index', compact('privacyPolicy', 'termsOfService'));
    }

    /**
     * Edit the specified legal document.
     */
    public function edit($type)
    {
        $document = LegalDocument::getByType($type);
        
        return response()->json([
            'document' => $document
        ]);
    }

    /**
     * Update the specified legal document.
     */
    public function update(Request $request, $type)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $document = LegalDocument::updateOrCreate(
            ['type' => $type],
            [
                'content' => $request->content,
                'last_updated' => now(),
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Dokumen berhasil diperbarui',
            'document' => $document
        ]);
    }

    /**
     * Display privacy policy page.
     */
    public function showPrivacyPolicy()
    {
        $document = LegalDocument::getByType(LegalDocument::PRIVACY_POLICY);
        $content = $document ? $document->content : null;
        
        // Convert timestamp string to Carbon instance if it isn't already
        $lastUpdated = null;
        if ($document && $document->last_updated) {
            $lastUpdated = $document->last_updated instanceof Carbon 
                ? $document->last_updated 
                : Carbon::parse($document->last_updated);
        }
        
        return view('legal.privacy_policy', compact('content', 'lastUpdated'));
    }

    /**
     * Display terms of service page.
     */
    public function showTermsOfService()
    {
        $document = LegalDocument::getByType(LegalDocument::TERMS_OF_SERVICE);
        $content = $document ? $document->content : null;
        
        // Convert timestamp string to Carbon instance if it isn't already
        $lastUpdated = null;
        if ($document && $document->last_updated) {
            $lastUpdated = $document->last_updated instanceof Carbon 
                ? $document->last_updated 
                : Carbon::parse($document->last_updated);
        }
        
        return view('legal.terms_of_service', compact('content', 'lastUpdated'));
    }
}