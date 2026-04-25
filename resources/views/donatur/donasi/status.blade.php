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
    .virtual-account-display.moota-style { border-color: #28a745; }
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
        margin-bottom: 0;
    }
    .virtual-account-number.green { color: #dc3545; }
    .copy-button {
        border: none;
        background: none;
        color: #dc3545 ; 
        cursor: pointer;
    }
    .copy-button.green { color: #28a745; }
    .copy-button:hover { opacity: 0.75; background: white; }
    .moota-countdown-box {
        background: #f0fdf4;
        border: 1px solid #86efac;
        border-radius: 10px;
        padding: 14px 20px;
        text-align: center;
        margin: 16px 0;
        margin-bottom: 0;
    }
    .moota-countdown-number {
        font-size: 2.2rem;
        font-weight: 800;
        color: #16a34a;
        letter-spacing: 2px;
        font-variant-numeric: tabular-nums;
    }
    .moota-countdown-label {
        font-size: 0.8rem;
        color: #6c757d;
        margin-top: 2px;
    }
    .moota-pulse {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #22c55e;
        margin-right: 6px;
        animation: pulse 1.5s ease-in-out infinite;
    }
    @keyframes pulse {
        0%,100% { opacity: 1; transform: scale(1); }
        50%      { opacity: 0.4; transform: scale(0.8); }
    }
    .countdown {
        font-weight: bold;
        color: #dc3545;
        font-size: 2rem;
    }
    .status-check-interval { font-size: 0.9rem; color: #6c757d; margin-top: 10px; }
    .accordion-button:not(.collapsed) {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }
    .accordion-button:focus {
        box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
    }
    .payment-amount-highlight {
        font-size: 1.25rem;
        font-weight: bold;
        color: #dc3545;
        padding: 10px 0;
    }
    .qr-code-container {
        max-width: 250px;
        margin: 20px auto;
        padding: 10px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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

                    @if($donation->status == 'pending')

                        {{-- ══════════════════════════════════════════════════════
                             CASE 1: MOOTA — Transfer Bank Otomatis
                             FIX: str_starts_with karena format "moota:bank_id"
                             BUKAN == 'moota' karena tidak akan pernah match!
                        ══════════════════════════════════════════════════════ --}}
                        @if($donation->payment_type == 'payment_gateway' && str_starts_with($donation->payment_method ?? '', 'moota'))

                            {{-- Alert estimasi waktu --}}
                            <div class="alert alert-info d-flex align-items-start mb-3">
                                <div>
                                    <strong>Estimasi Verifikasi: 3–5 menit</strong><br>
                                    <small>Setelah transfer, Sistem mendeteksi mutasi masuk dan donasi Anda langsung terverifikasi otomatis.</small>
                                    <small>
                                    Ada kendala? Hubungi kami di <a href="https://api.whatsapp.com/send/?phone=6287821211934&text&type=phone_number&app_absent=0" style="color:#28a745;" target="_blank"><strong>WhatsApp Sekarang</strong></a>
                                    </small>
                                </div>
                            </div>



                            @if(isset($paymentDetail) && ($paymentDetail['type'] ?? '') === 'moota_transfer')
                                <div class="payment-info-card">

                                    {{-- Nomor rekening --}}
                                   {{-- Nomor rekening --}}
<div class="virtual-account-display moota-style">
    <p class="mb-1 text-muted">Transfer ke Rekening</p>
    
    @php
        $bankNameLower = strtolower($paymentDetail['bank_name']);
        if (str_contains($bankNameLower, 'mandiri')) {
            $bankLogo = asset('assets/img/icon/mandiri.png');
            $bankLabel = 'Mandiri';
        } elseif (str_contains($bankNameLower, 'bca')) {
            $bankLogo = asset('assets/img/icon/bca.png');
            $bankLabel = 'BCA';
        } elseif (str_contains($bankNameLower, 'bri')) {
            $bankLogo = asset('assets/img/icon/bri.png');
            $bankLabel = 'BRI';
        } else {
            $bankLogo = null;
            $bankLabel = $paymentDetail['bank_name'];
        }
    @endphp


         <img src="{{ $bankLogo }}" alt="{{ $bankLabel }}" 
     class="{{ $bankLogo ? '' : 'd-none' }} mb-1" 
     style="height:32px;object-fit:contain;">

@if(!$bankLogo)
    <h5 class="fw-bold text-success mb-1">{{ $bankLabel }}</h5>
@endif

    <div class="virtual-account-number green" id="mootaAccountNumber">
        {{ $paymentDetail['account_number'] }}
    </div>
    <p class="mb-2 text-muted">a.n. <strong>{{ $paymentDetail['account_name'] }}</strong></p>
    <button class="copy-button green" onclick="copyText('{{ $paymentDetail['account_number'] }}')">
        <i class="fa fa-copy"></i> Salin Nomor Rekening
    </button>
</div>

                                    {{-- Nominal transfer --}}
                                    <div class="virtual-account-display moota-style mt-3">
                                        <p class="mb-1 text-muted">
                                            Nominal Transfer
                                            <span class="badge bg-danger ms-1">HARUS TEPAT</span>
                                        </p>
                                        <div class="virtual-account-number green" id="mootaTransferAmount">
                                            Rp {{ number_format($paymentDetail['total_amount']) }}
                                        </div>
                                      
                                        <button class="copy-button green mt-1" onclick="copyText('{{ $paymentDetail['total_amount'] }}')">
                                            <i class="fa fa-copy"></i> Salin Nominal
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle me-1"></i>
                                    Silakan transfer ke rekening yang telah ditentukan. Donasi terverifikasi otomatis setelah transfer masuk (estimasi 3–5 menit).
                                </div>
                            @endif

                            {{-- Countdown 30 detik SELALU tampil --}}
                            <div class="moota-countdown-box">
                                <div class="mb-1">
                                    <span class="moota-pulse"></span>
                                    <span style="font-size:0.85rem;color:#15803d;font-weight:600;">
                                        Memeriksa status otomatis...
                                    </span>
                                </div>
                                <div class="moota-countdown-number" id="mootaCountdown">30</div>
                                <div class="moota-countdown-label" id="mootaCountdownLabel">
                                    detik hingga pengecekan berikutnya
                                </div>
                            </div>

                        {{-- ══════════════════════════════════════════════════════
                             CASE 2: ESPAY — Payment Gateway
                        ══════════════════════════════════════════════════════ --}}
                        @elseif($donation->payment_type == 'payment_gateway')

                            <div class="text-center mb-4">
                                <div class="alert alert-warning">
                                    <h4 class="mb-0"><i class="fa fa-clock me-2"></i> Menunggu Pembayaran</h4>
                                </div>
                                @php
    $pmFee = App\Models\EspayPaymentMethod::where('code', $donation->payment_method)->first();
    $feeAmt  = (float)($pmFee->fee_amount ?? 0);
    $feeType = $pmFee->fee_type ?? 'flat';
    if ($feeAmt > 0) {
        if ($feeType === 'percent') {
            $feeNominal = $donation->amount * $feeAmt / 100;
            $feeInfo    = $feeAmt . '% ≈ Rp ' . number_format($feeNominal, 0, ',', '.');
        } else {
            $feeInfo = 'Rp ' . number_format($feeAmt, 0, ',', '.');
        }
    }
@endphp
@if($feeAmt > 0)
<div class="alert alert-info py-2 px-3 mb-3" style="font-size:0.85rem;">
    <i class="fa fa-info-circle me-1"></i>
    <strong>Estimasi biaya admin {{ $pmFee->name ?? '' }}:</strong>
    {{ $feeInfo }}
    <span class="text-muted ms-1">(ditagihkan saat pembayaran)</span>
</div>
@endif
                                <p class="mt-3">Silakan selesaikan pembayaran sebelum:</p>
                                <div class="mb-2 mt-4">
                                    <div id="current-date" class="text-muted"></div>
                                </div>
                                <h5 class="countdown" id="countdown">--:--:--</h5>

                            </div>

                            @if(isset($paymentDetail))
                                <div class="payment-info-card">
                                    <h5 class="text-center mb-3">Informasi Pembayaran</h5>
                                    <div class="row text-center mb-3">
                                        <div class="col-12">
                                            <small class="text-muted">Jumlah yang harus dibayar:</small>
                                            <div class="payment-amount-highlight">
                                                Rp {{ number_format($donation->amount) }}
                                                <button class="copy-button ms-2" onclick="copyToClipboard('{{ $paymentDetail['payment_amount'] ?? $donation->amount }}', this)">
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
                                            <small class="text-muted">{{ $paymentDetail['payment_method'] ?? '' }}</small>
                                        </div>
                                    @endif
                                    @if(isset($paymentDetail['qr_url']) && $paymentDetail['qr_url'])
                                        <div class="qr-code-container">
                                            <h6 class="text-center mb-2">Scan QR Code</h6>
                                            <img src="{{ $paymentDetail['qr_url'] }}" alt="QR Code" class="img-fluid">
                                        </div>
                                    @endif
                                    {{-- QR Code untuk QRIS / E-Wallet --}}
@if(isset($paymentDetail['qr_image']) && $paymentDetail['qr_image'])
    <div class="text-center my-3">
        <p class="text-muted mb-2">Scan QR Code dengan aplikasi pembayaran Anda</p>
        <div class="qr-code-container">
            <img src="{{ $paymentDetail['qr_image'] }}" alt="QR Code" class="img-fluid">
        </div>
        <p class="text-muted mt-2" style="font-size:0.8rem;">
            <i class="fa fa-clock me-1"></i> QR berlaku selama 15 menit
        </p>
    </div>
@elseif(isset($paymentDetail['checkout_url']) && $paymentDetail['checkout_url'])
    {{-- Fallback: tidak ada QR image, tampilkan tombol --}}
    <a href="{{ $paymentDetail['checkout_url'] }}" target="_blank" class="btn btn-danger btn-lg w-100 mb-3">
        <i class="fa fa-external-link-alt me-2"></i> Lanjutkan Pembayaran
    </a>
                               <p class="text-muted mb-0 mt-2" style="font-size:0.78rem;">
                <i class="fa fa-info-circle me-1"></i>
                Nominal + fee akan tertera setelah menekan tombol lanjutkan pembayaran di halaman Espay.
            </p>
@endif
                                    
                                </div>
                            @endif

                            <div class="text-center mt-4">
    <div id="status-check-info" class="status-check-interval mb-2">
        <i class="fa fa-sync-alt fa-spin me-1"></i> Memeriksa status setiap 5 detik...
    </div>
    <button id="checkStatus" class="btn btn-primary">
        <i class="fa fa-refresh me-1"></i> Cek Status Pembayaran
    </button>
    <div id="statusResult" class="mt-3"></div>
</div>

                          

                        {{-- ══════════════════════════════════════════════════════
                             CASE 3: MANUAL — Upload Bukti
                        ══════════════════════════════════════════════════════ --}}
                        @else
 
    <div class="text-center mb-4">
        @if(!$donation->payment_proof)
            <div class="alert alert-warning">
                <h4 class="mb-0">
                    <i class="fa fa-money-bill-transfer me-2"></i> Menunggu Pembayaran Manual
                </h4>
            </div>
            <p class="mt-4">Silakan transfer sesuai instruksi kemudian unggah bukti pembayaran.</p>
 
            {{-- Detail rekening tujuan --}}
            <div class="bg-light p-3 rounded mb-3 text-start">
                <p class="mb-1">
                    <strong>Bank/E-wallet:</strong>
                    {{ optional($donation->manualPaymentMethod)->name }}
                </p>
                <p class="mb-1">
                    <strong>Nomor Rekening:</strong>
                    <span id="accountNumberDisplay">
                        {{ optional($donation->manualPaymentMethod)->account_number }}
                    </span>
                    <button type="button"
                        class="copy-button btn btn-sm btn-outline-secondary ms-2"
                        onclick="copyToClipboard('{{ optional($donation->manualPaymentMethod)->account_number }}', this)"
                        title="Salin nomor rekening">
                        <i class="fa fa-copy"></i>
                    </button>
                </p>
                <p class="mb-1">
                    <strong>Atas Nama:</strong>
                    {{ optional($donation->manualPaymentMethod)->account_name }}
                </p>
 
                {{-- ══════════════════════════════════════════════════════
                     JUMLAH TRANSFER = amount + unique_code
                     Donatur HARUS transfer nominal TEPAT ini agar admin
                     bisa memverifikasi via kode unik 3 digit di belakang.
                     
                     Contoh: donasi Rp 50.000, unique_code = 819
                             → donatur transfer Rp 50.819 (TEPAT)
                             → admin lihat transfer Rp 50.819 masuk
                             → cocok dengan kode unik 819 di sistem
                ══════════════════════════════════════════════════════ --}}
                <p class="mb-1">
                    <strong>Jumlah Transfer:</strong>
                    <span class="fw-bold text-danger" id="manualTransferAmount">
                        Rp {{ number_format($donation->amount + $donation->unique_code) }}
                    </span>
                    <button type="button"
                        class="copy-button btn btn-sm btn-outline-secondary ms-2"
                        onclick="copyToClipboard('{{ $donation->amount + $donation->unique_code }}', this)"
                        title="Salin nominal transfer">
                        <i class="fa fa-copy"></i>
                    </button>
                </p>
 

            </div>
 
            {{-- Instruksi tambahan jika ada --}}
            @if(optional($donation->manualPaymentMethod)->instructions)
                <div class="alert alert-info mt-3 text-start">
                    {!! nl2br(e(optional($donation->manualPaymentMethod)->instructions)) !!}
                </div>
            @endif
 
            {{-- Form upload bukti --}}
            <form action="{{ route('donations.process-manual-payment') }}" method="POST"
                  enctype="multipart/form-data" class="mt-3" id="manualPaymentForm">
                @csrf
                <input type="hidden" name="donation_id" value="{{ $donation->id }}">
                <input type="hidden" name="payment_type" value="manual">
                <input type="hidden" name="selected_payment_method" value="{{ $donation->manual_payment_method_id }}">
                <div class="mb-3 text-start">
                    <label class="form-label fw-bold">
                        Upload Bukti Transfer <span class="text-danger">*</span>
                    </label>
                    <input type="file" class="form-control" id="payment_proof"
                           name="payment_proof" required accept="image/*">
                    <div class="form-text text-muted">Format: JPG, PNG, JPEG (Maks. 2MB)</div>
                    <div id="fileError" class="alert alert-danger mt-2 d-none"></div>
                </div>
                <button type="submit" class="btn btn-danger btn-lg w-100" id="submitPaymentBtn">
                    <i class="fa fa-upload me-1"></i> Kirim Bukti Pembayaran
                </button>
            </form>
 
        @else
            {{-- Bukti sudah diupload, menunggu verifikasi --}}
            <div class="alert alert-info">
                <h4 class="mb-0">
                    <i class="fa fa-clock me-2"></i> Menunggu Verifikasi Admin
                </h4>
            </div>
            <p class="mt-4">Bukti pembayaran Anda telah kami terima dan sedang diverifikasi oleh admin.</p>
            <p class="text-muted">Proses verifikasi biasanya memakan waktu <strong>1x24 jam kerja</strong>.</p>
        @endif
    </div>
 
@endif
{{-- END CASE 3 Manual --}}


                    @elseif($donation->status == 'sukses')

                        <div class="text-center mb-4">
                            <div class="alert alert-success">
                                <h4 class="mb-0 text-white">
                                    <i class="fa text-white fa-check-circle me-2"></i> Pembayaran Berhasil
                                </h4>
                            </div>
                            <p class="mt-4">Terima kasih atas donasi Anda!</p>
                            <p>Donasi Anda akan sangat membantu bagi <strong>{{ $campaign->title }}</strong>.</p>
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
                            <p class="mt-4">Mohon maaf, pembayaran Anda tidak berhasil atau waktu telah habis.</p>
                            <a href="{{ route('campaign.detail', $campaign->slug) }}" class="btn btn-primary">
                                <i class="fa fa-refresh me-1"></i> Coba Lagi
                            </a>
                        </div>

                    @endif
                    {{-- END @if $donation->status --}}

                    {{-- ── Detail Donasi ── --}}
                    <div class="mt-4">
                        <h5 class="border-bottom pb-2 mb-3">Detail Donasi</h5>
                        <div class="row">
                            <div class="col-5">
                                <p class="mb-1"><strong>Kampanye:</strong></p>
                                <p class="mb-1"><strong>Nama Donatur:</strong></p>
                                <p class="mb-1"><strong>Email:</strong></p>
                                <p class="mb-1"><strong>Nominal Donasi:</strong></p>
                                <p class="mb-1"><strong>Metode:</strong></p>
                                <p class="mb-1"><strong>Status:</strong></p>
                                <p class="mb-1"><strong>Waktu:</strong></p>
                            </div>
                            <div class="col-7 text-md-end">
                                <p class="mb-1 text-truncate" title="{{ $campaign->title }}">{{ $campaign->title }}</p>
                                <p class="mb-1">{{ $donation->is_anonymous ? 'Sahabat Baik' : $donation->name }}</p>
                                <p class="mb-1 text-truncate" title="{{ $donation->email }}">{{ $donation->email }}</p>
                                <p class="mb-1 fw-bold text-danger">Rp {{ number_format($donation->amount) }}</p>
                                <p class="mb-1">
                                    {{-- FIX: str_starts_with untuk display label Moota --}}
                                    @if($donation->payment_type == 'payment_gateway' && str_starts_with($donation->payment_method ?? '', 'moota'))
                                        <span class="badge bg-success">Transfer Bank (Moota)</span>
                                    @elseif($donation->payment_type == 'payment_gateway')
                                        {{ $donation->payment_method }}
                                    @else
                                        Manual ({{ optional($donation->manualPaymentMethod)->name ?? 'Transfer Manual' }})
                                    @endif
                                </p>
                                <p class="mb-1">
                                    @if($donation->status == 'pending') <span class="badge bg-warning">Menunggu</span>
                                    @elseif($donation->status == 'sukses') <span class="badge bg-success">Berhasil</span>
                                    @else <span class="badge bg-danger">Gagal</span>
                                    @endif
                                </p>
                                <p class="mb-1">{{ $donation->created_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Instruksi Espay (accordion) --}}
                    @if($donation->status == 'pending' && isset($paymentDetail['payment_instructions']) && !empty($paymentDetail['payment_instructions']))
                        <div class="mt-5">
                            <h5 class="border-bottom pb-2">Cara Pembayaran</h5>
                            <div class="accordion" id="paymentInstructionsAccordion">
                                @foreach($paymentDetail['payment_instructions'] as $index => $instruction)
                                    <div class="accordion-item mb-2 border">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button @if($index > 0) collapsed @endif" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}">
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

                </div>
            </div>
        </div>
    </div>
</div>
 
@include('includes.public.menu')
@endsection

@push('after-script')
<script>
(function() {
    function isEventTracked(id, t) { return localStorage.getItem(`donation_${id}_${t}`) === 'true'; }
    function markEventTracked(id, t) { localStorage.setItem(`donation_${id}_${t}`, 'true'); }
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
            fbq('track', 'Purchase', { value: {{ $donation->amount ?? 0 }}, currency: 'IDR', transaction_id: '{{ $donation->snap_token ?? "" }}' });
            @endif
            @if($adsense && $adsense->google_ads_id)
            gtag('event', 'purchase', { 'send_to': '{{ $adsense->google_ads_id }}', 'transaction_id': '{{ $donation->id }}', 'value': {{ $donation->amount ?? 0 }}, 'currency': 'IDR' });
            @endif
            window.pixelHelper.markEventTracked(donationId, 'purchase');
        }
    @elseif($donation->status === 'sukses')
        if (!window.pixelHelper.isEventTracked(donationId, 'donate')) {
            ['utm_source','utm_medium','utm_campaign','referral_code'].forEach(k => localStorage.removeItem(k));
            @if($adsense && $adsense->facebook_pixel)
            fbq('trackCustom', 'Donate', { value: {{ $donation->amount ?? 0 }}, currency: 'IDR', transaction_id: '{{ $donation->snap_token ?? "" }}' });
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

    // ── MOOTA: Countdown 30 detik ─────────────────────────────────
@if($donation->status == 'pending' && $donation->payment_type == 'payment_gateway' && str_starts_with($donation->payment_method ?? '', 'moota'))
(function() {
    const countdownEl = document.getElementById('mootaCountdown');
    const labelEl     = document.getElementById('mootaCountdownLabel');
    let secondsLeft   = 30;
    let stopped       = false;
    let checkCount    = 0;

    const timer = setInterval(function() {
        if (stopped) { clearInterval(timer); return; }
        secondsLeft--;
        if (countdownEl) countdownEl.textContent = secondsLeft;

        if (secondsLeft <= 0) {
            checkCount++;
            secondsLeft = 30;
            if (labelEl) labelEl.textContent = `Pengecekan ke-${checkCount}...`;

            $.ajax({
                url: '{{ route("donations.check-status-by-id", $donation->id) }}',
                type: 'GET',
                success: function(res) {
                    if (res.success && res.data && res.data.status === 'PAID') {
                        stopped = true; clearInterval(timer);
                        if (countdownEl) countdownEl.textContent = '✓';
                        if (labelEl) labelEl.textContent = 'Pembayaran terdeteksi! Memuat ulang...';
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        if (labelEl) labelEl.textContent = `Belum terdeteksi. Cek berikutnya dalam 30 detik.`;
                    }
                },
                error: function() {
                    if (labelEl) labelEl.textContent = `Gagal cek. Mencoba lagi...`;
                }
            });
        }
    }, 1000);
})();
@endif


// ── ESPAY: Countdown 24 jam ───────────────────────────────────
@if($donation->status == 'pending' && $donation->payment_type == 'payment_gateway' && !str_starts_with($donation->payment_method ?? '', 'moota'))
(function() {
    const expiration  = new Date(new Date("{{ $donation->created_at }}").getTime() + 24*60*60*1000);
    const countdownEl = document.getElementById('countdown');
    const timer = setInterval(function() {
        const distance = expiration - Date.now();
        if (distance < 0) {
            clearInterval(timer);
            if (countdownEl) countdownEl.innerHTML = 'EXPIRED';
            $.ajax({
                url: '{{ route("donations.mark-expired", $donation->id) }}',
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: function(r) { if (r.success) location.reload(); }
            });
            return;
        }
        const h = String(Math.floor(distance / (1000*60*60))).padStart(2,'0');
        const m = String(Math.floor((distance % (1000*60*60)) / (1000*60))).padStart(2,'0');
        const s = String(Math.floor((distance % (1000*60)) / 1000)).padStart(2,'0');
        if (countdownEl) countdownEl.innerHTML = `${h}:${m}:${s}`;
    }, 1000);
})();
@endif

    // ── Copy: Moota (tanpa parameter button) ─────────────────────────
    window.copyText = function(text) {
        navigator.clipboard.writeText(String(text)).then(function() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'success', title: 'Tersalin!', toast: true, position: 'top-end', showConfirmButton: false, timer: 1500 });
            }
        });
    };

    // ── Copy: Espay/Manual (dengan parameter button element) ─────────
    window.copyToClipboard = function(text, button) {
        if (typeof text === 'string') text = text.replace(/\D/g, '');
        navigator.clipboard.writeText(text).then(function() {
            const icon = button.querySelector('i');
            const orig = icon.className;
            icon.className = 'fa fa-check';
            icon.style.color = '#28a745';
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'success', title: 'Berhasil disalin!', toast: true, position: 'top-end', showConfirmButton: false, timer: 1500 });
            }
            setTimeout(() => { icon.className = orig; icon.style.color = ''; }, 2000);
        });
    };

    // ── Live clock (Espay) ────────────────────────────────────────────
    function updateClock() {
        const el = document.getElementById('current-date');
        if (!el) return;
        const now    = new Date();
        const days   = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
        const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        el.innerHTML = `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()} ${String(now.getHours()).padStart(2,'0')}:${String(now.getMinutes()).padStart(2,'0')} WIB`;
    }
    updateClock();
    setInterval(updateClock, 1000);

    // ── Tombol manual cek status (Espay) ─────────────────────────────
    $('#checkStatus').click(function(e) {
        e.preventDefault();
        $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Mengecek...');
        $('#statusResult').html('<div class="d-flex justify-content-center my-2"><div class="spinner-border text-primary"></div></div>');

        $.ajax({
            url: '{{ route("donations.check-status", $donation->snap_token) }}',
            type: 'GET',
            success: function(response) {
                $('#checkStatus').prop('disabled', false).html('<i class="fa fa-refresh me-1"></i> Cek Status Pembayaran');
                if (response.success && response.data) {
                    const s = response.data.status;
                    let text = 'Masih menunggu pembayaran...', cls = 'alert-warning';
                    if (s === 'PAID')    { text = 'Pembayaran berhasil! Memuat ulang...'; cls = 'alert-success'; setTimeout(() => location.reload(), 2000); }
                    if (s === 'EXPIRED') { text = 'Pembayaran kadaluarsa.'; cls = 'alert-danger'; setTimeout(() => location.reload(), 2000); }
                    $('#statusResult').html(`<div class="alert ${cls} mt-2">${text}</div>`);
                } else {
                    $('#statusResult').html('<div class="alert alert-danger mt-2">Gagal mendapatkan status. Coba lagi.</div>');
                }
            },
            error: function() {
                $('#checkStatus').prop('disabled', false).html('<i class="fa fa-refresh me-1"></i> Cek Status Pembayaran');
                $('#statusResult').html('<div class="alert alert-danger mt-2">Terjadi kesalahan jaringan. Coba lagi.</div>');
            }
        });
    });

    // ── Validasi file bukti manual ────────────────────────────────────
    const proofInput = document.getElementById('payment_proof');
    if (proofInput) {
        proofInput.addEventListener('change', function() {
            const file = this.files[0];
            const fileError = document.getElementById('fileError');
            const submitBtn = document.getElementById('submitPaymentBtn');
            if (file && file.size > 2 * 1024 * 1024) {
                fileError.textContent = `File terlalu besar (${(file.size/1024/1024).toFixed(2)} MB). Maks. 2MB.`;
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