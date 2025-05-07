@extends('layouts.public')
 
@section('title', 'Akses Ditolak | Merawat Indonesia')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center"  style="padding: 100px 0;">
        <div class="col-md-8 text-center">
            <h1 class="h2 text-danger mb-3">Akses Ditolak</h1>
            <p class="lead mb-4">Mohon maaf, Anda tidak memiliki izin untuk mengakses halaman ini.</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="{{ url('/') }}" class="btn btn-danger px-4">Kembali ke Beranda</a>
                @if(Auth::check())
                    <a href="{{ url('/profile') }}" class="btn btn-danger px-4">Lihat Profil Saya</a>
                @else
                    <a href="{{ url('/login') }}" class="btn btn-danger px-4">Masuk</a>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Tambahkan menu utama jika perlu -->
@include('includes.public.menu')
@endsection