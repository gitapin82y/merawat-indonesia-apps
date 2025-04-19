@extends('layouts.admin')
 
@section('title', 'Manajemen Prioritas Kampanye')

@push('after-style')

@endpush

@section('content')

    <!-- Page Heading -->
    <div class="card mb-4">
        <div class="card-header bg-white py-3 align-items-center justify-content-between row m-0">
            <div class="col-12 col-sm-6 p-0">
                <h4 class="m-0 font-weight-bold float-left text-danger">Manajemen Prioritas Kampanye</h4>
            </div>
            <div class="col-12 col-sm-6">
                <a href="{{ route('prioritas-kampanye.create') }}" class="btn btn-danger float-left mt-3 mt-sm-0 float-sm-right shadow-sm">Tambah Promosi</a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered yajra-datatable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Penggalang Dana</th>
                            <th>Nama Kampanye</th>
                            <th>Kategori</th>
                            <th>Total</th>
                            <th>Prioritas</th>
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
        ajax: "{{ route('prioritas-kampanye.index') }}",
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'admin', name: 'admin'},
            {data: 'title', name: 'title'},
            {data: 'category', name: 'category'},
            {data: 'total_donatur', name: 'total_donatur'},
            {data: 'prioritas', name: 'prioritas'},
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
function deletePrioritas(id) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Kampanye akan tidak berada di prioritas",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Hapus!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'prioritas-kampanye/' + id,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
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
                        'Terjadi kesalahan saat menghapus data prioritas',
                        'error'
                    );
                }
            });
        }
    });
}
</script>
@endpush