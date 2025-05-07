@extends('layouts.public')
 
@section('title', 'Kesalahan Server | Merawat Indonesia')

@section('content')
<div class="container my-5"  style="padding: 100px 0;">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <h1 class="h2 text-danger mb-3">Terjadi Kesalahan</h1>
            <p class="lead mb-4">Mohon maaf, server kami sedang mengalami gangguan. Tim kami sedang bekerja untuk menyelesaikan masalah ini.</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="{{ url('/') }}" class="btn btn-danger px-4">Kembali ke Beranda</a>
                <button onclick="window.location.reload()" class="btn btn-danger px-4">Coba Lagi</button>
            </div>
        </div>
    </div>
</div>
<!-- Tambahkan menu utama jika perlu -->
@include('includes.public.menu')
@endsection