<a href="{{ route('campaign.detail', $campaign->slug) }}" class="card swiper-slide">
    <div class="thumbnail">
        <img src="{{asset('assets/img/icon/prioritas-kampanye.svg')}}" class="prioritas" alt="">
        <img src="{{ asset('storage/' . $campaign->photo) }}" class="image-campaign-card" alt="{{ $campaign->title }}">
    </div>
    <small class="my-1 p-0">{{ $campaign->admin->name }}  <img src="{{asset('assets/img/icon/verify.svg')}}" style="margin-top: -3px;" alt=""></small>
    <h3>{{ $campaign->title }}</h3>
    <div class="progress mb-2">
        <div class="progress-bar progress-bar-striped bg-danger" role="progressbar" style="width: {{ min($campaign->progressPercentage, 100) }}%;" aria-valuenow="{{ min($campaign->progressPercentage, 100) }}" aria-valuemin="0" aria-valuemax="100">{{ min($campaign->progressPercentage, 100) }}%</div>
    </div>
    <div class="row col-12 m-0 p-0">
        <div class="col-6 p-0 text-start">
            <strong>Rp {{ number_format($campaign->jumlah_donasi, 0, ',', '.') }}</strong>
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
</a>