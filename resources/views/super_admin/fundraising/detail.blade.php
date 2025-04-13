@extends('layouts.admin')
 
@section('title', 'Lihat Detail Fundraising')

@push('after-style')

@endpush

@section('content')
<div class="card mb-4">
    <div class="card-header bg-danger py-3 align-items-center justify-content-between row m-0">
        <div class="col-12 col-sm-6 p-0">
            <h4 class="m-0 font-weight-bold float-left text-white">Detail Fundraising</h4>
        </div>
    </div>
        <div class="card-body">
            {{$fundraising->id}}
            <div class="form-group">
                <a href="{{ route('fundraising.index') }}" class="btn btn-secondary">Kembali</a>
            </div>
        </div>
</div>
@endsection

@push('after-script')
@endpush
