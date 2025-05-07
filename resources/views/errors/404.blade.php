@extends('layouts.public')
 
@section('title', 'Halaman Tidak Ditemukan | Merawat Indonesia')

@section('content')
<div class="container my-5" style="padding: 100px 0;">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <h1 class="h2 text-danger mb-3">Halaman Tidak Ditemukan</h1>
            <p class="lead mb-4">Halaman yang Anda cari tidak ditemukan. Mungkin alamat telah berubah atau halaman telah dihapus.</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="{{ url('/') }}" class="btn btn-danger px-4">Kembali ke Beranda</a>
                <a href="{{ url('/eksplore-kampanye') }}" class="btn btn-danger px-4">Lihat Galang Dana</a>
            </div>
        </div>
    </div>
</div>

<!-- Tambahkan menu utama jika perlu -->
@include('includes.public.menu')
@endsection