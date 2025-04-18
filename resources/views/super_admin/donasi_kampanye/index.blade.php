@extends('layouts.admin')
 
@section('title', 'Semua Data Donasi Kampanye')

@push('after-style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .modal-header {
        background-color: #e74a3b;
        color: white;
    }
    .btn-apply-filter {
        background-color: #e74a3b;
        border-color: #e74a3b;
    }
</style>
@endpush

@section('content')

    <!-- Page Heading -->
    <div class="card mb-4">
        <div class="card-header bg-white py-3 align-items-center justify-content-between row m-0">
            <div class="col-12 col-sm-6 p-0">
                <h4 class="m-0 font-weight-bold float-left text-danger">Semua Data Donasi Kampanye</h4>
            </div>
            <div class="col-12 col-sm-6">
                <button type="button" data-toggle="modal" data-target="#dateFilterModal" class="btn btn-danger float-left mt-3 mt-sm-0 float-sm-right shadow-sm ml-2">
                    <i class="fas fa-calendar fa-sm mr-1"></i> Filter Tanggal
                </button>
                <button type="button" data-toggle="modal" data-target="#campaignFilterModal" class="btn btn-danger float-left mt-3 mt-sm-0 float-sm-right shadow-sm">
                    <i class="fas fa-filter fa-sm mr-1"></i> Filter Kampanye
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Current Filters Display -->
            <div id="active-filters" class="mb-3">
                <!-- Active filters will be displayed here -->
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered yajra-datatable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kampanye</th>
                            <th>Donatur</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Campaign Filter Modal -->
    <div class="modal fade" id="campaignFilterModal" tabindex="-1" role="dialog" aria-labelledby="campaignFilterModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="campaignFilterModalLabel">Filter Berdasarkan Kampanye</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="campaignFilterForm">
                        <div class="form-group">
                            <label for="campaign_id">Pilih Kampanye</label>
                            <select class="form-control" id="campaign_id" name="campaign_id">
                                <option value="">Semua Kampanye</option>
                                @foreach($campaigns as $campaign)
                                    <option value="{{ $campaign->id }}">{{ $campaign->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-danger btn-apply-filter" id="applyCampaignFilter">Terapkan Filter</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Filter Modal -->
    <div class="modal fade" id="dateFilterModal" tabindex="-1" role="dialog" aria-labelledby="dateFilterModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dateFilterModalLabel">Filter Berdasarkan Tanggal</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="dateFilterForm">
                        <div class="form-group">
                            <label for="start_date">Tanggal Mulai</label>
                            <input type="text" class="form-control date-picker" id="start_date" name="start_date" placeholder="Pilih tanggal mulai">
                        </div>
                        <div class="form-group">
                            <label for="end_date">Tanggal Selesai</label>
                            <input type="text" class="form-control date-picker" id="end_date" name="end_date" placeholder="Pilih tanggal selesai">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-danger btn-apply-filter" id="applyDateFilter">Terapkan Filter</button>
                </div>
            </div>
        </div>
    </div>

@endsection
@push('after-script')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
$(function () {
    // Initialize datepickers
    $(".date-picker").flatpickr({
        dateFormat: "Y-m-d",
    });

    // Initialize DataTable
    var table = $('.yajra-datatable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('donasi-kampanye.index') }}",
            data: function(d) {
                d.campaign_id = $('#campaign_id').val();
                d.start_date = $('#start_date').val();
                d.end_date = $('#end_date').val();
            }
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'campaign_title', name: 'campaign.title'},
            {data: 'name', name: 'name'},
            {data: 'created_at', name: 'created_at'},
            {data: 'amount', name: 'amount', render: function(data) {
                return data ? 'Rp ' + new Intl.NumberFormat('id-ID').format(data) : '-';
            }},
        ],
        order: [[3, 'desc']], // Default order by created_at column descending
        language: {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>',
            emptyTable: "Tidak ada data yang tersedia",
            info: "Menampilkan _START_ hingga _END_ dari _TOTAL_ entri",
            infoEmpty: "Menampilkan 0 hingga 0 dari 0 entri",
            infoFiltered: "(disaring dari _MAX_ entri keseluruhan)",
            lengthMenu: "Tampilkan _MENU_ entri",
            loadingRecords: "Sedang memuat...",
            search: "Cari:",
            zeroRecords: "Tidak ditemukan data yang sesuai"
        }
    });

    // Apply campaign filter
    $('#applyCampaignFilter').click(function() {
        const campaignId = $('#campaign_id').val();
        const campaignText = $('#campaign_id option:selected').text();
        
        // Update active filters display
        updateActiveFilters();
        
        // Reload the table with filter
        table.ajax.reload();
        
        // Close the modal
        $('#campaignFilterModal').modal('hide');
    });

    // Apply date filter
    $('#applyDateFilter').click(function() {
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        
        if (!startDate || !endDate) {
            alert('Mohon pilih tanggal mulai dan tanggal selesai');
            return;
        }
        
        // Update active filters display
        updateActiveFilters();
        
        // Reload the table with filter
        table.ajax.reload();
        
        // Close the modal
        $('#dateFilterModal').modal('hide');
    });

    // Function to update active filters display
    function updateActiveFilters() {
        const campaignId = $('#campaign_id').val();
        const campaignText = campaignId ? $('#campaign_id option:selected').text() : '';
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        
        let filtersHtml = '';
        
        if (campaignId) {
            filtersHtml += `<span class="badge badge-danger mr-2 p-2">Kampanye: ${campaignText} <i class="fas fa-times ml-1 clear-filter" data-filter="campaign"></i></span>`;
        }
        
        if (startDate && endDate) {
            filtersHtml += `<span class="badge badge-danger mr-2 p-2">Periode: ${startDate} s/d ${endDate} <i class="fas fa-times ml-1 clear-filter" data-filter="date"></i></span>`;
        }
        
        if (filtersHtml) {
            filtersHtml = `<div class="mb-2">Filter Aktif:</div>` + filtersHtml + `<button id="clearAllFilters" class="btn btn-sm btn-outline-danger ml-2">Hapus Semua Filter</button>`;
        }
        
        $('#active-filters').html(filtersHtml);
    }

    // Handle clearing individual filters
    $(document).on('click', '.clear-filter', function() {
        const filterType = $(this).data('filter');
        
        if (filterType === 'campaign') {
            $('#campaign_id').val('');
        } else if (filterType === 'date') {
            $('#start_date').val('');
            $('#end_date').val('');
        }
        
        // Update display and reload table
        updateActiveFilters();
        table.ajax.reload();
    });

    // Handle clearing all filters
    $(document).on('click', '#clearAllFilters', function() {
        $('#campaign_id').val('');
        $('#start_date').val('');
        $('#end_date').val('');
        
        // Update display and reload table
        $('#active-filters').html('');
        table.ajax.reload();
    });
    
    // Debugging helper
    table.on('xhr', function() {
        console.log('DataTables XHR:', table.ajax.json());
    });
});
</script>
@endpush