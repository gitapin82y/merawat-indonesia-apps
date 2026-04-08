@extends('layouts.public')
 
@section('title', 'Status Donasi')

@push('after-style')
<script>
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
    .virtual-account-display.moota-style {
        border-color: #28a745;
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
    .virtual-account-number.text-success {
        color: #28a745 !important;
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
    .copy-button:hover { color: #a02622; }
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

                    {{-- ══════════════════════════════════════════════════════════ --}}
                    {{-- BLOK STATUS: satu @if besar dengan @elseif yang rapi      --}}
                    {{-- ══════════════════════════════════════════════════════════ --}}

                    @if($donation->status == 'pending')

                        {{-- ── CASE 1: Moota — Transfer Bank Otomatis ── --}}
                        @if($donation->payment_type == 'payment_gateway' && $donation->payment_method == 'moota')

                            <div class="text-center mb-4">
                                <div class="alert alert-success">
                                    <h4 class="mb-0">
                                        <i class="fa fa-university me-2"></i> Selesaikan Transfer Bank
                                    </h4>
                                </div>
                                <p class="mt-2 text-muted">
                                    <i class="fa-solid fa-bolt text-success me-1"></i>
                                    Donasi Anda akan terverifikasi <strong>otomatis</strong> setelah transfer masuk — tanpa perlu upload bukti.
                                </p>
                            </div>

                            @if(isset($paymentDetail) && ($paymentDetail['type'] ?? '') === 'moota_transfer')
                                <div class="payment-info-card">

                                    {{-- Nomor rekening tujuan --}}
                                    <div class="virtual-account-display moota-style">
                                        <p class="mb-1 text-muted">Transfer ke Rekening</p>
                                        <h5 class="fw-bold text-success mb-1">{{ $paymentDetail['bank_name'] }}</h5>
                                        <div class="virtual-account-number text-success" id="mootaAccountNumber">
                                            {{ $paymentDetail['account_number'] }}
                                        </div>
                                        <p class="mb-2 text-muted">a.n. <strong>{{ $paymentDetail['account_name'] }}</strong></p>
                                        <button class="copy-button" style="color:#28a745;"
                                            onclick="copyText('{{ $paymentDetail['account_number'] }}')"
                                            title="Salin nomor rekening">
                                            <i class="fa fa-copy"></i> Salin Nomor Rekening
                                        </button>
                                    </div>

                                    {{-- Nominal yang harus ditransfer --}}
                                    <div class="virtual-account-display moota-style mt-3">
                                        <p class="mb-1 text-muted">
                                            Nominal Transfer
                                            <span class="badge bg-danger ms-1">HARUS TEPAT</span>
                                        </p>
                                        <div class="virtual-account-number text-success" id="mootaTransferAmount">
                                            Rp {{ number_format($paymentDetail['total_amount']) }}
                                        </div>
                                        <small class="text-muted d-block">
                                            Donasi Rp {{ number_format($donation->amount) }}
                                            + Kode unik Rp {{ $paymentDetail['unique_code'] }}
                                        </small>
                                        <button class="copy-button mt-1" style="color:#28a745;"
                                            onclick="copyText('{{ $paymentDetail['total_amount'] }}')"
                                            title="Salin nominal">
                                            <i class="fa fa-copy"></i> Salin Nominal
                                        </button>
                                    </div>

                                    {{-- Catatan penting --}}
                                    <div class="alert alert-warning mt-3 mb-0">
                                        <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                        <strong>Penting:</strong> Transfer dengan nominal <strong>TEPAT</strong> termasuk 3 digit kode unik di belakang.
                                        Sistem akan memverifikasi otomatis dalam beberapa menit setelah transfer masuk.
                                    </div>
                                </div>

                                {{-- Info menunggu + auto-refresh --}}
                                <div class="text-center mt-3">
                                    <p class="text-muted status-check-interval">
                                        <i class="fa fa-clock me-1"></i>
                                        Halaman ini otomatis refresh setiap 30 detik untuk mengecek status pembayaran.
                                    </p>
                                    <div class="spinner-border spinner-border-sm text-success"></div>
                                </div>

                            @else
                                {{-- Fallback jika $paymentDetail tidak tersedia --}}
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle me-1"></i>
                                    Silakan transfer ke rekening BCA yang telah ditentukan. Donasi akan otomatis terverifikasi setelah transfer masuk.
                                </div>
                            @endif

                        {{-- ── CASE 2: Espay — Payment Gateway Virtual Account / QRIS ── --}}
                        @elseif($donation->payment_type == 'payment_gateway')

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

                            @if(isset($paymentDetail))
                                <div class="payment-info-card">
                                    <h5 class="text-center mb-3">Informasi Pembayaran</h5>
                                    <div class="row text-center mb-3">
                                        <div class="col-12">
                                            <small class="text-muted">Jumlah yang harus dibayar:</small>
                                            <div class="payment-amount-highlight">
                                                Rp {{ number_format($donation->amount) }}
                                                <button class="copy-button ms-2" onclick="copyToClipboard('{{ $paymentDetail['payment_amount'] ?? $donation->amount }}', this)" title="Salin nominal">
                                                    <i class="fa fa-copy"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

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

                                    @if(isset($paymentDetail['qr_url']) && $paymentDetail['qr_url'])
                                        <div class="qr-code-container">
                                            <h6 class="text-center mb-2">Scan QR Code</h6>
                                            <img src="{{ $paymentDetail['qr_url'] }}" alt="QR Code" class="img-fluid">
                                        </div>
                                    @endif

                                    @if(isset($paymentDetail['checkout_url']) && $paymentDetail['checkout_url'])
                                        <div class="text-center mt-3 mb-3">
                                            <a href="{{ $paymentDetail['checkout_url'] }}" target="_blank" class="btn btn-danger btn-lg">
                                                <i class="fa fa-external-link-alt me-1"></i> Lanjutkan Pembayaran
                                            </a>
                                        </div>
                                    @endif

                                    <div class="text-center mt-3">
                                        <p class="text-muted">Metode: {{ $paymentDetail['payment_method'] }}</p>
                                        @if(isset($paymentDetail['manual_account_number']))
                                            <div class="bg-light p-3 rounded d-inline-block mt-2">
                                                <p class="mb-1"><strong>Nomor Rekening:</strong>
                                                    <span id="manualAccountNumber">{{ $paymentDetail['manual_account_number'] }}</span>
                                                    <button type="button" class="copy-button btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('{{ $paymentDetail['manual_account_number'] }}', this)">
                                                        <i class="fa fa-copy"></i>
                                                    </button>
                                                </p>
                                                <p class="mb-0"><small>{{ $paymentDetail['manual_account_name'] }}</small></p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <div class="text-center mt-4">
                                <button id="checkStatus" class="btn btn-primary">
                                    <i class="fa fa-refresh me-1"></i> Cek Status Pembayaran
                                </button>
                                <div id="statusResult" class="mt-3"></div>
                            </div>

                        {{-- ── CASE 3: Manual — Upload Bukti ── --}}
                        @else

                            <div class="text-center mb-4">
                                @if(!$donation->payment_proof)
                                    <div class="alert alert-warning">
                                        <h4 class="mb-0"><i class="fa fa-money-bill-transfer me-2"></i> Menunggu Pembayaran Manual</h4>
                                    </div>
                                    <p class="mt-4">Silakan transfer sesuai instruksi kemudian unggah bukti pembayaran di bawah ini.</p>

                                    <div class="bg-light p-3 rounded mb-3">
                                        <p class="mb-1"><strong>Bank/E-wallet:</strong> {{ optional($donation->manualPaymentMethod)->name }}</p>
                                        <p class="mb-1"><strong>Nomor Rekening:</strong>
                                            <span id="accountNumberDisplay">{{ optional($donation->manualPaymentMethod)->account_number }}</span>
                                            <button type="button" class="copy-button btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('{{ optional($donation->manualPaymentMethod)->account_number }}', this)">
                                                <i class="fa fa-copy"></i>
                                            </button>
                                        </p>
                                        <p class="mb-1"><strong>Atas Nama:</strong> {{ optional($donation->manualPaymentMethod)->account_name }}</p>
                                        <p class="mb-0"><strong>Jumlah Transfer:</strong> Rp
                                            <span id="transferAmount">{{ number_format($donation->amount) }}</span>
                                            <button type="button" class="copy-button btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('{{ $donation->amount }}', this)">
                                                <i class="fa fa-copy"></i>
                                            </button>
                                        </p>
                                    </div>

                                    @if(optional($donation->manualPaymentMethod)->instructions)
                                        <div class="alert alert-secondary mt-3">
                                            {!! nl2br(e(optional($donation->manualPaymentMethod)->instructions)) !!}
                                        </div>
                                    @endif

                                    <form action="{{ route('donations.process-manual-payment') }}" method="POST" enctype="multipart/form-data" class="mt-3" id="manualPaymentForm">
                                        @csrf
                                        <input type="hidden" name="donation_id" value="{{ $donation->id }}">
                                        <input type="hidden" name="payment_type" value="manual">
                                        <input type="hidden" name="selected_payment_method" value="{{ $donation->manual_payment_method_id }}">
                                        <div class="mb-3">
                                            <label for="payment_proof" class="form-label">Upload Bukti Transfer <span class="text-danger">*</span></label>
                                            <input type="file" class="form-control" id="payment_proof" name="payment_proof" required accept="image/*">
                                            <div class="form-text">Format: JPG, PNG, JPEG (Maks. 2MB)</div>
                                            <div id="fileError" class="alert alert-danger mt-2 d-none"></div>
                                        </div>
                                        <button type="submit" class="btn btn-danger btn-lg" id="submitPaymentBtn">
                                            <i class="fa fa-upload me-1"></i> Kirim Bukti Pembayaran
                                        </button>
                                    </form>
                                @else
                                    <div class="alert alert-info">
                                        <h4 class="mb-0"><i class="fa fa-clock me-2"></i> Menunggu Verifikasi Admin</h4>
                                    </div>
                                    <p class="mt-4">Bukti pembayaran Anda telah kami terima dan sedang diverifikasi oleh admin.</p>
                                    <p>Proses verifikasi biasanya memakan waktu 1x24 jam kerja.</p>
                                @endif
                            </div>

                        @endif
                        {{-- END @if payment_type --}}

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
                    {{-- END @if $donation->status --}}

                    {{-- ── Detail Donasi ── --}}
                    <div class="mt-5">
                        <h5 class="border-bottom pb-2 mb-3">Detail Donasi</h5>
                        <div class="row">
                            <div class="col-5">
                                <p class="mb-1"><strong>Kampanye:</strong></p>
                                <p class="mb-1"><strong>Nama Donatur:</strong></p>
                                <p class="mb-1"><strong>Email:</strong></p>
                                <p class="mb-1"><strong>Nominal Donasi:</strong></p>
                                <p class="mb-1"><strong>Metode Pembayaran:</strong></p>
                                <p class="mb-1"><strong>Status:</strong></p>
                                <p class="mb-1"><strong>Waktu:</strong></p>
                            </div>
                            <div class="col-7 text-md-end">
                                <p class="mb-1 text-truncate" title="{{ $campaign->title }}">{{ $campaign->title }}</p>
                                <p class="mb-1 text-truncate">{{ $donation->is_anonymous ? 'Sahabat Baik' : $donation->name }}</p>
                                <p class="mb-1 text-truncate" title="{{ $donation->email }}">{{ $donation->email }}</p>
                                <p class="mb-1 fw-bold text-danger">Rp {{ number_format($donation->amount) }}</p>
                                <p class="mb-1">
                                    @if($donation->payment_type == 'payment_gateway')
                                        @if($donation->payment_method == 'moota')
                                            <span class="badge bg-success">Transfer Bank (Moota)</span>
                                        @else
                                            {{ $donation->payment_method }}
                                        @endif
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

                    {{-- Instruksi pembayaran Espay (accordion) --}}
                    @if($donation->status == 'pending' && isset($paymentDetail['payment_instructions']) && !empty($paymentDetail['payment_instructions']))
                        <div class="payment-instructions mb-4 mt-5">
                            <h5 class="border-bottom pb-2">Cara Pembayaran</h5>
                            <div class="accordion" id="paymentInstructionsAccordion">
                                @foreach($paymentDetail['payment_instructions'] as $index => $instruction)
                                    <div class="accordion-item mb-2 border">
                                        <h2 class="accordion-header" id="heading{{ $index }}">
                                            <button class="accordion-button @if($index > 0) collapsed @endif" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}"
                                                aria-expanded="{{ $index == 0 ? 'true' : 'false' }}">
                                                <strong>{{ $instruction['title'] }}</strong>
                                            </button>
                                        </h2>
                                        <div id="collapse{{ $index }}" class="accordion-collapse collapse @if($index == 0) show @endif"
                                            data-bs-parent="#paymentInstructionsAccordion">
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

                </div>{{-- end card-body --}}
            </div>
        </div>
    </div>
</div>

@include('includes.public.menu')
@endsection

@push('after-script')
<script>
(function() {
    function isEventTracked(id, type) { return localStorage.getItem(`donation_${id}_${type}`) === 'true'; }
    function markEventTracked(id, type) { localStorage.setItem(`donation_${id}_${type}`, 'true'); }
    window.pixelHelper = { isEventTracked, markEventTracked };
})();
</script>
<script src="{{ asset('assets/js/payment-realtime.js') }}"></script>
@php $adsense = \App\Models\Adsense::first(); @endphp
<script>
document.addEventListener('DOMContentLoaded', function() {
    const donationId = {{ $donation->id }};

    @if($donation->status == 'pending')
        if (!window.pixelHelper.isEventTracked(donationId, 'purchase')) {
            @if($adsense && $adsense->facebook_pixel)
            fbq('track', 'Purchase', { content_name: '{{ $campaign->title ?? "" }}', value: {{ $donation->amount ?? 0 }}, currency: 'IDR', transaction_id: '{{ $donation->snap_token ?? "" }}' });
            @endif
            @if($adsense && $adsense->google_ads_id)
            gtag('event', 'purchase', { 'send_to': '{{ $adsense->google_ads_id }}', 'transaction_id': '{{ $donation->id }}', 'value': {{ $donation->amount ?? 0 }}, 'currency': 'IDR' });
            @endif
            @if($adsense && $adsense->tiktok_pixel)
            ttq.track('CompletePayment', { content_id: '{{ $campaign->id ?? "" }}', value: {{ $donation->amount ?? 0 }}, currency: 'IDR' });
            @endif
            window.pixelHelper.markEventTracked(donationId, 'purchase');
        }
    @elseif($donation->status === 'sukses')
        if (!window.pixelHelper.isEventTracked(donationId, 'donate')) {
            ['utm_source','utm_medium','utm_campaign','referral_code'].forEach(k => localStorage.removeItem(k));
            @if($adsense && $adsense->facebook_pixel)
            fbq('trackCustom', 'Donate', { value: {{ $donation->amount ?? 0 }}, currency: 'IDR', transaction_id: '{{ $donation->snap_token ?? "" }}' });
            @endif
            @if($adsense && $adsense->google_ads_id)
            gtag('event', 'donation_completed', { 'send_to': '{{ $adsense->google_ads_id }}', 'value': {{ $donation->amount ?? 0 }}, 'currency': 'IDR' });
            @endif
            @if($adsense && $adsense->google_ads_id && $adsense->google_ads_label)
            gtag('event', 'conversion', { 'send_to': '{{ $adsense->google_ads_id }}/{{ $adsense->google_ads_label }}', 'value': {{ $donation->amount ?? 0 }}, 'currency': 'IDR', 'transaction_id': '{{ $donation->snap_token ?? "" }}' });
            @endif
            window.pixelHelper.markEventTracked(donationId, 'donate');
        }
    @endif
});
</script>

<script>
$(document).ready(function() {

    // ── Copy to clipboard (versi tombol button dengan icon) ──
    window.copyToClipboard = function(text, button) {
        if (typeof text === 'string') text = text.replace(/\D/g, '');
        navigator.clipboard.writeText(text).then(function() {
            const icon = button.querySelector('i');
            const orig = icon.className;
            icon.className = 'fa fa-check';
            icon.style.color = '#28a745';
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'success', title: 'Berhasil disalin!', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
            }
            setTimeout(() => { icon.className = orig; icon.style.color = ''; }, 2000);
        });
    };

    // ── Copy to clipboard versi Moota (tanpa parameter button) ──
    window.copyText = function(text) {
        navigator.clipboard.writeText(String(text)).then(function() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'success', title: 'Tersalin!', text: 'Berhasil disalin ke clipboard.', toast: true, position: 'top-end', showConfirmButton: false, timer: 1500 });
            }
        });
    };

    // ── Live clock ──
    function updateClock() {
        const el = document.getElementById('current-date');
        if (!el) return;
        const now = new Date();
        const days   = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
        const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        el.innerHTML = `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()} ${String(now.getHours()).padStart(2,'0')}:${String(now.getMinutes()).padStart(2,'0')} WIB`;
    }
    updateClock();
    setInterval(updateClock, 1000);

    // ── Countdown Espay ──
    @if($donation->status == 'pending' && $donation->payment_type == 'payment_gateway' && $donation->payment_method != 'moota')
        const createdAt      = new Date("{{ $donation->created_at }}");
        const expirationTime = new Date(createdAt.getTime() + (24 * 60 * 60 * 1000));
        const countdownEl    = document.getElementById("countdown");

        const countdownTimer = setInterval(function() {
            const distance = expirationTime - new Date().getTime();
            if (distance < 0) {
                clearInterval(countdownTimer);
                if (countdownEl) countdownEl.innerHTML = "EXPIRED";
                $.ajax({ url: '{{ route("donations.mark-expired", $donation->id) }}', type: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    success: function(r) { if (r.success) location.reload(); }
                });
                return;
            }
            const h = String(Math.floor((distance % (1000*60*60*24)) / (1000*60*60))).padStart(2,'0');
            const m = String(Math.floor((distance % (1000*60*60)) / (1000*60))).padStart(2,'0');
            const s = String(Math.floor((distance % (1000*60)) / 1000)).padStart(2,'0');
            if (countdownEl) countdownEl.innerHTML = `${h}:${m}:${s}`;
        }, 1000);
    @endif

    // ── Auto-refresh Espay setiap 3 detik ──
    @if($donation->status == 'pending' && $donation->payment_type == 'payment_gateway' && $donation->payment_method != 'moota')
        const espayInterval = setInterval(function() {
            $.ajax({
                url: '{{ route("donations.check-status", $donation->snap_token) }}',
                type: 'GET',
                success: function(response) {
                    if (response.success && response.data) {
                        if (['PAID','EXPIRED'].includes(response.data.status)) {
                            clearInterval(espayInterval);
                            location.reload();
                        }
                    }
                }
            });
        }, 3000);
    @endif

    // ── Auto-refresh Moota setiap 30 detik ──
    @if($donation->status == 'pending' && $donation->payment_type == 'payment_gateway' && $donation->payment_method == 'moota')
        const mootaInterval = setInterval(function() {
            $.get('/donations/check-status/' + '{{ $donation->snap_token }}', function(res) {
                if (res.success && res.data && res.data.status === 'PAID') {
                    clearInterval(mootaInterval);
                    location.reload();
                }
            });
        }, 30000);
    @endif

    // ── Tombol manual cek status Espay ──
    $('#checkStatus').click(function(e) {
        e.preventDefault();
        $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Sedang mengecek...');
        $('#statusResult').html('<div class="spinner-border text-primary" role="status"></div>');

        $.ajax({
            url: '{{ route("donations.check-status", $donation->snap_token) }}',
            type: 'GET',
            success: function(response) {
                $('#checkStatus').prop('disabled', false).html('<i class="fa fa-refresh me-1"></i> Cek Status Pembayaran');
                if (response.success && response.data) {
                    const s = response.data.status;
                    let text = 'Masih menunggu pembayaran...', cls = 'alert-warning';
                    if (s === 'PAID')    { text = 'Pembayaran telah berhasil!'; cls = 'alert-success'; setTimeout(() => location.reload(), 2000); }
                    if (s === 'EXPIRED') { text = 'Pembayaran telah kadaluarsa.'; cls = 'alert-danger'; setTimeout(() => location.reload(), 2000); }
                    $('#statusResult').html(`<div class="alert ${cls}">${text}</div>`);
                } else {
                    $('#statusResult').html('<div class="alert alert-danger">Gagal mendapatkan status. Silakan coba lagi.</div>');
                }
            },
            error: function() {
                $('#checkStatus').prop('disabled', false).html('<i class="fa fa-refresh me-1"></i> Cek Status Pembayaran');
                $('#statusResult').html('<div class="alert alert-danger">Terjadi kesalahan. Silakan coba lagi.</div>');
            }
        });
    });

    // ── Validasi ukuran file bukti transfer manual ──
    const proofInput = document.getElementById('payment_proof');
    if (proofInput) {
        proofInput.addEventListener('change', function() {
            const file = this.files[0];
            const fileError = document.getElementById('fileError');
            const submitBtn = document.getElementById('submitPaymentBtn');
            if (file && file.size > 2 * 1024 * 1024) {
                fileError.textContent = 'File terlalu besar! Maksimal 2MB. File Anda: ' + (file.size/1024/1024).toFixed(2) + 'MB';
                fileError.classList.remove('d-none');
                submitBtn.disabled = true;
                this.value = '';
            } else {
                fileError.classList.add('d-none');
                if (submitBtn) submitBtn.disabled = false;
            }
        });
    }

});
</script>
@endpush