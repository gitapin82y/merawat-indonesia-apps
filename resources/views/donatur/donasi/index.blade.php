@extends('layouts.public')
 
@section('title', 'Donasi')

@push('after-style')
<style>
    .donation-amount-card {
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .donation-amount-card.selected {
        border: 2px solid #dc3545;
        background-color: rgba(220, 53, 69, 0.1);
    }
</style>
@endpush

@section('content')

    @include('includes.public.navbar-back', ['title' => 'Donasi Kampanye'])
                <div class="card">
                    <div class="card-header">{{ $campaign->title }}</div>
                    <div class="card-body">
                        <form id="donationForm" action="{{ route('donations.process') }}" method="POST">
                            @csrf
                            <input type="hidden" name="campaign_id" value="{{ $campaign->id }}">
                            
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
                                <h3>Isi Data Diri</h3>
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="full_name" name="name" placeholder="Nama Lengkap" value="{{ auth()->check() ? auth()->user()->name : old('name') }}" required>
                                    <label for="full_name">Nama Lengkap</label>
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                </div>
                                <div class="d-flex mb-3">
                                    <label class="form-check-label ms-0" for="is_anonymous">Tampilkan Sebagai anonim "Sahabat Baik"?</label>
                                    <div class="form-check form-switch mx-2">
                                        <input class="form-check-input" type="checkbox" id="is_anonymous" name="is_anonymous">
                                    </div>
                                </div>
                                
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" id="whatsapp" name="phone" placeholder="Nomor Whatsapp" value="{{ auth()->check() && auth()->user()->phone ? auth()->user()->phone : old('phone') }}" required>
                                    <label for="whatsapp">Nomor Whatsapp</label>
                                    @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                </div>
                                <div class="form-floating mb-3">
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="name@example.com" value="{{ auth()->check() ? auth()->user()->email : old('email') }}" required>
                                    <label for="email">Email</label>
                                    @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                </div>
                                <div class="form-floating">
                                    <textarea class="form-control" placeholder="Leave a comment here" id="pesan_doa" name="doa" style="height: 100px"></textarea>
                                    <label for="pesan_doa">Tulis Pesan atau doa (optional)</label>
                                </div>
                            </div>
                            
                            <div class="footer mb-5 text-center">
                                <div class="main-menu row col-12 mx-0 justify-content-between d-flex ">
                                    <button type="submit" id="submitForm" class="button w-100 d-flex align-items-center justify-content-center text-white shadow-sm">
                                        <span class="text-white">Pilih Metode Pembayaran</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
    @include('includes.public.menu')

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

        $('#submitForm').on('click', function(e) {

            const amount = $('#customAmount').val();

            let submit = false;
            
            if (!amount || parseInt(amount) < 10000) {
                e.preventDefault();
                Swal.fire({
                    icon: 'info',
                    text: 'Minimal donasi adalah Rp 10000',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000
                });
                return false;
            }
            
            $('#donationForm').submit();
        });
    });
    // Parse URL parameters
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

