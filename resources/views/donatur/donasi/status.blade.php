@extends('layouts.public')
 
@section('title', 'Status Donasi')

@push('after-style')
<script>
// Pass payment config to JavaScript
@if($donation->status == 'pending' && $donation->payment_type == 'payment_gateway')
window.paymentConfig = {
    donationId: {{ $donation->id }},
    snapToken: '{{ $donation->snap_token }}',
    status: '{{ $donation->status }}'
};
@endif
</script>
<style>
    .payment-info-card {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin: 20px 0;
    }
    
    .virtual-account-display {
        background: #fff;
        border: 2px dashed #dc3545;
        border-radius: 8px;
        padding: 15px;
        text-align: center;
        margin: 15px 0;
    }
    .text-truncate {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
    }
    
    .virtual-account-number {
        font-size: 1.5rem;
        font-weight: bold;
        color: #dc3545;
        letter-spacing: 2px;
        margin: 10px 0;
    }
    
    .countdown {
        font-weight: bold;
        color: #dc3545;
        font-size: 2rem;
    }
    
    .qr-code-container {
        max-width: 250px;
        margin: 20px auto;
        padding: 10px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .payment-amount-highlight {
        font-size: 1.25rem;
        font-weight: bold;
        color: #dc3545;
        padding: 10px 0;
    }
    
    .copy-button {
        border: none;
        background: none;
        color: #dc3545;
        cursor: pointer;
        padding: 5px;
    }
    
    .copy-button:hover {
        color: #a02622;
    }
    
    .accordion-button:not(.collapsed) {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }

    .accordion-button:focus {
        box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
    }

    .status-check-interval {
        font-size: 0.9rem;
        color: #6c757d;
        margin-top: 10px;
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
                        @if($donation->payment_type == 'payment_gateway')
                            <!-- Gateway Payment Instructions -->
                            <div class="text-center mb-4">
                                <div class="alert alert-warning">
                                    <h4 class="mb-0"><i class="fa fa-clock me-2"></i> Menunggu Pembayaran</h4>
                                </div>
                                
                                <p class="mt-3">Silakan selesaikan pembayaran sebelum:</p>
                                
                                <div class="mb-2 mt-4">
                                    <div id="current-date" class="text-muted"></div>
                                </div>
                                <h5 class="countdown" id="countdown">0</h5>
                                
                                <div id="status-check-info" class="status-check-interval">
                                    <i class="fa fa-sync-alt fa-spin me-1"></i> Memeriksa status pembayaran setiap 3 detik...
                                </div>
                            </div>
                            
                            <!-- Payment Information Card -->
                            @if(isset($paymentDetail))
                                <div class="payment-info-card">
                                    <h5 class="text-center mb-3">Informasi Pembayaran</h5>
                                    
                                    <div class="row text-center mb-3">
                                        <div class="col-12">
                                            <small class="text-muted">Jumlah yang harus dibayar:</small>
                                            <div class="payment-amount-highlight">
            Rp {{ number_format($paymentDetail['payment_amount'] ?? $donation->amount) }}
            <button class="copy-button ms-2" onclick="copyToClipboard('{{ $paymentDetail['payment_amount'] ?? $donation->amount }}', this)" title="Salin nominal">
                <i class="fa fa-copy"></i>
            </button>
        </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Virtual Account Number -->
                                    @if(isset($paymentDetail['virtual_account']) && $paymentDetail['virtual_account'])
                                        <div class="virtual-account-display">
                                            <small class="text-muted">Nomor Virtual Account</small>
                                            <div class="virtual-account-number">
                                                {{ $paymentDetail['virtual_account'] }}
                                                <button class="copy-button ms-2" onclick="copyToClipboard('{{ $paymentDetail['virtual_account'] }}', this)">
                                                    <i class="fa fa-copy"></i>
                                                </button>
                                            </div>
                                            <small class="text-muted">{{ $paymentDetail['payment_method'] }}</small>
                                        </div>
                                    @endif
                                    
                                    <!-- QR Code -->
                                    @if(isset($paymentDetail['qr_url']) && $paymentDetail['qr_url'])
                                        <div class="qr-code-container">
                                            <h6 class="text-center mb-2">Scan QR Code</h6>
                                            <img src="{{ $paymentDetail['qr_url'] }}" alt="QR Code" class="img-fluid">
                                        </div>
                                    @endif

                                    @php
$ewalletMethods = ['DANA', 'DANAMONVA', 'OVO', 'SHOPEEPAY', 'LINKAJA', 'GOPAY'];
$isEwallet = false;
if (isset($paymentDetail['payment_method'])) {
    $paymentMethod = strtoupper($paymentDetail['payment_method']);
    foreach ($ewalletMethods as $method) {
        if (strpos($paymentMethod, $method) !== false) {
            $isEwallet = true;
            break;
        }
    }
}
@endphp

@if(isset($paymentDetail['checkout_url']) && $paymentDetail['checkout_url'] && $isEwallet)
    <div id="checkout-button-container" class="text-center mt-3 mb-3">
        <div class="alert alert-info">
            <p class="mb-0"><i class="fa fa-info-circle me-1"></i> Pembayaran menggunakan {{ $paymentDetail['payment_method'] }} memerlukan redirect ke aplikasi atau layanan pihak ketiga</p>
        </div>
        <a href="{{ $paymentDetail['checkout_url'] }}" target="_blank" class="btn btn-success btn-lg">
            <i class="fa fa-external-link-alt me-1"></i> Lanjutkan Pembayaran {{ $paymentDetail['payment_method'] }}
        </a>
        <p class="mt-2 text-muted">Klik tombol di atas untuk melanjutkan pembayaran</p>
    </div>
@endif
                                    
                                    <div class="text-center mt-3">
                                        <p class="text-muted">Metode: {{ $paymentDetail['payment_method'] }}</p>
                                    </div>
                                </div>
                            @endif
                            
                            <!-- Tombol Cek Status Manual -->
                            <div class="text-center mt-4">
                                <button id="checkStatus" class="btn btn-primary">
                                    <i class="fa fa-refresh me-1"></i> Cek Status Pembayaran
                                </button>
                                <div id="statusResult" class="mt-3"></div>
                            </div>
                            
                        @else
                            <!-- Manual Payment Status -->
                            <div class="text-center mb-4">
                                @if(!$donation->payment_proof)
                                    <div class="alert alert-warning">
                                        <h4 class="mb-0"><i class="fa fa-money-bill-transfer me-2"></i> Menunggu Pembayaran Manual</h4>
                                    </div>
                                    <p class="mt-4">Silakan lakukan transfer manual dan upload bukti pembayaran.</p>
                                    <a href="{{ route('donations.select-payment-method', $donation->id) }}" class="btn btn-danger btn-lg">
                                        <i class="fa fa-money-bill-transfer me-1"></i> Bayar Sekarang
                                    </a>
                                @else
                                    <div class="alert alert-info">
                                        <h4 class="mb-0"><i class="fa fa-clock me-2"></i> Menunggu Verifikasi Admin</h4>
                                    </div>
                                    <p class="mt-4">Bukti pembayaran Anda telah kami terima dan sedang diverifikasi oleh admin.</p>
                                    <p>Proses verifikasi biasanya memakan waktu 1x24 jam kerja.</p>
                                @endif
                            </div>
                        @endif
                        
                    @elseif($donation->status == 'sukses')
                        <div class="text-center mb-4">
                            <div class="alert alert-success">
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
                            <div class="alert alert-danger">
                                <h4 class="mb-0"><i class="fa fa-times-circle me-2"></i> Pembayaran Gagal</h4>
                            </div>
                            <p class="mt-4">Mohon maaf, pembayaran Anda tidak berhasil.</p>
                            <p>Waktu pembayaran telah habis atau transaksi dibatalkan.</p>
                            
                            <div class="text-center mt-4">
                                <a href="{{ route('campaign.detail', $campaign->slug) }}" class="btn btn-primary">
                                    <i class="fa fa-refresh me-1"></i> Coba Lagi
                                </a>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Detail Donasi -->
                    <div class="mt-5">
    <h5 class="border-bottom pb-2 mb-3">Detail Donasi</h5>
    <div class="row">
        <div class="col-5">
            <div class="mb-3">
                <p class="mb-1"><strong>Kampanye:</strong></p>
                <p class="mb-1"><strong>Nama Donatur:</strong></p>
                <p class="mb-1"><strong>Email:</strong></p>
                <p class="mb-1"><strong>Nominal Donasi:</strong></p>
                <p class="mb-1"><strong>Metode Pembayaran:</strong></p>
                <p class="mb-1"><strong>Status:</strong></p>
                <p class="mb-1"><strong>Waktu:</strong></p>
            </div>
        </div>
        <div class="col-7 text-md-end">
            <div class="mb-3">
                <p class="mb-1 text-truncate" title="{{ $campaign->title }}">{{ $campaign->title }}</p>
                <p class="mb-1 text-truncate" title="{{ $donation->is_anonymous ? 'Sahabat Baik' : $donation->name }}">{{ $donation->is_anonymous ? 'Sahabat Baik' : $donation->name }}</p>
                <p class="mb-1 text-truncate" title="{{ $donation->email }}">{{ $donation->email }}</p>
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

<style>
    /* Tambahkan di bagian style */
    .text-truncate {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
    }
</style>

                    <!-- Instruksi Pembayaran dengan Dropdown -->
                    @if($donation->status == 'pending' && isset($paymentDetail['payment_instructions']) && !empty($paymentDetail['payment_instructions']))
                        <div class="payment-instructions mb-4 mt-5">
                            <h5 class="border-bottom pb-2">Cara Pembayaran</h5>
                            
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
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@include('includes.public.menu')
@endsection

@push('after-style')
<script>
// Pass payment config to JavaScript
@if($donation->status == 'pending' && $donation->payment_type == 'payment_gateway')
window.paymentConfig = {
    donationId: {{ $donation->id }},
    snapToken: '{{ $donation->snap_token }}',
    status: '{{ $donation->status }}'
};
@endif
</script>
@endpush

@push('after-script')
<script>
// Simple event tracking manager to prevent duplicate events
(function() {
    // Function to check if an event has already been tracked
    function isEventTracked(donationId, eventType) {
        const key = `donation_${donationId}_${eventType}`;
        return localStorage.getItem(key) === 'true';
    }
    
    // Function to mark an event as tracked
    function markEventTracked(donationId, eventType) {
        const key = `donation_${donationId}_${eventType}`;
        localStorage.setItem(key, 'true');
        console.log(`Event ${eventType} sudah ditandai sebagai dipicu untuk donasi ${donationId}`);
    }
    
    // Make functions globally available
    window.pixelHelper = {
        isEventTracked: isEventTracked,
        markEventTracked: markEventTracked
    };
})();
</script>
<script src="{{ asset('assets/js/payment-realtime.js') }}"></script>
@php $adsense = \App\Models\Adsense::first(); @endphp
<script>
document.addEventListener('DOMContentLoaded', function() {
    const donationId = {{ $donation->id }};
    
    @if($donation->status == 'pending')
        // Check if Purchase event has already been tracked for this donation
        if (!window.pixelHelper.isEventTracked(donationId, 'purchase')) {
            console.log('Tracking Purchase event for the first time');
            
            // Facebook Pixel - Purchase (Invoice)
            @if($adsense && $adsense->facebook_pixel)
            fbq('track', 'Purchase', {
                content_name: '{{ $campaign->title ?? "Donation" }}',
                content_category: '{{ $campaign->category->name ?? "Campaign" }}',
                content_ids: ['{{ $campaign->id ?? "" }}'],
                content_type: 'product',
                value: {{ $donation->amount ?? 0 }},
                currency: 'IDR',
                transaction_id: '{{ $donation->snap_token ?? "" }}'
            });
            @endif

            // Google Ads - purchase event (Invoice)
            @if($adsense && $adsense->google_ads_id)
            gtag('event', 'purchase', {
                'send_to': '{{ $adsense->google_ads_id }}',
                'transaction_id': '{{ $donation->id }}',
                'value': {{ $donation->amount ?? 0 }},
                'currency': 'IDR',
                'items': [{
                    'item_id': '{{ $campaign->id ?? "" }}',
                    'item_name': '{{ $campaign->title ?? "Donation" }}',
                    'item_category': '{{ $campaign->category->name ?? "Campaign" }}',
                    'price': {{ $donation->amount ?? 0 }},
                    'quantity': 1
                }]
            });
            @endif

            // TikTok Pixel - CompletePayment (Invoice)
            @if($adsense && $adsense->tiktok_pixel)
            ttq.track('CompletePayment', {
                content_type: 'product',
                content_id: '{{ $campaign->id ?? "" }}',
                content_name: '{{ $campaign->title ?? "Donation" }}',
                value: {{ $donation->amount ?? 0 }},
                currency: 'IDR',
                quantity: 1,
                transaction_id: '{{ $donation->snap_token ?? "" }}'
            });
            @endif
            
            // Mark Purchase event as tracked
            window.pixelHelper.markEventTracked(donationId, 'purchase');
        } else {
            console.log('Purchase event already tracked for this donation. Skipping...');
        }

    @elseif($donation->status === 'sukses')
        // Check if Donate event has already been tracked for this donation
        if (!window.pixelHelper.isEventTracked(donationId, 'donate')) {
            console.log('Tracking Donate event for the first time');
            
            // Delete localStorage UTM parameters
            localStorage.removeItem('utm_source');
            localStorage.removeItem('utm_medium');
            localStorage.removeItem('utm_campaign');
            localStorage.removeItem('referral_code');

            // Facebook Pixel - Donate (Custom Event)
            @if($adsense && $adsense->facebook_pixel)
            fbq('trackCustom', 'Donate', {
                content_name: '{{ $campaign->title ?? "Donation" }}',
                content_category: '{{ $campaign->category->name ?? "Campaign" }}',
                content_ids: ['{{ $campaign->id ?? "" }}'],
                content_type: 'product',
                value: {{ $donation->amount ?? 0 }},
                currency: 'IDR',
                transaction_id: '{{ $donation->snap_token ?? "" }}'
            });
            @endif

            // Google Ads - custom event "donation_completed"
            @if($adsense && $adsense->google_ads_id)
            gtag('event', 'donation_completed', {
                'send_to': '{{ $adsense->google_ads_id }}',
                'transaction_id': '{{ $donation->id }}',
                'value': {{ $donation->amount ?? 0 }},
                'currency': 'IDR',
                'event_category': 'ecommerce',
                'event_label': 'donation_success'
            });
            @endif

            // TikTok Pixel - Custom Donate event
            @if($adsense && $adsense->tiktok_pixel)
            ttq.track('Donate', {
                content_type: 'product',
                content_id: '{{ $campaign->id ?? "" }}',
                content_name: '{{ $campaign->title ?? "Donation" }}',
                value: {{ $donation->amount ?? 0 }},
                currency: 'IDR',
                quantity: 1,
                transaction_id: '{{ $donation->snap_token ?? "" }}'
            });
            @endif

            // Google Ads Conversion
            @if($adsense && $adsense->google_ads_id && $adsense->google_ads_label)
            gtag('event', 'conversion', {
                'send_to': '{{ $adsense->google_ads_id }}/{{ $adsense->google_ads_label }}',
                'value': {{ $donation->amount ?? 0 }},
                'currency': 'IDR',
                'transaction_id': '{{ $donation->snap_token ?? "" }}'
            });
            @endif
            
            // Mark Donate event as tracked
            window.pixelHelper.markEventTracked(donationId, 'donate');
        } else {
            console.log('Donate event already tracked for this donation. Skipping...');
        }
    @endif

    // Your existing code with payment status checking can remain unchanged...
});
</script>

<script> 
$(document).ready(function() {
    // Copy to clipboard function
    window.copyToClipboard = function(text, button) {
        navigator.clipboard.writeText(text).then(function() {
            // Tampilkan feedback sukses
            const icon = button.querySelector('i');
            const originalClass = icon.className;
            icon.className = 'fa fa-check';
            icon.style.color = '#28a745';
            
            setTimeout(function() {
                icon.className = originalClass;
                icon.style.color = '';
            }, 2000);
        }, function(err) {
            console.error('Gagal menyalin: ', err);
        });
    };
    
    // Live clock function
    function updateClock() {
        const now = new Date();
        
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        
        const dayName = days[now.getDay()];
        const day = now.getDate();
        const month = months[now.getMonth()];
        const year = now.getFullYear();
        
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        
        document.getElementById('current-date').innerHTML = `${dayName}, ${day} ${month} ${year} ${hours}:${minutes} WIB`;
    }
    
    // Update the clock every second
    updateClock();
    setInterval(updateClock, 1000);
    
    // Calculate expiration time (24 hours from donation creation)
    @if($donation->status == 'pending')
        const createdAt = new Date("{{ $donation->created_at }}");
        const expirationTime = new Date(createdAt.getTime() + (24 * 60 * 60 * 1000));
        
        // Update countdown every second
        const countdownTimer = setInterval(function() {
            const now = new Date().getTime();
            const distance = expirationTime - now;
            
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
            
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            const displayHours = String(hours).padStart(2, '0');
            const displayMinutes = String(minutes).padStart(2, '0');
            const displaySeconds = String(seconds).padStart(2, '0');
            
            document.getElementById("countdown").innerHTML = `${displayHours}:${displayMinutes}:${displaySeconds}`;
        }, 1000);
    @endif
    
    // Auto refresh page to check payment status every 3 seconds when pending
    @if($donation->status == 'pending' && $donation->payment_type == 'payment_gateway')
        const statusCheckInterval = setInterval(function() {
            $.ajax({
                url: '{{ route("donations.check-status", $donation->snap_token) }}',
                type: 'GET',
                success: function(response) {
                    if (response.success && response.data) {
                        if (response.data.status === 'PAID') {
                            clearInterval(statusCheckInterval);
                            location.reload();
                        } else if (response.data.status === 'EXPIRED') {
                            clearInterval(statusCheckInterval);
                            location.reload();
                        }
                    }
                }
            });
        }, 3000); // Check every 3 seconds
    @endif
    
    // Manual check status button
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
                
                if (response.success && response.data) {
                    let statusText = '';
                    let statusClass = '';
                    
                    if (response.data.status === 'PAID') {
                        statusText = 'Pembayaran telah berhasil!';
                        statusClass = 'alert-success';
                        
                        // Reload halaman setelah 2 detik
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else if (response.data.status === 'EXPIRED') {
                        statusText = 'Pembayaran telah kadaluarsa.';
                        statusClass = 'alert-danger';
                        
                        // Reload halaman setelah 2 detik
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        statusText = 'Masih menunggu pembayaran...';
                        statusClass = 'alert-warning';
                    }
                    
                    $('#statusResult').html(`<div class="alert ${statusClass}">${statusText}</div>`);
                } else {
                    $('#statusResult').html('<div class="alert alert-danger">Gagal mendapatkan status pembayaran. Silakan coba lagi.</div>');
                }
            },
            error: function() {
                $('#checkStatus').prop('disabled', false);
                $('#checkStatus').html('<i class="fa fa-refresh me-1"></i> Cek Status Pembayaran');
                $('#statusResult').html('<div class="alert alert-danger">Terjadi kesalahan saat mengecek status. Silakan coba lagi.</div>');
            }
        });
    });
});
</script>
<script>
window.copyToClipboard = function(text, button) {
    // Hapus pemformatan ribuan jika ada
    if (typeof text === 'string') {
        text = text.replace(/\D/g, '');
    }
    
    navigator.clipboard.writeText(text).then(function() {
        // Tampilkan feedback sukses
        const icon = button.querySelector('i');
        const originalClass = icon.className;
        icon.className = 'fa fa-check';
        icon.style.color = '#28a745';
        
        // Tampilkan alert sukses menggunakan SweetAlert2 jika tersedia
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil disalin!',
                text: `Nominal Rp ${new Intl.NumberFormat('id-ID').format(text)} telah disalin ke clipboard`,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        } else {
            // Gunakan alert biasa jika SweetAlert2 tidak tersedia
            alert(`Nominal Rp ${new Intl.NumberFormat('id-ID').format(text)} telah disalin ke clipboard`);
        }
        
        setTimeout(function() {
            icon.className = originalClass;
            icon.style.color = '';
        }, 2000);
    }, function(err) {
        console.error('Gagal menyalin: ', err);
    });
};
</script>
@endpush