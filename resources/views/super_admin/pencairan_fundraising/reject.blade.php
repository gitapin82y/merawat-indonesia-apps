@extends('layouts.admin')
 
@section('title', 'Tolak Pencairan Dana')

@section('content')

<!-- Page Heading -->
<div class="card mb-4">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h4 class="m-0 font-weight-bold text-danger">Tolak Pencairan Dana</h4>
        <a href="{{ route('pencairan-fundraising.index') }}" class="btn btn-danger btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="p-4 bg-light rounded">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="text-primary font-weight-bold">{{ strtoupper($withdrawal->payment_method) }} a/n {{ $withdrawal->account_name }}</div>
                        <div class="text-danger font-weight-bold">Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}</div>
                    </div>
                    <div class="mb-3">
                        <div>{{ $withdrawal->account_number }}</div>
                    </div>

                    <form action="{{ route('pencairan-fundraising.updateStatus') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" value="{{ $withdrawal->id }}">
                        <input type="hidden" name="status" value="ditolak">

                        <div class="form-group">
                            <label for="rejection_reason" class="font-weight-bold">Alasan Ditolak</label>
                            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="4" required></textarea>
                            <small class="form-text text-muted">Berikan alasan mengapa permintaan pencairan dana ini ditolak.</small>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-danger btn-block">
                                <i class="fas fa-times-circle"></i> Simpan Dan Kirim Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection