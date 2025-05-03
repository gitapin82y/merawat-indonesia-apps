@extends('layouts.public')
 
@section('title', 'Status Donasi')

@push('after-style')
<style>
    .accordion-button:not(.collapsed) {
    background-color: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

.accordion-button:focus {
    box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
}

.accordion-item {
    border-radius: 0.25rem;
    overflow: hidden;
}

.accordion-button {
    font-size: 0.95rem;
    padding: 0.75rem 1rem;
}

.accordion-body {
    padding: 1rem;
    font-size: 0.9rem;
}

/* Tombol instruksi tambahan untuk pembayaran manual */
.btn-outline-primary {
    color: #dc3545;
    border-color: #dc3545;
}

.btn-outline-primary:hover {
    background-color: #dc3545;
    border-color: #dc3545;
    color: white;
}
    .countdown {
        font-weight: bold;
        color: #dc3545;
        font-size: 3rem;
    }
    
    .payment-info {
        border-left: 3px solid #dc3545;
        padding-left: 15px;
    }
    
    .transfer-steps li {
        margin-bottom: 10px;
    }
    
    .status-box {
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 20px;
    }
    
    .status-pending {
        background-color: #ffc107;
    }
    
    .status-success {
        background-color: #28a745;
    }
    
    .status-failed {
        background-color: #dc3545;
    }
</style>
@endpush

@section('content')
<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0 text-white">Status Donasi</h5>
                </div>
                <div class="card-body">
                    <!-- Status Donasi -->
                    @if($donation->status == 'pending')
                        <div class="text-center mb-4">
                            @if($donation->payment_type == 'payment_gateway')
                            <div class="status-box status-pending text-dark">
                                <h4 class="mb-0"><i class="fa fa-clock me-2"></i> Menunggu Pembayaran</h4>
                            </div>
                            
                            <p class="mt-3">Silakan selesaikan pembayaran sebelum:</p>
                                 
                            <!-- Live clock -->
                            <div class="mb-2 mt-4">
                                <div id="current-date" class="text-muted"></div>
                            </div>
                            <h5 class="countdown" id="countdown">0</h5>

                            @endif
                            
                            <!-- Button bayar sekarang -->
                            <div class="mt-4">
                                @if($donation->payment_type == 'payment_gateway')
                                    @if(isset($paymentDetail['checkout_url']))
                                        <a href="{{ $paymentDetail['checkout_url'] }}" class="btn btn-danger btn-lg">
                                            <i class="fa fa-credit-card me-1"></i> Bayar Sekarang
                                        </a>
                                    @else
                                        <a href="{{ route('donations.select-payment-method', $donation->id) }}" class="btn btn-danger btn-lg">
                                            <i class="fa fa-credit-card me-1"></i> Bayar Sekarang
                                        </a>
                                    @endif
                                @elseif($donation->payment_type == 'manual' && !$donation->payment_proof)
                                    <a href="{{ route('donations.select-payment-method', $donation->id) }}" class="btn btn-danger btn-lg">
                                        <i class="fa fa-money-bill-transfer me-1"></i> Bayar Sekarang
                                    </a>
                                @elseif($donation->payment_type == 'manual' && $donation->payment_proof)
                                    <button class="btn btn-warning btn-lg" disabled>
                                        <i class="fa fa-clock me-1"></i> Menunggu Verifikasi Admin
                                    </button>
                                @else
                                    <a href="{{ route('donations.select-payment-method', $donation->id) }}" class="btn btn-danger btn-lg">
                                        <i class="fa fa-credit-card me-1"></i> Bayar Sekarang
                                    </a>
                                @endif
                            </div>
                        </div>
                            
                        @if($donation->payment_type == 'payment_gateway')
                        <!-- Tombol Cek Status -->
                        <div class="text-center mt-4">
                            <button id="checkStatus" class="btn btn-primary">
                                <i class="fa fa-refresh me-1"></i> Cek Status Pembayaran
                            </button>
                            <div id="statusResult" class="mt-3"></div>
                        </div>
                        @endif

                    @elseif($donation->status == 'sukses')
                        <div class="text-center mb-4">
                            <div class="status-box status-success text-white">
                                <h4 class="mb-0 text-white"><i class="fa text-white fa-check-circle me-2"></i> Pembayaran Berhasil</h4>
                            </div>
                            <p class="mt-4">Terima kasih atas donasi Anda!</p>
                            <p>Donasi Anda akan sangat membantu bagi {{ $campaign->title }}.</p>
                            
                            <div class="text-center mt-4">
                                <a href="{{ route('campaign.detail', $campaign->slug) }}" class="btn btn-primary">
                                    <i class="fa fa-arrow-left me-1"></i> Kembali ke Kampanye
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="text-center mb-4">
                            <div class="status-box status-failed text-white">
                                <h4 class="mb-0"><i class="fa fa-times-circle me-2"></i> Pembayaran Gagal</h4>
                            </div>
                            <p class="mt-4">Mohon maaf, pembayaran Anda tidak berhasil.</p>
                            <p>Waktu pembayaran telah habis atau transaksi dibatalkan.</p>
                            
                            <div class="text-center mt-4">
                                <a href="{{ route('donations.form', $campaign->id) }}" class="btn btn-primary">
                                    <i class="fa fa-refresh me-1"></i> Coba Lagi
                                </a>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Detail Donasi -->
                    <div class="mt-5">
                        <h5 class="border-bottom pb-2 mb-3">Detail Donasi</h5>
                        <div class="row">
                            <div class="col-6">
                                <div class="payment-info mb-3">
                                    <p class="mb-1"><strong>Kampanye:</strong></p>
                                    <p class="mb-1"><strong>Nama Donatur:</strong></p>
                                    <p class="mb-1"><strong>Email:</strong></p>
                                    <p class="mb-1"><strong>Nominal Donasi:</strong></p>
                                    <p class="mb-1"><strong>Metode Pembayaran:</strong></p>
                                    <p class="mb-1"><strong>Status:</strong></p>
                                    <p class="mb-1"><strong>Waktu:</strong></p>
                                </div>
                            </div>
                            <div class="col-6 text-md-end">
                                <div class="mb-3">
                                    <p class="mb-1">{{ $campaign->title }}</p>
                                    <p class="mb-1">{{ $donation->is_anonymous ? 'Sahabat Baik' : $donation->name }}</p>
                                    <p class="mb-1">{{ $donation->email }}</p>
                                    <p class="mb-1 fw-bold text-danger">Rp {{ number_format($donation->amount) }}</p>
                                    <p class="mb-1">
                                        @if($donation->payment_type == 'payment_gateway')
                                            {{ $donation->payment_method }}
                                        @else
                                            Manual ({{ optional($donation->manualPaymentMethod)->name ?? 'Transfer Manual' }})
                                        @endif
                                    </p>
                                    <p class="mb-1">
                                        @if($donation->status == 'pending')
                                            <span class="badge bg-warning">Menunggu</span>
                                        @elseif($donation->status == 'sukses')
                                            <span class="badge bg-success">Berhasil</span>
                                        @else
                                            <span class="badge bg-danger">Gagal</span>
                                        @endif
                                    </p>
                                    <p class="mb-1">{{ $donation->created_at->format('d M Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Instruksi Pembayaran dengan Dropdown -->
                <div class="payment-instructions mb-4 mt-5">
                    <h5 class="border-bottom pb-2">Instruksi Pembayaran</h5>
                    
                    @if($donation->payment_type == 'payment_gateway' && isset($paymentDetail['payment_instructions']) && !empty($paymentDetail['payment_instructions']))
                        <div class="accordion" id="paymentInstructionsAccordion">
                            @foreach($paymentDetail['payment_instructions'] as $index => $instruction)
                                <div class="accordion-item mb-2 border">
                                    <h2 class="accordion-header" id="heading{{ $index }}">
                                        <button class="accordion-button @if($index > 0) collapsed @endif" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}" aria-expanded="{{ $index == 0 ? 'true' : 'false' }}" aria-controls="collapse{{ $index }}">
                                            <strong>{{ $instruction['title'] }}</strong>
                                        </button>
                                    </h2>
                                    <div id="collapse{{ $index }}" class="accordion-collapse collapse @if($index == 0) show @endif" aria-labelledby="heading{{ $index }}" data-bs-parent="#paymentInstructionsAccordion">
                                        <div class="accordion-body">
                                            <ol class="mb-0">
                                                @foreach($instruction['steps'] as $step)
                                                    <li class="mb-1">{!! $step !!}</li>
                                                @endforeach
                                            </ol>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @elseif($donation->payment_type == 'manual' && isset($paymentDetail['manual_account_number']))
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="card-title">Detail Transfer:</h6>
                                <p class="card-text mb-1"><strong>Bank/E-Wallet:</strong> {{ $paymentDetail['payment_method'] ?? '-' }}</p>
                                <p class="card-text mb-1"><strong>Nomor Rekening:</strong> {{ $paymentDetail['manual_account_number'] ?? '-' }}</p>
                                <p class="card-text mb-1"><strong>Atas Nama:</strong> {{ $paymentDetail['manual_account_name'] ?? '-' }}</p>
                                <p class="card-text mb-1"><strong>Nominal Transfer:</strong> <span class="text-danger fw-bold">Rp {{ number_format($donation->amount) }}</span></p>
                                
                                @if(isset($paymentDetail['manual_instructions']) && $paymentDetail['manual_instructions'])
                                    <div class="mt-2">
                                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#manualInstructions" aria-expanded="false" aria-controls="manualInstructions">
                                            Lihat Instruksi Tambahan
                                        </button>
                                        <div class="collapse mt-2" id="manualInstructions">
                                            <div class="card card-body bg-light">
                                                {!! $paymentDetail['manual_instructions'] !!}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                
                                @if(isset($paymentDetail['payment_proof']) && $paymentDetail['payment_proof'])
                                    <div class="mt-3">
                                        <p class="mb-1"><strong>Bukti Pembayaran:</strong></p>
                                        <img src="{{ $paymentDetail['payment_proof'] }}" alt="Bukti Pembayaran" class="img-thumbnail mt-1" style="max-height: 150px">
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle me-2"></i> Silakan klik tombol "Bayar Sekarang" untuk melanjutkan pembayaran atau tombol "Cek Status Pembayaran" untuk memeriksa status donasi Anda.
                        </div>
                    @endif
                </div>


                </div>
            </div>
        </div>
    </div>
</div>

@include('includes.public.menu')
@endsection

@push('after-script')
@php $adsense = \App\Models\Adsense::first(); @endphp
<script>
    // Only track conversion if payment is successful
    @if(isset($donation) && $donation->status === 'sukses')
    
    // Hapus localStorage UTM parameters jika ada
    localStorage.removeItem('utm_source');
    localStorage.removeItem('utm_medium');
    localStorage.removeItem('utm_campaign');
    localStorage.removeItem('referral_code');

    // Facebook Pixel - Purchase
    @if($adsense && $adsense->facebook_pixel)
    fbq('track', 'Purchase', {
        content_name: '{{ $campaign->title ?? "Donation" }}',
        content_category: '{{ $campaign->category->name ?? "Campaign" }}',
        content_ids: ['{{ $campaign->id ?? "" }}'],
        content_type: 'product',
        value: {{ $donation->amount ?? 0 }},
        currency: 'IDR',
        transaction_id: '{{ $donation->transaction_id ?? "" }}'
    });
    @endif

    // Google Ads - purchase event
    @if($adsense && $adsense->google_ads_id && $adsense->google_ads_label)
    gtag('event', 'conversion', {
        'send_to': '{{ $adsense->google_ads_id }}/{{ $adsense->google_ads_label }}',
        'value': {{ $donation->amount ?? 0 }},
        'currency': 'IDR',
        'transaction_id': '{{ $donation->transaction_id ?? "" }}'
    });
    @endif

    // TikTok Pixel - CompletePayment
    @if($adsense && $adsense->tiktok_pixel)
    ttq.track('CompletePayment', {
        content_type: 'product',
        content_id: '{{ $campaign->id ?? "" }}',
        content_name: '{{ $campaign->title ?? "Donation" }}',
        value: {{ $donation->amount ?? 0 }},
        currency: 'IDR',
        quantity: 1,
        transaction_id: '{{ $donation->transaction_id ?? "" }}'
    });
    @endif
    
    @endif
</script>

<script> 
$(document).ready(function() {
    // Live clock function
    function updateClock() {
        const now = new Date();
        
        // Format tanggal: Minggu, 13 April 2025
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        
        const dayName = days[now.getDay()];
        const day = now.getDate();
        const month = months[now.getMonth()];
        const year = now.getFullYear();
        
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        
        // Update date display
        document.getElementById('current-date').innerHTML = `${dayName}, ${day} ${month} ${year} ${hours}:${minutes} WIB`;
    
    }
    
    // Update the clock every second
    updateClock();
    setInterval(updateClock, 1000);
    
    // Calculate expiration time (24 hours from donation creation)
    @if($donation->status == 'pending')
        const createdAt = new Date("{{ $donation->created_at }}");
        
        // Add 24 hours to creation time
        const expirationTime = new Date(createdAt.getTime() + (24 * 60 * 60 * 1000));
        
        // Update countdown every second
        const countdownTimer = setInterval(function() {
            const now = new Date().getTime();
            const distance = expirationTime - now;
            
            // If expired, update status to "gagal"
            if (distance < 0) {
                clearInterval(countdownTimer);
                document.getElementById("countdown").innerHTML = "EXPIRED";
                
                // Auto-update status to failed if expired
                $.ajax({
                    url: '{{ route("donations.mark-expired", $donation->id) }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        }
                    }
                });
                
                return;
            }
            
            // Calculate hours, minutes, seconds
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            // Format hours with leading zero
            const displayHours = String(hours).padStart(2, '0');
            const displayMinutes = String(minutes).padStart(2, '0');
            const displaySeconds = String(seconds).padStart(2, '0');
            
            // Display countdown (HH:MM:SS format)
            document.getElementById("countdown").innerHTML = `${displayHours}:${displayMinutes}:${displaySeconds}`;
        }, 1000);
    @endif
    
    // Auto refresh page to check payment status every 30 seconds when pending
    @if($donation->status == 'pending' && $donation->payment_type == 'payment_gateway')
        setInterval(function() {
            $.ajax({
                url: '{{ route("donations.check-status", $donation->snap_token) }}',
                type: 'GET',
                success: function(response) {
                    if (response.success && response.data) {
                        if (response.data.status === 'PAID') {
                            location.reload();
                        }
                    }
                }
            });
        }, 3000); // Check every 30 seconds
    @endif
    
    // Cek status pembayaran
    $('#checkStatus').click(function(e) {
        e.preventDefault();
        
        $(this).prop('disabled', true);
        $(this).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Sedang mengecek...');
        
        $('#statusResult').html('<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>');
        
        $.ajax({
            url: '{{ route("donations.check-status", $donation->snap_token) }}',
            type: 'GET',
            success: function(response) {
                $('#checkStatus').prop('disabled', false);
                $('#checkStatus').html('<i class="fa fa-refresh me-1"></i> Cek Status Pembayaran');
                
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
                        
                        // Reload halaman setelah 2 detik
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
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
                $('#checkStatus').prop('disabled', false);
                $('#checkStatus').html('<i class="fa fa-refresh me-1"></i> Cek Status Pembayaran');
                $('#statusResult').html('<div class="alert alert-danger">Terjadi kesalahan, silakan coba lagi</div>');
            }
        });
    });
});
</script>
@endpush