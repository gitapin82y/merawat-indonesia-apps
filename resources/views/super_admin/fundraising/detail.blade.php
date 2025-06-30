@extends('layouts.admin')
 
@section('title', 'Lihat Detail Fundraising')

@push('after-style')
<style>
    .detail-card {
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        background-color: white;
        margin-bottom: 20px;
    }
    
    .detail-header {
        padding: 20px;
        border-bottom: 1px solid #f3f3f3;
    }
    
    .detail-body {
        padding: 20px;
    }
    
    .detail-label {
        color: #6c757d;
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 5px;
    }
    
    .detail-value {
        font-size: 18px;
        font-weight: 600;
        color: #212529;
        margin-bottom: 20px;
    }
    
    .donatur-card {
        background-color: #f9f9f9;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .donatur-info {
        display: flex;
        flex-direction: column;
    }
    
    .donatur-name {
        font-weight: 600;
        font-size: 16px;
        color: #212529;
    }
    
    .donatur-date {
        font-size: 14px;
        color: #6c757d;
    }
    
    .donatur-amount {
        font-weight: 600;
        font-size: 16px;
        color: #000;
    }
    
    .summary-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    
    .summary-label {
        font-weight: 500;
        color: #212529;
    }
    
    .summary-value {
        font-weight: 600;
        color: #212529;
    }
    
    .summary-value.commission {
        color: #28a745;
    }
    
    .summary-value.request {
        color: #dc3545;
    }
    
    .divider {
        height: 1px;
        background-color: #f3f3f3;
        margin: 15px 0;
    }
</style>
@endpush

@section('content')
<div class="card mb-4">
    <div class="card-header bg-danger py-3 align-items-center justify-content-between row m-0">
        <div class="col-12 col-sm-6 p-0">
            <h4 class="m-0 font-weight-bold float-left text-white">Detail Fundraising</h4>
        </div>
    </div>
    <div class="card-body">
        <div class="detail-card">
            <div class="detail-header">
                <h5 class="mb-0">Informasi Fundraiser</h5>
            </div>
            <div class="detail-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="detail-label">Nama</div>
                        <div class="detail-value">{{ $fundraising->user->name }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="detail-label">Email</div>
                        <div class="detail-value">{{ $fundraising->user->email }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="detail-label">Nomor Whatsapp</div>
                        <div class="detail-value">{{ $fundraising->user->phone }}</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="detail-card">
            <div class="detail-header">
                <h5 class="mb-0">Informasi Kampanye</h5>
            </div>
            <div class="detail-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-label">Nama Kampanye</div>
                        <div class="detail-value">{{ $fundraising->campaign->title }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-label">Kode Referral</div>
                        <div class="detail-value">{{ $fundraising->code_link }}</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="detail-card">
            <div class="detail-header">
                <h5 class="mb-0">Berhasil Mengajak {{ $fundraising->total_donatur }} Donatur</h5>
            </div>
            <div class="detail-body">
                @if($fundraising->donations && is_string($fundraising->donations))
                @php 
                    $donationsArray = json_decode($fundraising->donations, true);
                @endphp
                
                @if(is_array($donationsArray) && count($donationsArray) > 0)
                    @foreach($donationsArray as $donation)
                        <div class="donatur-card">
                            <div class="donatur-info">
                                <div class="donatur-name">{{ $donation['user_name'] ?? 'Anonim' }}</div>
                                <div class="donatur-date">
                                    {{ \Carbon\Carbon::parse($donation['created_at'] ?? now())->diffForHumans() }}
                                </div>
                            </div>
                            <div class="donatur-amount">
                                RP {{ number_format($donation['amount'] ?? 0, 0, ',', '.') }}
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-4">
                        <p>Belum ada donatur</p>
                    </div>
                @endif
            @else
                <div class="text-center py-4">
                    <p>Belum ada donatur</p>
                </div>
            @endif
                
                <div class="divider"></div>
                
                <div class="summary-item">
                    <div class="summary-label">Total Donasi</div>
                    <div class="summary-value">Rp {{ number_format($fundraising->jumlah_donasi, 0, ',', '.') }}</div>
                </div>
                
                <div class="summary-item">
                    <div class="summary-label">Total Komisi</div>
                    <div class="summary-value commission">
                        Rp {{ number_format($fundraising->commission, 0, ',', '.') }}
                    </div>
                </div>
                
                @php
                    $totalWithdrawals = $fundraising->fundraisingWithdrawals->sum('amount');
                @endphp
                
                <div class="summary-item">
                    <div class="summary-label">Request Pencairan Dana</div>
                    <div class="summary-value request">
                        Rp {{ number_format($totalWithdrawals, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>
        
        <div class="detail-card">
            <div class="detail-header">
                <h5 class="mb-0">Riwayat Pencairan Dana</h5>
            </div>
            <div class="detail-body">
                @if($fundraising->fundraisingWithdrawals->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Metode Pembayaran</th>
                                    <th>Rekening/Akun</th>
                                    <th>Jumlah</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fundraising->fundraisingWithdrawals as $index => $withdrawal)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ \Carbon\Carbon::parse($withdrawal->created_at)->format('d M Y') }}</td>
                                    <td>{{ strtoupper($withdrawal->payment_method) }}</td>
                                    <td>{{ $withdrawal->account_number }} ({{ $withdrawal->account_name }})</td>
                                    <td>Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}</td>
                                    <td>
                                        @if($withdrawal->status == 'pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @elseif($withdrawal->status == 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($withdrawal->status == 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <p>Belum ada riwayat pencairan dana</p>
                    </div>
                @endif
            </div>
        </div>
        
        <div class="form-group">
            <a href="{{ url('/super-admin/fundraising-campaign/'.$fundraising->campaign->slug) }}" class="btn btn-secondary">Kembali</a>
        </div>
    </div>
</div>
@endsection

@push('after-script')
<script>
    // Any additional JavaScript can go here
</script>
@endpush