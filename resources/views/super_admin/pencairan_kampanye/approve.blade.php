@extends('layouts.admin')
 
@section('title', 'Setujui Pencairan Dana Kampanye')

@section('content')

<!-- Page Heading -->
<div class="card mb-4">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h4 class="m-0 font-weight-bold text-danger">Setujui Pencairan Dana Kampanye</h4>
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

                    <!-- Validasi Dana -->
                    <div class="alert {{ $withdrawal->amount <= $withdrawal->campaign->current_donation ? 'alert-info' : 'alert-danger' }} mb-3">
                        <div><strong>Dana kampanye tersedia:</strong> Rp {{ number_format($withdrawal->campaign->current_donation, 0, ',', '.') }}</div>
                        <div><strong>Jumlah pencairan:</strong> Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}</div>
                        <div>
                            <strong>Status:</strong> 
                            @if($withdrawal->amount <= $withdrawal->campaign->current_donation)
                                <span class="text-success">Dana mencukupi</span>
                            @else
                                <span class="text-danger">Dana tidak mencukupi!</span>
                            @endif
                        </div>
                    </div>

                    <form action="{{ route('pencairan-kampanye.updateStatus') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="id" value="{{ $withdrawal->id }}">
                        <input type="hidden" name="status" value="disetujui">

                        <div class="form-group">
                            <label for="bukti_pencairan" class="font-weight-bold">Bukti Transfer</label>
                            <input type="file" class="form-control-file @error('bukti_pencairan') is-invalid @enderror" id="bukti_pencairan" name="bukti_pencairan" required>
                            <small class="form-text text-muted">Upload bukti transfer sebagai konfirmasi pencairan dana.</small>
                            @error('bukti_pencairan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mt-4">
                            @if($withdrawal->amount <= $withdrawal->campaign->current_donation)
                                <button type="submit" class="btn btn-danger btn-block">
                                    <i class="fas fa-check-circle"></i> Setujui dan Kirim Bukti Transfer
                                </button>
                            @else
                                <button type="button" class="btn btn-secondary btn-block" disabled>
                                    <i class="fas fa-times-circle"></i> Dana Tidak Mencukupi
                                </button>
                                <small class="text-danger">Jumlah pencairan melebihi dana yang tersedia.</small>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection