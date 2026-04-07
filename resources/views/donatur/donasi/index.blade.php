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
        background-color: rgba(220, 53, 69, 0.1);
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
                                    <h3 class="text-white d-flex">Atau Masukkan Donasi Lainnya &nbsp; <span class="text-white small"> (Min. Rp 10.000)</span></h3>
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
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
                                    @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                </div>
                                <div class="form-floating p-0 mb-3">
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="name@example.com" value="{{ auth()->check() ? auth()->user()->email : old('email') }}" required>
                                    <label for="email">Email</label>
                                    @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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

                         
                             <div class="row container m-0 pb-4">
                                <h3 class="p-0">Pilih Metode Pembayaran</h3>

                                <ul class="nav nav-tabs payment-type-tabs mb-3" id="paymentTypeTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="gateway-tab" data-bs-toggle="tab" data-bs-target="#gateway-content" type="button" role="tab" aria-controls="gateway-content" aria-selected="true">
                                            <i class="fa-solid fa-credit-card me-1"></i> Otomatis
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="manual-tab" data-bs-toggle="tab" data-bs-target="#manual-content" type="button" role="tab" aria-controls="manual-content" aria-selected="false">
                                            <i class="fa-solid fa-money-bill-transfer me-1"></i> Manual
                                        </button>
                                    </li>
                                </ul>

                                <!-- REPLACE bagian tab-content dengan kode ini -->
<div class="tab-content" id="paymentTypeTabsContent">
    <!-- PAYMENT GATEWAY (ESPAY) TAB -->
    <div class="tab-pane fade show active" id="gateway-content" role="tabpanel" aria-labelledby="gateway-tab">
         <div class="alert alert-warning">
                <i class="fa-solid fa-exclamation-triangle me-2"></i>
                JANGAN GUNAKAN!! Metode pembayaran otomatis sedang maintenance. Silakan gunakan pembayaran manual.
            </div>


            <div class="d-none">
      
        @if(count($channels) > 0)
            <!-- Group channels by category -->
            @php
                $groupedChannels = collect($channels)->groupBy('category');
                $categoryLabels = [
                    'virtual_account' => 'Virtual Account',
                    'qris' => 'QRIS',
                    'ewallet' => 'E-Wallet',
                    'credit_card' => 'Kartu Kredit',
                    'bank_transfer' => 'Transfer Bank',
                    'other' => 'Lainnya'
                ];
            @endphp

            @foreach($groupedChannels as $category => $methods)
                <!-- Category Header -->
                <div class="payment-category-header mb-2 mt-4">
                    <h6 class="text-muted mb-0">
                        <i class="fa-solid 
                            @if($category == 'virtual_account') fa-building-columns
                            @elseif($category == 'qris') fa-qrcode
                            @elseif($category == 'ewallet') fa-wallet
                            @elseif($category == 'credit_card') fa-credit-card
                            @elseif($category == 'bank_transfer') fa-money-bill-transfer
                            @else fa-money-bill
                            @endif me-2"></i>
                        {{ $categoryLabels[$category] ?? ucfirst($category) }}
                    </h6>
                </div>

                <!-- Payment Methods in this category -->
                <div class="payment-methods-group mb-3">
                    @foreach($methods as $channel)
                        <div class="payment-method-card card mb-2" 
                             data-method="{{ $channel['code'] }}" 
                             data-type="payment_gateway"
                             data-pay-method="{{ $channel['pay_method'] }}"
                             data-pay-option="{{ $channel['pay_option'] }}">
                            <div class="card-body d-flex justify-content-between align-items-center py-2">
                                <div class="d-flex align-items-center">
                                    @if(isset($channel['icon_url']) && $channel['icon_url'])
                                        <img src="{{ $channel['icon_url'] }}" 
                                             alt="{{ $channel['name'] }}" 
                                             height="30" 
                                             class="me-3"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    @endif
                                    
                                    <!-- Fallback icon if image not available -->
                                    <div class="bg-light rounded me-3 align-items-center justify-content-center" 
                                         style="width: 40px; height: 30px; display: {{ isset($channel['icon_url']) ? 'none' : 'flex' }};">
                                        <i class="fa-solid 
                                            @if($category == 'virtual_account') fa-building-columns
                                            @elseif($category == 'qris') fa-qrcode
                                            @elseif($category == 'ewallet') fa-wallet
                                            @elseif($category == 'credit_card') fa-credit-card
                                            @else fa-money-bill
                                            @endif text-danger"></i>
                                    </div>

                                    <div>
                                        <h6 class="mb-0">{{ $channel['name'] }}</h6>
                                        @if(isset($channel['fee_amount']) && $channel['fee_amount'] > 0)
                                            <small class="text-muted">
                                                Biaya: 
                                                @if($channel['fee_type'] == 'percent')
                                                    {{ $channel['fee_amount'] }}%
                                                @else
                                                    Rp {{ number_format($channel['fee_amount'], 0, ',', '.') }}
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
                </div>
            @endforeach
        @else
            <!-- No payment methods available -->
            <div class="alert alert-warning">
                <i class="fa-solid fa-exclamation-triangle me-2"></i>
                Metode pembayaran otomatis sedang tidak tersedia. Silakan gunakan pembayaran manual atau hubungi admin.
            </div>
        @endif
    </div>
            </div>
  

    <!-- MANUAL PAYMENT TAB -->
    <div class="tab-pane fade" id="manual-content" role="tabpanel" aria-labelledby="manual-tab">
        <h5 class="mb-3">Metode Pembayaran Manual (Verifikasi Admin 1x24 Jam)</h5>
        <div class="payment-methods">
            @foreach($manualMethods as $method)
            <div class="payment-method-card card mb-3" data-method="{{ $method->id }}" data-type="manual">
                <div class="card-body d-flex justify-content-between align-items-center py-2">
                    <div class="d-flex align-items-center">
                        @if($method->icon)
                            <img src="{{ asset('storage/' . $method->icon) }}" alt="{{ $method->name }}" height="30" class="me-3">
                        @else
                            <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 30px;">
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
</div>
                            </div>
                            
                            {{-- <div class="footer mb-5 text-center">
                                <div class="main-menu row col-12 mx-0 justify-content-between d-flex ">
                                    <button type="submit" id="submitForm" class="button w-100 d-flex align-items-center justify-content-center text-white shadow-sm">
                                        <span class="text-white">Donasi Sekarang</span>
                                    </button>
                                </div>
                            </div> --}}
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
    {{-- @include('includes.public.menu') --}}

@endsection

@push('after-script')
@php $adsense = \App\Models\Adsense::first(); @endphp
<script>
    // Facebook Pixel - AddToCart
    @if($adsense && $adsense->facebook_pixel)
    fbq('track', 'AddToCart', {
        content_name: '{{ $campaign->title ?? "Donation" }}',
        content_category: '{{ $campaign->category->name ?? "Campaign" }}',
        content_ids: ['{{ $campaign->id ?? "" }}'],
        content_type: 'product',
        value: {{ $donation->amount ?? 0 }},
        currency: 'IDR'
    });
    @endif

    // Google Ads - add_to_cart event
    @if($adsense && $adsense->google_ads_id)
    gtag('event', 'add_to_cart', {
        'send_to': '{{ $adsense->google_ads_id }}',
        'value': {{ $donation->amount ?? 0 }},
        'currency': 'IDR',
        'items': [{
            'id': '{{ $campaign->id ?? "" }}',
            'name': '{{ $campaign->title ?? "Donation" }}',
            'category': '{{ $campaign->category->name ?? "Campaign" }}',
            'quantity': 1,
            'price': {{ $donation->amount ?? 0 }}
        }]
    });
    @endif

    // TikTok Pixel - AddToCart
    @if($adsense && $adsense->tiktok_pixel)
    ttq.track('AddToCart', {
        content_type: 'product',
        content_id: '{{ $campaign->id ?? "" }}',
        content_name: '{{ $campaign->title ?? "Donation" }}',
        value: {{ $donation->amount ?? 0 }},
        currency: 'IDR'
    });
    @endif
</script>

<script>
    $(document).ready(function() {
        // Format input angka dengan separator ribuan
        $("#customAmount").on('input', function() {
            // Hanya izinkan angka
            this.value = this.value.replace(/[^0-9]/g, '');
            
            // Format dengan separator ribuan
            if(this.value.length > 0) {
                let value = parseInt(this.value);
                if(!isNaN(value)) {
                    // Clear selected card
                    $('.donation-amount-card').removeClass('selected');
                }
            }
        });
        
        // Pilih nominal donasi
        $('.donation-amount-card').click(function() {
            // Remove selected class from all cards
            $('.donation-amount-card').removeClass('selected');
            
            // Add selected class to clicked card
            $(this).addClass('selected');
            
            // Set amount value to input
            const amount = $(this).data('amount');
            $('#customAmount').val(amount);
        });

        // Pilih metode pembayaran (UPDATED untuk Espay)
        $('.payment-method-card').click(function() {
            // Remove selected from all payment methods
            $('.payment-method-card').removeClass('selected');
            $('.payment-check-icon').addClass('d-none');

            // Add selected to clicked method
            $(this).addClass('selected');
            $(this).find('.payment-check-icon').removeClass('d-none');

            const method = $(this).data('method');
            const type = $(this).data('type');
            
            // Set values
            $('#selected_payment_method').val(method);
            $('#payment_type').val(type);

            // Handle manual payment details
            if (type === 'manual') {
                $('#manualPaymentDetails').removeClass('d-none');

                const methodName = $(this).find('h6').text();
                const methodInfo = $(this).find('small').text();
                
                $('#methodDetails').html(`
                    <p class="mb-1"><strong>Bank/E-wallet:</strong> ${methodName}</p>
                    <p class="mb-1"><strong>Nomor Rekening:</strong> ${methodInfo}</p>
                `);
            } else {
                $('#manualPaymentDetails').addClass('d-none');
            }

            // Log for debugging (optional, hapus di production)
            console.log('Payment method selected:', {
                method: method,
                type: type,
                payMethod: $(this).data('pay-method'),
                payOption: $(this).data('pay-option')
            });
        });

        // Submit form validation
        $('#submitForm').on('click', function(e) {
            e.preventDefault();

            // Validasi amount
            const amount = $('#customAmount').val();
            
            if (!amount || parseInt(amount) < 10000) {
                Swal.fire({
                    icon: 'info',
                    text: 'Minimal donasi adalah Rp 10.000',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000
                });
                return false;
            }

            // Validasi nama, phone, dan email
            const name = $('#full_name').val().trim();
            const phone = $('#whatsapp').val().trim();
            const email = $('#email').val().trim();
            
            if (!name || !phone || !email) {
                Swal.fire({
                    icon: 'error',
                    text: 'Silakan lengkapi data diri Anda',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000
                });
                return false;
            }

            // Validasi email format
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                Swal.fire({
                    icon: 'error',
                    text: 'Format email tidak valid',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000
                });
                return false;
            }

            // Validasi phone (minimal 10 digit)
            const phoneDigits = phone.replace(/\D/g, '');
            if (phoneDigits.length < 10) {
                Swal.fire({
                    icon: 'error',
                    text: 'Nomor WhatsApp tidak valid',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000
                });
                return false;
            }

            // Validasi payment method
            const paymentType = $('#payment_type').val();
            const paymentMethod = $('#selected_payment_method').val();

            if (!paymentType || !paymentMethod) {
                Swal.fire({
                    icon: 'error',
                    text: 'Silakan pilih metode pembayaran',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000
                });
                return false;
            }

            // Show loading
            Swal.fire({
                title: 'Memproses...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Submit form
            $('#donationForm').submit();
        });

        // Auto-select first payment method (optional)
        // Uncomment jika ingin auto-select metode pertama
        // setTimeout(function() {
        //     $('.payment-method-card:first').click();
        // }, 500);
    });

    // Parse URL parameters for UTM tracking
    const urlParams = new URLSearchParams(window.location.search);
    const utmParams = ['utm_source', 'utm_medium', 'utm_campaign'];
    
    // Add UTM parameters to donation forms
    document.querySelectorAll('form[action*="donations"]').forEach(form => {
        utmParams.forEach(param => {
            const value = localStorage.getItem(param);
            if (value && !form.querySelector(`[name="${param}"]`)) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = param;
                input.value = value;
                form.appendChild(input);
            }
        });
    });
</script>
@endpush

