@extends('layouts.admin')
 
@section('title', 'Detail Pencairan Dana')

@section('content')

<!-- Page Heading -->
<div class="card mb-4">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h4 class="m-0 font-weight-bold text-danger">Detail Pencairan Dana</h4>
        <a href="{{ route('pencairan-fundraising.index') }}" class="btn btn-danger btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="p-4 bg-light rounded">
                    <!-- Informasi Pengguna -->
                    <h5 class="border-bottom pb-2 mb-3">Informasi Pengguna</h5>
                    <div class="mb-3">
                        <div class="row mb-2">
                            <div class="col-sm-4 font-weight-bold">Nama:</div>
                            <div class="col-sm-8">{{ $pencairanFundraising->user->name }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 font-weight-bold">Email:</div>
                            <div class="col-sm-8">{{ $pencairanFundraising->user->email }}</div>
                        </div>
                        @if($pencairanFundraising->user->phone)
                        <div class="row mb-2">
                            <div class="col-sm-4 font-weight-bold">Nomor Whatsapp:</div>
                            <div class="col-sm-8">{{ $pencairanFundraising->user->phone }}</div>
                        </div>
                        @endif
                    </div>

                    <!-- Informasi Pencairan -->
                    <h5 class="border-bottom pb-2 mb-3">Informasi Pencairan</h5>
                    <div class="mb-3">
                        <div class="row mb-2">
                            <div class="col-sm-4 font-weight-bold">Berhasil Mengajak:</div>
                            <div class="col-sm-8">{{ $pencairanFundraising->fundraising->total_donatur }} donatur</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 font-weight-bold">Total Donasi:</div>
                            <div class="col-sm-8">Rp {{ number_format($pencairanFundraising->fundraising->jumlah_donasi, 0, ',', '.') }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 font-weight-bold">Jumlah Pencairan:</div>
                            <div class="col-sm-8 text-danger font-weight-bold">Rp {{ number_format($pencairanFundraising->amount, 0, ',', '.') }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 font-weight-bold">Status:</div>
                            <div class="col-sm-8">
                                @if($pencairanFundraising->status == 'menunggu')
                                    <span class="badge badge-warning text-white">Menunggu</span>
                                @elseif($pencairanFundraising->status == 'disetujui')
                                    <span class="badge badge-success">Disetujui</span>
                                @elseif($pencairanFundraising->status == 'ditolak')
                                    <span class="badge badge-danger">Ditolak</span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 font-weight-bold">Tanggal Pengajuan:</div>
                            <div class="col-sm-8">{{ $pencairanFundraising->created_at->format('d M Y H:i') }}</div>
                        </div>
                    </div>

                    <!-- Informasi Rekening -->
                    <h5 class="border-bottom pb-2 mb-3">Informasi Rekening</h5>
                    <div class="mb-3">
                        <div class="row mb-2">
                            <div class="col-sm-4 font-weight-bold">Bank:</div>
                            <div class="col-sm-8">{{ strtoupper($pencairanFundraising->payment_method) }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 font-weight-bold">Nama Rekening:</div>
                            <div class="col-sm-8">{{ $pencairanFundraising->account_name }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 font-weight-bold">Nomor Rekening:</div>
                            <div class="col-sm-8">{{ $pencairanFundraising->account_number }}</div>
                        </div>
                    </div>

                    @if($pencairanFundraising->status == 'disetujui' && $pencairanFundraising->bukti_pencairan)
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

                    @if($pencairanFundraising->status == 'ditolak' && $pencairanFundraising->rejection_reason)
                    <!-- Alasan Penolakan -->
                    <h5 class="border-bottom pb-2 mb-3">Alasan Penolakan</h5>
                    <div class="mb-3 p-3 bg-danger-light text-danger rounded">
                        {{ $pencairanFundraising->rejection_reason }}
                    </div>
                    @endif

                    @if($pencairanFundraising->status == 'menunggu')
                    <!-- Tombol Aksi -->
                    <div class="mt-4 d-flex justify-content-between">
                        <a href="{{ route('pencairan-fundraising.approve', $pencairanFundraising->id) }}" class="btn btn-success">
                            <i class="fas fa-check-circle"></i> Setujui
                        </a>
                        <a href="{{ route('pencairan-fundraising.reject', $pencairanFundraising->id) }}" class="btn btn-danger">
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