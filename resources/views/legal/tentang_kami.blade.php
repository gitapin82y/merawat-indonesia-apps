@extends('layouts.public')

@section('title', 'Tentang Kami | Merawat Indonesia')

@section('content')
<div class="navbar-back col-12 align-items-center d-flex">
    <a href="{{ url()->current() == url()->previous() ? url('/') : url()->previous() }}" class="bg-white">
        <i class="fa-solid fa-angle-left"></i>
    </a>
    <h1 class="text-white mb-0 ms-2">Tentang Kami</h1>
</div>
<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <p class="lead">Terakhir diperbarui: {{ is_object($lastUpdated) && method_exists($lastUpdated, 'format') ? $lastUpdated->format('d F Y') : date('d F Y') }}</p>

                    @if ($content)
                        {!! $content !!}
                    @else
                        <p>Informasi tentang kami belum tersedia. Silahkan kembali nanti.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection