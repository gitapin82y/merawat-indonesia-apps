@extends('layouts.admin')

@section('title', 'Detail Statistik Pencairan - ' . $admin->name)

@push('after-style')
<style>
    .stat-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 2px 12px rgba(0,0,0,0.07);
        transition: transform 0.2s;
    }
    .stat-card:hover { transform: translateY(-2px); }
    .stat-card .icon-wrap {
        width: 52px; height: 52px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px;
    }
    .stat-number { font-size: 1.6rem; font-weight: 700; color: #202020; }
    .stat-label { font-size: 13px; color: #888; margin-top: 2px; }
    .filter-bar { background: #fff; border-radius: 12px; padding: 18px 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); margin-bottom: 24px; }
    .filter-btn-group .btn { border-radius: 20px !important; font-size: 13px; padding: 5px 16px; margin-right: 6px; margin-bottom: 6px; }
    .profile-header { border-radius: 16px; background: linear-gradient(135deg, #FE0101 0%, #FF4747 100%); color: white; padding: 24px; margin-bottom: 24px; }
    .avatar-wrap img { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid rgba(255,255,255,0.5); }
    .chart-card { border-radius: 12px; border: none; box-shadow: 0 2px 12px rgba(0,0,0,0.07); }
    .table-pencairan th { background-color: #f8f9fa; font-size: 13px; }
    .badge-status { padding: 4px 12px; border-radius: 20px; font-size: 12px; }
</style>
@endpush

@section('content')


{{-- Filter Bar --}}
<div class="filter-bar mb-4">
    <form method="GET" action="{{ route('statistik-pencairan.show', $admin->id) }}" id="filterForm">
        <div class="d-flex align-items-center flex-wrap">
            <strong class="mr-3"><i class="fas fa-filter mr-1 text-danger"></i> Filter:</strong>
            <div class="filter-btn-group">
                <button type="button" class="btn {{ $filterType == 'all' ? 'btn-danger' : 'btn-outline-danger' }} filter-type-btn" data-type="all">Semua</button>
                <button type="button" class="btn {{ $filterType == 'daily' ? 'btn-danger' : 'btn-outline-danger' }} filter-type-btn" data-type="daily">Per Hari</button>
                <button type="button" class="btn {{ $filterType == 'weekly' ? 'btn-danger' : 'btn-outline-danger' }} filter-type-btn" data-type="weekly">Per Minggu</button>
                <button type="button" class="btn {{ $filterType == 'monthly' ? 'btn-danger' : 'btn-outline-danger' }} filter-type-btn" data-type="monthly">Per Bulan</button>
                <button type="button" class="btn {{ $filterType == 'range' ? 'btn-danger' : 'btn-outline-danger' }} filter-type-btn" data-type="range">Rentang Tanggal</button>
            </div>
        </div>

        <input type="hidden" name="filter_type" id="filter_type_input" value="{{ $filterType }}">

        {{-- Daily --}}
        <div id="filter-daily" class="filter-options {{ $filterType == 'daily' ? '' : 'd-none' }} mt-2">
            <div class="d-flex align-items-center">
                <label class="mr-2 mb-0" style="font-size:14px;">Tanggal:</label>
                <input type="date" name="day" class="form-control form-control-sm" style="width:180px;" value="{{ $filterDay ?? now()->format('Y-m-d') }}">
                <button type="submit" class="btn btn-danger btn-sm ml-2">Tampilkan</button>
            </div>
        </div>

        {{-- Weekly --}}
        <div id="filter-weekly" class="filter-options {{ $filterType == 'weekly' ? '' : 'd-none' }} mt-2">
            <div class="d-flex align-items-center">
                <label class="mr-2 mb-0" style="font-size:14px;">Minggu:</label>
                <input type="week" name="week" class="form-control form-control-sm" style="width:200px;" value="{{ $filterWeek ?? now()->format('Y-\WW') }}">
                <button type="submit" class="btn btn-danger btn-sm ml-2">Tampilkan</button>
            </div>
        </div>

        {{-- Monthly --}}
        <div id="filter-monthly" class="filter-options {{ $filterType == 'monthly' ? '' : 'd-none' }} mt-2">
            <div class="d-flex align-items-center">
                <label class="mr-2 mb-0" style="font-size:14px;">Bulan:</label>
                <input type="month" name="month" class="form-control form-control-sm" style="width:180px;" value="{{ $filterMonth ?? now()->format('Y-m') }}">
                <button type="submit" class="btn btn-danger btn-sm ml-2">Tampilkan</button>
            </div>
        </div>

        {{-- Range --}}
        <div id="filter-range" class="filter-options {{ $filterType == 'range' ? '' : 'd-none' }} mt-2">
            <div class="d-flex align-items-center flex-wrap" style="gap:10px;">
                <label class="mb-0" style="font-size:14px;">Dari:</label>
                <input type="date" name="date_from" class="form-control form-control-sm" style="width:170px;" value="{{ $dateFrom }}">
                <label class="mb-0" style="font-size:14px;">Sampai:</label>
                <input type="date" name="date_to" class="form-control form-control-sm" style="width:170px;" value="{{ $dateTo }}">
                <button type="submit" class="btn btn-danger btn-sm">Tampilkan</button>
            </div>
        </div>

        {{-- Label periode aktif --}}
        @if($filterType != 'all' && $startDate && $endDate)
        <div class="mt-2">
            <span class="badge" style="background:rgba(254,1,1,0.1);color:#FE0101;border-radius:20px;padding:5px 14px;font-size:12px;">
                <i class="fas fa-calendar mr-1"></i>
                Periode: {{ $startDate->format('d M Y') }} – {{ $endDate->format('d M Y') }}
            </span>
        </div>
        @endif
    </form>
</div>

{{-- Statistik Cards --}}
<div class="row mb-4">
    <div class="col-6 col-md-4 col-lg-2 mb-3">
        <div class="card stat-card p-3 h-100">
            <div class="icon-wrap mb-2" style="background:rgba(74,144,226,0.12);">
                <i class="fas fa-users text-primary"></i>
            </div>
            <div class="stat-number">{{ number_format($totalDonaturs) }}</div>
            <div class="stat-label">Donatur</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2 mb-3">
        <div class="card stat-card p-3 h-100">
            <div class="icon-wrap mb-2" style="background:rgba(80,200,120,0.12);">
                <i class="fas fa-bullhorn" style="color:#50c878;"></i>
            </div>
            <div class="stat-number">{{ number_format($totalKampanye) }}</div>
            <div class="stat-label">Total Kampanye</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2 mb-3">
        <div class="card stat-card p-3 h-100">
            <div class="icon-wrap mb-2" style="background:rgba(255,149,43,0.12);">
                <i class="fas fa-newspaper" style="color:#ff952b;"></i>
            </div>
            <div class="stat-number">{{ number_format($totalKabarTerbaru) }}</div>
            <div class="stat-label">Kabar Terbaru</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2 mb-3">
        <div class="card stat-card p-3 h-100">
            <div class="icon-wrap mb-2" style="background:rgba(255,71,71,0.12);">
                <i class="fas fa-pray" style="color:#ff4747;"></i>
            </div>
            <div class="stat-number">{{ number_format($totalDoa) }}</div>
            <div class="stat-label">Doa Sahabat Baik</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2 mb-3">
        <div class="card stat-card p-3 h-100">
            <div class="icon-wrap mb-2" style="background:rgba(74,144,226,0.12);">
                <i class="fas fa-donate text-primary"></i>
            </div>
            <div class="stat-number" style="font-size:1.1rem;">Rp {{ number_format($totalDonasiTerkumpul, 0, ',', '.') }}</div>
            <div class="stat-label">Total Donasi Terkumpul</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2 mb-3">
        <div class="card stat-card p-3 h-100">
            <div class="icon-wrap mb-2" style="background:rgba(254,1,1,0.12);">
                <i class="fas fa-hand-holding-usd text-danger"></i>
            </div>
            <div class="stat-number" style="font-size:1.1rem;">Rp {{ number_format($totalPencairanDana, 0, ',', '.') }}</div>
            <div class="stat-label">Total Pencairan Dana</div>
        </div>
    </div>
</div>

{{-- Chart --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card chart-card p-4">
            <h6 class="font-weight-bold mb-3"><i class="fas fa-chart-bar text-danger mr-2"></i> Grafik Donasi & Pencairan (12 Bulan Terakhir)</h6>
            <canvas id="pencairanChart" height="100"></canvas>
        </div>
    </div>
</div>

{{-- Riwayat Pencairan --}}
<div class="card chart-card mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="font-weight-bold mb-0"><i class="fas fa-history text-danger mr-2"></i> Riwayat Pencairan Dana Disetujui</h6>
    </div>
    <div class="card-body p-0">
        @if($riwayatPencairan->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover table-pencairan mb-0">
                <thead>
                    <tr>
                        <th class="pl-4">No</th>
                        <th>Kampanye</th>
                        <th>Jumlah</th>
                        <th>Metode</th>
                        <th>Atas Nama</th>
                        <th>No. Rekening</th>
                        <th>Tanggal Disetujui</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($riwayatPencairan as $i => $withdrawal)
                    <tr>
                        <td class="pl-4">{{ $i + 1 }}</td>
                        <td>{{ $withdrawal->campaign->title ?? '-' }}</td>
                        <td><strong class="text-danger">Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}</strong></td>
                        <td><span class="text-uppercase">{{ $withdrawal->payment_method }}</span></td>
                        <td>{{ $withdrawal->account_name }}</td>
                        <td>{{ $withdrawal->account_number }}</td>
                        <td>{{ \Carbon\Carbon::parse($withdrawal->updated_at)->format('d M Y, H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background:#fff5f5;">
                        <td colspan="2" class="pl-4 font-weight-bold">Total</td>
                        <td colspan="5" class="font-weight-bold text-danger">
                            Rp {{ number_format($riwayatPencairan->sum('amount'), 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @else
        <div class="text-center py-5 text-muted">
            <i class="fas fa-inbox fa-3x mb-3"></i>
            <p>Tidak ada riwayat pencairan pada periode ini.</p>
        </div>
        @endif
    </div>
</div>

@endsection

@push('after-script')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Filter type toggle
document.querySelectorAll('.filter-type-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var type = this.dataset.type;
        document.getElementById('filter_type_input').value = type;

        // Update button styles
        document.querySelectorAll('.filter-type-btn').forEach(function(b) {
            b.classList.remove('btn-danger');
            b.classList.add('btn-outline-danger');
        });
        this.classList.remove('btn-outline-danger');
        this.classList.add('btn-danger');

        // Show/hide filter options
        document.querySelectorAll('.filter-options').forEach(function(el) {
            el.classList.add('d-none');
        });

        if (type !== 'all') {
            var target = document.getElementById('filter-' + type);
            if (target) target.classList.remove('d-none');
        } else {
            // Submit immediately for "all"
            document.getElementById('filterForm').submit();
        }
    });
});

// Chart
var labels = @json(array_keys($pencairanPerBulan));
var pencairanData = @json(array_values($pencairanPerBulan));
var donasiData = @json(array_values($donasiPerBulan));

var ctx = document.getElementById('pencairanChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [
            {
                label: 'Total Donasi',
                data: donasiData,
                backgroundColor: 'rgba(74,144,226,0.7)',
                borderColor: 'rgba(74,144,226,1)',
                borderWidth: 1,
                borderRadius: 6,
            },
            {
                label: 'Total Pencairan',
                data: pencairanData,
                backgroundColor: 'rgba(254,1,1,0.7)',
                borderColor: 'rgba(254,1,1,1)',
                borderWidth: 1,
                borderRadius: 6,
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top' },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': Rp ' + context.parsed.y.toLocaleString('id-ID');
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        if (value >= 1000000) return 'Rp ' + (value/1000000).toFixed(1) + 'jt';
                        return 'Rp ' + value.toLocaleString('id-ID');
                    }
                }
            }
        }
    }
});
</script>
@endpush