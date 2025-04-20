@forelse($donations as $donation)
<div class="donation-card p-3 border rounded shadow-sm">
    <div class="d-flex justify-content-between">
        <span class="fw-bold">{{ $donation->name }}</span>
        <span class="fw-bold text-end">Rp {{ number_format($donation->amount, 0, ',', '.') }}</span>
    </div>
    <small class="text-muted d-block">{{ $donation->created_at->diffForHumans() }}</small>
    @if($donation->doa)
    <div class="mt-2 pt-2 border-top">
        <small class="text-muted">Doa:</small>
        <p class="mb-0 small">{{ Str::limit($donation->doa, 100) }}</p>
    </div>
    @endif
    @if($donation->campaign)
    <div class="mt-2">
        <small class="text-muted">Kampanye:</small>
        <a href="{{ url('kampanye/'.$donation->campaign->slug) }}" class="small d-block text-danger">{{ $donation->campaign->title }}</a>
    </div>
    @endif
</div>
@empty
@if($donations->currentPage() == 1)
<div class="text-center">
    <img src="{{ asset('assets/img/icon/success-data.svg') }}" alt="Not Found" class="mb-3" style="width: 150px; height: 150px;">
    <p>Belum Memiliki Riwayat Donasi</p>
</div>
@endif
@endforelse