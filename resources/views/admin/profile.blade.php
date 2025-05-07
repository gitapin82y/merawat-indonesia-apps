@extends('layouts.public')
 
@section('title', 'Profile Galang Dana')

@push('after-style')
<style>
      /* Profile Avatar */
      .profile-avatar .avatar-container img {
        width: 150px;
        height: 150px;
        bottom: -45px;
        border: 6px solid white;
      }
    .btn-danger {
        background-color: #FF4747;
    }

    body {
        font-family: 'Poppins', sans-serif;
        background-color: #fef7f7;
        margin: 0;
        /* Hapus margin body */
        padding: 0;
        
        /* Hapus padding body */
    }


    .content-box {
        background: white;

        padding: 15px;

        width: 100%;



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
        height: 40px;
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

        min-width: 0;

    }

    .btn-responsive img {
        width: 15px;

        height: 15px;
        margin-right: 8px;
    }

    @media (max-width: 768px) {
        .btn-responsive {
            font-size: 12px;

            padding: 6px 10px;
        }

        .profile-avatar .avatar-container img {
                width: 120px;
                height: 120px;
      }

        .btn-responsive img {
            width: 12px;

            height: 12px;
            margin-right: 6px;
        }

        .profile-img {
            width: 120px;
            height: 120px;
            margin-top: -60px;
        }
    }

    .gradient-bg {
        background: linear-gradient(90deg, #BD2727 0%, #FF4747 100%);
        color: white;
    }

    .logout-box {
        margin-top: auto;
        padding-bottom: 20px;

    }

    .btn-active {
        background-color: #fff !important;
        color: #FF4747 !important;
        border: 2px solid #FF4747 !important;
        font-weight: bold !important;
    }

    .status-overlay small {
        color: #FF4747
    }

    .text-second {
        color: #FF4747
    }
</style>
@endpush

@section('content')

       <!-- Profile & Banner Section -->
       <div class="profile-header position-relative mt-4 px-4">
        <img
         src="{{ $admin->thumbnail_url }}"
          alt="Banner"
          class="w-100" style="border-radius: 10px;max-height:400px;"
        />
        <div class="profile-avatar text-center">
          <div class="avatar-container position-relative d-inline-block">
            <img
              src="{{ $admin->avatar_url }}"
              alt="William Saliba"
              class="rounded-circle position-absolute start-50 translate-middle-x"
            />
          </div>
        </div>
      </div>

      
       <div class="content-box text-center">

        
     

        <div class="d-flex flex-column align-items-center">
            <p class="mb-2 text-center mt-4 fs-5 fw-bold text-second"> {{$admin->name}} </p>
            <div class="d-flex align-items-center bg-opacity rounded-pill px-3 py-1">
                <img src="{{asset('assets/img/icon/verify.svg')}}" alt="Akun Terverifikasi" class="me-2"
                    style="width: 16px; height: 16px;">
                <small class="text-second">Akun Terverifikasi</small>
            </div>
        </div>

        <p class="text-muted mx-3 mt-2 mb-1">
            {{$admin->bio ?? 'Bio belum tersedia'}}
        </p>
        <div class="d-flex justify-content-center gap-3 mt-3">
            @if(isset($admin->social['instagram']))
            <div class="social-icon d-flex align-items-center justify-content-center">
                <a href="{{ $admin->social['instagram'] }}" target="_blank">
                    <img src="{{asset('assets/img/icon/instagram.svg')}}" alt="Instagram" class="img-fluid">
                </a>
            </div>
            @endif
    
            @if(isset($admin->social['youtube']))
            <div class="social-icon d-flex align-items-center justify-content-center">
                <a href="{{ $admin->social['youtube'] }}" target="_blank">
                    <img src="{{asset('assets/img/icon/youtube.svg')}}" alt="YouTube" class="img-fluid">
                </a>
            </div>
            @endif
    
            @if(isset($admin->social['facebook']))
            <div class="social-icon d-flex align-items-center justify-content-center">
                <a href="{{ $admin->social['facebook'] }}" target="_blank">
                    <img src="{{asset('assets/img/icon/facebook.svg')}}" alt="Facebook" class="img-fluid">
                </a>
            </div>
            @endif
    
            @if(isset($admin->social['tiktok']))
            <div class="social-icon d-flex align-items-center justify-content-center">
                <a href="{{ $admin->social['tiktok'] }}" target="_blank">
                    <img src="{{asset('assets/img/icon/tiktok.svg')}}" alt="TikTok" class="img-fluid">
                </a>
            </div>
            @endif
        </div>
        <div class="d-flex justify-content-center gap-2 mt-3 flex-nowrap w-100">
            <button class="btn btn-danger btn-responsive" onclick="window.location.href='{{asset('notifikasi')}}'">
                <img src="{{asset('assets/img/icon/notification-1.svg')}}" alt="Notifikasi"> Notifikasi({{ Auth::user()->notifications()->unread()->count() }})
            </button>
            <button class="btn btn-danger btn-responsive" onclick="window.location.href='{{url('admin/edit-profile')}}'">
                <img src="{{asset('assets/img/icon/edit-profile.svg')}}" alt="Edit Profil"> Edit Profil
            </button>

        </div>
    </div>
    <div class="line-spacing"></div>
    <!-- Kotak Total Transaksi & Statistik -->
    <div class="content-box text-center justify-content-center">
        <div class="row">
            <!-- Bagian atas: Informasi jumlah donatur, kampanye, kabar terbaru, doa sahabat baik -->
            <div class="col-12 row mx-0 my-3">
                <div class="col-3  text-center">
                    <div class="d-flex align-items-center justify-content-center">
                        <img src="{{asset('assets/img/icon/user-donatur.svg')}}" alt="" class="me-2" style="height: 20px;">
                        <p class="count m-0 d-flex align-items-center">{{ $totalDonaturs }}</p>
                    </div>

                    <small>Donatur</small>
                </div>
                <div class="col-3  text-center">
                    <div class="d-flex align-items-center justify-content-center">
                        <img src="{{asset('assets/img/icon/total-kampanye.svg')}}" alt="" class="me-2" style="height: 20px;">
                        <p class="count m-0 d-flex align-items-center">{{ $totalKampanye }}</p>
                    </div>

                    <small>Total Kampanye</small>
                </div>
                <div class="col-3  text-center">
                    <div class="d-flex align-items-center justify-content-center">
                        <img src="{{asset('assets/img/icon/kabar-terbaru.svg')}}" alt="" class="me-2" style="height: 20px;">
                        <p class="count m-0 d-flex align-items-center">{{ $totalKabarTerbaru }}</p>
                    </div>

                    <small>Kabar Terbaru</small>
                </div>

                <div class="col-3  text-center">
                    <div class="d-flex align-items-center justify-content-center">
                        <img src="{{asset('assets/img/icon/doa-orang-baik.svg')}}" alt="" class="me-2" style="height: 20px;">
                        <p class="count m-0 d-flex align-items-center">{{ $totalDoa }}</p>
                    </div>
                    <small>Doa Sahabat Baik</small>
                </div>
            </div>
            <!-- Bagian bawah: Total Donasi Terkumpul & Total Pencairan Dana -->
            <div class="row mt-3 mx-auto d-flex justify-content-center">
                <div class="col-6 d-flex justify-content-center">
                    <div class="gradient-bg rounded p-3 w-100 shadow-sm text-center">
                        <div class="d-flex align-items-center justify-content-center">
                            <img src="{{asset('assets/img/icon/total-donasi.svg')}}" alt="Total Donasi Terkumpul"
                                class="img-fluid me-2" style="max-width: 30px;">
                            <div class="d-flex flex-column text-white text-start">
                                <p class="fw-bold mb-0 text-white">{{ 'Rp ' . number_format($totalDonasiTerkumpul, 0, ',', '.') }}</p>
                                <p class="small mb-0 text-white">Total Donasi Terkumpul</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 d-flex justify-content-center">
                    <div class="gradient-bg rounded p-3 w-100 shadow-sm text-center">
                        <div class="d-flex align-items-center justify-content-center">
                            <img src="{{asset('assets/img/icon/dompet.svg')}}" alt="Total Pencairan Dana"
                                class="img-fluid me-2" style="max-width: 30px;">
                            <div class="d-flex flex-column text-white text-start">
                                <p class="fw-bold mb-0 text-white">{{ 'Rp ' . number_format($totalPencairanDanaRupiah, 0, ',', '.') }}</p>
                                <p class="small mb-0 text-white">Total Pencairan Dana</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <div class="line-spacing"></div>

    <!-- Kotak Riwayat Donasi, Tersimpan, dan Dukungan -->
    <div class="content-box" style="padding-bottom: 500px;">

        <div
            class="gradient-bg rounded p-4  text-white text-center shadow-sm d-flex align-items-center justify-content-between">
            <div class="text-start">
                <h6 class="mb-3 fw-bold fs-7 text-white text-wrap">Tampilkan Kampanye Galang Dana di Halaman
                    Utama Merawat
                    Indonesia</h6>
                <a href="https://wa.me/088805011740" class="btn btn-light text-danger fw-bold px-4 py-1 rounded-pill"
                    style="font-size: 14px;">Hubungi
                    Kami</a>
            </div>
            <img src="{{asset('assets/img/icon/grafik.svg')}}" alt="Grafik Galang Dana" class="img-fluid ms-3"
                style="max-height: 100px;">
        </div>



        <!-- Tombol Buat Kampanye -->
        <div class="text-center mt-3">
            <button class="btn btn-danger w-100 py-2 fw-bold" onclick="window.location.href='{{url('admin/ajukan/buat-kampanye')}}'">
                <img src="{{asset('assets/img/icon/buat-kampanye.svg')}}" alt="buat-kampanye" style="margin-right: 2px;">
                Buat Kampanye
            </button>
        </div>

        <!-- Judul untuk tombol filter -->
        <h6 class="fw-bold mt-4 ">Kelola Kampanye Galang Dana</h6>

        <div class="container-fluid px-0">
            <div class="d-flex scroll-x gap-2 mt-3 w-100">
                <button class="btn btn-danger text-nowrap px-3 text-center btn-filter"
                    data-target="contentSemua" id="btnSemua">Semua</button>
                <button class="btn btn-danger text-nowrap px-3 text-center btn-filter"
                    data-target="contentAktif" id="btnAktif">Aktif</button>
                <button class="btn btn-danger text-nowrap px-3 text-center btn-filter"
                    data-target="contentBerakhir" id="btnBerakhir">Berakhir</button>
                <button class="btn btn-danger text-nowrap px-3 text-center btn-filter"
                    data-target="contentValidasi" id="btnValidasi">Dalam Validasi</button>
                <button class="btn btn-danger text-nowrap px-3 text-center btn-filter"
                    data-target="contentDitolak" id="btnDitolak">Ditolak</button>
            </div>
        </div>

        <!-- Konten yang akan ditampilkan berdasarkan tombol -->
        <div id="contentSemua" class="donation-history mt-3 d-none">
            @if($campaignsByStatus['semua']->count() == 0)
            <div class="text-center">
                <img src="{{ asset('assets/img/icon/success-data.svg') }}" alt="Not Found" class="mb-3" style="width: 150px; height: 150px;">
                <p>Kampanye Tidak Ditemukan</p>
            </div>
        @else
            @foreach($campaignsByStatus['semua'] as $campaign)
                @include('includes.manage-campaign-card', ['campaign' => $campaign])
            @endforeach
        @endif

        </div>

        <div id="contentAktif" class="donation-history mt-3 d-none">
            @if($campaignsByStatus['aktif']->count() == 0)
            <div class="text-center">
                <img src="{{ asset('assets/img/icon/success-data.svg') }}" alt="Not Found" class="mb-3" style="width: 150px; height: 150px;">
                <p>Kampanye Tidak Ditemukan</p>
            </div>
        @else
            @foreach($campaignsByStatus['aktif'] as $campaign)
                @include('includes.manage-campaign-card', ['campaign' => $campaign])
            @endforeach
        @endif

        </div>

        <div id="contentBerakhir" class="donation-history mt-3 d-none">
            @if($campaignsByStatus['berakhir']->count() == 0)
            <div class="text-center">
                <img src="{{ asset('assets/img/icon/success-data.svg') }}" alt="Not Found" class="mb-3" style="width: 150px; height: 150px;">
                <p>Kampanye Tidak Ditemukan</p>
            </div>
        @else
            @foreach($campaignsByStatus['berakhir'] as $campaign)
                @include('includes.manage-campaign-card', ['campaign' => $campaign])
            @endforeach
        @endif
        </div>

        <div id="contentValidasi" class="donation-history mt-3 d-none">
            @if($campaignsByStatus['validasi']->count() == 0)
            <div class="text-center">
                <img src="{{ asset('assets/img/icon/success-data.svg') }}" alt="Not Found" class="mb-3" style="width: 150px; height: 150px;">
                <p>Kampanye Tidak Ditemukan</p>
            </div>
        @else
            @foreach($campaignsByStatus['validasi'] as $campaign)
                @include('includes.manage-campaign-card', ['campaign' => $campaign])
            @endforeach
        @endif
        </div>

        <div id="contentDitolak"
            class="donation-history mt-3 d-none d-flex justify-content-center align-items-center"
            style="height: 200px;">
            @if($campaignsByStatus['ditolak']->count() == 0)
            <div class="text-center">
                <img src="{{ asset('assets/img/icon/success-data.svg') }}" alt="Not Found" class="mb-3" style="width: 150px; height: 150px;">
                <p>Kampanye Tidak Ditemukan</p>
            </div>
        @else
            @foreach($campaignsByStatus['ditolak'] as $campaign)
                @include('includes.manage-campaign-card', ['campaign' => $campaign])
            @endforeach
        @endif
        </div>

    </div>
 
    @include('includes.public.menu')

@endsection

@push('after-script')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Ambil semua tombol dan konten terkait
        const buttons = document.querySelectorAll(".btn-filter");
        const contents = document.querySelectorAll(".donation-history");

        function toggleContent(activeBtn) {
            // Sembunyikan semua konten
            contents.forEach(content => content.classList.add("d-none"));

            // Nonaktifkan semua tombol (hapus class aktif)
            buttons.forEach(btn => btn.classList.remove("btn-active"));

            // Ambil target dari tombol yang diklik
            const targetId = activeBtn.getAttribute("data-target");
            const targetContent = document.getElementById(targetId);

            // Tampilkan konten yang sesuai dan aktifkan tombolnya
            if (targetContent) targetContent.classList.remove("d-none");
            activeBtn.classList.add("btn-active");
        }

        // Tambahkan event listener ke setiap tombol
        buttons.forEach(button => {
            button.addEventListener("click", function () {
                toggleContent(this);
            });
        });

        // Simulasikan klik tombol "Semua" saat pertama kali halaman dimuat
        const defaultButton = document.getElementById("btnSemua");
        toggleContent(defaultButton);
    });
</script>
@endpush

