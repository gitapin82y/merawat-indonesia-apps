<a href="{{ route('campaign.detail', $campaign->slug) }}" class="col-12 row mt-2">
    <div class="foto col-6 col-sm-6 col-md-4 align-self-center mb-3 position-relative">
        <img src="{{ asset('storage/' . $campaign->photo) }}" class="image-campaign-card" alt="{{ $campaign->title }}">
    </div>

    <div class="col-6 col-sm-6 col-md-8 p-0 align-items-center">
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
                            0
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
    </div>
</a>