@extends('layouts.auth')
 
@section('title', 'Lupa Password')

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
    }

    .btn-transparent {
        background-color: rgba(255, 71, 71, 0.1) !important;
        border: none !important;
        transition: background-color 0.5s ease-in-out;
    }

    .btn-transparent:hover {
        background-color: rgba(238, 25, 25, 0.295) !important;
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
                            <a href="{{url('/')}}">
                            <img src="{{asset('assets/img/merawat-indonesia.png')}}" alt="Logo" width="75" class="mb-2">
                            </a>
                        </div>
                        <div class="d-flex flex-column text-start">
                            <h5 class="mb-2t">Lupa Password<br><strong>Merawat Indonesia</strong></h5>
                            <button class="btn btn-danger btn-sm py-2 text-second px-4 mb-3 fs-6 btn-transparent">
                                <strong>Reset Password</strong>
                            </button>
                        </div>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                <p class="text-muted mb-4">Masukkan alamat email Anda dan kami akan mengirimkan link untuk mengatur ulang password Anda.</p>

                <form action="{{ route('password.email') }}" method="POST">
                    @csrf
                    <div class="form-floating mb-3 mt-4">
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" id="email" value="{{ old('email') }}" placeholder="Email" required autofocus>
                        <label for="email">Email</label>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <button type="submit" class="btn btn-second text-white w-100">Kirim Link Reset Password</button>
                </form>

                <div class="text-center mt-4">
                    <a href="{{ route('login') }}" class="text-decoration-none">
                        <i class="fas fa-arrow-left me-1"></i> Kembali ke halaman login
                    </a>
                </div>
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