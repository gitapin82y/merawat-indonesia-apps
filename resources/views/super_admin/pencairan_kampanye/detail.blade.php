@extends('layouts.admin')
 
@section('title', 'Detail Pencairan Dana Kampanye')

@section('content')

<!-- Page Heading -->
<div class="card mb-4">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h4 class="m-0 font-weight-bold text-danger">Detail Pencairan Dana Kampanye</h4>
        <a href="{{ route('pencairan-kampanye.index') }}" class="btn btn-danger btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-10 mx-auto">
                <div class="p-4 bg-light rounded">
                    <!-- Informasi Kampanye -->
                    <h5 class="border-bottom pb-2 mb-3">Informasi Kampanye</h5>
                    <div class="mb-4">
                        <div class="row mb-2">
                            <div class="col-sm-4 font-weight-bold">Judul Kampanye:</div>
                            <div class="col-sm-8">{{ $pencairanKampanye->campaign->title }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 font-weight-bold">Dibuat oleh:</div>
                            <div class="col-sm-8">{{ $pencairanKampanye->admin->name }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 font-weight-bold">Email:</div>
                            <div class="col-sm-8">{{ $pencairanKampanye->admin->user->email }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 font-weight-bold">Telepon:</div>
                            <div class="col-sm-8">{{ $pencairanKampanye->admin->phone }}</div>
                        </div>
                    </div>

                    <!-- Informasi Donasi -->
                    <h5 class="border-bottom pb-2 mb-3">Informasi Donasi Kampanye</h5>
                    <div class="mb-4">
                        <div class="row mb-2">
                            <div class="col-sm-4 font-weight-bold">Total Donasi:</div>
                            <div class="col-sm-8">Rp {{ number_format($pencairanKampanye->campaign->current_donation_formatted + $pencairanKampanye->amount, 0, ',', '.') }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 font-weight-bold">Jumlah Pencairan:</div>
                            <div class="col-sm-8 text-danger font-weight-bold">Rp {{ number_format($pencairanKampanye->amount, 0, ',', '.') }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 font-weight-bold">Sisa Dana yang belum dicairkan:</div>
                            <div class="col-sm-8">Rp {{ number_format($pencairanKampanye->campaign->current_donation_formatted, 0, ',', '.') }}</div>
                        </div>
                    </div>

                    <!-- Informasi Pencairan -->
                    <h5 class="border-bottom pb-2 mb-3">Informasi Pencairan</h5>
                    <div class="mb-4">
                        <div class="row mb-2">
                            <div class="col-sm-4 font-weight-bold">Status:</div>
                            <div class="col-sm-8">
                                @if($pencairanKampanye->status == 'menunggu')
                                    <span class="badge badge-warning text-white">Menunggu</span>
                                @elseif($pencairanKampanye->status == 'disetujui')
                                    <span class="badge badge-success">Disetujui</span>
                                @elseif($pencairanKampanye->status == 'ditolak')
                                    <span class="badge badge-danger">Ditolak</span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 font-weight-bold">Metode Pembayaran:</div>
                            <div class="col-sm-8">{{ strtoupper($pencairanKampanye->payment_method) }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 font-weight-bold">Nama Rekening:</div>
                            <div class="col-sm-8">{{ $pencairanKampanye->account_name }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 font-weight-bold">Nomor Rekening:</div>
                            <div class="col-sm-8">{{ $pencairanKampanye->account_number }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 font-weight-bold">Tanggal Pengajuan:</div>
                            <div class="col-sm-8">{{ $pencairanKampanye->created_at->format('d M Y H:i') }}</div>
                        </div>
                    </div>

                    <!-- Dokumen RAB -->
                    <h5 class="border-bottom pb-2 mb-3">Dokumen RAB</h5>
                    <div class="mb-4">
                        @if($pencairanKampanye->document_rab)
                            <div class="text-center mb-3">
                                <a href="{{ asset('storage/' . $pencairanKampanye->document_rab) }}" target="_blank" class="btn btn-primary">
                                    <i class="fas fa-file-download"></i> Lihat Dokumen RAB
                                </a>
                            </div>
                        @else
                            <div class="alert alert-info">Tidak ada dokumen RAB yang diunggah.</div>
                        @endif
                    </div>

                    @if($pencairanKampanye->status == 'disetujui' && $pencairanKampanye->bukti_pencairan)
                    <!-- Bukti Transfer -->
                    <h5 class="border-bottom pb-2 mb-3">Bukti Transfer</h5>
                    <div class="mb-4">
                
                        @if($pencairanKampanye->bukti_pencairan)
                            <div class="text-center mb-3">
                                <a href="{{ asset('storage/' . $pencairanKampanye->bukti_pencairan) }}" target="_blank" class="btn btn-danger">
                                    <i class="fas fa-file-download"></i> Lihat File
                                </a>
                            </div>
                        @else
                            <div class="alert alert-info">Tidak ada dokumen RAB yang diunggah.</div>
                        @endif

                    </div>
                    @endif

                    @if($pencairanKampanye->status == 'ditolak' && $pencairanKampanye->rejection_reason)
                    <!-- Alasan Penolakan -->
                    <h5 class="border-bottom pb-2 mb-3">Alasan Penolakan</h5>
                    <div class="mb-4 p-3 bg-danger-light text-danger rounded">
                        {{ $pencairanKampanye->rejection_reason }}
                    </div>
                    @endif

                    @if($pencairanKampanye->status == 'menunggu')
                    <!-- Tombol Aksi -->
                    <div class="mt-4 d-flex justify-content-between">
                        <a href="{{ route('pencairan-kampanye.approve', $pencairanKampanye->id) }}" class="btn btn-success">
                            <i class="fas fa-check-circle"></i> Setujui
                        </a>
                        <a href="{{ route('pencairan-kampanye.reject', $pencairanKampanye->id) }}" class="btn btn-danger">
                            <i class="fas fa-times-circle"></i> Tolak
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection