<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DonationController;

// routes/api.php
// Pastikan ini sesuai dengan URL callback yang Anda daftarkan di Tripay
Route::middleware('api')->group(function () {
    Route::post('/tripay/callback', [DonationController::class, 'callback'])
        ->name('tripay.callback')
        ->withoutMiddleware('web');  // Hapus middleware web yang mungkin mengandung CSRF
});

Route::get('/api-test', function() {
    return response()->json(['status' => 'API routes loaded']);
});

// Di routes/api.php
Route::post('/tripay-debug', function (Request $request) {
    Log::info('Tripay Debug route hit with POST');
    Log::info('Request headers: ', $request->headers->all());
    Log::info('Request body: ' . $request->getContent());
    
    return response()->json(['success' => true, 'message' => 'Debug route hit successfully']);
});