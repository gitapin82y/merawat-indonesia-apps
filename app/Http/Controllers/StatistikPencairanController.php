<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Campaign;
use App\Models\CampaignWithdrawal;
use App\Models\Donation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class StatistikPencairanController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $admins = Admin::with(['campaigns.donations', 'campaigns.campaignWithdrawals'])->get();
 
            return DataTables::of($admins)
                ->addIndexColumn()
                ->addColumn('avatar', function ($row) {
                    $avatarUrl = $row->avatar
                        ? asset('storage/' . $row->avatar)
                        : asset('assets/img/default/avatar.png');
                    return '<img src="' . $avatarUrl . '" class="rounded-circle" width="40" height="40" style="object-fit:cover;">';
                })
                ->addColumn('leader_name', function ($row) {
                    return $row->leader_name ?? '-';
                })
                ->addColumn('total_kampanye', function ($row) {
                    return $row->campaigns->count();
                })
                ->addColumn('total_donatur', function ($row) {
                    return $row->campaigns->sum(function ($c) {
                        return $c->donations->where('status', 'sukses')->count();
                    });
                })
                ->addColumn('total_donasi', function ($row) {
                    $total = $row->campaigns->sum(function ($c) {
                        return $c->donations->where('status', 'sukses')->sum('amount');
                    });
                    return 'Rp ' . number_format($total, 0, ',', '.');
                })
                ->addColumn('total_pencairan', function ($row) {
                    $total = $row->campaigns->sum(function ($c) {
                        return $c->campaignWithdrawals->where('status', 'disetujui')->sum('amount');
                    });
                    return 'Rp ' . number_format($total, 0, ',', '.');
                })
                ->addColumn('action', function ($row) {
                    return '<a href="' . route('statistik-pencairan.show', $row->id) . '" class="btn btn-danger btn-sm">
                        <i class="fas fa-chart-bar"></i> Detail
                    </a>';
                })
                ->rawColumns(['avatar', 'action'])
                ->make(true);
        }
 
        return view('super_admin.statistik_pencairan.index');
    }
  

    public function show(Request $request, $id)
    {
        $admin = Admin::with([
            'campaigns.donations',
            'campaigns.kabarTerbaru',
            'campaigns.campaignWithdrawals'
        ])->findOrFail($id);

        // Ambil filter
        $filterType = $request->get('filter_type', 'all'); // all, monthly, weekly, daily, range
        $filterMonth = $request->get('month', now()->format('Y-m'));
        $filterWeek  = $request->get('week');
        $filterDay   = $request->get('day', now()->format('Y-m-d'));
        $dateFrom    = $request->get('date_from');
        $dateTo      = $request->get('date_to');

        // Build date range berdasarkan filter
        $startDate = null;
        $endDate   = null;

        switch ($filterType) {
            case 'monthly':
                $startDate = Carbon::parse($filterMonth)->startOfMonth();
                $endDate   = Carbon::parse($filterMonth)->endOfMonth();
                break;
            case 'weekly':
                if ($filterWeek) {
                    [$year, $week] = explode('-W', $filterWeek);
                    $startDate = Carbon::now()->setISODate($year, $week)->startOfWeek();
                    $endDate   = Carbon::now()->setISODate($year, $week)->endOfWeek();
                } else {
                    $startDate = Carbon::now()->startOfWeek();
                    $endDate   = Carbon::now()->endOfWeek();
                }
                break;
            case 'daily':
                $startDate = Carbon::parse($filterDay)->startOfDay();
                $endDate   = Carbon::parse($filterDay)->endOfDay();
                break;
            case 'range':
                $startDate = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : null;
                $endDate   = $dateTo   ? Carbon::parse($dateTo)->endOfDay()     : null;
                break;
            default: // all
                break;
        }

        $campaigns = $admin->campaigns;

        // Hitung statistik dengan filter tanggal pada donasi & pencairan
        $totalDonaturs = $campaigns->sum(function ($campaign) use ($startDate, $endDate) {
            $q = $campaign->donations->where('status', 'sukses');
            if ($startDate && $endDate) {
                $q = $q->filter(fn($d) => Carbon::parse($d->updated_at)->between($startDate, $endDate));
            }
            return $q->count();
        });

        $totalKampanye = $campaigns->count();
        $totalKabarTerbaru = $campaigns->sum(fn($c) => $c->kabarTerbaru->count());

        $totalDoa = $campaigns->sum(function ($campaign) use ($startDate, $endDate) {
            $q = $campaign->donations->where('status', 'sukses')->whereNotNull('doa');
            if ($startDate && $endDate) {
                $q = $q->filter(fn($d) => Carbon::parse($d->updated_at)->between($startDate, $endDate));
            }
            return $q->count();
        });

        $totalDonasiTerkumpul = $campaigns->sum(function ($campaign) use ($startDate, $endDate) {
            $q = $campaign->donations->where('status', 'sukses');
            if ($startDate && $endDate) {
                $q = $q->filter(fn($d) => Carbon::parse($d->updated_at)->between($startDate, $endDate));
            }
            return $q->sum('amount');
        });

        $totalPencairanDana = $campaigns->sum(function ($campaign) use ($startDate, $endDate) {
            $q = $campaign->campaignWithdrawals->where('status', 'disetujui');
            if ($startDate && $endDate) {
                $q = $q->filter(fn($w) => Carbon::parse($w->updated_at)->between($startDate, $endDate));
            }
            return $q->sum('amount');
        });

        // Data pencairan per bulan untuk chart (12 bulan terakhir)
        $pencairanPerBulan = [];
        $donasiPerBulan = [];
        for ($i = 11; $i >= 0; $i--) {
            $bulan = Carbon::now()->subMonths($i);
            $label = $bulan->format('M Y');

            $pencairanBulanIni = $campaigns->sum(function ($c) use ($bulan) {
                return $c->campaignWithdrawals
                    ->where('status', 'disetujui')
                    ->filter(fn($w) => Carbon::parse($w->updated_at)->format('Y-m') === $bulan->format('Y-m'))
                    ->sum('amount');
            });

            $donasiBulanIni = $campaigns->sum(function ($c) use ($bulan) {
                return $c->donations
                    ->where('status', 'sukses')
                    ->filter(fn($d) => Carbon::parse($d->updated_at)->format('Y-m') === $bulan->format('Y-m'))
                    ->sum('amount');
            });

            $pencairanPerBulan[$label] = $pencairanBulanIni;
            $donasiPerBulan[$label]    = $donasiBulanIni;
        }

        // Riwayat pencairan dengan filter
        $withdrawalQuery = CampaignWithdrawal::where('admin_id', $admin->id)
            ->with('campaign')
            ->where('status', 'disetujui');

        if ($startDate && $endDate) {
            $withdrawalQuery->whereBetween('updated_at', [$startDate, $endDate]);
        }

        $riwayatPencairan = $withdrawalQuery->orderBy('updated_at', 'desc')->get();

        return view('super_admin.statistik_pencairan.show', compact(
            'admin',
            'totalDonaturs',
            'totalKampanye',
            'totalKabarTerbaru',
            'totalDoa',
            'totalDonasiTerkumpul',
            'totalPencairanDana',
            'pencairanPerBulan',
            'donasiPerBulan',
            'riwayatPencairan',
            'filterType',
            'filterMonth',
            'filterWeek',
            'filterDay',
            'dateFrom',
            'dateTo',
            'startDate',
            'endDate'
        ));
    }
}