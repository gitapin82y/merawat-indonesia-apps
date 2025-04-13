@extends('layouts.public')
 
@section('title', 'Galang Dana')

@push('after-style')

@endpush

@section('content')

<div class="container-fluid p-0">
    <!-- Banner Foto Full Width -->
    <img src="{{asset('assets/img/banner-galang-dana.png')}}" class="img-fluid w-100" alt="Banner Penggalangan Dana">
  </div>
  <!-- Header -->


  <!-- Konten -->
  <div class="container d-flex flex-column ">
    <h5 class="fw-bold text-dark fs-6">Panduan Membuat Penggalangan Dana</h5>
    <div class="list-group mt-3 flex-grow-1">
      <div class="list-group-item d-flex align-items-center p-3 border-0 shadow-sm mb-3 rounded">
        <div
          class="rounded-circle bg-opacity text-second d-flex align-items-center justify-content-center flex-shrink-0"
          style="width: 40px; height: 40px; font-weight: bold;">1
        </div>
        <div class="ms-3">
          <h6 class="fw-bold text-second">Lengkapi Data Akun Galang Dana</h6>
          <p class="mb-0">Lengkapi semua data profil akun sesuai instruksi, dan tunggu notifikasi email hasil dari
            validasi admin.</p>
        </div>
      </div>
      <div class="list-group-item d-flex align-items-center p-3 border-0 shadow-sm mb-3 rounded">
        <div
          class="rounded-circle bg-opacity text-second d-flex align-items-center justify-content-center flex-shrink-0"
          style="width: 40px; height: 40px; font-weight: bold;">2
        </div>
        <div class="ms-3">
          <h6 class="fw-bold text-second">Buat Kampanye Galang Dana</h6>
          <p class="mb-0">Isi form secara lengkap dengan mengikuti instruksi seperti nama, foto, video, kategori,
            cerita, target dana, dan lainnya.</p>
        </div>
      </div>
      <div class="list-group-item d-flex align-items-center p-3 border-0 shadow-sm mb-3 rounded">
        <div
          class="rounded-circle bg-opacity text-second d-flex align-items-center justify-content-center flex-shrink-0"
          style="width: 40px; height: 40px; font-weight: bold;">3
        </div>
        <div class="ms-3">
          <h6 class="fw-bold text-second">Tunggu Verifikasi</h6>
          <p class="mb-0">Tunggu admin melakukan validasi data galang dana, hasil dari validasi admin akan dikirim
            melalui email.</p>
        </div>
      </div>
      <div class="list-group-item d-flex align-items-center p-3 border-0 shadow-sm mb-3 rounded">
        <div
          class="rounded-circle bg-opacity text-second d-flex align-items-center justify-content-center flex-shrink-0"
          style="width: 40px; height: 40px; font-weight: bold;">4
        </div>
        <div class="ms-3">
          <h6 class="fw-bold text-second">Galang Dana Siap Dibagikan</h6>
          <p class="mb-0">Setelah disetujui admin, galang dana kamu siap dibagikan, dan update kabar terbaru galang
            dana untuk donatur.</p>
        </div>
      </div>
    </div>

    <!-- Tombol Buat Akun Galang Dana -->
    <div class="container text-center mt-1 mb-5">
      <div class="row justify-content-center">
        <div class="col-12">
          <button type="submit" class="btn btn-second text-white shadow-sm w-100 py-2"
            onclick="window.location.href='{{url('galang-dana/buat-akun')}}'">
            <img src="{{asset('assets/img/icon/edit-profile.svg')}}" alt="Kirim" class="me-2"
              style="width: 20px; height: 20px;" />
            Buat Akun Galang Dana
          </button>
        </div>
      </div>
    </div>

  </div>
 
    @include('includes.public.menu')

@endsection

@push('after-script')
   
@endpush

