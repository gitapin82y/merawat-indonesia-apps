<a href="{{ route('admin.campaign.detail', $campaign->slug) }}" class="col-12 row mt-2">
    <div class="foto col-6 col-sm-6 col-md-4 align-self-center position-relative">
        <div class="status-overlay text-danger position-absolute top-0 ms-1 mt-1 bg-white p-1 rounded">
            <small>
                @switch($campaign->status)
                    @case('aktif')
                        Aktif
                        @break
                    @case('selesai')
                        Berakhir
                        @break
                    @case('validasi')
                        Dalam Validasi
                        @break
                    @case('ditolak')
                        Ditolak
                        @break
                @endswitch
            </small>
        </div>

        <img src="{{ asset('storage/' . $campaign->photo) }}" class="image-campaign-card" alt="{{ $campaign->title }}">
    </div>

    <div class="col-6 col-sm-6 col-md-8 p-0 align-self-center">
        <small class="p-0">{{ $campaign->admin->name }} 
            <img src="{{ asset('assets/img/icon/verify.svg') }}" style="margin-top: -3px;" alt="">
        </small>
        <h3>{{ $campaign->title }}</h3>
        <div class="progress mb-2" style="height: 12px;font-size:8px;">
            <div class="progress-bar progress-bar-striped bg-danger" role="progressbar"
                style="width: {{ min($campaign->progress_percentage_real, 100) }}%;" 
                aria-valuenow="{{ min($campaign->progress_percentage_real, 100) }}" 
                aria-valuemin="0" aria-valuemax="100">
                {{ min($campaign->progress_percentage_real, 100) }}%
            </div>
        </div>
        <div class="row col-12 m-0 p-0">
            <div class="col-6 p-0 text-start">
                <strong>Rp {{ $campaign->total_donasi_formatted }}</strong>
                <small>Terkumpul</small>
            </div>
            <div class="col-6 p-0 text-end">
                
                <strong>
    @if($campaign->deadline)
        @if($campaign->remainingDays < 0)
            <span class="text-secondary" style="font-size:11px;">Sudah Berakhir</span>
        @elseif($campaign->remainingDays == 0)
            {{ $campaign->remainingTime }}
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
            {{-- kosong --}}
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

        <button class="btn btn-danger text-white w-100 mt-2 rounded py-1 px-2"
            style="font-size: 12px; min-width: 150px;">
            <img src="{{ asset('assets/img/icon/edit-profile.svg') }}" class="me-1" style="width: 10px;" alt="Manage"> 
            Kelola Kampanye
        </button>
    </div>
</a>