@extends('layouts.public')
 
@section('title', 'Terlalu Banyak Permintaan | Merawat Indonesia')

@section('content')
<div class="container my-5"  style="padding: 100px 0;">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <h1 class="h2 text-danger mb-3">Terlalu Banyak Permintaan</h1>
            <p class="lead mb-4">Mohon maaf, Anda telah mengirim terlalu banyak permintaan. Silakan tunggu beberapa saat dan coba lagi.</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="{{ url('/') }}" class="btn btn-danger px-4">Kembali ke Beranda</a>
                <button onclick="window.location.reload()" class="btn btn-danger px-4" id="retry-button" disabled>
                    <span id="countdown">30</span> Tunggu...
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Tambahkan menu utama jika perlu -->
@include('includes.public.menu')
@endsection

@push('after-script')
<script>
    // Countdown timer untuk tombol coba lagi
    let timeLeft = 30;
    const countdownEl = document.getElementById('countdown');
    const retryBtn = document.getElementById('retry-button');
    
    const timer = setInterval(() => {
        timeLeft--;
        countdownEl.textContent = timeLeft;
        
        if (timeLeft <= 0) {
            clearInterval(timer);
            retryBtn.disabled = false;
            retryBtn.innerHTML = 'Coba Lagi';
        }
    }, 1000);
</script>
@endpush