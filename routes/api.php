<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DonationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/tripay/callback', [DonationController::class, 'callback'])->name('tripay.callback');

Route::get('/api-test', function() {
    return response()->json(['status' => 'API routes loaded']);
});
