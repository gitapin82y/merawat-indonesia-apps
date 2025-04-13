@extends('layouts.public')
 
@section('title', 'Detail Kampanye - {{ $campaign->title }}')

@push('after-style')
<style>
    /* ... (previous styles remain the same) ... */
</style>
@endpush

@section('content')
    <div class="thumbnail-detail-kampanye">
        <div class="justify-content-between nav-top d-flex">
            <a href="{{ route('campaigns.index') }}" class="bg-white">
                <i class="fa-solid fa-angle-left"></i>
            </a>
            <a href="" class="bg-white">
                <i class="fa-regular fa-bookmark"></i>
            </a>
        </div>
        <img src="{{ asset($campaign->photo) }}" width="100%" alt="{{ $campaign->title }}">
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
                        Rp {{ number_format($campaign->current_donation, 0, ',', '.') }} 
                        <span class="small">Kebutuhan</span> 
                        <span class="fw-bold">Rp {{ number_format($campaign->jumlah_target_donasi, 0, ',', '.') }}</span> 
                    </h2>
                </div>
                <div class="col d-flex justify-content-end p-0">
                    <h2>{{ $remainingDays }}</h2>
                    <small class="ms-1" style="margin-top:2px;">Hari Lagi</small>
                </div>
            </div>

            <div class="col-12 row mx-0">
                <div class="progress my-1 px-0">
                    <div class="progress-bar progress-bar-striped bg-danger" role="progressbar"
                        style="width: {{min($campaign->progressPercentage, 100) }}%;" 
                        aria-valuenow="{{min($campaign->progressPercentage, 100) }}" 
                        aria-valuemin="0" 
                        aria-valuemax="100">
                        {{min($campaign->progressPercentage, 100) }}%
                    </div>
                </div>
            </div>

            <div class="col-12 row px-0 pt-2 mx-0">
                <small>Sisa dana yang belum dicairkan <strong>Rp {{ number_format($campaign->jumlah_target_donasi - $campaign->current_donation, 0, ',', '.') }}</strong></small>
            </div>

            <div class="col-12 row mx-0 my-3">
                <div class="col-3 text-center">
                    <div class="d-flex align-items-center justify-content-center">
                        <img src="{{ asset('assets/img/icon/user-donatur.svg') }}" alt="" class="me-2" style="height: 20px;">
                        <p class="count m-0 d-flex align-items-center">{{ $campaign->total_donatur }}</p>
                    </div>
                    <small>Donatur</small>
                </div>
                <div class="col-3 text-center">
                    <div class="d-flex align-items-center justify-content-center">
                        <img src="{{ asset('assets/img/icon/kabar-terbaru.svg') }}" alt="" class="me-2" style="height: 20px;">
                        <p class="count m-0 d-flex align-items-center">{{ $campaign->total_kabar_terbaru }}</p>
                    </div>
                    <small>Kabar Terbaru</small>
                </div>
                <div class="col-3 text-center">
                    <div class="d-flex align-items-center justify-content-center">
                        <img src="{{ asset('assets/img/icon/pencairan-dana.svg') }}" alt="" class="me-2" style="height: 20px;">
                        <p class="count m-0 d-flex align-items-center">{{ $campaign->total_pencairan_dana }}</p>
                    </div>
                    <small>Pencairan Dana</small>
                </div>
                <div class="col-3 text-center">
                    <div class="d-flex align-items-center justify-content-center">
                        <img src="{{ asset('assets/img/icon/doa-orang-baik.svg') }}" alt="" class="me-2" style="height: 20px;">
                        <p class="count m-0 d-flex align-items-center">{{ $campaign->total_donatur }}</p>
                    </div>
                    <small>Doa Orang Baik</small>
                </div>
            </div>

            <div class="row mx-0 col-12">
                <a href="{{ asset($campaign->document_rab) }}" class="button my-3 w-100 text-center" download>
                    <i class="fa-solid fa-download"></i> &nbsp; Unduh Laporan RAB
                </a>
            </div>

            <div class="line-spacing"></div>

            <!-- Buttons -->
            <div class="row info-kampanye my-4 mx-0">
                <div class="d-flex scroll-x gap-2 mb-3">
                    <button class="btn btn-outline-danger tab-button" data-target="keterangan">Keterangan</button>
                    <button class="btn btn-outline-danger tab-button" data-target="kabar-terbaru">Kabar Terbaru</button>
                    <button class="btn btn-outline-danger tab-button" data-target="donatur">Donatur</button>
                    <button class="btn btn-outline-danger tab-button" data-target="kabar-pencairan">Kabar Pencairan</button>
                </div>

                <!-- Content Sections -->
                <div class="tab-content keterangan">
                    <p>{{ $campaign->description }}</p>
                </div>

                <div class="tab-content kabar-terbaru d-none">
                    <div class="accordion" id="penyaluranDanaAccordion">
                        @foreach($campaign->kabarTerbaru as $index => $kabar)
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
                        @endforeach
                    </div>
                </div>

                <div class="tab-content donatur d-none">
                    @foreach($campaign->donations as $donation)
                    <div class="card box-shadow mx-0 p-3 w-100 mb-2">
                        <div class="col-12 row">
                            <div class="col-7">
                                <h2>{{ $donation->donor_name }}</h2>
                                <small>{{ $donation->created_at->diffForHumans() }}</small>
                            </div>
                            <div class="col-5 align-self-center text-end p-0">
                                <span class="text-color large">Rp {{ number_format($donation->amount, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="tab-content kabar-pencairan d-none">
                    <div class="accordion" id="kabarPencairanAccordion">
                        @foreach($campaign->kabarPencairan as $index => $pencairan)
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
                                    <a href="{{ asset($pencairan->document_rab) }}" class="button my-3 d-block text-center" download>
                                        <i class="fa-solid fa-download"></i> &nbsp; Laporan Penggunaan Dana
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="line-spacing"></div>

    <div class="row col-12 m-0 px-3 doa-orang-baik mt-3">
        <h2 class="mx-0 px-0">Doa Orang Baik</h2>
        @foreach($campaign->donations->where('prayer', '!=', null) as $donation)
        <div class="card box-shadow mb-2">
            <div class="d-flex justify-content-between">
                <h3>{{ $donation->donor_name }}</h3>
                <small>{{ $donation->created_at->diffForHumans() }}</small>
            </div>
            <p>{{ $donation->prayer }}</p>
            <div class="justify-content-between d-flex">
                <a href="#" class="badge pt-1 px-3 rounded-pill bg-main-opacity text-color fw-normal">
                    <i class="fa-solid fa-hands-praying"></i> &nbsp; {{ $donation->amin_count }} Aaminn
                </a>
                <h2 class="d-flex mb-0 align-self-center">
                    Rp {{ number_format($donation->amount, 0, ',', '.') }} 
                    <small class="fw-normal ms-1 mt-1">Donasi Terkirim</small>
                </h2>
            </div>
        </div>
        @endforeach
    </div>
        
    <div class="footer">
        <div class="container my-3">
            <div class="p-3 rounded">
                <p class="fw-bold text-dark mb-2">Update Informasi Kampanye ?</p>
                <div class="d-flex gap-2">
                    <a href="{{ route('campaigns.edit', $campaign->id) }}" class="btn btn-second flex-fill text-white">Edit Kampanye</a>
                    <a href="{{ route('campaigns.add-news', $campaign->id) }}" class="btn btn-second flex-fill text-white">Kabar Terbaru</a>
                    <a href="{{ route('campaigns.withdraw', $campaign->id) }}" class="btn btn-second flex-fill text-white">Pencairan Dana</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('after-script')
<script>
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
</script>
@endpush