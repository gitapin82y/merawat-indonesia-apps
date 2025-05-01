@forelse($donations as $donation)
<div class="card box-shadow mx-0 p-3 w-100 mb-2">
    <div class="col-12 row">
        <div class="col-7">
            <h2>{{ $donation->is_anonymous ? 'Sahabat Baik' : $donation->name }}</h2>
            <small>{{ $donation->created_at->diffForHumans() }}</small>
        </div>
        <div class="col-5 align-self-center text-end p-0">
            <span class="text-color large sizeDonatur">Rp {{ number_format($donation->amount, 0, ',', '.') }}</span>
        </div>
    </div>
</div>
@empty
    <div class="text-center">
        <img src="{{ asset('assets/img/icon/success-data.svg') }}" alt="Not Found" class="mb-3" style="width: 150px; height: 150px;">
        <p>Belum ada donatur, jadilah orang pertama yang memberikan donasi</p>
    </div>
@endforelse