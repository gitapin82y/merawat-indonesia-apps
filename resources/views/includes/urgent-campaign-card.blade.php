<a href="{{ route('campaign.detail', $urgentItem->slug) }}" class="card swiper-slide">
    <div class="thumbnail">
        <img src="{{ asset('storage/' . $urgentItem->photo) }}" class="image-campaign-card" alt="{{ $urgentItem->title }}">
    </div>
    <small class="my-1 p-0">{{ $urgentItem->admin->name }}  <img src="{{asset('assets/img/icon/verify.svg')}}" style="margin-top: -3px;" alt=""></small>
    <h3>{{ $urgentItem->title }}</h3>
    <div class="progress mb-2" style="height: 12px;font-size:8px;">
        <div class="progress-bar progress-bar-striped bg-danger" role="progressbar" style="width: {{ min($urgentItem->progress_percentage_real, 100) }}%;" aria-valuenow="{{ min($urgentItem->progress_percentage_real, 100) }}" aria-valuemin="0" aria-valuemax="100">{{ min($urgentItem->progress_percentage_real, 100) }}%</div>
    </div>
    <div class="row col-12 m-0 p-0">
        <div class="col-6 p-0 text-start">
            <strong>Rp {{ $urgentItem->total_donasi_formatted }}</strong>
        <small>Terkumpul</small>
        </div>
        <div class="col-6 p-0 text-end">
            {{-- <strong>
                @if($urgentItem->deadline)
                    @if($urgentItem->remainingDays < 0)
                        0
                    @elseif($urgentItem->remainingDays == 0)
                        {{ $urgentItem->remainingTime }}
                    @else
                        {{ $urgentItem->remainingDays }}
                    @endif
                @else
                    <i class="fas fa-infinity"></i>
                @endif
            </strong>
            <small>
                @if($urgentItem->deadline)
                    @if($urgentItem->remainingDays < 0)
                        Hari Lagi
                    @elseif($urgentItem->remainingDays == 0)
                        Jam Lagi
                    @else
                        Hari Lagi
                    @endif
                @else
                    Tanpa Batas Waktu
                @endif
            </small> --}}
            {{-- SESUDAH --}}
<strong>
    @if($urgentItem->deadline)
        @if($urgentItem->remainingDays < 0)
            <span class="text-secondary" style="font-size:11px;">Sudah Berakhir</span>
        @elseif($urgentItem->remainingDays == 0)
            {{ $urgentItem->remainingTime }}
        @else
            {{ $urgentItem->remainingDays }}
        @endif
    @else
        <i class="fas fa-infinity"></i>
    @endif
</strong>
<small>
    @if($urgentItem->deadline)
        @if($urgentItem->remainingDays < 0)
            {{-- kosong --}}
        @elseif($urgentItem->remainingDays == 0)
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