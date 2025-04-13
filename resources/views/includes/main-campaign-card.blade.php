<a href="{{ route('campaign.detail', $campaign->title) }}" class="card swiper-slide">
    <img src="{{ asset('storage/' . $campaign->photo) }}" class="image-campaign-card" alt="{{ $campaign->title }}">
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
            {{ $campaign->remainingDays }}
        @else
            0
        @endif
        </strong>
        <small>Hari Lagi</small>
    </div>
</div>
</a>