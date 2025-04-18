@extends('layouts.admin')
 
@section('title', 'Tolak Pencairan Dana Kampanye')

@section('content')

<!-- Page Heading -->
<div class="card mb-4">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h4 class="m-0 font-weight-bold text-danger">Tolak Pencairan Dana Kampanye</h4>
        <a href="{{ route('pencairan-kampanye.index') }}" class="btn btn-danger btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="p-4 bg-light rounded">
                    <!-- Informasi Kampanye -->
                    <div class="mb-3">
                        <h5 class="font-weight-bold">{{ $withdrawal->campaign->title }}</h5>
                        <p class="text-muted mb-0">Diajukan oleh: {{ $withdrawal->admin->name }}</p>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="text-primary font-weight-bold">{{ strtoupper($withdrawal->payment_method) }} a/n {{ $withdrawal->account_name }}</div>
                        <div class="text-danger font-weight-bold">Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}</div>
                    </div>

                    <div class="mb-3">
                        <div>No. Rekening: {{ $withdrawal->account_number }}</div>
                    </div>

                    <div class="mb-3">
                        @if($withdrawal->document_rab)
                            <div class="mb-2">
                                <strong>Dokumen RAB:</strong>
                                <a href="{{ asset('storage/' . $withdrawal->document_rab) }}" target="_blank" class="ml-2">
                                    <i class="fas fa-file-download"></i> Lihat Dokumen
                                </a>
                            </div>
                        @endif
                    </div>

                    <form action="{{ route('pencairan-kampanye.updateStatus') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" value="{{ $withdrawal->id }}">
                        <input type="hidden" name="status" value="ditolak">

                        <div class="form-group">
                            <label for="rejection_reason" class="font-weight-bold">Alasan Penolakan</label>
                            <textarea class="form-control @error('rejection_reason') is-invalid @enderror" id="rejection_reason" name="rejection_reason" rows="4" required></textarea>
                            <small class="form-text text-muted">Berikan alasan mengapa permintaan pencairan dana ini ditolak. Alasan ini akan dikirimkan ke pengelola kampanye.</small>
                            @error('rejection_reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-danger btn-block">
                                <i class="fas fa-times-circle"></i> Tolak Permintaan Pencairan Dana
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection