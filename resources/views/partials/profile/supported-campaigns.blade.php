@forelse($supportedCampaigns as $donation)
    @if($donation->campaign)
            <a href="{{ route('campaign.detail', $donation->campaign->slug) }}" class="col-12 row mt-2">
                <div class="foto col-6 col-sm-6 col-md-4 align-self-center mb-3 position-relative">
                    <img src="{{ asset('storage/' . $donation->campaign->photo) }}" class="image-campaign-card" alt="{{ $donation->campaign->title }}">
                </div>

                <div class="col-6 col-sm-6 col-md-8 p-0 align-items-center">
                    <small class="p-0">{{ $donation->campaign->admin->name }} 
                        <img src="{{ asset('assets/img/icon/verify.svg') }}" style="margin-top: -3px;" alt="">
                    </small>
                    <h3>{{ $donation->campaign->title }}</h3>
                    <div class="progress mb-2">
                        <div class="progress-bar progress-bar-striped bg-danger" role="progressbar"
                            style="width: {{ min($donation->campaign->progressPercentage, 100) }}%;" 
                            aria-valuenow="{{ min($donation->campaign->progressPercentage, 100) }}" 
                            aria-valuemin="0" aria-valuemax="100">
                            {{ min($donation->campaign->progressPercentage, 100) }}%
                        </div>
                    </div>
                    <div class="row col-12 m-0 p-0">
                        <div class="col-6 p-0 text-start">
                            <strong>Rp {{ number_format($donation->campaign->jumlah_donasi, 0, ',', '.') }}</strong>
                            <small>Terkumpul</small>
                        </div>
                        <div class="col-6 p-0 text-end">
                            <strong>
                                @if($donation->campaign->deadline)
                                    @if($donation->campaign->remainingDays < 0)
                                        0
                                    @elseif($donation->campaign->remainingDays == 0)
                                        {{ floor($donation->campaign->remainingTime) }}
                                    @else
                                        {{ $donation->campaign->remainingDays }}
                                    @endif
                                @else
                                    <i class="fas fa-infinity"></i>
                                @endif
                            </strong>
                            <small>
                                @if($donation->campaign->deadline)
                                    @if($donation->campaign->remainingDays < 0)
                                        Hari Lagi
                                    @elseif($donation->campaign->remainingDays == 0)
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
    @endif
@empty
@if($supportedCampaigns->currentPage() == 1)
<div class="text-center">
    <img src="{{ asset('assets/img/icon/success-data.svg') }}" alt="Not Found" class="mb-3" style="width: 150px; height: 150px;">
    <p>Belum Memiliki Dukungan Kampanye</p>
</div>
@endif
@endforelse