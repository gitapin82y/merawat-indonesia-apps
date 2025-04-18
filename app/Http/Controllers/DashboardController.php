<?php

namespace App\Http\Controllers;
use App\Models\Donation;
use App\Models\CampaignWithdrawal;
use App\Models\FundraisingWithdrawal;
use App\Models\Campaign;
use App\Models\DonationSource;
use App\Models\Admin;
use App\Models\Adsense;
use Carbon\Carbon;


use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
{
    // Data untuk statistik donasi dasar
    $totalDonasi = Donation::where('status', 'sukses')->sum('amount');
    $pencairanDonasi = CampaignWithdrawal::where('status', 'disetujui')->sum('amount');
    $sisaDonasi = $totalDonasi - $pencairanDonasi;
    $pencairanFundraising = FundraisingWithdrawal::where('status', 'disetujui')->sum('amount');
    $totalKampanye = Campaign::count();
    
    // Statistik donasi berdasarkan sumber
    $googleAdsDonations = Donation::where('status', 'sukses')
        ->where('utm_source', 'like', '%google%')
        ->count();
    
    $fbAdsDonations = Donation::where('status', 'sukses')
        ->where(function($query) {
            $query->where('utm_source', 'like', '%facebook%')
                  ->orWhere('utm_source', 'like', '%fb%')
                  ->orWhere('utm_source', 'like', '%meta%');
        })
        ->count();
    
    $tiktokAdsDonations = Donation::where('status', 'sukses')
        ->where('utm_source', 'like', '%tiktok%')
        ->count();
    
    $totalAdsDonations = $googleAdsDonations + $fbAdsDonations + $tiktokAdsDonations;
    
    // Jumlah donasi dari iklan (dalam Rupiah)
    $googleAdsDonationAmount = Donation::where('status', 'sukses')
        ->where('utm_source', 'like', '%google%')
        ->sum('amount');
    
    $fbAdsDonationAmount = Donation::where('status', 'sukses')
        ->where(function($query) {
            $query->where('utm_source', 'like', '%facebook%')
                  ->orWhere('utm_source', 'like', '%fb%')
                  ->orWhere('utm_source', 'like', '%meta%');
        })
        ->sum('amount');
    
    $tiktokAdsDonationAmount = Donation::where('status', 'sukses')
        ->where('utm_source', 'like', '%tiktok%')
        ->sum('amount');
    
    $totalAdsDonationAmount = $googleAdsDonationAmount + $fbAdsDonationAmount + $tiktokAdsDonationAmount;
    
    // Statistik donasi harian dan bulanan
    $today = Carbon::today();
    $monthStart = Carbon::today()->startOfMonth();
    
    $donasiToday = Donation::where('status', 'sukses')
        ->whereDate('updated_at', $today)
        ->count();
    
    $donasiMonth = Donation::where('status', 'sukses')
        ->whereDate('updated_at', '>=', $monthStart)
        ->count();
    
    $donasiAmountToday = Donation::where('status', 'sukses')
        ->whereDate('updated_at', $today)
        ->sum('amount');
    
    $donasiAmountMonth = Donation::where('status', 'sukses')
        ->whereDate('updated_at', '>=', $monthStart)
        ->sum('amount');
    
    // Total akun admin
    $totalAdmin = Admin::count();
    
    // Pengaturan iklan
    $adsense = Adsense::first();

      
    // Menyiapkan array untuk card dashboard
    $cards = [
        ["title" => "Jumlah Donasi", "value" => "Rp " . number_format($totalDonasi)],
        ["title" => "Pencairan Donasi", "value" => "Rp " . number_format($pencairanDonasi)],
        ["title" => "Sisa Donasi Saat Ini", "value" => "Rp " . number_format($sisaDonasi)],
        ["title" => "Pencairan Fundraising", "value" => "Rp " . number_format($pencairanFundraising)],
        ["title" => "Total Kampanye", "value" => number_format($totalKampanye)],
        ["title" => "Donasi dari Google Ads", "value" => $googleAdsDonations . "x"],
        ["title" => "Donasi dari FB Ads", "value" => $fbAdsDonations . "x"],
        ["title" => "Donasi dari Tiktok Ads", "value" => $tiktokAdsDonations . "x"],
        ["title" => "Total Donasi dari Ads", "value" => $totalAdsDonations . "x"],
        ["title" => "Jumlah Donasi Dari Ads", "value" => "Rp " . number_format($totalAdsDonationAmount)],
        ["title" => "Donasi Hari Ini", "value" => $donasiToday . "x"],
        ["title" => "Donasi Bulan Ini", "value" => $donasiMonth . "x"],
        ["title" => "Jumlah Donasi Hari Ini", "value" => "Rp " . number_format($donasiAmountToday)],
        ["title" => "Jumlah Donasi Bulan Ini", "value" => "Rp " . number_format($donasiAmountMonth)],
        ["title" => "Total Akun Admin", "value" => number_format($totalAdmin)]
    ];

        // Data untuk grafik bulanan total donasi
        $monthlyDonations = Donation::where('status', 'sukses')
        ->selectRaw('MONTH(created_at) as month, SUM(amount) as total')
        ->whereYear('created_at', date('Y')) // Tahun saat ini (2025)
        ->groupBy('month')
        ->orderBy('month')
        ->get();


    // Menyiapkan array data donasi bulanan untuk chart
    $monthlyData = [];
    for ($i = 1; $i <= 12; $i++) {
        $monthData = $monthlyDonations->where('month', $i)->first();
        $monthlyData[$i] = $monthData ? $monthData->total : 0;
    }
    
    // Data untuk pie chart kategori
    $categoryDonations = Donation::join('campaigns', 'donations.campaign_id', '=', 'campaigns.id')
        ->join('categories', 'campaigns.category_id', '=', 'categories.id')
        ->where('donations.status', 'sukses')
        ->selectRaw('categories.name, SUM(donations.amount) as total')
        ->groupBy('categories.id', 'categories.name')
        ->get();
        
    // Menyiapkan data kategori untuk pie chart
    $categoryData = [];
    foreach ($categoryDonations as $donation) {
        $categoryData[$donation->name] = $donation->total;
    }
    
  
    
    return view('super_admin.dashboard', compact('cards', 'adsense', 'monthlyData', 'categoryData'));
}

}
