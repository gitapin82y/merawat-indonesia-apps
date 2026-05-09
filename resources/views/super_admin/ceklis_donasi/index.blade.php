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
            <div class="col-12 col-sm-4 p-0">
                <h4 class="m-0 font-weight-bold float-left text-danger">Semua Data Ceklis Donasi</h4>
            </div>
            <div class="col-12 col-sm-8">
                   <button type="button" data-toggle="modal" data-target="#campaignFilterModal" class="btn btn-danger float-left mt-3 mt-sm-0 float-sm-right shadow-sm ml-2">
        <i class="fas fa-filter fa-sm mr-1"></i> Filter Kampanye
    </button>
                <button type="button" data-toggle="modal" data-target="#statusFilterModal" class="btn btn-danger float-left mt-3 mt-sm-0 float-sm-right shadow-sm ml-2">
                    <i class="fas fa-filter fa-sm mr-1"></i> Filter Status
                </button>
                <button type="button" data-toggle="modal" data-target="#methodFilterModal" class="btn btn-danger float-left mt-3 mt-sm-0 float-sm-right shadow-sm">
                    <i class="fas fa-filter fa-sm mr-1"></i> Filter Metode
                </button>
                <button type="button" data-toggle="modal" data-target="#dateFilterModal" class="btn btn-danger float-left mt-3 mt-sm-0 float-sm-right shadow-sm ml-2">
    <i class="fas fa-calendar fa-sm mr-1"></i> Filter Tanggal
</button>
                   <button type="button" data-toggle="modal" data-target="#contactFilterModal" class="btn btn-danger float-left mt-3 mt-sm-0 float-sm-right shadow-sm mx-2">
                    <i class="fas fa-filter fa-sm mr-1"></i> Filter Kontak
                </button>
                  <button type="button" id="exportExcel" class="btn btn-success float-left mt-3 mt-sm-0 float-sm-right shadow-sm ml-2">
                    <i class="fas fa-file-excel fa-sm mr-1"></i> Export Excel
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
                            <th>Bersedia Dihubungi</th>
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


    <!-- Modal Bukti Pembayaran -->
<div class="modal fade" id="modalBuktiPembayaran" tabindex="-1" role="dialog" aria-labelledby="modalBuktiLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalBuktiLabel">Bukti Pembayaran</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="gambarBukti" src="" alt="Bukti Pembayaran" class="img-fluid rounded" style="max-height: 70vh; object-fit: contain;">
            </div>
            <div class="modal-footer">
                <a id="linkUnduhBukti" href="" target="_blank" class="btn btn-primary">
                    <i class="fas fa-download"></i> Buka di Tab Baru
                </a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
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

<!-- Contact Filter Modal -->
<div class="modal fade" id="contactFilterModal" tabindex="-1" role="dialog" aria-labelledby="contactFilterModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contactFilterModalLabel">Filter Bersedia Dihubungi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="contactFilterForm">
                    <div class="form-group">
                        <label for="is_contactable">Status</label>
                        <select class="form-control" id="is_contactable" name="is_contactable">
                            <option value="">Semua</option>
                            <option value="1">Bersedia</option>
                            <option value="0">Tidak Bersedia</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-danger btn-apply-filter" id="applyContactFilter">Terapkan Filter</button>
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
                        <label for="date_from">Dari Tanggal</label>
                        <input type="date" class="form-control" id="date_from" name="date_from">
                    </div>
                    <div class="form-group">
                        <label for="date_to">Sampai Tanggal</label>
                        <input type="date" class="form-control" id="date_to" name="date_to">
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
<script>
$(function () {
   
      $('#campaign_id').select2({
        placeholder: 'Pilih Kampanye',
        allowClear: true,
        width: '100%',
        dropdownParent: $('#campaignFilterModal')
    });

     $('#exportExcel').click(function(){
        const params = {
            payment_type: $('#payment_type').val(),
            status: $('#status').val(),
            campaign_id: $('#campaign_id').val(),
            is_contactable: $('#is_contactable').val(),
                date_from:      $('#date_from').val(), // tambah ini
        date_to:        $('#date_to').val(),   // tambah ini
            search: table.search()
        };
        const query = $.param(params);
        window.location = "{{ route('ceklis-donasi.export') }}?" + query;
    });
    
    var table = $('.yajra-datatable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('ceklis-donasi.index') }}",
            data: function(d) {
               d.payment_type = $('#payment_type').val();
               d.status = $('#status').val();
               d.campaign_id = $('#campaign_id').val();
                d.is_contactable = $('#is_contactable').val();
                    d.date_from      = $('#date_from').val(); // tambah ini
        d.date_to        = $('#date_to').val();   // tambah ini
           }
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'name', name: 'name'},
            {data: 'email', name: 'email'},
            {data: 'phone', name: 'phone'},
            {data: 'is_contactable', name: 'is_contactable'},
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
        order: [[5, 'desc']],
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

    $('#applyCampaignFilter').click(function() {
        const campaignId = $('#campaign_id').val();
        const campaignLabel = $('#campaign_id option:selected').text();
        
        // Update active filters display
        updateActiveFilters();
        
        // Reload the table with filter
        table.ajax.reload();
        
        // Close the modal
        $('#campaignFilterModal').modal('hide');
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

    // Apply contact filter
    $('#applyContactFilter').click(function() {
        const contactStatus = $('#is_contactable').val();
        const contactLabel = $('#is_contactable option:selected').text();

        updateActiveFilters();
        table.ajax.reload();
        $('#contactFilterModal').modal('hide');
    });

    $('#applyDateFilter').click(function() {
    updateActiveFilters();
    table.ajax.reload();
    $('#dateFilterModal').modal('hide');
});

    function updateActiveFilters() {
        const paymentType = $('#payment_type').val();
       const paymentLabel = $('#payment_type option:selected').text();
       const status = $('#status').val();
       const statusLabel = $('#status option:selected').text();
       const campaignId = $('#campaign_id').val();
       const campaignLabel = $('#campaign_id option:selected').text();
        const contactStatus = $('#is_contactable').val();
        const contactLabel = $('#is_contactable option:selected').text();
            const dateFrom      = $('#date_from').val();  // tambah ini
    const dateTo        = $('#date_to').val();    // tambah ini
        
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

        if (contactStatus) {
            filtersHtml += `<span class="badge badge-danger mr-2 p-2">Kontak: ${contactLabel} <i class="fas fa-times ml-1 clear-filter" data-filter="contact"></i></span>`;
        }

           if (dateFrom || dateTo) {
        const label = (dateFrom && dateTo)
            ? `${dateFrom} s/d ${dateTo}`
            : (dateFrom ? `Dari ${dateFrom}` : `Sampai ${dateTo}`);
        filtersHtml += `<span class="badge badge-danger mr-2 p-2">Tanggal: ${label} <i class="fas fa-times ml-1 clear-filter" data-filter="date"></i></span>`;
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
        } else if (filterType === 'contact') {
            $('#is_contactable').val('');
       }else if (filterType === 'date') {  // tambah ini
        $('#date_from').val('');
        $('#date_to').val('');
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
        $('#is_contactable').val('');
            $('#date_from').val('');  // tambah ini
    $('#date_to').val('');    // tambah ini
        
        // Update display and reload table
        $('#active-filters').html('');
        table.ajax.reload();
    });
});

function updateStatus(id, status) {
    let title, text, confirmText, icon;

    if (status === 'sukses') {
        title = 'Approve Donasi?';
        text = 'Dana akan ditambahkan ke statistik kampanye.';
        confirmText = 'Ya, Approve!';
        icon = 'question';
    } else if (status === 'gagal') {
        title = 'Reject Donasi?';
        text = 'Donasi akan ditandai sebagai gagal.';
        confirmText = 'Ya, Reject!';
        icon = 'warning';
    } else if (status === 'pending') {
        title = 'Kembalikan ke Pending?';
        text = 'Status akan dikembalikan ke pending. Statistik kampanye akan disesuaikan kembali jika sebelumnya sudah diapprove.';
        confirmText = 'Ya, Kembalikan!';
        icon = 'info';
    }

    Swal.fire({
        title: title,
        text: text,
        icon: icon,
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: confirmText,
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route('donasi.updateStatus') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    status: status
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: response.message,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                        $('.yajra-datatable').DataTable().ajax.reload(null, false);
                    } else {
                        Swal.fire('Gagal!', response.message || 'Gagal mengupdate status.', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Terjadi Kesalahan!', 'Tidak dapat mengupdate status.', 'error');
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
                        $('.yajra-datatable').DataTable().ajax.reload(null, false);
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

function noPaymentProofAlert() {
    Swal.fire({
        icon: 'warning',
        title: 'Bukti pembayaran belum diupload!',
        text: 'User sudah mengisi form donasi dan mungkin sedang proses melakukan pembayaran sehingga belum mengirimkan foto bukti pembayaran.'
    });
}
function lihatBukti(url) {
    $('#gambarBukti').attr('src', url);
    $('#linkUnduhBukti').attr('href', url);
    $('#modalBuktiPembayaran').modal('show');
}
</script>
@endpush