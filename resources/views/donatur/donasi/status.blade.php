@extends('layouts.public')
 
@section('title', 'Status Donasi')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Status Donasi</div>
                <div class="card-body text-center">
                    @if($donation->status == 'pending')
                        <div class="alert alert-warning">
                            <h4><i class="fa fa-clock"></i> Menunggu Pembayaran</h4>
                            <p>Silakan selesaikan pembayaran Anda sesuai dengan instruksi.</p>
                        </div>
                        <div class="mt-4 mb-3">
                            <h5>Detail Donasi:</h5>
                            <p><strong>Kampanye:</strong> {{ $campaign->title }}</p>
                            <p><strong>Nominal:</strong> Rp {{ number_format($donation->amount) }}</p>
                            <p><strong>Metode Pembayaran:</strong> {{ $donation->payment_method }}</p>
                            <p><strong>Waktu:</strong> {{ $donation->created_at->format('d M Y H:i') }}</p>
                        </div>
                        <a href="#" id="checkStatus" class="btn btn-primary">Cek Status Pembayaran</a>
                        <div id="statusResult" class="mt-3"></div>
                    @elseif($donation->status == 'sukses')
                        <div class="alert alert-success">
                            <h4><i class="fa fa-check-circle"></i> Pembayaran Berhasil</h4>
                            <p>Terima kasih atas donasi Anda!</p>
                        </div>
                        <div class="mt-4 mb-3">
                            <h5>Detail Donasi:</h5>
                            <p><strong>Kampanye:</strong> {{ $campaign->title }}</p>
                            <p><strong>Nominal:</strong> Rp {{ number_format($donation->amount) }}</p>
                            <p><strong>Metode Pembayaran:</strong> {{ $donation->payment_method }}</p>
                            <p><strong>Waktu:</strong> {{ $donation->created_at->format('d M Y H:i') }}</p>
                        </div>
                        <a href="{{ route('campaign.detail', $campaign->title) }}" class="btn btn-primary">Kembali ke Kampanye</a>
                    @else
                        <div class="alert alert-danger">
                            <h4><i class="fa fa-times-circle"></i> Pembayaran Gagal</h4>
                            <p>Mohon maaf, pembayaran Anda tidak berhasil.</p>
                        </div>
                        <div class="mt-4 mb-3">
                            <h5>Detail Donasi:</h5>
                            <p><strong>Kampanye:</strong> {{ $campaign->title }}</p>
                            <p><strong>Nominal:</strong> Rp {{ number_format($donation->amount) }}</p>
                            <p><strong>Metode Pembayaran:</strong> {{ $donation->payment_method }}</p>
                            <p><strong>Waktu:</strong> {{ $donation->created_at->format('d M Y H:i') }}</p>
                        </div>
                        <a href="{{ route('donations.form', $campaign->id) }}" class="btn btn-primary">Coba Lagi</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('after-script')
<script>
    $(document).ready(function() {
        $('#checkStatus').click(function(e) {
            e.preventDefault();
            
            $('#statusResult').html('<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>');
            
            $.ajax({
                url: '{{ route("donations.check-status", $donation->snap_token) }}',
                type: 'GET',
                success: function(response) {

                    console.log(response);
                    if (response.success && response.data) {
                        let statusText = '';
                        let statusClass = '';
                        
                        if (response.data.status === 'PAID') {
                            statusText = 'Pembayaran telah berhasil';
                            statusClass = 'alert-success';
                            
                            // Reload halaman setelah 2 detik
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else if (response.data.status === 'EXPIRED') {
                            statusText = 'Pembayaran telah kadaluarsa';
                            statusClass = 'alert-danger';
                        } else {
                            statusText = 'Menunggu pembayaran';
                            statusClass = 'alert-warning';
                        }
                        
                        $('#statusResult').html(`<div class="alert ${statusClass}">${statusText}</div>`);
                    } else {
                        $('#statusResult').html('<div class="alert alert-danger">Gagal mendapatkan status pembayaran</div>');
                    }
                },
                error: function() {
                    $('#statusResult').html('<div class="alert alert-danger">Terjadi kesalahan, silakan coba lagi</div>');
                }
            });
        });
    });
</script>
@endpush