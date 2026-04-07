@extends('layouts.admin')

@section('title', 'Statistik Pencairan')

@push('after-style')
<style>
    .stat-badge {
        font-size: 12px;
        padding: 4px 10px;
        border-radius: 20px;
    }
</style>
@endpush

@section('content')
<div class="card mb-4">
    <div class="card-header bg-danger py-3 align-items-center justify-content-between row m-0">
        <div class="col-12 col-sm-6 p-0">
            <h4 class="m-0 font-weight-bold float-left text-white">
                <i class="fas fa-chart-line mr-2"></i> Statistik Pencairan Admin Yayasan
            </h4>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered yajra-datatable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Foto</th>
                        <th>Nama Yayasan</th>
                        <th>Nama Ketua</th>
                        <th>Total Kampanye</th>
                        <th>Total Donatur</th>
                        <th>Total Donasi</th>
                        <th>Total Pencairan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('after-script')
<script>
$(function () {
    $('.yajra-datatable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('statistik-pencairan.index') }}",
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '5%'},
            {data: 'avatar', name: 'avatar', orderable: false, searchable: false, width: '60px'},
            {data: 'name', name: 'name'},
            {data: 'leader_name', name: 'leader_name'},
            {data: 'total_kampanye', name: 'total_kampanye', orderable: false, searchable: false, className: 'text-center'},
            {data: 'total_donatur', name: 'total_donatur', orderable: false, searchable: false, className: 'text-center'},
            {data: 'total_donasi', name: 'total_donasi', orderable: false, searchable: false},
            {data: 'total_pencairan', name: 'total_pencairan', orderable: false, searchable: false},
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
                width: '10%'
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
</script>
@endpush