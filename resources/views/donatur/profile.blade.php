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

            <!-- Profile & Banner Section -->
     <!-- Profile & Banner Section -->
     <div class="profile-header position-relative mt-4 px-4">
        <img
         src="{{ $user->thumbnail_url}}"
          alt="Banner"
          class="w-100" style="border-radius: 10px;max-height:400px;"
        />
        <div class="profile-avatar text-center">
          <div class="avatar-container position-relative d-inline-block">
            <img
              src="{{ $user->avatar_url}}"
              alt="William Saliba"
              class="rounded-circle position-absolute start-50 translate-middle-x"
            />
          </div>
        </div>
      </div>

            <div class="content-box text-center">
              <p class="mb-2 text-center mt-4 fs-5 fw-bold text-second">{{$user->name}}</p>
              {{-- <div class="d-flex mb-3 justify-content-center">
                  <label class="form-check-label ms-0 text-second mt-2" for="flexSwitchCheckChecked">Tampilkan Sebagai
                      anonim “Orang Baik”?</label>
                  <div class="form-check form-switch mx-2 mt-2">
                      <input class="form-check-input" type="checkbox" id="flexSwitchCheckChecked" checked>
                  </div>
              </div> --}}
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
              <div class="d-flex justify-content-center gap-2 mt-3 flex-nowrap w-100">
                  <button class="btn btn-danger btn-responsive"onclick="window.location.href='{{asset('notifikasi')}}'">
                      <img src="{{asset('assets/img/icon/notification-1.svg')}}" alt="Notifikasi"> Notifikasi({{ Auth::user()->notifications()->unread()->count() }})
                  </button>
                  <button class="btn btn-danger btn-responsive" onclick="window.location.href='{{url('/edit-profile')}}'">
                      <img src="{{asset('assets/img/icon/edit-profile.svg')}}" alt="Edit Profil"> Edit Profil
                  </button>
                  <button class="btn btn-danger btn-responsive" onclick="window.location.href='{{url('/profile/fundraising')}}'">
                      <img src="{{asset('assets/img/icon/fundraising.svg')}}" alt="Fundraising"> Fundraising
                  </button>
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
          <div class="content-box">
              <div class="d-flex justify-content-center gap-2 flex-nowrap w-100">
                  <button class="btn btn-danger btn-responsive" id="btnRiwayat">Riwayat Donasi</button>
                  <button class="btn btn-danger btn-responsive" id="btnTersimpan">Tersimpan</button>
                  <button class="btn btn-danger btn-responsive" id="btnDukungan">Dukungan</button>
              </div>

              <!-- Riwayat Donasi (Awalnya Tersembunyi) -->
              <div id="donationHistory" class="donation-history mt-3 d-none">
                <div id="donations-container">
                    @include('partials.profile.donations', ['donations' => $donations])
                </div>
                
                @if($donations->hasMorePages())
                    <button id="load-more-donations" data-next-page="{{ $donations->nextPageUrl() }}&tab=donations" 
                        class="btn btn-outline-danger w-100 mt-3 load-more-btn" data-tab="donations">
                        Lihat Lebih Banyak
                    </button>
                @endif
            </div>

                <!-- Tersimpan (Awalnya Tersembunyi) -->
    <div id="tersimpanContent" class="donation-history mt-3 d-none">
        <div id="saved-campaigns-container">
            @include('partials.profile.saved-campaigns', ['savedCampaigns' => $savedCampaigns])
        </div>
        
        @if($savedCampaigns->hasMorePages())
            <button id="load-more-saved" data-next-page="{{ $savedCampaigns->nextPageUrl() }}&tab=saved" 
                class="btn btn-outline-danger w-100 mt-3 load-more-btn" data-tab="saved">
                Lihat Lebih Banyak
            </button>
        @endif
    </div>

    <div id="dukunganContent" class="donation-history mt-3 d-none">
        <div id="supported-campaigns-container">
            @include('partials.profile.supported-campaigns', ['supportedCampaigns' => $supportedCampaigns])
        </div>
        
        @if($supportedCampaigns->hasMorePages())
            <button id="load-more-supported" data-next-page="{{ $supportedCampaigns->nextPageUrl() }}&tab=supported" 
                class="btn btn-outline-danger w-100 mt-3 load-more-btn" data-tab="supported">
                Lihat Lebih Banyak
            </button>
        @endif
    </div>

          </div>
          <div class="line-spacing"></div>
          <div class="logout-box content-box text-center mt-3 mb-5" style="padding-bottom: 300px;">
              <div class="main-menu row col-12 mx-0 justify-content-between d-flex ">
                  <a href="{{route('logout')}}"
                      class="button w-100 d-flex align-items-center justify-content-center text-white shadow-sm">
                      <img src="{{asset('assets/img/icon/edit-profile.svg')}}" alt="Kirim"
                          style="width: 20px; height: 20px; margin-right: 8px;" />
                      <span class="text-white">Keluar</span>
                  </a>
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
      const btnTersimpan = document.getElementById("btnTersimpan");
      const btnDukungan = document.getElementById("btnDukungan");

      const donationHistory = document.getElementById("donationHistory");
      const tersimpanContent = document.getElementById("tersimpanContent");
      const dukunganContent = document.getElementById("dukunganContent");

      const buttons = [btnRiwayat, btnTersimpan, btnDukungan];
      const contents = [donationHistory, tersimpanContent, dukunganContent];

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
      btnTersimpan.addEventListener("click", () => toggleContent(btnTersimpan, tersimpanContent));
      btnDukungan.addEventListener("click", () => toggleContent(btnDukungan, dukunganContent));

      // Simulasikan klik tombol "Riwayat" saat halaman pertama kali dimuat
      toggleContent(btnRiwayat, donationHistory);
      
      // Handle pagination for all tabs
      $('.load-more-btn').click(function() {
          var button = $(this);
          var nextPageUrl = button.data('next-page');
          var tab = button.data('tab');
          var container;
          
          // Determine which container to update based on the tab
          switch(tab) {
              case 'donations':
                  container = $('#donations-container');
                  break;
              case 'saved':
                  container = $('#saved-campaigns-container');
                  break;
              case 'supported':
                  container = $('#supported-campaigns-container');
                  break;
          }
          
          // Show loading indicator
          button.html('<i class="fa fa-spinner fa-spin"></i> Memuat...');
          button.prop('disabled', true);
          
          // Perform AJAX request
          $.ajax({
              url: nextPageUrl,
              type: 'GET',
              success: function(response) {
                  // Append new content
                  container.append(response.html);
                  
                  // Update or hide the load more button
                  if (!response.hasMorePages) {
                      button.hide();
                  } else {
                      button.data('next-page', response.nextPageUrl);
                      button.html('Lihat Lebih Banyak');
                      button.prop('disabled', false);
                  }
                  
                  // Re-attach event listeners for save buttons if needed
                  attachSaveButtonEvents();
              },
              error: function() {
                  button.html('Lihat Lebih Banyak');
                  button.prop('disabled', false);
                  alert('Terjadi kesalahan, silakan coba lagi.');
              }
          });
      });
      
      // Function to attach events to campaign save buttons
      function attachSaveButtonEvents() {
          $('.save-campaign-btn').off('click').on('click', function(e) {
              e.preventDefault();
              
              var btn = $(this);
              var campaignId = btn.data('campaign-id');
              var icon = btn.find('.save-icon');
              
              $.ajax({
                  url: '{{ route("campaign.toggle-save") }}',
                  method: 'POST',
                  data: {
                      campaign_id: campaignId,
                      _token: '{{ csrf_token() }}'
                  },
                  success: function(response) {
                      if (response.status === 'saved') {
                          Swal.fire({
                              icon: 'success',
                              text: 'Kampanye Berhasil Disimpan di Profile Anda',
                              toast: true,
                              position: 'top-end',
                              showConfirmButton: false,
                              timer: 3000
                          });
                          icon.removeClass('fa-regular').addClass('fa-solid').addClass('text-danger');
                      } else {
                          icon.removeClass('fa-solid').addClass('fa-regular').removeClass('text-danger');
                          
                          // If we're on the saved tab and unsaving, consider removing the item
                          if (tersimpanContent.classList.contains('d-none') === false) {
                              btn.closest('.campaign-card').fadeOut(300, function() {
                                  $(this).remove();
                                  
                                  // If no more campaigns, show empty state
                                  if ($('#saved-campaigns-container .campaign-card').length === 0) {
                                      $('#saved-campaigns-container').html(`
                                          <div class="text-center">
                                              <img src="{{ asset('assets/img/icon/success-data.svg') }}" alt="Not Found" class="mb-3" style="width: 150px; height: 150px;">
                                              <p>Belum ada Kampanye yang Tersimpan</p>
                                          </div>
                                      `);
                                  }
                              });
                          }
                      }
                  },
                  error: function(xhr) {
                      if (xhr.status === 401) {
                          window.location.href = '{{ route("login") }}';
                      } else {
                          Swal.fire({
                              icon: 'error',
                              text: 'Terjadi kesalahan. Silakan coba lagi.',
                              toast: true,
                              position: 'top-end',
                              showConfirmButton: false,
                              timer: 3000
                          });
                      }
                  }
              });
          });
      }
      
      // Initialize save button events
      attachSaveButtonEvents();
  });
</script>

@endpush

