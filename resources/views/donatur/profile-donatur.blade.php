@extends('layouts.public')
 
@section('title', 'Profile')

@push('after-style')
 <style>
     .profile-avatar .avatar-container img {
        width: 150px;
        height: 150px;
        bottom: -45px;
        border: 6px solid white;
      }
        .btn-danger {
            background-color: #FF4747;
        }

        .profile-img {
            width: 150px;
            height: 150px;
            border: 7px solid white;
            margin-top: -75px;
        }

        .content-box {
            background: white;

            padding: 15px;


        }

        .donation-card {
            background: #fff;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 10px;
            box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.1);
        }

        .social-icon img {
            width: auto;
            height: 60px;
            object-fit: contain;
        }

        .btn-responsive {
            font-size: 14px;
            padding: 8px 12px;
            white-space: nowrap;
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 1;
            /* Tombol akan menyesuaikan lebar secara proporsional */
            min-width: 0;
            /* Memastikan tombol bisa mengecil */
        }

        .btn-responsive img {
            width: 15px;
            /* Ukuran ikon default */
            height: 15px;
            margin-right: 8px;
        }

        @media (max-width: 768px) {
            .btn-responsive {
                font-size: 10px;
                /* Ukuran font lebih kecil di layar kecil */
                padding: 6px 10px;
            }

            .btn-responsive img {
                width: 10px;
                /* Ukuran ikon lebih kecil di layar kecil */
                height: 10px;
                margin-right: 6px;
            }

            .social-icon img {
                height: 40px;
            }

            .profile-img {
                width: 120px;
                height: 120px;
                margin-top: -60px;
            }
        }

        .logout-box {
            margin-top: auto;
            padding-bottom: 20px;
            /* Memberikan jarak di bagian bawah */
        }

        .btn-active {
            background-color: #fff !important;
            /* Warna latar belakang putih */
            color: #ff3b3b !important;
            /* Warna teks merah */
            border: 2px solid #ff3b3b !important;
            /* Border merah */
            font-weight: bold !important;
            /* Teks tebal */
        }
    </style>
@endpush

@section('content')

<div class="profile-header position-relative mt-4 px-4">
    <img
     src="{{ $user->thumbnail ? asset('storage/' . $user->thumbnail) : asset('assets/img/banner/banner-slider.png') }}"
      alt="Banner"
      class="w-100" style="border-radius: 10px;max-height:400px;"
    />
    <div class="profile-avatar text-center">
      <div class="avatar-container position-relative d-inline-block">
        <img
          src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('assets/img/avatar/main-avatar.png') }}"
          alt="William Saliba"
          class="rounded-circle position-absolute start-50 translate-middle-x"
        />
      </div>
    </div>
  </div>

            <!-- Profile & Banner Section -->
            <div class="content-box text-center">
              <p class="mb-2 text-center mt-4 fs-5 fw-bold text-second">{{$user->name}}</p>
              <p class="text-muted mx-3 mt-2">
                {{$user->bio ?? 'Bio belum tersedia'}}
              </p>
              <div class="d-flex justify-content-center gap-3 mt-3">
                @if(isset($user->social['instagram']))
                <div class="social-icon d-flex align-items-center justify-content-center">
                    <a href="{{ url($user->social['instagram']) }}" target="_blank">
                        <img src="{{asset('assets/img/icon/instagram.svg')}}" alt="Instagram" class="img-fluid">
                    </a>
                </div>
                @endif
        
                @if(isset($user->social['youtube']))
                <div class="social-icon d-flex align-items-center justify-content-center">
                    <a href="{{ url($user->social['youtube']) }}" target="_blank">
                        <img src="{{asset('assets/img/icon/youtube.svg')}}" alt="YouTube" class="img-fluid">
                    </a>
                </div>
                @endif
        
                @if(isset($user->social['facebook']))
                <div class="social-icon d-flex align-items-center justify-content-center">
                    <a href="{{ url($user->social['facebook']) }}" target="_blank">
                        <img src="{{asset('assets/img/icon/facebook.svg')}}" alt="Facebook" class="img-fluid">
                    </a>
                </div>
                @endif
        
                @if(isset($user->social['tiktok']))
                <div class="social-icon d-flex align-items-center justify-content-center">
                    <a href="{{ url($user->social['tiktok']) }}" target="_blank">
                        <img src="{{asset('assets/img/icon/tiktok.svg')}}" alt="TikTok" class="img-fluid">
                    </a>
                </div>
                @endif
            </div>
          </div>
          <div class="line-spacing"></div>
          <!-- Kotak Total Transaksi & Statistik -->
          <div class="content-box text-center justify-content-center">
              <div class="row">
                  <div class="col-6 d-flex align-items-center">
                      <div class=" d-flex justify-content-center align-items-center"
                          style="width: 85px; height: 85px;">
                          <img src="{{asset('assets/img/icon/pencairan-dana.svg')}}" alt="Total Transaksi" class="img-fluid"
                              style="max-width: 75px;">
                      </div>
                      <div class="text-start">
                          <p class="fw-bold text-dark mb-1">Rp {{$totalDonasi}}</p>
                          <p class="text-muted kecil mb-0">Total Transaksi Donasi</p>
                      </div>
                  </div>
                  <div class="col-6 d-flex align-items-center">
                      <div class=" d-flex justify-content-center align-items-center"
                          style="width: 85px; height: 85px;">
                          <img src="{{asset('assets/img/icon/total-kampanye.svg')}}" alt="Dukungan Kampanye" class="img-fluid"
                              style="max-width: 75px;">
                      </div>
                      <div class="text-start">
                          <p class="fw-bold text-dark mb-1">{{$jumlahDukungan}}</p>
                          <p class="text-muted kecil mb-0">Dukungan Kampanye</p>
                      </div>
                  </div>
              </div>
          </div>
          <div class="line-spacing"></div>
          <!-- Kotak Riwayat Donasi, Tersimpan, dan Dukungan -->
          <div class="content-box" style="padding-bottom: 500px;">
              <div class="d-flex justify-content-center gap-2 flex-nowrap w-100">
                  <button class="btn btn-danger btn-responsive" id="btnRiwayat">Riwayat Donasi</button>
                  <button class="btn btn-danger btn-responsive" id="btnDukungan">Dukungan</button>
              </div>

              <!-- Riwayat Donasi (Awalnya Tersembunyi) -->
              <div id="donationHistory" class="donation-history mt-3 d-none">
                @if($user->donations->isEmpty())
                <div class="text-center">
                    <img src="{{ asset('assets/img/icon/success-data.svg') }}" alt="Not Found" class="mb-3" style="width: 150px; height: 150px;">
                    <p>Belum Memiliki Riwayat Donasi di Kampanye</p>
                </div>
            @else
                @foreach($user->donations as $donation)
                <div class="donation-card p-3 border rounded shadow-sm">
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold">{{$donation->name}}</span>
                        <span class="fw-bold text-end">Rp {{ number_format($donation->amount, 0, ',', '.') }} </span>
                    </div>
                    <small class="text-muted d-block">{{ $donation->created_at->diffForHumans() }}</small>
                </div>
                @endforeach
            @endif
              </div>

              <!-- Dukungan (Awalnya Tersembunyi) -->
             <!-- Dukungan (Tersembunyi Awalnya) -->
<div id="dukunganContent" class="donation-history mt-3 d-none">
    @if($user->donations->isEmpty() || $user->donations->every(fn($donation) => !$donation->campaign))
    <div class="text-center">
        <img src="{{ asset('assets/img/icon/success-data.svg') }}" alt="Not Found" class="mb-3" style="width: 150px; height: 150px;">
        <p>Belum Memiliki Riwayat Donasi di Kampanye</p>
    </div>
@else
    @foreach($user->donations as $donation)
        @if($donation->campaign) <!-- Pastikan kampanye terkait ada -->
            @include('includes.campaign-card', ['campaign' => $donation->campaign])
        @endif
    @endforeach
@endif
</div>

          </div>
         
          <!-- Footer -->
 
    @include('includes.public.menu')

@endsection

@push('after-script')
<script>
  document.addEventListener("DOMContentLoaded", function () {
      // Ambil elemen tombol dan konten yang terkait
      const btnRiwayat = document.getElementById("btnRiwayat");
      const btnDukungan = document.getElementById("btnDukungan");

      const donationHistory = document.getElementById("donationHistory");
      const dukunganContent = document.getElementById("dukunganContent");

      const buttons = [btnRiwayat, btnDukungan];
      const contents = [donationHistory, dukunganContent];

      function toggleContent(activeBtn, activeContent) {
          // Sembunyikan semua konten
          contents.forEach(content => content.classList.add("d-none"));

          // Nonaktifkan semua tombol (kembalikan ke merah)
          buttons.forEach(btn => btn.classList.remove("btn-active"));

          // Jika konten yang diklik sedang tersembunyi, tampilkan
          if (activeContent.classList.contains("d-none")) {
              activeContent.classList.remove("d-none");
              activeBtn.classList.add("btn-active"); // Tambahkan efek tombol aktif
          }
      }

      // Event listener untuk masing-masing tombol
      btnRiwayat.addEventListener("click", () => toggleContent(btnRiwayat, donationHistory));
      btnDukungan.addEventListener("click", () => toggleContent(btnDukungan, dukunganContent));

      // Simulasikan klik tombol "Riwayat" saat halaman pertama kali dimuat
      toggleContent(btnRiwayat, donationHistory);
  });
</script>
@endpush

