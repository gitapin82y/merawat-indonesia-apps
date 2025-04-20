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
            <a href="{{url('galang-dana')}}" class="bg-white">
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
                        <span class="fw-bold">Rp {{ number_format($campaign->jumlah_target_donasi, 0, ',', '.') }}</span> 
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
                    <small>Doa Orang Baik</small>
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
                    <p>{!! $campaign->description !!}</p>
                </div>

                <div class="tab-content kabar-terbaru d-none">
                    <div class="accordion" id="penyaluranDanaAccordion">
                        @forelse($campaign->kabarTerbaru as $index => $kabar)
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed d-flex justify-content-between align-items-center"
                                    type="button" data-bs-toggle="collapse" data-bs-target="#collapseKabarTerbaru{{ $index }}">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="circle-number">{{ $index + 1 }}</div>
                                        <div>
                                            <div class="text-muted small mb-1">{{ $kabar->created_at->format('d F Y') }}</div>
                                            <div class="fw-bold text-danger">{{ $kabar->title }}</div>
                                        </div>
                                    </div>
                                    <i class="fa-solid fa-chevron-down ms-auto circle-dropdown"></i>
                                </button>
                            </h2>
                            <div id="collapseKabarTerbaru{{ $index }}" class="accordion-collapse collapse"
                                data-bs-parent="#penyaluranDanaAccordion">
                                <div class="accordion-body">
                                    {!! $kabar->description !!}
                                    @if($kabar->image)
                                        <img src="{{ asset($kabar->image) }}" alt="{{ $kabar->title }}" class="img-fluid rounded">
                                    @endif
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center">
                          <img src="{{ asset('assets/img/icon/success-data.svg') }}" alt="Not Found" class="mb-3" style="width: 150px; height: 150px;">
                          <p>Belum ada kabar terbaru</p>
                      </div>
                    @endforelse
                    </div>
                </div>

                <div class="tab-content donatur d-none">
                    @forelse($campaign->donations as $donation)
                    <div class="card box-shadow mx-0 p-3 w-100 mb-2">
                        <div class="col-12 row">
                            <div class="col-7">
                                <h2>{{ $donation->name }}</h2>
                                <small>{{ $donation->created_at->diffForHumans() }}</small>
                            </div>
                            <div class="col-5 align-self-center text-end p-0">
                                <span class="text-color large">Rp {{ number_format($donation->amount, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center">
                      <img src="{{ asset('assets/img/icon/success-data.svg') }}" alt="Not Found" class="mb-3" style="width: 150px; height: 150px;">
                      <p>Belum ada donatur, jadilah orang pertama yang memberikan donasi</p>
                  </div>
                @endforelse
                </div>

                <div class="tab-content kabar-pencairan d-none">
                    <div class="accordion" id="kabarPencairanAccordion">
                        @forelse($campaign->kabarPencairan as $index => $pencairan)
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed d-flex justify-content-between align-items-center"
                                    type="button" data-bs-toggle="collapse" data-bs-target="#collapsePencairan{{ $index }}">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="circle-number">{{ $index + 1 }}</div>
                                        <div>
                                            <div class="text-muted small mb-1">{{ $pencairan->created_at->format('d F Y') }}</div>
                                            <div class="fw-bold text-danger">{{ $pencairan->title }}</div>
                                        </div>
                                    </div>
                                    <i class="fa-solid fa-chevron-down ms-auto circle-dropdown"></i>
                                </button>
                            </h2>
                            <div id="collapsePencairan{{ $index }}" class="accordion-collapse collapse"
                                data-bs-parent="#kabarPencairanAccordion">
                                <div class="accordion-body">
                                    <p>{!! $pencairan->description !!}</p>
                                    <a href="{{ asset('storage/' . $pencairan->document_rab) }}" target="_blank" class="button my-3 d-block text-center">
                                        <i class="fa-solid fa-download"></i> &nbsp; Laporan Penggunaan Dana
                                    </a>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center">
                          <img src="{{ asset('assets/img/icon/success-data.svg') }}" alt="Not Found" class="mb-3" style="width: 150px; height: 150px;">
                          <p>Belum ada kabar pencairan</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>



    <div class="line-spacing"></div>

    <div class="row col-12 m-0 px-3 doa-orang-baik mt-3">
        <h2 class="mx-0 px-0">Doa Orang Baik</h2>
        @forelse($campaign->donations->where('doa', '!=', null) as $donation)
        <div class="card box-shadow mb-2">
            <div class="d-flex justify-content-between">
                <h3>{{ $donation->name }}</h3>
                <small>{{ $donation->created_at->diffForHumans() }}</small>
            </div>
            <p>{{ $donation->doa }}</p>
            <div class="justify-content-between d-flex">
                <a href="javascript:void(0);" class="badge pt-1 px-3 rounded-pill bg-main-opacity text-color fw-normal like-button
                     {{ $donation->donationLikes()->where('user_id', auth()->id())->exists() ? 'liked' : '' }}" data-donation-id="{{ $donation->id }}">
                     <i class="fa-solid fa-hands-praying"></i> &nbsp; 
                     <span class="like-count">{{ $donation->donationLikes->count() }}</span> Aaminn
                </a>
                <h2 class="d-flex mb-0 align-self-center">
                    Rp {{ number_format($donation->amount, 0, ',', '.') }} 
                    <small class="fw-normal ms-1 mt-1">Donasi Terkirim</small>
                </h2>
            </div>
        </div>
        @empty
        <div class="text-center">
            <img src="{{ asset('assets/img/icon/success-data.svg') }}" alt="Not Found" class="mb-3" style="width: 150px; height: 150px;">
            <p>Belum ada doa orang baik, donasi sekarang dan jadilah orang pertama yang memberikan doa</p>
        </div>
        @endforelse

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
@endpush

