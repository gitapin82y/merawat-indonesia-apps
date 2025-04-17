@extends('layouts.auth')
 
@section('title', 'Login Merawat Indonesia')

@push('after-style')
<style>
    body {
        background-color: #f8f9fa;
        overflow: hidden;
        height: 100vh;
        margin: 0;

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

    .btn-dark {
        background-color: black;
    }

    .text-second {
        color: #FF4747;
    }

    .btn-second {
        background-color: #FF4747;
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
                <div class="d-flex flex-column align-items-center justify-content-center" style="height: 100%;">
                    <div class="d-flex align-items-center">
                        <div class="d-flex flex-column align-items-center justify-content-center me-3">
                            <img src="{{asset('assets/img/merawat-indonesia.png')}}" alt="Logo" width="75" class="mb-2">
                        </div>
                        <div class="d-flex flex-column text-start">
                            <h5 class="mb-2t">Gabung Bersama<br><strong>Merawat Indonesia</strong></h5>
                            <button class="btn btn-danger btn-sm py-2 text-second px-4 mb-3 fs-6 btn-transparent">
                                <strong> Masuk Sekarang</strong>
                            </button>

                        </div>
                    </div>
                </div>



                <form action="{{ route('login') }}" method="POST">
                    @csrf
                    <div class="form-floating mb-3 mt-4">
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" id="email" value="{{ old('email') }}" placeholder="Email">
                        <label for="email">Email</label>
                        @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
                    </div>
                    <div class="form-floating mb-3">
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" value="{{ old('Password') }}" placeholder="Password"  id="password">
                        <label for="password">Password</label>
                        @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
                    </div>
                    <div class="text-start mb-3">
                        <a>Lupa Password?</a>
                        <a href="#" class="text-second text-decoration-none"><b>Ganti Sekarang</b></a>
                    </div>
                    <button type="submit" class="btn btn-second text-white w-100">Masuk Sekarang</button>
                </form>
                <div class="d-flex align-items-center my-3">
                    <hr class="flex-grow-1">
                    <span class="mx-2">Atau</span>
                    <hr class="flex-grow-1">
                </div>

                <button class="btn btn-primary btn-social py-3">
                    <img src="{{asset('assets/img/icon/login-facebook.svg')}}" alt="fb" width="20">
                    <i class="bi bi-facebook me-2"></i> Masuk Dengan Facebook
                </button>
                <button class="btn btn-light btn-social py-3 shadow">
                    <img src="{{asset('assets/img/icon/login-google.svg')}}" alt="google" width="20">
                    <i class="bi bi-google me-2"></i> Masuk Dengan Google
                </button>
                <button class="btn btn-dark btn-social py-3">
                    <img src="{{asset('assets/img/icon/apple.svg')}}" alt="apple" width="18">
                    <i class="bi bi-apple me-2"></i> Masuk Dengan Apple
                </button>

                <p class="mt-3 ">Belum punya akun? <a href="{{url('/register')}}"
                        class="text-second text-decoration-none"><b>Daftar Sekarang</b></a></p>
            </div>
        </div>
    </div>
</div>

@endsection

@push('after-script')
   <script>
     @if(session('success'))
    Swal.fire({
      icon: 'success',
      title: 'Berhasil!',
      text: "{{ session('success') }}",
      timer: 3000
    });
    @endif

    @if(session('error'))
    Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: "{{ session('error') }}",
      timer: 3000
    });
    @endif
   </script>
@endpush

