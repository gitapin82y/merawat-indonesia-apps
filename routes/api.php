<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DonationController;
use App\Http\Middleware\TripayIpMiddleware;

// Tripay callback route with IP middleware
Route::post('/tripay/callback', [DonationController::class, 'callback']);
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