@extends('layouts.admin')
 
@section('title', 'Detail Donasi Kampanye')

@push('after-style')

@endpush

@section('content')

    <!-- Page Heading -->
    <div class="card mb-4">
        <div class="card-header py-3 align-items-center justify-content-between row m-0">
            <div class="col-12 col-sm-6 p-0">
                <h4 class="m-0 font-weight-bold text-dark float-left"><a href="{{ url()->previous() }}" class="text-danger">Kembali</a> / Detail Data Donasi Kampanye</h4>
            </div>
            <div class="col-12 col-sm-6">
                <a href="" class="btn btn-danger float-left mt-3 mt-sm-0 float-sm-right shadow-sm ml-2">Filter Tanggal</a>
                <a href="" class="btn btn-danger float-left mt-3 mt-sm-0 float-sm-right shadow-sm">Filter Kampanye</a>
            </div>
        </div>
        <div class="card-body">
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

@endsection

@push('after-script')
<script>
$(function () {
    var campaignTitle = "{{ $title }}"; // Ambil title dari Blade

    var table = $('.yajra-datatable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('donasi-kampanye.show', ':title') }}".replace(':title', encodeURIComponent(campaignTitle)),
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'campaign_title', name: 'campaign_title'},
            {data: 'name', name: 'name'},
            {data: 'created_at', name: 'created_at'},
            {data: 'amount', name: 'amount'},
        ]
    });
});
</script>
@endpush