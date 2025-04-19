@extends('layouts.admin')
 
@section('title', 'Request Pencairan Dana Kampanye')

@push('after-style')

@endpush

@section('content')

    <!-- Page Heading -->
    <div class="card mb-4">
        <div class="card-header bg-white py-3 align-items-center justify-content-between row m-0">
            <div class="col-12 col-sm-6 p-0">
                <h4 class="m-0 font-weight-bold float-left text-danger">Request Pencairan Dana Kampanye</h4>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered yajra-datatable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Nama Bank</th>
                            <th>Nomor Rekening</th>
                            <th>Total</th>
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

@endsection

@push('after-script')
<script>
$(function () {
    var table = $('.yajra-datatable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('pencairan-kampanye.index') }}",
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'name', name: 'name'},
            {data: 'payment_method', name: 'email'},
            {data: 'account_number', name: 'account_number'},
            {data: 'amount', name: 'amount'},
            {data: 'created_at', name: 'created_at'},
            {data: 'status', name: 'status'},
            {
                data: 'action', 
                name: 'action', 
                orderable: false, 
                searchable: false
            },
        ],
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
                url: "{{ route('pencairan-kampanye.updateStatus') }}",  // Pastikan route sudah benar
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

function deletePencairanKampanye(id) {
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
                url: 'pencairan-kampanye/' + id,  // Pastikan URL sesuai dengan route POST
                type: 'DELETE',
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