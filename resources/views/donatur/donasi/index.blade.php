@extends('layouts.public')
 
@section('title', 'Donasi')

@push('after-style')
<style>
    .payment-category-header {
        border-bottom: 2px solid #f8f9fa;
        padding-bottom: 8px;
    }
    .payment-category-header h6 {
        font-weight: 600;
        color: #6c757d;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }
    .donation-amount-card {
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .donation-amount-card.selected {
        border: 2px solid #dc3545;
        background-color: rgba(220, 53, 69, 0.1);
    }
    .payment-method-card {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid #f8f9fa;
    }
    .payment-method-card.selected {
        border: 2px solid #dc3545;

    }
    .payment-type-tabs .nav-link {
        color: #495057;
        background-color: #fff;
        border-color: #dee2e6 #dee2e6 #fff;
    }
    .payment-type-tabs .nav-link.active {
        color: #fff;
        background-color: #dc3545;
        border-color: #dc3545;
    }
    .recommendation-badge {
        position: absolute;
        top: -10px;
        right: 10px;
        background-color: #007bff;
        color: white;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: bold;
        z-index: 1;
    }
    .badge-moota-auto {
        display: inline-block;
        background: #28a745;
        color: #fff;
        font-size: 10px;
        font-weight: 600;
        padding: 2px 7px;
        border-radius: 20px;
    }
    .gateway-section-label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: #6c757d;
        border-bottom: 1px solid #e9ecef;
        padding-bottom: 6px;
        margin-bottom: 10px;
        margin-top: 16px;
    }
    .gateway-section-label:first-child { margin-top: 0; }
</style>
@endpush

@section('content')

    @include('includes.public.navbar-back', ['title' => 'Donasi Kampanye'])
    <div class="card">
        <div class="card-header">{{ $campaign->title }}</div>
        <div class="card-body">
            <form id="donationForm" action="{{ route('donations.process') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="campaign_id" value="{{ $campaign->id }}">
                <input type="hidden" name="payment_type" id="payment_type">
                <input type="hidden" name="selected_payment_method" id="selected_payment_method">
                {{-- 
                    FIX BUG: selected_gateway & selected_moota_bank_id dikirim ke controller
                    selected_gateway    = 'moota' atau 'espay'
                    selected_moota_bank_id = bank_id spesifik dari Moota (misal bpPkB9RxWB2)
                    Ini yang sebelumnya menyebabkan salah bank tampil di status page
                --}}
                <input type="hidden" name="selected_gateway" id="selected_gateway" value="">
                <input type="hidden" name="selected_moota_bank_id" id="selected_moota_bank_id" value="">

                <!-- Pilihan Nominal Donasi -->
                <div class="row container m-0">
                    <div class="donation-amount-card card box-shadow mt-4 px-0" data-amount="25000">
                        <div class="col-12 row justify-between mx-0 py-2">
                            <div class="col-9 d-flex align-self-center">
                                <img src="{{asset('assets/img/nominal-donasi-1.png')}}" width="60px" height="60px">
                                <h2 class="d-flex mb-0 align-self-center ms-3 text-color">Rp 25.000</h2>
                            </div>
                            <div class="col-3 d-flex justify-content-end align-self-center">
                                <i class="fa-solid fa-angle-right circle-arrow bg-danger text-white"></i>
                            </div>
                        </div>
                    </div>
                    <div class="donation-amount-card card box-shadow mt-4 px-0" data-amount="50000">
                        <span class="recommendation-badge">REKOMENDASI ✨</span>
                        <div class="col-12 row justify-between mx-0 py-2">
                            <div class="col-9 d-flex align-self-center">
                                <img src="{{asset('assets/img/nominal-donasi-2.png')}}" width="60px" height="60px">
                                <h2 class="d-flex mb-0 align-self-center ms-3 text-color">Rp 50.000</h2>
                            </div>
                            <div class="col-3 d-flex justify-content-end align-self-center">
                                <i class="fa-solid fa-angle-right circle-arrow bg-danger text-white"></i>
                            </div>
                        </div>
                    </div>
                    <div class="donation-amount-card card box-shadow mt-4 px-0" data-amount="100000">
                        <span class="recommendation-badge">PILIHAN TERBANYAK 👑</span>
                        <div class="col-12 row justify-between mx-0 py-2">
                            <div class="col-9 d-flex align-self-center">
                                <img src="{{asset('assets/img/nominal-donasi-3.png')}}" width="60px" height="60px">
                                <h2 class="d-flex mb-0 align-self-center ms-3 text-color">Rp 100.000</h2>
                            </div>
                            <div class="col-3 d-flex justify-content-end align-self-center">
                                <i class="fa-solid fa-angle-right circle-arrow bg-danger text-white"></i>
                            </div>
                        </div>
                    </div>
                    <div class="donation-amount-card card box-shadow mt-4 px-0" data-amount="500000">
                        <div class="col-12 row justify-between mx-0 py-2">
                            <div class="col-9 d-flex align-self-center">
                                <img src="{{asset('assets/img/nominal-donasi-4.png')}}" width="60px" height="60px">
                                <h2 class="d-flex mb-0 align-self-center ms-3 text-color">Rp 500.000</h2>
                            </div>
                            <div class="col-3 d-flex justify-content-end align-self-center">
                                <i class="fa-solid fa-angle-right circle-arrow bg-danger text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Jumlah Donasi Custom -->
                <div class="row container m-0 my-4">
                    <div class="card bg-danger p-3 align-self-center">
                        <h3 class="text-white d-flex">Atau Masukkan Donasi Lainnya &nbsp; <span class="text-white small">(Min. Rp 10.000)</span></h3>
                        <div class="input-group outline-none border-none">
                            <span class="input-group-text bg-white">Rp</span>
                            <input type="text" class="form-control" id="customAmount" name="amount" aria-label="Amount">
                        </div>
                    </div>
                </div>
                
                <!-- Form Data Diri -->
                <div class="row container m-0 pb-5">
                    <h3 class="p-0">Isi Data Diri</h3>
                    <div class="form-floating p-0 mb-3">
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="full_name" name="name" placeholder="Nama Lengkap" value="{{ auth()->check() ? auth()->user()->name : old('name') }}" required>
                        <label for="full_name">Nama Lengkap</label>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="d-flex p-0 mb-3">
                        <label class="form-check-label ms-0" for="is_anonymous">Tampilkan Sebagai anonim "Sahabat Baik"?</label>
                        <div class="form-check form-switch mx-2">
                            <input class="form-check-input" type="checkbox" id="is_anonymous" name="is_anonymous">
                        </div>
                    </div>
                    <div class="form-floating p-0 mb-3">
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" id="whatsapp" name="phone" placeholder="Nomor Whatsapp" value="{{ auth()->check() && auth()->user()->phone ? auth()->user()->phone : old('phone') }}" required>
                        <label for="whatsapp">Nomor Whatsapp</label>
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-floating p-0 mb-3">
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="name@example.com" value="{{ auth()->check() ? auth()->user()->email : old('email') }}" required>
                        <label for="email">Email</label>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-floating p-0">
                        <textarea class="form-control" placeholder="Leave a comment here" id="pesan_doa" name="doa" style="height: 100px"></textarea>
                        <label for="pesan_doa">Tulis Pesan atau doa (optional)</label>
                    </div>
                    <div class="form-check my-2">
                        <input class="form-check-input" type="checkbox" id="contact_agree" name="contact_agree" {{ old('contact_agree') ? 'checked' : '' }} checked>
                        <label class="form-check-label m-0 pt-1" for="contact_agree">Saya bersedia dihubungi</label>
                    </div>
                </div>

                <!-- Pilih Metode Pembayaran -->
                <div class="row container m-0 pb-4">
                    <h3 class="p-0">Pilih Metode Pembayaran</h3>

                    <ul class="nav nav-tabs payment-type-tabs mb-3" id="paymentTypeTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="gateway-tab" data-bs-toggle="tab" data-bs-target="#gateway-content" type="button" role="tab">
                                <i class="fa-solid fa-credit-card me-1"></i> Otomatis
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="manual-tab" data-bs-toggle="tab" data-bs-target="#manual-content" type="button" role="tab">
                                <i class="fa-solid fa-money-bill-transfer me-1"></i> Manual
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="paymentTypeTabsContent">

                        {{-- TAB OTOMATIS: MOOTA (atas) + ESPAY (bawah) --}}
                        <div class="tab-pane fade show active" id="gateway-content" role="tabpanel">

                            @php
                                $hasMoota = !empty($mootaBanks);
                                $hasEspay = !empty($channels);
                            @endphp

                            @if(!$hasMoota && !$hasEspay)
                                <div class="alert alert-warning">
                                    <i class="fa-solid fa-exclamation-triangle me-2"></i>
                                    Metode pembayaran otomatis sedang tidak tersedia. Silakan gunakan pembayaran manual.
                                </div>
                            @else

                            {{-- SECTION 2: ESPAY -- QRIS DIATAS, lalu VA, lalu lainnya --}}
@if($hasEspay)
    @php
        $groupedChannels = collect($channels)->groupBy('category');
        $categoryLabels = [
            'qris'            => 'QRIS',
            'virtual_account' => 'Virtual Account', 
            'ewallet'         => 'E-Wallet',
            'credit_card'     => 'Kartu Kredit',
            'bank_transfer'   => 'Transfer Bank',
            'other'           => 'Lainnya',
        ];
        
        // Urutan kategori yang diinginkan: QRIS dulu, baru VA, baru lainnya
        $preferredOrder = ['qris', 'ewallet', 'virtual_account', 'credit_card', 'bank_transfer', 'other'];
        $orderedCategories = collect($preferredOrder)->filter(function($category) use ($groupedChannels) {
            return $groupedChannels->has($category);
        })->merge($groupedChannels->keys()->diff($preferredOrder));
    @endphp
    
    @foreach($orderedCategories as $category)
        @if($groupedChannels->has($category))
            <div class="payment-category-header mb-2 mt-3">
                <h6 class="text-muted mb-0">
                    <i class="fa-solid
                        @if($category == 'qris') fa-qrcode
                        @elseif($category == 'virtual_account') fa-building-columns
                        @elseif($category == 'ewallet') fa-wallet
                        @elseif($category == 'credit_card') fa-credit-card
                        @elseif($category == 'bank_transfer') fa-money-bill-transfer
                        @else fa-money-bill @endif me-2"></i>
                    {{ $categoryLabels[$category] ?? ucfirst($category) }}
                </h6>
            </div>
            @foreach($groupedChannels[$category] as $channel)
                <div class="payment-method-card card mb-2 va-method-{{ $category === 'virtual_account' ? 'true' : 'false' }}"
                     data-method="{{ $channel['code'] }}"
                     data-type="payment_gateway"
                     data-gateway="espay"
                     data-bank-id=""
                     data-pay-method="{{ $channel['pay_method'] }}"
                     data-pay-option="{{ $channel['pay_option'] }}">
                    {{-- content card sama seperti sebelumnya --}}
                    <div class="card-body d-flex justify-content-between align-items-center py-2">
                        <div class="d-flex align-items-center">
                            @if(isset($channel['icon_url']) && $channel['icon_url'])
                                <img src="{{ $channel['icon_url'] }}" alt="{{ $channel['name'] }}" height="30" class="me-3"
                                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                            @endif
                            <div class="bg-light rounded me-3 align-items-center justify-content-center"
                                 style="width:40px;height:30px;display:{{ isset($channel['icon_url']) && $channel['icon_url'] ? 'none' : 'flex' }};">
                                <i class="fa-solid
                                    @if($category == 'qris') fa-qrcode
                                    @elseif($category == 'virtual_account') fa-building-columns
                                    @elseif($category == 'ewallet') fa-wallet
                                    @else fa-money-bill @endif text-danger"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $channel['name'] }}</h6>
                                @if(isset($channel['fee_amount']) && (float)$channel['fee_amount'] > 0)
                                    <small class="text-muted">
                                        Estimasi biaya:
                                        @if($channel['fee_type'] == 'percent')
                                            {{ $channel['fee_amount'] }}% per transaksi
                                        @else
                                            Rp {{ number_format($channel['fee_amount'], 0, ',', '.') }} per transaksi
                                        @endif
                                    </small>
                                @else
                                    <small class="text-success">Gratis biaya admin</small>
                                @endif
                            </div>
                        </div>
                        <i class="fa-solid fa-circle-check text-success payment-check-icon d-none"></i>
                    </div>
                </div>
            @endforeach
        @endif
    @endforeach
@endif
                                {{-- SECTION 1: MOOTA –- Transfer Bank Otomatis --}}
                                @if($hasMoota)
                                    <div class="gateway-section-label">
                                        <i class="fa-solid fa-bolt me-1 text-success"></i>
                                        Transfer Bank — Verifikasi Otomatis
                                    </div>
                                 

                                    {{-- 
                                        FIX: Setiap card Moota menyimpan bank_id spesifiknya di data-bank-id
                                        Saat diklik, bank_id ini dikirim ke hidden input #selected_moota_bank_id
                                        Controller kemudian tahu BANK MANA yang dipilih donatur
                                    --}}
                                    @foreach($mootaBanks as $bank)
    <div class="payment-method-card card mb-2"
         data-method="moota"
         data-type="payment_gateway"
         data-gateway="moota"
         data-bank-id="{{ $bank['bank_id'] }}">
        <div class="card-body d-flex justify-content-between align-items-center py-2">
            <div class="d-flex align-items-center">
                <div class="bg-opacity-10 rounded me-3 d-flex align-items-center justify-content-center" style="width:40px;height:30px;flex-shrink:0;">
                    @php
                        $label = strtolower($bank['label']);
                    @endphp

                    @if(str_contains($label, 'mandiri'))
                        <img src="{{ asset('assets/img/icon/mandiri.png') }}" alt="Mandiri" style="width:100%;height:100%;object-fit:contain;">
                    @elseif(str_contains($label, 'bca'))
                        <img src="{{ asset('assets/img/icon/bca.png') }}" alt="BCA" style="width:100%;height:100%;object-fit:contain;">
                    @elseif(str_contains($label, 'bri'))
                        <img src="{{ asset('assets/img/icon/bri.png') }}" alt="BRI" style="width:100%;height:100%;object-fit:contain;">
                    @else
                        <i class="fa-solid fa-building-columns text-success"></i>
                    @endif
                </div>
                <div>
                    <h6 class="mb-0">{{ $bank['label'] }}</h6>
                    <div class="d-flex align-items-center flex-wrap gap-1">
                        <small class="text-muted">{{ $bank['account_number'] }} &bull; a.n. {{ $bank['account_name'] }}</small>
                    </div>
                </div>
            </div>
            <i class="fa-solid fa-circle-check text-success payment-check-icon d-none"></i>
        </div>
    </div>
@endforeach
                                @endif


                              
                                </div>


                            @endif
                        {{-- END TAB OTOMATIS --}}

                        {{-- TAB MANUAL --}}
                        <div class="tab-pane fade" id="manual-content" role="tabpanel">
                            <h5 class="mb-3">Metode Pembayaran Manual (Verifikasi Admin 1x24 Jam)</h5>
                            <div class="payment-methods">
                                @foreach($manualMethods as $method)
                                <div class="payment-method-card card mb-3"
                                     data-method="{{ $method->id }}"
                                     data-type="manual"
                                     data-gateway=""
                                     data-bank-id="">
                                    <div class="card-body d-flex justify-content-between align-items-center py-2">
                                        <div class="d-flex align-items-center">
                                            @if($method->icon)
                                                <img src="{{ asset('storage/' . $method->icon) }}" alt="{{ $method->name }}" height="30" class="me-3">
                                            @else
                                                <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" style="width:40px;height:30px;">
                                                    <i class="fa-solid fa-building-columns text-muted"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <h6 class="mb-0">{{ $method->name }}</h6>
                                                <small class="text-muted">{{ $method->account_number }} ({{ $method->account_name }})</small>
                                            </div>
                                        </div>
                                        <i class="fa-solid fa-circle-check text-success payment-check-icon d-none"></i>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div id="manualPaymentDetails" class="mt-4 d-none">
                                <div class="alert alert-info">
                                    <i class="fa-solid fa-info-circle me-1"></i> Jumlah transfer akan diinformasikan setelah submit.
                                </div>
                                <div class="selected-method-details mb-3">
                                    <h6>Detail Pembayaran:</h6>
                                    <div id="methodDetails" class="bg-light p-3 rounded"></div>
                                </div>
                            </div>
                        </div>
                        {{-- END TAB MANUAL --}}

                    </div>
                </div>

                <div class="footer">
                    <div class="main-menu row col-12 mx-0 justify-content-between d-flex">
                        <button type="button" id="submitForm" class="button w-100">
                            <i class="fa-solid fa-hand-holding-heart"></i> Donasi Sekarang
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('after-script')
@php $adsense = \App\Models\Adsense::first(); @endphp
<script>
    @if($adsense && $adsense->facebook_pixel)
    fbq('track', 'AddToCart', { content_name: '{{ $campaign->title ?? "" }}', value: {{ $donation->amount ?? 0 }}, currency: 'IDR' });
    @endif
</script>
<script>
$(document).ready(function() {

    // ── Format input angka ──
    $("#customAmount").on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value.length > 0) $('.donation-amount-card').removeClass('selected');
    });

    // ── Pilih nominal donasi ──
    $('.donation-amount-card').click(function() {
        $('.donation-amount-card').removeClass('selected');
        $(this).addClass('selected');
        $('#customAmount').val($(this).data('amount'));
    });

    // ── Pilih metode pembayaran ──────────────────────────────────────────
    // FIX: Sekarang membaca data-bank-id dari card yang diklik
    // sehingga BCA vs BRI vs Mandiri bisa dibedakan di controller
    $('.payment-method-card').click(function() {
        $('.payment-method-card').removeClass('selected');
        $('.payment-check-icon').addClass('d-none');

        $(this).addClass('selected');
        $(this).find('.payment-check-icon').removeClass('d-none');

        const method  = $(this).data('method');    // 'moota' / espay_code / manual_id
        const type    = $(this).data('type');       // 'payment_gateway' / 'manual'
        const gateway = $(this).data('gateway');    // 'moota' / 'espay' / ''
        const bankId  = $(this).data('bank-id');    // FIX: bank_id spesifik Moota

        $('#selected_payment_method').val(method);
        $('#payment_type').val(type);
        $('#selected_gateway').val(gateway || '');
        $('#selected_moota_bank_id').val(bankId || ''); // FIX: simpan bank_id yang dipilih

        if (type === 'manual') {
            $('#manualPaymentDetails').removeClass('d-none');
            const methodName = $(this).find('h6').text().trim();
            const methodInfo = $(this).find('small').first().text().trim();
            $('#methodDetails').html(`
                <p class="mb-1"><strong>Bank/E-wallet:</strong> ${methodName}</p>
                <p class="mb-1"><strong>Nomor Rekening:</strong> ${methodInfo}</p>
            `);
        } else {
            $('#manualPaymentDetails').addClass('d-none');
        }
    });

    // ── Submit form ──
    $('#submitForm').on('click', function(e) {
        e.preventDefault();

         const amount = $('#customAmount').val();
    const selectedCard = $('.payment-method-card.selected');
    
    if (selectedCard.length === 0) {
        Swal.fire({ icon: 'error', text: 'Silakan pilih metode pembayaran', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
        return;
    }

    const selectedGateway = selectedCard.data('gateway') || '';
    const payMethod = selectedCard.data('pay-method') || '';
    const cardClass = selectedCard.attr('class') || '';
    const cardText = selectedCard.find('h6').first().text().toLowerCase();
    const categoryHeader = selectedCard.closest('.payment-category-header').find('h6').first().text().toLowerCase();
    
    // 🔥 LOGIKA VA YANG 100% AKURAT BERDASARKAN HTML ANDA
    const isVA = selectedGateway === 'espay' && (
        cardClass.includes('va-method-true') ||                    // Class dari blade
        categoryHeader.includes('virtual') ||                      // Header "Virtual Account"  
        cardText.includes('va ') ||                                // Text "VA BRI", "VA CIMB"
        payMethod === 'virtual_account'                            // Fallback
    );
    
   

    const minAmount = isVA ? 15000 : 10000;
    const minLabel = isVA ? 'Rp 15.000 untuk Virtual Account' : 'Rp 10.000';

    if (!amount || parseInt(amount) < minAmount) {
        Swal.fire({ 
            icon: 'info', 
            title: 'Minimal Donasi',
            text: `Minimal donasi ${minLabel}`, 
            toast: true, 
            position: 'top-end', 
            showConfirmButton: false, 
            timer: 3000 
        });
        return;
    }
   
   

        const name  = $('#full_name').val().trim();
        const phone = $('#whatsapp').val().trim();
        const email = $('#email').val().trim();
        if (!name || !phone || !email) {
            Swal.fire({ icon: 'error', text: 'Silakan lengkapi data diri Anda', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
            return;
        }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            Swal.fire({ icon: 'error', text: 'Format email tidak valid', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
            return;
        }
        if (phone.replace(/\D/g, '').length < 10) {
            Swal.fire({ icon: 'error', text: 'Nomor WhatsApp tidak valid', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
            return;
        }
        if (!$('#payment_type').val() || !$('#selected_payment_method').val()) {
            Swal.fire({ icon: 'error', text: 'Silakan pilih metode pembayaran', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
            return;
        }

        Swal.fire({ title: 'Memproses...', text: 'Mohon tunggu sebentar', allowOutsideClick: false, allowEscapeKey: false, didOpen: () => Swal.showLoading() });
        $('#donationForm').submit();
    });

    // UTM tracking
    ['utm_source','utm_medium','utm_campaign'].forEach(param => {
        const value = localStorage.getItem(param);
        if (value) {
            document.querySelectorAll('form[action*="donations"]').forEach(form => {
                if (!form.querySelector(`[name="${param}"]`)) {
                    const input = document.createElement('input');
                    input.type = 'hidden'; input.name = param; input.value = value;
                    form.appendChild(input);
                }
            });
        }
    });
});
</script>
@endpush