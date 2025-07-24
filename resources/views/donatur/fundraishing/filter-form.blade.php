<!-- Filter Section - Tambahkan sebelum div "Fundraising Kampanye Anda" -->
<div class="container mt-4">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <h6 class="mb-2 text-secondary">Filter Perolehan Donasi Fundraising</h6>
            <small class="text-muted mb-3 d-block">Filter berdasarkan tanggal donasi yang masuk dari referral link Anda</small>
            
            <form id="filterForm" method="GET" action="{{ route('profile.fundraising.index') }}">
                <div class="row g-3">
                    <!-- Filter Type -->
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label small px-0 mx-0">Jenis Filter</label>
                        <select name="filter_type" id="filter_type" class="form-select form-select-sm">
                            <option value="all" {{ ($filterData['filter_type'] ?? 'all') == 'all' ? 'selected' : '' }}>Semua Donasi</option>
                            <option value="daily" {{ ($filterData['filter_type'] ?? '') == 'daily' ? 'selected' : '' }}>Donasi Harian</option>
                            <option value="monthly" {{ ($filterData['filter_type'] ?? '') == 'monthly' ? 'selected' : '' }}>Donasi Bulanan</option>
                            <option value="range" {{ ($filterData['filter_type'] ?? '') == 'range' ? 'selected' : '' }}>Rentang Tanggal Donasi</option>
                        </select>
                    </div>
                    
                    <!-- Daily Filter -->
                    <div class="col-md-3 col-sm-6" id="daily_filter" style="display: none;">
                        <label class="form-label small px-0 mx-0">Tanggal Donasi</label>
                        <input type="date" name="date" id="date" class="form-control form-control-sm" 
                               value="{{ $filterData['date'] ?? '' }}" max="{{ date('Y-m-d') }}">
                    </div>
                    
                    <!-- Monthly Filter -->
                    <div class="col-md-3 col-sm-6" id="monthly_filter" style="display: none;">
                        <label class="form-label small px-0 mx-0">Bulan Donasi</label>
                        <input type="month" name="month" id="month" class="form-control form-control-sm" 
                               value="{{ $filterData['month'] ?? '' }}" max="{{ date('Y-m') }}">
                    </div>
                    
                    <!-- Range Filter -->
                    <div class="col-md-6" id="range_filter" style="display: none;">
                        <div class="row">
                            <div class="col-6">
                                <label class="form-label small px-0 mx-0">Tanggal Donasi Mulai</label>
                                <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" 
                                       value="{{ $filterData['start_date'] ?? '' }}" max="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label small px-0 mx-0">Tanggal Donasi Akhir</label>
                                <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" 
                                       value="{{ $filterData['end_date'] ?? '' }}" max="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Filter Actions -->
                    <div class="col-md-6 col-sm-6 d-flex align-items-end">
                        <div class="w-100">
                            <button type="submit" class="btn btn-sm btn-primary me-2">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="{{ route('profile.fundraising.index') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-refresh"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Filter Summary -->
                @if(($filterData['filter_type'] ?? 'all') != 'all')
                <div class="mt-3 p-2 bg-light rounded">
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i> 
                        Filter aktif - Perolehan donasi: 
                        @if(($filterData['filter_type'] ?? '') == 'daily' && $filterData['date'])
                            Harian {{ \Carbon\Carbon::parse($filterData['date'])->format('d/m/Y') }}
                        @elseif(($filterData['filter_type'] ?? '') == 'monthly' && $filterData['month'])
                            Bulanan {{ \Carbon\Carbon::parse($filterData['month'])->format('F Y') }}
                        @elseif(($filterData['filter_type'] ?? '') == 'range' && $filterData['start_date'] && $filterData['end_date'])
                            {{ \Carbon\Carbon::parse($filterData['start_date'])->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($filterData['end_date'])->format('d/m/Y') }}
                        @endif
                    </small>
                </div>
                @endif
            </form>
        </div>
    </div>
</div>

<style>
    .card {
        border-radius: 12px;
    }
    
    .form-select-sm, .form-control-sm {
        border-radius: 6px;
        border: 1px solid #e0e0e0;
    }
    
    .form-select-sm:focus, .form-control-sm:focus {
        border-color: var(--second-color, #007bff);
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    .btn-sm {
        border-radius: 6px;
        font-size: 12px;
        padding: 6px 12px;
    }
    
    .btn-primary {
        background-color: var(--second-color, #007bff);
        border-color: var(--second-color, #007bff);
    }
    
    .bg-light {
        background-color: #f8f9fa !important;
    }
    
    @media (max-width: 576px) {
        .card-body {
            padding: 1rem;
        }
        
        .btn-sm {
            font-size: 11px;
            padding: 5px 10px;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterType = document.getElementById('filter_type');
    const dailyFilter = document.getElementById('daily_filter');
    const monthlyFilter = document.getElementById('monthly_filter');
    const rangeFilter = document.getElementById('range_filter');
    
    function showHideFilters() {
        const selectedType = filterType.value;
        
        // Hide all filters first
        dailyFilter.style.display = 'none';
        monthlyFilter.style.display = 'none';
        rangeFilter.style.display = 'none';
        
        // Show relevant filter
        switch(selectedType) {
            case 'daily':
                dailyFilter.style.display = 'block';
                break;
            case 'monthly':
                monthlyFilter.style.display = 'block';
                break;
            case 'range':
                rangeFilter.style.display = 'block';
                break;
        }
    }
    
    // Show appropriate filter on page load
    showHideFilters();
    
    // Handle filter type change
    filterType.addEventListener('change', showHideFilters);
    
    // Validate date range
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');
    
    if (startDate && endDate) {
        startDate.addEventListener('change', function() {
            endDate.min = this.value;
        });
        
        endDate.addEventListener('change', function() {
            startDate.max = this.value;
        });
    }
});
</script>