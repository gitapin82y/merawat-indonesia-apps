@extends('layouts.auth')
 
@section('title', 'Daftar Akun')

@push('after-style')
<style>
    body {
        background-color: #f8f9fa;
        height: 100vh;
    }

    .login-container {
        height: 100vh;
        margin-bottom: 50px;
    }

    .login-box {
        background: white;
        padding: 2rem;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .btn-social {
        width: 100%;
        margin: 0.5rem 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .login-image {
        position: relative;
        background: url('assets/img/login-page.png') no-repeat center center;
        background-size: contain;

    }

    .login-image::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to bottom, #FF4747 34%, #DF5454 54%, #A50505 100%);
        z-index: -1;
        /* Menempatkan gradasi di belakang gambar */
    }

    .btn-transparent {
        background-color: rgba(255, 71, 71, 0.1) !important;
        /* Warna merah dengan opacity */
        border: none !important;
        transition: background-color 0.5s ease-in-out,
    }

    .btn-transparent:hover {
        background-color: rgba(238, 25, 25, 0.295) !important;
        /* Warna merah lebih kuat saat hover */
    }

    .btn-second {
        background-color: #FF4747;
    }

    .text-second {
        color: #FF4747;
    }

    .btn-second:hover {
        background-color: var(--bs-danger);
        color: white;
    }
</style>
@endpush

@section('content')

<div class="container-fluid h-100">
    <div class="row login-container">
        <!-- Kolom gambar hanya muncul di layar besar -->
        <div class="col-lg-6 d-none d-lg-block login-image"></div>
        <div class="col-lg-6 d-flex align-items-center justify-content-center">
            <div class="login-box w-100" style="max-width: 500px;">
                <div class="d-flex flex-column align-items-center" style="height: 100%;">
                    <div class="d-flex align-items-center">
                        <div class="d-flex flex-column align-items-center me-3">
                            <img src="assets/img/merawat-indonesia.png" alt="Logo" width="75" class="mb-2">
                        </div>
                        <div class="d-flex flex-column text-start">
                            <h5 class="mb-2">Gabung Bersama<br><strong>Merawat Indonesia</strong></h5>
                            <button class="btn btn-second btn-sm py-2 text-second px-4 mb-3 form-floating fs-6 btn-transparent">
                                <strong> Daftar Sekarang</strong>
                            </button>

                        </div>
                    </div>
                </div>

                <form action="{{ route('register') }}" method="POST">
                    @csrf
                    <div class="mb-3 form-floating mt-4">
                        <input type="name" name="name"  class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Nama Depan" id="name">
                        <label for="name">Nama Lengkap</label>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    </div>
                    <div class="mb-3 form-floating">
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="Email" id="email">
                        <label for="email">Email</label>
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    </div>
                    <div class="mb-3 form-floating">
                        <input type="phone" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="Nomor Telepon" id="phone">
                        <label for="phone">Nomor Telepon</label>
                        @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    </div>
                    <div class="mb-3 form-floating">
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" value="{{ old('password') }}" placeholder="Password" id="password">
                        <label for="password">Password</label>
                        @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    </div>
                    <div class="mb-4 form-floating">
                        <input type="password" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" id="password_confirmation" value="{{ old('password_confirmation') }}" placeholder="Konfirmasi Password">
                        <label for="password_confirmation">Konfirmasi Password</label>
                        @error('password_confirmation')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    </div>

                    <button type="submit" class="btn btn-second text-white w-100 ">Daftar Sekarang</button>

                    <p class="mt-4 ">Sudah Punya akun? <a href="{{url('/login')}}"
                            class="text-second text-decoration-none"><b>Masuk Sekarang</b></a></p>
            </div>
        </div>
    </div>
</div>

@endsection

@push('after-script')
   
@endpush

