<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DonationController;
use App\Http\Middleware\TripayIpMiddleware;

// Tripay callback on api.php
Route::post('/tripay/callback', [DonationController::class, 'callback'])
    ->name('tripay.callback');

// Route for testing
Route::get('/test-tripay', function () {
    return response()->json([
        'status' => 'API accessible',
        'ip' => request()->ip(),
        'time' => now()
    ]);
});

// Debug route for Tripay (development only)
Route::post('/tripay-debug', function (Request $request) {
    if (env('APP_ENV') !== 'local') {
        return response()->json(['message' => 'Not allowed'], 403);
    }
    
    Log::info('Tripay Debug route hit with POST');
    Log::info('Request headers: ', $request->headers->all());
    Log::info('Request body: ' . $request->getContent());
    
    return response()->json(['success' => true, 'message' => 'Debug route hit successfully']);
});
Route::get('/test-ip-all', function (Request $request) {
    return response()->json([
        'request_ip' => $request->ip(),
        'server_addr' => $_SERVER['SERVER_ADDR'] ?? null,
        'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? null,
        'x_forwarded_for' => $request->header('X-Forwarded-For'),
        'x_real_ip' => $request->header('X-Real-IP'),
        'all_headers' => $request->headers->all(),
    ]);
});