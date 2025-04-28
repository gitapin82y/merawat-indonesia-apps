<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdsenseController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\FundraisingController;
use App\Http\Controllers\FundraisingWithdrawalController;
use App\Http\Controllers\DonationLikeController;
use App\Http\Controllers\PrioritasCampaignController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\KabarTerbaruController;
use App\Http\Controllers\KabarPencairanController;
use App\Http\Controllers\CampaignWithdrawalController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ManualPaymentMethodController;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\CheckAuth;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\TripayPaymentMethodController;

Route::get('/kampanye/{slug}/ref/{code}', [FundraisingController::class, 'showCampaignWithReferral'])->name('campaign.referral');


Route::get('admin/kampanye/{slug}', [CampaignController::class, 'show'])->name('admin.campaign.detail');
Route::get('kampanye/{slug}', [CampaignController::class, 'donaturKampanye'])->name('campaign.detail');



Route::middleware(['checkRole:yayasan'])->prefix('admin')->group(function () {
    Route::get('/buat-kampanye', function(){
        return view('admin.kampanye.buat-kampanye');
    });
    Route::get('/kampanye/{slug}/edit-kampanye', [CampaignController::class, 'editKampanye']);

    Route::get('/kampanye/{slug}/kabar-terbaru', [KabarTerbaruController::class, 'kabarTerbaru']);
    Route::get('/kampanye/{slug}/kabar-pencairan', [KabarPencairanController::class, 'kabarPencairan']);
    Route::get('/kampanye/{slug}/buat-kabar', [KabarTerbaruController::class, 'buatKabarTerbaru']);
    Route::get('/kampanye/{slug}/pencairan-dana', [KabarPencairanController::class, 'buatKabarPencairan']);
});

Route::middleware(['checkRole:super_admin,yayasan'])->group(function () {
    Route::get('admin/edit-profile', function(){
        return view('admin.edit-profile');
    });
    Route::resource('admin', AdminController::class);

    Route::resource('kampanye', CampaignController::class);

    Route::resource('kabar-terbaru', KabarTerbaruController::class);


Route::resource('kabar-pencairan', KabarPencairanController::class);
});


Route::middleware(['checkAuth'])->group(function () {
    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifikasi/{notification}', [NotificationController::class, 'show'])->name('notifications.show');
    Route::post('/notifikasi/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifikasi/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifikasi/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    Route::get('/profile/fundraising', [FundraisingController::class, 'fundraising'])->name('profile.fundraising.index');
    Route::post('/kampanye/{slug}/join-fundraising', [FundraisingController::class, 'join'])->name('fundraising.join');
    Route::post('/profile/fundraising/withdraw', [FundraisingController::class, 'withdrawFunds'])->name('fundraising.withdraw');
    Route::get('/profile', [UserController::class, 'profileDonatur']);


    Route::resource('user', UserController::class);

});

Route::post('/store-utm-params', function (Request $request) {
    foreach ($request->all() as $key => $value) {
        if (strpos($key, 'utm_') === 0) {
            session([$key => $value]);
        }
    }
    return response()->json(['success' => true]);
});

Route::post('/clear-utm-params', function (Request $request) {
    // Hapus semua parameter UTM dari session
    session()->forget(['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content']);
    return response()->json(['success' => true]);
});

Route::get('/galang-dana/buat-akun', function(){
    return view('donatur.galang-dana.buat-akun');
})->middleware(['checkAuth']);

// donatur
Route::get('/', [UserController::class, 'home']);
Route::get('/eksplore-kampanye', [UserController::class, 'eksplore']);
Route::get('/eksplore', [UserController::class, 'result']);
Route::get('/menu-lainnya', [UserController::class, 'allMenu']);

Route::get('/galang-dana', [AdminController::class, 'galangDana']);


Route::get('/galang-dana/{name}', [AdminController::class, 'profileGalangDana'])->name('galangDanaProfile');
Route::get('/profile-donatur/{name}', [UserController::class, 'profileDonaturLeaderboard'])->name('profileDonatur');

Route::get('/galang-dana/berhasil-buat-akun', function(){
    return view('donatur.galang-dana.berhasil');
});

Route::get('/leaderboard', [UserController::class, 'leaderboard']);

Route::get('/edit-profile', function(){
    return view('donatur.edit-profile');
});
Route::get('/kalkulator-zakat', function(){
    return view('donatur.kalkulator-zakat');
});




// end donatur

Route::post('/donation/{donationId}/like', [DonationLikeController::class, 'store'])->name('donation.like');

Route::resource('donation-likes', DonationLikeController::class);
Route::resource('donations', DonationController::class);
Route::get('/kampanye/{slug}/donasi', [DonationController::class, 'showDonationForm']);


Route::post('/donations/process', [DonationController::class, 'processDonation'])->name('donations.process');
Route::get('/donations/{id}/status', [DonationController::class, 'status'])->name('donations.status');
Route::post('/donations/{id}/mark-expired', [DonationController::class, 'markExpired'])->name('donations.mark-expired');

Route::get('/donations/check-status/{reference}', [DonationController::class, 'checkStatus'])
    ->name('donations.check-status');

Route::get('/donations/{id}/payment-method', [DonationController::class, 'selectPaymentMethod'])->name('donations.select-payment-method');
Route::post('/donations/process-payment', [DonationController::class, 'processPayment'])->name('donations.process-payment');
Route::post('/donations/process-manual-payment', [DonationController::class, 'processManualPayment'])->name('donations.process-manual-payment');

// Callback URL untuk Tripay (harus diakses secara publik)
Route::post('/tripay/callback', [DonationController::class, 'callback'])->name('tripay.callback')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);


Route::resource('campaign-withdrawals', CampaignWithdrawalController::class);


// ->middleware(['auth', 'superadmin'])
Route::post('kampanye/toggle-save', [CampaignController::class, 'toggleSave'])->name('campaign.toggle-save');
Route::middleware(['checkRole:super_admin'])->prefix('super-admin')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');

    Route::get('/commission', [CommissionController::class, 'getCommission'])
    ->name('commission.get');
Route::post('/commission/update', [CommissionController::class, 'updateCommission'])
    ->name('commission.update');
    
    Route::resource('donasi-kampanye', DonationController::class);

    Route::resource('prioritas-kampanye', PrioritasCampaignController::class);

   

    Route::resource('fundraising', FundraisingController::class);
    Route::resource('pencairan-fundraising', FundraisingWithdrawalController::class);
    Route::resource('pencairan-kampanye', CampaignWithdrawalController::class);
    Route::resource('adsense', AdsenseController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('banner', BannerController::class);

    // Add to your routes/web.php
    Route::post('admin/{admin}/change-status', [AdminController::class, 'changeStatus'])->name('admin.change-status');

    Route::get('/pencairan-fundraising/{id}/approve', [FundraisingWithdrawalController::class, 'approve'])->name('pencairan-fundraising.approve');
    Route::get('/pencairan-fundraising/{id}/reject', [FundraisingWithdrawalController::class, 'reject'])->name('pencairan-fundraising.reject');

    Route::post('/upload-image', [CampaignController::class, 'upload'])->name('image.upload');

    Route::post('categories/{id}', [CategoryController::class, 'update']);
    // rev1
    // Route::post('prioritas-kampanye/{id}', [PrioritasCampaignController::class, 'destroy'])->name('prioritas-kampanye.destroy');
    // endrev1
    
    // Route::post('kabar-terbaru/{id}', [KabarTerbaruController::class, 'destroy'])->name('kabar-terbaru.destroy');
    Route::post('/pencairan-fundraising/update-status', [FundraisingWithdrawalController::class, 'updateStatus'])->name('pencairan-fundraising.updateStatus');
    Route::post('/pencairan-kampanye/update-status', [CampaignWithdrawalController::class, 'updateStatus'])->name('pencairan-kampanye.updateStatus');
    Route::get('/pencairan-kampanye/{id}/approve', [CampaignWithdrawalController::class, 'approve'])->name('pencairan-kampanye.approve');
    Route::get('/pencairan-kampanye/{id}/reject', [CampaignWithdrawalController::class, 'reject'])->name('pencairan-kampanye.reject');

    Route::get('ceklis-donasi', [DonationController::class, 'ceklis'])->name('ceklis-donasi.index');
    Route::post('ceklis-donasi/{id}', [DonationController::class, 'destroy'])->name('ceklis-donasi.destroy');
    Route::post('/donasi/update-status', [DonationController::class, 'updateStatus'])->name('donasi.updateStatus');  

    Route::get('/manual-payment-methods', [ManualPaymentMethodController::class, 'index'])->name('manual-payment-methods.index');
    Route::post('/manual-payment-methods', [ManualPaymentMethodController::class, 'store'])->name('manual-payment-methods.store');
    Route::get('/manual-payment-methods/{id}', [ManualPaymentMethodController::class, 'show'])->name('manual-payment-methods.show');
    Route::put('/manual-payment-methods/{id}', [ManualPaymentMethodController::class, 'update'])->name('manual-payment-methods.update');
    Route::delete('/manual-payment-methods/{id}', [ManualPaymentMethodController::class, 'destroy'])->name('manual-payment-methods.destroy');
    Route::patch('/manual-payment-methods/{id}/toggle-status', [ManualPaymentMethodController::class, 'toggleStatus'])->name('manual-payment-methods.toggle-status');

    // Add these routes to your existing super-admin routes
Route::get('/tripay-payment-methods', [TripayPaymentMethodController::class, 'index'])->name('tripay-payment-methods.index');
Route::get('/tripay-payment-methods/fetch', [TripayPaymentMethodController::class, 'fetchFromTripay'])->name('tripay-payment-methods.fetch');
Route::post('/tripay-payment-methods/sync', [TripayPaymentMethodController::class, 'syncPaymentMethods'])->name('tripay-payment-methods.sync');
Route::post('/tripay-payment-methods/toggle-status', [TripayPaymentMethodController::class, 'toggleStatus'])->name('tripay-payment-methods.toggle-status');
});

// custom route
Route::get('campaigns/{campaign}/donations', [CampaignController::class, 'donations']);
Route::get('users/{user}/campaigns', [UserController::class, 'campaigns']);


Route::get('/login', [AuthController::class, 'login']);
Route::post('/login', [AuthController::class, 'loginProcess'])->name('login');
Route::get('/register', [AuthController::class, 'register']);
Route::post('/register', [AuthController::class, 'registerProcess'])->name('register');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
// Password Reset Routes
Route::get('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'resetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'updatePassword'])->name('password.update');


// Social Login Routes
Route::get('/auth/{provider}', [SocialiteController::class, 'redirectToProvider'])->name('social.login');
Route::get('/auth/{provider}/callback', [SocialiteController::class, 'handleProviderCallback'])->name('social.callback');