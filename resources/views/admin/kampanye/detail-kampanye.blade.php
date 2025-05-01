@extends('layouts.public')
 
@section('title', 'Detail Kampanye')

@push('after-style')
<style>
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
        .section-deadline{
            padding-bottom: 10px;
        }
        .position-deadline{
        margin-right: 3px;
        }
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
            <a href="{{ url('/galang-dana') }}" class="bg-white">
                <i class="fa-solid fa-angle-left"></i>
            </a>
        </div>
        <img src="{{ asset('storage/' . $campaign->photo) }}" class="image-campaign-card" alt="{{ $campaign->title }}">
    </div>

    <div class="container">

        <!-- Campaign Section -->
        <div class="row kampanye my-3">
            <div class="justify-content-between d-flex mb-2">
                <h2>{{ $campaign->title }}</h2>
            </div>

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
                <div class="col d-flex justify-content-end p-0 section-deadline">
                    <strong class="position-deadline">
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
                            @if($campaign->remainingDays <= 0)
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
                        <p class="count m-0 d-flex align-items-center">{{ $campaign->donations->where('status', 'sukses')->count() }}</p>
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
                        <p class="count m-0 d-flex align-items-center">{{ $campaign->kabarPencairan->where('status','disetujui')->count() }}</p>
                    </div>

                    <small>Pencairan Dana</small>
                </div>
                <div class="col-3  text-center">
                    <div class="d-flex align-items-center justify-content-center">
                        <img src="{{asset('assets/img/icon/doa-orang-baik.svg')}}" alt="" class="me-2" style="height: 20px;">
                        <p class="count m-0 d-flex align-items-center">{{ $campaign->donations->where('status', 'sukses')->where('doa', '!=', null)->count() }}</p>
                    </div>
                    <small>Doa Sahabat Baik</small>
                </div>
            </div>

            <div class="row mx-0 col-12">
                <a href="{{ asset('storage/' . $campaign->document_rab) }}" target="_blank" class="button my-3 w-100 text-center"><i class="fa-solid fa-download"></i> &nbsp;
                    Unduh Laporan RAB</a>
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
        <div class="container my-3">
            <div class="p-3  rounded">
                <p class="fw-bold text-dark mb-2">Update Informasi Kampanye ?</p>
                <div class="d-flex gap-2">
                    <a href="{{ url('admin/kampanye/'.$campaign->slug.'/edit-kampanye') }}" class="btn btn-second flex-fill text-white">Edit Kampanye</a>
                    <a href="{{url('admin/kampanye/'.$campaign->slug.'/kabar-terbaru')}}" class="btn btn-second flex-fill text-white">Kabar Terbaru</a>
                    <a href="{{url('admin/kampanye/'.$campaign->slug.'/kabar-pencairan')}}" class="btn btn-second flex-fill text-white">Pencairan Dana</a>
                </div>
            </div>
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




@endsection

@push('after-script')
<script>
    @if(session('success'))
    Swal.fire({
      icon: 'success',
      title: 'Berhasil!',
      text: "{{ session('success') }}",
      timer: 3000
    });
    @endif

    @if(session('error'))
    Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: "{{ session('error') }}",
      timer: 3000
    });
    @endif

    $(document).ready(function() {
    // Menangani klik tombol like/unlike
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
            success: function(response) {
                if (response.status === 'liked') {
                    console.log(response.count);
                    // Update tombol menjadi un-like dan tambah like count
                    button.find('.like-count').text(response.count);
                    button.addClass('liked');
                } else {
                    console.log(response.count);
                    // Update tombol menjadi like dan kurangi like count
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

