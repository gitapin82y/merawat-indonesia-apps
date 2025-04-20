@extends('layouts.public')
 
@section('title', 'Merawat Indonesia')

@push('after-style')
<style>
      .avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
  }
</style>
@endpush

@section('content')

    @include('includes.public.navbar')
    <!-- Hero Section -->
    <div class="hero">
        <img src="{{asset('assets/img/main-banner1.png')}}" class="mt-0 pt-0" width="100%" alt="">
        <div class="caption">
            <h2>Unduh Aplikasi Donasi Sekarang!</h2>
            <p>Nikmati kemudahan berondasi langsung dari perangkat Anda, Download sekarang!</p>
            <div class="d-flex">
                <a href="#" class="button w-50 mx-1 text-center"><i class="fa-brands fa-google-play"></i> Google Play</a>
                <a href="#" class="button w-50 mx-1 text-center"><i class="fa-brands fa-app-store"></i> App Store</a>
            </div>
        </div>
    </div>

    <div class="container">

    <!-- Donasi Options -->
    <div class="category-menu">
        <h2>Sudahkan Anda Berbuat Baik?</h2>
        <div class="grid-category">
            <a href="{{url('eksplore-kampanye')}}"><img src="{{asset('assets/img/kategori/donasi.svg')}}" alt="Donasi"><p>Donasi</p></a>
            <a href="{{url('galang-dana')}}"><img src="{{asset('assets/img/kategori/galang dana.svg')}}" alt="Galang Dana"><p>Galang Dana</p></a>
            <a href="{{url('kalkulator-zakat')}}"><img src="{{asset('assets/img/kategori/kalkulator zakat.svg')}}" alt="Kalkulator Zakat"><p>Kalkulator Zakat</p></a>
             {{-- Loop through the categories --}}
            @foreach($categories as $category)
                <a href="/eksplore?category={{ $category->name }}">
                    <img src="{{ asset('storage/' . $category->icon) }}" alt="{{ $category->name }}" style="border-radius:10px;">
                    <p>{{ $category->name }}</p>
                </a>
            @endforeach
            @if($categoriesCount >= 6)
                <a href="{{ url('menu-lainnya') }}"><img src="{{ asset('assets/img/kategori/lainnya.svg') }}" alt="Lainnya"><p>Lainnya</p></a>
            @endif
        </div>
    </div>

    <!-- Leaderboard -->
    <div class="top-donatur mt-4">
        <div class="justify-content-between d-flex">
            <h2>10 Donasi Terbanyak</h2>
            <a href="{{url('leaderboard')}}">Lihat Semua</a>
        </div>
        <div class="swiper donaturSwiper mt-2">
            <div class="swiper-wrapper">
                @forelse($donaturLeaderboard as $donatur)
                    <a href="{{route('profileDonatur',$donatur['name'] )}}" class="text-center swiper-slide">
                        <div class="avatar-container">
                            <img src="{{ $donatur['avatar'] }}" alt="Donasi" class="avatar">
                            <p>{{ strlen($donatur['name']) > 10 ? substr($donatur['name'], 0, 10) . '..' : $donatur['name'] }}</p>
                        </div>
                    </a>
                @empty
                <div class="d-flex align-items-center justify-content-center">
                  <img src="{{ asset('assets/img/icon/success-data.svg') }}" alt="Not Found" class="mb-3 pe-3" style="width: 50px; height: 50px;">
                  <p>Belum ada leaderboard donatur</p>
              </div>
            @endforelse
        </div>
        </div>
    </div>


    <!-- Campaign Section -->
    <div class="row kampanye mt-4">
        <div class="justify-content-between d-flex">
            <h2>Pilihan Kampanye Bantu Bersama</h2>
            <a href="{{url('eksplore-kampanye')}}">Lihat Semua</a>
         </div>

         <div class="swiper mySwiper mt-2">
            <div class="swiper-wrapper">
                @if($prioritasCampaigns->isEmpty())
                <div class="text-center w-100">
                    <img src="{{ asset('assets/img/icon/success-data.svg') }}" alt="Not Found" class="mb-3" style="width: 150px; height: 150px;">
                    <p>Belum Ada Prioritas Galang Dana</p>
                </div>
            @else
                @foreach($prioritasCampaigns as $prioritas)
                    @if($prioritas) <!-- Pastikan kampanye terkait ada -->
                        @include('includes.prioritas-campaign-card', ['campaign' => $prioritas->campaign])
                    @endif
                @endforeach
            @endif

            <!-- <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>-->
        </div>
        <!-- <div class="swiper-pagination"></div>  -->
        </div>
    </div>

    <!-- slider banner -->
    <div class="swiper sliderBanner mt-5">
        <div class="swiper-wrapper">
            @foreach($banners as $banner)
            <img src="{{ asset('storage/' . $banner->photo) }}" class="swiper-slide" width="100%" alt="">
            @endforeach
        </div>
        <div class="swiper-pagination"></div> 
    </div>

    <div class="row kampanye mt-4">
        <div class="justify-content-between d-flex">
            <h2>Galang Dana Akhir Pekan</h2>
            <a href="{{url('eksplore-kampanye')}}">Lihat Semua</a>
         </div>

         <div class="swiper mySwiper mt-2">
            <div class="swiper-wrapper">
            @if($weekendCampaigns->isEmpty())
            <div class="text-center w-100">
                <img src="{{ asset('assets/img/icon/success-data.svg') }}" alt="Not Found" class="mb-3" style="width: 150px; height: 150px;">
                <p>Belum Ada Galang Dana Akhir Pekan</p>
            </div>
            @else
                @foreach($weekendCampaigns as $campaign)
                        @include('includes.main-campaign-card', ['campaign' => $campaign])
                @endforeach
            @endif
            <!-- <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>-->
        </div>
        <!-- <div class="swiper-pagination"></div>  -->
        </div>
    </div>

    <div class="row kampanye mt-5 mb-5">
        <div class="justify-content-between d-flex">
            <h2>Galang Dana Lainnya</h2>
            <a href="{{url('eksplore-kampanye')}}">Lihat Semua</a>
         </div>
         @if($campaigns->isEmpty())
         <div class="text-center w-100">
            <img src="{{ asset('assets/img/icon/success-data.svg') }}" alt="Not Found" class="mb-3" style="width: 150px; height: 150px;">
            <p>Belum Ada Galang Dana</p>
        </div>
        @else
        <div id="campaigns-container">
            @foreach ($campaigns as $campaign)
        @include('includes.campaign-card', ['campaign' => $campaign])
        @endforeach

        </div>
        @endif

     @if ($campaigns->hasMorePages())
        <button id="load-more" data-next-page="{{ $campaigns->nextPageUrl() }}" class="btn btn-primary mt-1 mb-4">
            Lihat Lebih Banyak
        </button>
    @endif
  
    </div>

    </div>

    @include('includes.public.menu')

@endsection

@push('after-script')
{{-- <script>
    var swiper = new Swiper(".mySwiper", {
      spaceBetween: 20,
      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
      },
      pagination: {
        el: ".swiper-pagination",
        clickable: true,
      },
      autoplay: {
      delay: 3000,
      disableOnInteraction: false,
    },
    loop: true, 
    slidesPerView: 1.8,
      breakpoints: {
        1024: {
          slidesPerView: 2.5,
        },
      }
    });

    var swiper = new Swiper(".sliderBanner", {
      spaceBetween: 20,
      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
      },
      pagination: {
        el: ".swiper-pagination",
        clickable: true,
      },
      autoplay: {
      delay: 3000,
      disableOnInteraction: false,
    },
    loop: true, 
    slidesPerView: 1,
    });

    var swiper = new Swiper(".donaturSwiper", {
      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
      },
      pagination: {
        el: ".swiper-pagination",
        clickable: true,
      },
      autoplay: {
      delay: 3000,
      disableOnInteraction: false,
    },
    loop: true, 
    slidesPerView: 5.5,
      breakpoints: {
        1024: {
          slidesPerView: 7.5,
        },
      }
    });

  </script> --}}

<script>
    $(document).ready(function() {
        // Saat tombol "Lihat Lebih Banyak" diklik
        $('#load-more').click(function() {
            var nextPageUrl = $(this).data('next-page'); // Ambil URL untuk halaman berikutnya

            // Lakukan AJAX untuk memuat kampanye berikutnya
            $.ajax({
                url: nextPageUrl,
                type: 'GET',
                success: function(data) {
                    // Tambahkan kampanye yang baru ke dalam container kampanye
                    $('#campaigns-container').append(data.campaigns);

                    // Jika tidak ada lagi kampanye, sembunyikan tombol
                    if (!data.hasMorePages) {
                        $('#load-more').hide();
                    } else {
                        // Update URL untuk tombol "Lihat Lebih Banyak"
                        $('#load-more').data('next-page', data.nextPageUrl);
                    }
                },
                error: function() {
                    // Menangani error jika terjadi kesalahan pada AJAX
                    alert("Terjadi kesalahan, coba lagi.");
                }
            });
        });
    });
</script>


@endpush

