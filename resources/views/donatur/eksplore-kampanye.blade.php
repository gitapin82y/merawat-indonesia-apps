@extends('layouts.public')
 
@section('title', 'Eksplore Kampanye')

@push('after-style')
<style>
      .avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
  }
    .accordion-button::after {
  display: none;
}
.circle-number {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: #FF4747;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}
.accordion-item {
  border-radius: 12px;
  margin-bottom: 10px;
  box-shadow: 0px 10px 30px rgba(0,0,0,0.1);
}

</style>
@endpush

@section('content')

@include('includes.public.navbar')

<div class="container">

  <!-- Donasi Options -->
  <div class="mt-4">
      <h2>Sudahkan Anda Berbuat Baik?</h2>
      <div class="grid-category">
        {{-- menu yang hanya menampilkan 9 menu termasuk category --}}
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
            @foreach($donaturLeaderboard as $donatur)
            <a href="{{route('profileDonatur',$donatur['name'] )}}"  class="text-center swiper-slide"><img src="{{  $donatur['avatar'] }}" alt="Donasi" class="avatar">
                <p>{{ strlen($donatur['name']) >10 ? substr($donatur['name'], 0,10) . '..' : $donatur['name'] }}</p>
            </a>
        @endforeach
      </div>
      </div>
  </div>


  <!-- Campaign Section -->
  <div class="row kampanye mt-4">
      <div class="justify-content-between d-flex">
          <h2>Pilihan Kampanye</h2>
          {{-- <a href="kampanye.html?detail=Pilihan%20Kampanye%20Bantu%20Bersama">Lihat Semua</a> --}}
       </div>

       <div class="swiper mySwiper mt-2">
          <div class="swiper-wrapper">
            @if($prioritasCampaigns->isEmpty())
                <div class="text-center w-100">
                    <img src="{{ asset('assets/img/icon/success-data.svg') }}" alt="Not Found" class="mb-3" style="width: 150px; height: 150px;">
                    <p>Belum Ada Kampanye</p>
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

  <div class="row kampanye mt-4">
      <div class="justify-content-between d-flex">
          <h2>Galang Dana Akhir Pekan</h2>
          {{-- <a href="kampanye.html?detail=Akhir%20Pekan">Lihat Semua</a> --}}
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
          {{-- <a href="/kampanye/bantu">Lihat Semua</a> --}}
       </div>
       @if($campaigns->isEmpty())
       <div class="text-center">
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

