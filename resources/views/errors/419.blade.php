@extends('layouts.public')
 
@section('title', 'Sesi Kadaluarsa | Merawat Indonesia')

@section('content')
<div class="container my-5"  style="padding: 100px 0;">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <h1 class="h2 text-danger mb-3">Sesi Anda Telah Kadaluarsa</h1>
            <p class="lead mb-4">Mohon maaf, sesi Anda telah kadaluarsa karena tidak ada aktivitas dalam waktu yang lama. Silakan muat ulang halaman dan coba lagi.</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="{{ url('/') }}" class="btn btn-danger px-4">Kembali ke Beranda</a>
                <button onclick="window.location.reload()" class="btn btn-danger px-4">Muat Ulang</button>
            </div>
        </div>
    </div>
</div>

<!-- Tambahkan menu utama jika perlu -->
@include('includes.public.menu')
@endsection