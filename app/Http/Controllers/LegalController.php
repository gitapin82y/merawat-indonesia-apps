<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LegalController extends Controller
{
    public function privacyPolicy()
    {
        return view('legal.privacy-policy');
    }

    public function termsOfService()
    {
        return view('legal.terms-of-service');
    }

    public function dataDeletion()
    {
        return view('legal.data-deletion');
    }
 
public function processDeletionRequest(Request $request)
{
    $validated = $request->validate([
        'email' => 'required|email',
        'name' => 'required|string',
        'reason' => 'nullable|string',
        'confirm' => 'required'
    ]);
    
    try {
        // Kirim email ke admin
        Mail::to('merawatindonesia2@gmail.com')
            ->send(new DataDeletionRequestMail($validated));
        
        // Log permintaan
        \Log::info('Data deletion request received', [
            'email' => $validated['email'],
            'name' => $validated['name']
        ]);
        
        // Opsional: Simpan permintaan ke database
        // DataDeletionRequest::create($validated);
        
        return redirect()->back()->with('success', 'Permintaan penghapusan data Anda telah diterima. Kami akan memproses dalam 30 hari dan menghubungi Anda melalui email.');
    } catch (\Exception $e) {
        \Log::error('Error sending data deletion request email: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Terjadi kesalahan saat memproses permintaan Anda. Silakan coba lagi atau hubungi kami melalui email.');
    }
}

}