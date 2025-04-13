@extends('layouts.public')
 
@section('title', 'Berhasil Buat Galang Dana')

@push('after-style')
<style>
    .btn-upload {
      background: #e0e0e0;
      border: none;
      padding: 5px 10px;
      height: 52px !important;
      min-width: 120px;
      background: #aca9a933;
    }

    .btn-upload:hover {
      background: #d6d6d6 !important;
      transform: scale(1.05);
      color: #333 !important;
    }

    .btn-second {
      background-color: var(--second-color);
    }

    .form-control[readonly] {
      color: black !important;
    }
  </style>
@endpush

@section('content')

      <!-- Konten -->
      <div class="container mt-5 flex-grow-1 text-center">
        <img src="{{asset('assets/img/icon/success-data.svg')}}" class="img-fluid mb-3" alt="Success Icon"
            style="max-width: 200px;" />
        <h5 class="fw-bold text-dark fs-6 mt-4">Data Berhasil Dikirim</h5>
        <p class="text-dark">Berkas anda sedang divalidasi, tunggu notifikasi hasil validasi dari admin untuk
            melanjutkan galang dana.</p>
        <a href="{{url('/')}}" class="btn btn-second text-light w-100 py-2 mt-2">Kembali ke Beranda</a>
    </div>

    @include('includes.public.menu')

@endsection

@push('after-script')
<script>
    function updateInput(inputId, fileInput) {
      document.getElementById(inputId).value = fileInput.files[0] ? fileInput.files[0].name : "";
    }
  </script>
@endpush

