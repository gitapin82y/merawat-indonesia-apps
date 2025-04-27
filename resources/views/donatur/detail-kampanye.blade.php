@extends('layouts.public')
 
@section('title', 'Detail Kampanye')

@push('after-style')
<style>
      .avatar {
    width: 60px;
    height: 60px;
    border-radius: 50% !important;
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
        box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.1);
    }

    @media (max-width: 480px) {
        .footer {
            font-size: 12px !important;
        }

        .btn {
            font-size: 12px !important;
        }
    }
    .like-count{
        color:#FF4747;
    }
    .liked, .liked .like-count{
        color: white !important;
    }

    .liked {
    background-color: #f73333;
    color: white;
}
</style>
@endpush

@section('content')


    <div class="thumbnail-detail-kampanye">
        <div class="justify-content-between nav-top d-flex">
            <a href="{{ url()->current() == url()->previous() ? url('/') : url()->previous() }}" class="bg-white">
                <i class="fa-solid fa-angle-left"></i>
            </a>
            @if(Auth::check())
                <a href="#" class="save-campaign-btn bg-white" data-campaign-id="{{ $campaign->id }}">
                    @if(Auth::user()->savedCampaigns->contains($campaign))
                        <i class="fa-solid fa-bookmark save-icon"></i>
                    @else
                        <i class="fa-regular fa-bookmark save-icon"></i>
                    @endif
                </a>
            @else
            <a href="#" id="notSaved" class="bg-white">
                <i class="fa-regular fa-bookmark"></i>
            </a>
        @endif
        </div>
        <img src="{{ asset('storage/' . $campaign->photo) }}" class="image-campaign-card" alt="{{ $campaign->title }}">
    </div>

    <div class="container">

        <!-- Campaign Section -->
        <div class="row kampanye my-3">
            <div class="justify-content-between d-flex mb-2">
                <h2>{{ $campaign->title }}</h2>
            </div>

            <small>Donasi Terkumpul</small>

            <div class="col-12 row mx-0 align-items-end">
                <div class="col-9 p-0 d-flex">
                    <h2 class="text-color">
                        Rp {{ number_format($campaign->jumlah_donasi, 0, ',', '.') }} 
                        <span class="small">Kebutuhan</span> 
                        @if($campaign->jumlah_target_donasi)
                        <span class="fw-bold">Rp {{ number_format($campaign->jumlah_target_donasi, 0, ',', '.') }} </span> 
                        @else
                        <span class="small"><i class="fas fa-infinity text-danger"></i> Tanpa Target</span>
                        @endif
                    </h2>
                </div>
                <div class="col d-flex justify-content-end p-0">
                    <strong style="margin-top: -4px; margin-right:3px;">
                        @if($campaign->deadline)
                            @if($campaign->remainingDays < 0)
                                0
                            @elseif($campaign->remainingDays == 0)
                                {{ floor($campaign->remainingTime) }}
                            @else
                                {{ $campaign->remainingDays }}
                            @endif
                        @else
                            <i class="fas fa-infinity"></i>
                        @endif
                    </strong>
                    <small>
                        @if($campaign->deadline)
                            @if($campaign->remainingDays < 0)
                                Hari Lagi
                            @elseif($campaign->remainingDays == 0)
                                Jam Lagi
                            @else
                                Hari Lagi
                            @endif
                        @else
                            Tanpa Batas Waktu
                        @endif
                    </small>
                </div>
            </div>

            <div class="col-12 row mx-0">
                <div class="progress my-1 px-0">
                    <div class="progress-bar progress-bar-striped bg-danger" role="progressbar"
                    style="width: {{ min($campaign->progressPercentage, 100) }}%;" 
                    aria-valuenow="{{ min($campaign->progressPercentage, 100) }}" 
                    aria-valuemin="0" aria-valuemax="100">
                    {{ min($campaign->progressPercentage, 100) }}%
                    </div>
                </div>
            </div>

            @if($campaign->kabarPencairan->count() >= 1)
            <div class="col-12 row px-0 pt-2 mx-0">
                <small>Donasi saat ini <strong class="text-color">Rp {{ number_format($campaign->current_donation, 0, ',', '.') }}</strong> Dana yang sudah dicairkan <strong>Rp {{ number_format($campaign->jumlah_pencairan_dana, 0, ',', '.') }}</strong></small>
            </div>
            @endif

            <div class="col-12 row mx-0 my-3">
                <div class="col-3  text-center">
                    <div class="d-flex align-items-center justify-content-center">
                        <img src="{{asset('assets/img/icon/user-donatur.svg')}}" alt="" class="me-2" style="height: 20px;">
                        <p class="count m-0 d-flex align-items-center">{{ $campaign->donations->count() }}</p>
                    </div>

                    <small>Donatur</small>
                </div>
                <div class="col-3  text-center">
                    <div class="d-flex align-items-center justify-content-center">
                        <img src="{{asset('assets/img/icon/kabar-terbaru.svg')}}" alt="" class="me-2" style="height: 20px;">
                        <p class="count m-0 d-flex align-items-center">{{ $campaign->kabarTerbaru->count() }}</p>
                    </div>

                    <small>Kabar Terbaru</small>
                </div>
                <div class="col-3  text-center">
                    <div class="d-flex align-items-center justify-content-center">
                        <img src="{{asset('assets/img/icon/pencairan-dana.svg')}}" alt="" class="me-2" style="height: 20px;">
                        <p class="count m-0 d-flex align-items-center">{{ $campaign->kabarPencairan->count() }}</p>
                    </div>

                    <small>Pencairan Dana</small>
                </div>
                <div class="col-3  text-center">
                    <div class="d-flex align-items-center justify-content-center">
                        <img src="{{asset('assets/img/icon/doa-orang-baik.svg')}}" alt="" class="me-2" style="height: 20px;">
                        <p class="count m-0 d-flex align-items-center">{{ $campaign->donations->where('doa', '!=', null)->count() }}</p>
                    </div>
                    <small>Doa Sahabat Baik</small>
                </div>
            </div>

            <div class="row mx-0 col-12">
                <a href="{{ asset('storage/' . $campaign->document_rab) }}" target="_blank" class="button my-3 w-100 text-center"><i class="fa-solid fa-download"></i> &nbsp;
                    Unduh Laporan RAB</a>
            </div>

            <div class="line-spacing"></div>

            <div class="row penggalang-dana my-4 px-0 mx-0">
                <div class="justify-content-between d-flex pb-3">
                    <h2>Penggalang Dana</h2>
                    <a href="{{route('galangDanaProfile',$campaign->admin->name )}}">Detail Galang Dana</a>
                 </div>
               <div class="col-12 row px-0 mx-0">
                <div class="col-6 row mx-0 px-0">
                    <a href="{{route('galangDanaProfile',$campaign->admin->name )}}" class="col-4 col-lg-3 align-self-center">
                        <img src="{{ $campaign->admin->avatar_url}}" width="100%" class="avatar" alt="{{$campaign->admin->name}}">
                    </a>
                    <div class="col-8 col-lg-9 align-self-center">
                        <a href="{{route('galangDanaProfile',$campaign->admin->name )}}" class="bold">{{$campaign->admin->name}}</a>
                        <br>
                        <span class="badge rounded-pill bg-main-opacity text-color fw-normal">
                            <img src="{{asset('assets/img/icon/verify.svg')}}" alt=""> Terverifikasi</span>
                    </div>
                </div>
                <div class="col-6 row mx-0 px-0">
                    <div class="col-6 align-self-center text-center">
                    
                            <div class="d-flex align-items-center justify-content-center">
                                <img src="{{asset('assets/img/icon/total-donatur.svg')}}" alt="" class="me-2" style="height: 20px;">
                                <p class="count m-0">{{$totalDonaturs}}</p>
                              </div>
                              
                            <small>Donatur</small>
                    </div>
                    <div class="col-6 align-self-center text-center">
                        <div class="d-flex align-items-center justify-content-center">
                            <img src="{{asset('assets/img/icon/total-kampanye.svg')}}" alt="" class="me-2" style="height: 20px;">
                            <p class="count m-0">{{$totalKampanye}}</p>
                          </div>
                          
                        <small>Kampanye</small>
                </div>
                </div>
               </div>
            </div>
            <div class="line-spacing"></div>

            <!-- Buttons -->
            <div class="row info-kampanye my-4 mx-0">
                <div class="d-flex scroll-x gap-2 mb-3">
                    <button class="btn btn-outline-danger tab-button"
                        data-target="keterangan">Keterangan</button>
                    <button class="btn btn-outline-danger tab-button" data-target="kabar-terbaru">Kabar
                        Terbaru</button>
                    <button class="btn btn-outline-danger tab-button" data-target="donatur">Donatur</button>
                    <button class="btn btn-outline-danger tab-button" data-target="kabar-pencairan">Kabar
                        Pencairan</button>
                </div>

                <!-- Content Sections -->
                <div class="tab-content keterangan">
                    <div class="description-preview">
                        <p id="description-short">
                            {!! \Illuminate\Support\Str::limit(strip_tags($campaign->description), 300, '...') !!}
                        </p>
                        
                        @if(strlen(strip_tags($campaign->description)) > 300)
                            <button type="button" class="btn btn-outline-danger btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#descriptionModal">
                                <i class="fa-solid fa-book-open"></i> Lihat Lebih Lengkap
                            </button>
                        @endif
                    </div>
                </div>

<div class="tab-content kabar-terbaru d-none">
    <div class="accordion" id="penyaluranDanaAccordion">
        <div id="kabar-terbaru-container">
            @include('partials.kabar-terbaru')
        </div>
    </div>
    
    @if($kabarTerbaru->hasMorePages())
        <button id="load-more-kabar" data-next-page="{{ $kabarTerbaru->nextPageUrl() }}&load_tab=kabar-terbaru" class="btn btn-primary mt-3 w-100 load-more-btn" data-tab="kabar-terbaru">
            Lihat Lebih Banyak
        </button>
    @endif
</div>

<div class="tab-content donatur d-none">
    <div id="donatur-container">
        @include('partials.donatur')
    </div>
    
    @if($donations->hasMorePages())
        <button id="load-more-donatur" data-next-page="{{ $donations->nextPageUrl() }}&load_tab=donatur" class="btn btn-primary mt-3 w-100 load-more-btn" data-tab="donatur">
            Lihat Lebih Banyak
        </button>
    @endif
</div>

<div class="tab-content kabar-pencairan d-none">
    <div class="accordion" id="kabarPencairanAccordion">
        <div id="kabar-pencairan-container">
            @include('partials.kabar-pencairan')
        </div>
    </div>
    
    @if($kabarPencairan->hasMorePages())
        <button id="load-more-pencairan" data-next-page="{{ $kabarPencairan->nextPageUrl() }}&load_tab=kabar-pencairan" class="btn btn-primary mt-3 w-100 load-more-btn" data-tab="kabar-pencairan">
            Lihat Lebih Banyak
        </button>
    @endif
</div>
            </div>

        </div>
    </div>


    <div class="line-spacing"></div>

<div class="row col-12 mx-0 pb-md-2 pt-md-0 pb-5 pt-4">
    <div class="col-5 align-self-center">
        <img src="{{asset('assets/img/fundraishing.png')}}" width="100%" alt="">
    </div>
    <div class="col-7 align-self-center">
        <h2>Jadilah Fundraising Kampanye Ini</h2>
        <p class="pb-2">Sebarkan kebaikan melalui kampanye ini dan dapatkan komisi sebesar 10% per-transaksi donatur yang anda ajak.</p>
        @auth
            <button id="joinFundraising" data-slug="{{ $campaign->slug }}" class="button">Gabung Sekarang</button>
        @else
            <a href="{{ route('login') }}" class="button">Login untuk Gabung</a>
        @endauth
    </div>
</div>


    <div class="line-spacing"></div>
    
    <div class="row col-12 m-0 px-3 doa-orang-baik mt-3 pb-5">
        <h2 class="mx-0 px-0">Doa Sahabat Baik</h2>
        <div id="comments-container">
            @include('partials.comments', ['comments' => $comments, 'guestIdentifier' => $guestIdentifier])
        </div>
    
        @if($comments->hasMorePages())
            <button id="load-more" data-next-page="{{ $comments->nextPageUrl() }}" class="btn btn-primary mt-3 mb-5 w-100 load-more-btn" data-tab="comments">
                Lihat Lebih Banyak
            </button>
        @endif
    </div>
        
    <div class="footer">
        <div class="main-menu row col-12 mx-0 justify-content-between d-flex">
            <a href="#" class="col-2 me-2" style="border-radius: 6px;border:1px solid #FF4747; padding:10px;" data-bs-toggle="modal" data-bs-target="#bagikanModal">
                <i class="fa-solid fa-share"></i>
            </a>
                <a href="{{url('kampanye/'.$campaign->slug.'/donasi')}}" class="button col-10"><i class="fa-solid fa-hand-holding-heart"></i> Donasi</a>
        </div>
    </div>

    <div class="modal fade" id="descriptionModal" tabindex="-1" aria-labelledby="descriptionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="descriptionModalLabel">{{ $campaign->title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {!! $campaign->description !!}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Bagikan -->
<div class="modal fade" id="bagikanModal" tabindex="-1" aria-labelledby="bagikanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content p-3">
        <div class="modal-header border-0">
          <h5 class="modal-title" id="bagikanModalLabel">Bagikan melalui:</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body text-center">
          @php
              $url = urlencode(Request::fullUrl());
              $text = urlencode("Yuk, dukung kampanye ini:");
          @endphp
          <div class="d-flex justify-content-around">
            <a href="https://wa.me/?text={{ $text }}%20{{ $url }}" target="_blank" class="p-2 btn btn-success">
                <i class="fab fa-whatsapp"></i> WhatsApp
            </a>
            <a href="https://www.facebook.com/sharer/sharer.php?u={{ $url }}" target="_blank" class="p-2 btn" style="background-color:rgb(0, 68, 255);color:white;">
                <i class="fab fa-facebook"></i> Facebook
            </a>
            <a href="https://twitter.com/intent/tweet?text={{ $text }}%20{{ $url }}" target="_blank" class="p-2 btn btn-info text-white">
                <i class="fab fa-twitter"></i> Twitter
            </a>
            <a href="https://t.me/share/url?url={{ $url }}&text={{ $text }}" target="_blank" class="p-2 btn btn-secondary">
                <i class="fab fa-telegram"></i> Telegram
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

@endsection

@push('after-script')
<script>
    $(document).ready(function() {
        $('.like-button').on('click', function(e) {
        e.preventDefault();

        var donationId = $(this).data('donation-id');
        var button = $(this);
        
        $.ajax({
            url: '/donation/' + donationId + '/like',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
            },
            xhrFields: {
                withCredentials: true // Important for cookies
            },
            success: function(response) {
                if (response.status === 'liked') {
                    console.log(response.count);
                    // Update button to un-like and increase like count
                    button.find('.like-count').text(response.count);
                    button.addClass('liked');
                } else {
                    console.log(response.count);
                    // Update button to like and decrease like count
                    button.find('.like-count').text(response.count);
                    button.removeClass('liked');
                }
            },
            error: function(xhr, status, error) {
                console.log('Error: ' + error);
            }
        });
    });
});


     // Keep the existing script for tab switching and CKEditor
     document.addEventListener('DOMContentLoaded', function() {
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');

        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const target = this.getAttribute('data-target');

                // Remove active class from all buttons
                tabButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');

                // Hide all tab contents
                tabContents.forEach(content => content.classList.add('d-none'));

                // Show target tab content
                document.querySelector(`.tab-content.${target}`).classList.remove('d-none');
            });
        });
    });

  document.querySelectorAll(".accordion-button").forEach((button) => {
      button.addEventListener("click", () => {
          const icon = button.querySelector("i");
          icon.classList.toggle("fa-chevron-up");
          icon.classList.toggle("fa-chevron-down");
      });
  });

  $(document).ready(function() {
    $('.save-campaign-btn').on('click', function(e) {
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
                    icon.removeClass('fa-regular').addClass('fa-solid');
                } else {
                    icon.removeClass('fa-solid').addClass('fa-regular');
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

    $('#notSaved').on('click', function(e) {
        Swal.fire({
            icon: 'error',
            text: 'Untuk Menyimpan, Harap Login',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
    });
});


$(document).ready(function() {
        $('#joinFundraising').click(function() {
            const slug = $(this).data('slug');
            
            $.ajax({
                url: `/kampanye/${slug}/join-fundraising`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonText: 'Lihat Halaman Fundraising'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = response.redirect;
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Perhatian!',
                            text: response.message,
                            icon: 'info',
                            confirmButtonText: 'Lihat Halaman Fundraising'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = response.redirect;
                            }
                        });
                    }
                },
                error: function(error) {
                    console.log(error);
                    Swal.fire({
                        title: 'Terjadi Kesalahan',
                        text: 'Gagal bergabung sebagai fundraiser, silahkan coba lagi.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });
    });
</script>

<script>
   $(document).ready(function() {
    // Handle pagination for all tabs
    $('.load-more-btn').click(function() {
        var button = $(this);
        var nextPageUrl = button.data('next-page');
        var tab = button.data('tab');
        var container;
        
        // Determine which container to update based on the tab
        switch(tab) {
            case 'kabar-terbaru':
                container = $('#kabar-terbaru-container');
                break;
            case 'donatur':
                container = $('#donatur-container');
                break;
            case 'kabar-pencairan':
                container = $('#kabar-pencairan-container');
                break;
            case 'comments':
                container = $('#comments-container');
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
                
                // Re-attach event listeners for accordion buttons
                attachAccordionEvents();
            },
            error: function() {
                button.html('Lihat Lebih Banyak');
                button.prop('disabled', false);
                alert('Terjadi kesalahan, silakan coba lagi.');
            }
        });
    });
    
    // Update existing load-more button for comments to use the same system
    $('#load-more').addClass('load-more-btn').data('tab', 'comments');
    
    // Function to re-attach accordion event listeners
    function attachAccordionEvents() {
        document.querySelectorAll(".accordion-button").forEach((button) => {
            button.addEventListener("click", () => {
                const icon = button.querySelector("i");
                icon.classList.toggle("fa-chevron-up");
                icon.classList.toggle("fa-chevron-down");
            });
        });
    }

});

// Update comments pagination to match the rest of the tabs
$(document).ready(function() {
    // Modify existing comments load-more button behavior
    $('#load-more').off('click').on('click', function() {
        var button = $(this);
        var nextPageUrl = button.data('next-page') + '&load_tab=comments';
        var container = $('#comments-container');
        
        // Show loading indicator
        button.html('<i class="fa fa-spinner fa-spin"></i> Memuat...');
        button.prop('disabled', true);
        
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
                
                // Re-attach event listeners for like buttons if needed
                attachLikeEvents();
            },
            error: function() {
                button.html('Lihat Lebih Banyak');
                button.prop('disabled', false);
                alert('Terjadi kesalahan, silakan coba lagi.');
            }
        });
    });
    
    // Function to re-attach like button event listeners to newly loaded comments
    function attachLikeEvents() {
        $('.like-button').off('click').on('click', function(e) {
            e.preventDefault();
            
            var donationId = $(this).data('donation-id');
            var button = $(this);
            
            $.ajax({
                url: '/donation/' + donationId + '/like',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                },
                xhrFields: {
                    withCredentials: true
                },
                success: function(response) {
                    if (response.status === 'liked') {
                        button.find('.like-count').text(response.count);
                        button.addClass('liked');
                    } else {
                        button.find('.like-count').text(response.count);
                        button.removeClass('liked');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Error: ' + error);
                }
            });
        });
    }
});
</script>
@endpush

