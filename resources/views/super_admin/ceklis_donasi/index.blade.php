@extends('layouts.admin')
 
@section('title', 'Semua Data Ceklis Donasi')

@push('after-style')
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
                <h4 class="m-0 font-weight-bold float-left text-danger">Semua Data Ceklis Donasi</h4>
            </div>
            <div class="col-12 col-sm-6">
                   <button type="button" data-toggle="modal" data-target="#campaignFilterModal" class="btn btn-danger float-left mt-3 mt-sm-0 float-sm-right shadow-sm ml-2">
        <i class="fas fa-filter fa-sm mr-1"></i> Filter Kampanye
    </button>
                <button type="button" data-toggle="modal" data-target="#statusFilterModal" class="btn btn-danger float-left mt-3 mt-sm-0 float-sm-right shadow-sm ml-2">
                    <i class="fas fa-filter fa-sm mr-1"></i> Filter Status
                </button>
                <button type="button" data-toggle="modal" data-target="#methodFilterModal" class="btn btn-danger float-left mt-3 mt-sm-0 float-sm-right shadow-sm">
                    <i class="fas fa-filter fa-sm mr-1"></i> Filter Metode
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
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Kampanye</th> 
                            <th>Total Donasi</th>
                            <th>Metode</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Aksi</th>
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

    <!-- Payment Method Filter Modal -->
    <div class="modal fade" id="methodFilterModal" tabindex="-1" role="dialog" aria-labelledby="methodFilterModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="methodFilterModalLabel">Filter Berdasarkan Metode Pembayaran</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="methodFilterForm">
                        <div class="form-group">
                            <label for="payment_type">Pilih Metode Pembayaran</label>
                            <select class="form-control" id="payment_type" name="payment_type">
                                <option value="">Semua Metode</option>
                                <option value="payment_gateway">Payment Gateway</option>
                                <option value="manual">Transfer Manual</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-danger btn-apply-filter" id="applyMethodFilter">Terapkan Filter</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Filter Modal -->
<div class="modal fade" id="statusFilterModal" tabindex="-1" role="dialog" aria-labelledby="statusFilterModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusFilterModalLabel">Filter Berdasarkan Status</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="statusFilterForm">
                    <div class="form-group">
                        <label for="status">Pilih Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="">Semua Status</option>
                            <option value="pending">Pending</option>
                            <option value="sukses">Sukses</option>
                            <option value="gagal">Gagal</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-danger btn-apply-filter" id="applyStatusFilter">Terapkan Filter</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('after-script')
<script>
$(function () {
    var table = $('.yajra-datatable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('ceklis-donasi.index') }}",
            data: function(d) {
                d.payment_type = $('#payment_type').val();
                d.status = $('#status').val(); 
                d.campaign_id = $('#campaign_id').val();
            }
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'name', name: 'name'},
            {data: 'email', name: 'email'},
            {data: 'phone', name: 'phone'},
            {data: 'campaign_title', name: 'campaign_title'},
            {data: 'amount', name: 'amount'},
            {data: 'method', name: 'method'},
            {data: 'created_at', name: 'created_at'},
            {data: 'status', name: 'status'},
            {
                data: 'action', 
                name: 'action', 
                orderable: false, 
                searchable: false
            },
        ],
        order: [[4, 'desc']],
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

    $('#applyStatusFilter').click(function() {
        const status = $('#status').val();
        const statusLabel = $('#status option:selected').text();
        
        // Update active filters display
        updateActiveFilters();
        
        // Reload the table with filter
        table.ajax.reload();
        
        // Close the modal
        $('#statusFilterModal').modal('hide');
    });


    // Apply payment method filter
    $('#applyMethodFilter').click(function() {
        const paymentType = $('#payment_type').val();
        const paymentLabel = $('#payment_type option:selected').text();
        
        // Update active filters display
        updateActiveFilters();
        
        // Reload the table with filter
        table.ajax.reload();
        
        // Close the modal
        $('#methodFilterModal').modal('hide');
    });

    function updateActiveFilters() {
        const paymentType = $('#payment_type').val();
        const paymentLabel = $('#payment_type option:selected').text();
        const status = $('#status').val();
        const statusLabel = $('#status option:selected').text();
        const campaignId = $('#campaign_id').val();
        const campaignLabel = $('#campaign_id option:selected').text();
        
        let filtersHtml = '';
        
        if (paymentType) {
            filtersHtml += `<span class="badge badge-danger mr-2 p-2">Metode: ${paymentLabel} <i class="fas fa-times ml-1 clear-filter" data-filter="payment"></i></span>`;
        }
        
        if (status) {
            filtersHtml += `<span class="badge badge-danger mr-2 p-2">Status: ${statusLabel} <i class="fas fa-times ml-1 clear-filter" data-filter="status"></i></span>`;
        }

        if (campaignId) {
            filtersHtml += `<span class="badge badge-danger mr-2 p-2">Kampanye: ${campaignLabel} <i class="fas fa-times ml-1 clear-filter" data-filter="campaign"></i></span>`;
        }
        
        if (filtersHtml) {
            filtersHtml = `<div class="mb-2">Filter Aktif:</div>` + filtersHtml + `<button id="clearAllFilters" class="btn btn-sm btn-outline-danger ml-2">Hapus Filter</button>`;
        }
        
        $('#active-filters').html(filtersHtml);
    }

    // Handle clearing filters
    $(document).on('click', '.clear-filter', function() {
        const filterType = $(this).data('filter');
        
        if (filterType === 'payment') {
            $('#payment_type').val('');
        } else if (filterType === 'status') {
            $('#status').val('');
        } else if (filterType === 'campaign') {
            $('#campaign_id').val('');
        }
        
        // Update display and reload table
        updateActiveFilters();
        table.ajax.reload();
    });

    // Handle clearing all filters
    $(document).on('click', '#clearAllFilters', function() {
        $('#payment_type').val('');
        $('#status').val('');
        $('#campaign_id').val('');
        
        // Update display and reload table
        $('#active-filters').html('');
        table.ajax.reload();
    });
});

function updateStatus(id, status) {
    // Menggunakan SweetAlert untuk konfirmasi
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Anda ingin mengubah status menjadi " + status + "?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Ubah!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Kirim AJAX jika konfirmasi diterima
            $.ajax({
                url: "{{ route('donasi.updateStatus') }}",  // Pastikan route sudah benar
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id,
                    status: status
                },
                success: function(response) {
                    if (response.success) {
                        // Tampilkan pesan sukses menggunakan SweetAlert
                        Swal.fire(
                            'Status Diubah!',
                            response.message,
                            'success'
                        );
                        // Reload data tabel
                        $('.yajra-datatable').DataTable().ajax.reload();
                    } else {
                        // Tampilkan pesan error jika gagal
                        Swal.fire(
                            'Gagal!',
                            "Gagal mengupdate status.",
                            'error'
                        );
                    }
                },
                error: function() {
                    // Tampilkan pesan error jika terjadi kesalahan
                    Swal.fire(
                        'Terjadi Kesalahan!',
                        'Tidak dapat mengupdate status.',
                        'error'
                    );
                }
            });
        }
    });
}

function deleteDonasi(id) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Anda tidak dapat mengembalikan data yang dihapus!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Hapus!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'ceklis-donasi/' + id,  // Pastikan URL sesuai dengan route POST
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',  // Pastikan token CSRF disertakan
                },
                success: function(response) {
                    if (response.status === 'success') {
                       Swal.fire({
                            icon: 'success',
                            title: response.message,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                        // Reload data tabel setelah berhasil menghapus
                        $('.yajra-datatable').DataTable().ajax.reload();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: response.message,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                },
                error: function() {
                    Swal.fire(
                        'Error!',
                        'Terjadi kesalahan saat menghapus data',
                        'error'
                    );
                }
            });
        }
    });
}
</script>
@endpush