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
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\CheckAuth;

Route::middleware(['checkAuth'])->group(function () {
    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifikasi/{notification}', [NotificationController::class, 'show'])->name('notifications.show');
    Route::post('/notifikasi/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifikasi/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifikasi/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    Route::get('/profile/fundraising', [FundraisingController::class, 'fundraising'])->name('profile.fundraising.index');
    Route::post('/kampanye/{title}/join-fundraising', [FundraisingController::class, 'join'])->name('fundraising.join');
    Route::post('/profile/fundraising/withdraw', [FundraisingController::class, 'withdrawFunds'])->name('fundraising.withdraw');
    Route::get('/profile', [UserController::class, 'profileDonatur']);

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

Route::get('admin/kampanye/{title}', [CampaignController::class, 'show'])->name('admin.campaign.detail');
Route::get('kampanye/{title}', [CampaignController::class, 'donaturKampanye'])->name('campaign.detail');

Route::get('/kampanye/{title}/ref/{code}', [FundraisingController::class, 'showCampaignWithReferral'])->name('campaign.referral');

// end donatur
Route::middleware(['checkRole:admin'])->prefix('admin')->group(function () {

    Route::get('/edit-profile', function(){
        return view('admin.edit-profile');
    });
    Route::get('/buat-kampanye', function(){
        return view('admin.kampanye.buat-kampanye');
    });
    Route::get('/kampanye/{title}/edit-kampanye', [CampaignController::class, 'editKampanye']);

    Route::get('/kampanye/{title}/kabar-terbaru', [KabarTerbaruController::class, 'kabarTerbaru']);
    Route::get('/kampanye/{title}/kabar-pencairan', [KabarPencairanController::class, 'kabarPencairan']);
    Route::get('/kampanye/{title}/buat-kabar', [KabarTerbaruController::class, 'buatKabarTerbaru']);
    Route::get('/kampanye/{title}/pencairan-dana', [KabarPencairanController::class, 'buatKabarPencairan']);
});

Route::post('/donation/{donationId}/like', [DonationLikeController::class, 'store'])->name('donation.like');

Route::resource('commissions', CommissionController::class);
Route::resource('donation-likes', DonationLikeController::class);
Route::resource('donations', DonationController::class);
Route::get('/kampanye/{title}/donasi', [DonationController::class, 'showDonationForm']);


Route::post('/donations/process', [DonationController::class, 'processDonation'])->name('donations.process');
Route::get('/donations/{id}/status', [DonationController::class, 'status'])->name('donations.status');

Route::get('/donations/check-status/{reference}', [DonationController::class, 'checkStatus'])
    ->name('donations.check-status');

// Callback URL untuk Tripay (harus diakses secara publik)
Route::post('/tripay/callback', [DonationController::class, 'callback'])->name('tripay.callback')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);


Route::resource('kabar-pencairan', KabarPencairanController::class);
Route::resource('campaign-withdrawals', CampaignWithdrawalController::class);

// ->middleware(['auth', 'superadmin'])
Route::post('kampanye/toggle-save', [CampaignController::class, 'toggleSave'])->name('campaign.toggle-save');
Route::prefix('super-admin')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::resource('admin', AdminController::class);
    Route::resource('user', UserController::class);
    Route::resource('donasi-kampanye', DonationController::class);
    Route::resource('kampanye', CampaignController::class);
    Route::resource('prioritas-kampanye', PrioritasCampaignController::class);
    Route::resource('kabar-terbaru', KabarTerbaruController::class);
    Route::resource('fundraising', FundraisingController::class);
    Route::resource('pencairan-fundraising', FundraisingWithdrawalController::class);
    Route::resource('pencairan-kampanye', CampaignWithdrawalController::class);
    Route::resource('adsense', AdsenseController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('banner', BannerController::class);

    Route::post('/upload-image', [CampaignController::class, 'upload'])->name('image.upload');

    Route::post('categories/{id}', [CategoryController::class, 'update']);

    Route::post('prioritas-kampanye/{id}', [PrioritasCampaignController::class, 'destroy'])->name('prioritas-kampanye.destroy');
    Route::post('kabar-terbaru/{id}', [KabarTerbaruController::class, 'destroy'])->name('kabar-terbaru.destroy');
    Route::post('/pencairan-fundraising/update-status', [FundraisingWithdrawalController::class, 'updateStatus'])->name('pencairan-fundraising.updateStatus');
    Route::post('/pencairan-kampanye/update-status', [CampaignWithdrawalController::class, 'updateStatus'])->name('pencairan-kampanye.updateStatus');

    Route::get('ceklis-donasi', [DonationController::class, 'ceklis'])->name('ceklis-donasi.index');
    Route::post('ceklis-donasi/{id}', [DonationController::class, 'destroy'])->name('ceklis-donasi.destroy');
    Route::post('/donasi/update-status', [DonationController::class, 'updateStatus'])->name('donasi.updateStatus');  
});

// custom route
Route::get('campaigns/{campaign}/donations', [CampaignController::class, 'donations']);
Route::get('users/{user}/campaigns', [UserController::class, 'campaigns']);


Route::get('/login', [AuthController::class, 'login']);
Route::post('/login', [AuthController::class, 'loginProcess'])->name('login');
Route::get('/register', [AuthController::class, 'register']);
Route::post('/register', [AuthController::class, 'registerProcess'])->name('register');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');