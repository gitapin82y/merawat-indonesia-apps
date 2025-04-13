@extends('layouts.public')
 
@section('title', 'Buat Galang Dana')

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

    @include('includes.public.navbar-back', ['title' => 'Buat Akun Galang Dana', 'url' => url('galang-dana')])

      <div class="container mt-4 flex-grow-1 ">
        <div class="alert alert-light shadow-sm d-flex align-items-center bg-white">
          <img src="{{asset('assets/img/icon/form-data.svg')}}" alt="Info" class="me-3" style="width: 120px; height: 120px;" />
          <div>
            <h6 class="fw-bold">Lengkapi Semua Data</h6>
            <p class="mb-0">Membuat akun galang dana memerlukan verifikasi akun dari admin terlebih dahulu sebelum
              membuka kampanye.</p>
          </div>
        </div>

        <form id="galangDanaForm" action="{{ route('admin.store') }}" method="POST" enctype="multipart/form-data">
          @csrf
          
          <div class="form-floating mb-3">
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="namaGalangDana" name="name" placeholder="Nama Galang Dana" value="{{ old('name') }}" required />
            <label for="namaGalangDana">Nama Galang Dana</label>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          
          <div class="form-floating mb-3">
            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" placeholder="Nomor Whatsapp" value="{{ old('phone') }}" required />
            <label for="whatsapp">Nomor Whatsapp</label>
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          
          <div class="form-floating mb-3">
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="Email" value="{{ old('email') }}" required />
            <label for="email">Email</label>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          
          <div class="form-floating mb-3">
            <input type="text" class="form-control @error('leader_name') is-invalid @enderror" id="namaKetua" name="leader_name" placeholder="Nama Ketua / Pimpinan" value="{{ old('leader_name') }}" required />
            <label for="namaKetua">Nama Ketua / Pimpinan</label>
            @error('leader_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          
          <div class="form-floating mb-3">
            <textarea class="form-control @error('address') is-invalid @enderror" id="alamatOrganisasi" name="address" placeholder="Alamat Organisasi" required>{{ old('address') }}</textarea>
            <label for="alamatOrganisasi">Alamat Organisasi</label>
            @error('address')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-3 position-relative">
            <div class="form-floating ">
              <input type="text" required class="form-control bg-white @error('legality') is-invalid @enderror" id="legalitasOrganisasi"
                placeholder="Legalitas Organisasi" style="padding-right: 120px; pointer-events: none;" readonly>
              <label for="legalitasOrganisasi">Legalitas Organisasi</label>
            </div>
            <input type="file" required id="fileLegalitas" name="legality" class="d-none @error('legality') is-invalid @enderror" onchange="updateInput('legalitasOrganisasi', this)">
            <button type="button" class="btn btn-upload position-absolute"
              style="right: 3px; top: 50%; transform: translateY(-50%); border-radius: 5px;"
              onclick="document.getElementById('fileLegalitas').click();">Unggah File</button>
          </div>
          @error('legality')
          <div class="invalid-file">{{ $message }}</div>
      @enderror

          <div class="mb-3 position-relative">
            <div class="form-floating ">
              <input type="text" required  class="form-control bg-white @error('thumbnail') is-invalid @enderror" id="thumbnailProfil"
                placeholder="Thumbnail Profil Galang Dana" style="padding-right: 120px; pointer-events: none;" readonly>
              <label for="thumbnailProfil">Thumbnail Profil Galang Dana</label>
            </div>
            <input type="file" required id="fileThumbnail" name="thumbnail" class="d-none @error('thumbnail') is-invalid @enderror" onchange="updateInput('thumbnailProfil', this)">
            
            <button type="button" class="btn btn-upload position-absolute"
              style="right: 3px; top: 50%; transform: translateY(-50%); border-radius: 5px;"
              onclick="document.getElementById('fileThumbnail').click();">Unggah File</button>
          </div>
          @error('thumbnail')
          <div class="invalid-file">{{ $message }}</div>
      @enderror
           

          <div class="mb-3 position-relative">
            <div class="form-floating">
              <input type="text" required class="form-control bg-white @error('avatar') is-invalid @enderror" id="fotoProfil" placeholder="Foto Profil Galang Dana"
                style="padding-right: 120px; pointer-events: none;" readonly>
              <label for="fotoProfil">Foto Profil Galang Dana</label>
            </div>
            <input type="file" required id="fileFoto" name="avatar" class="d-none @error('avatar') is-invalid @enderror" onchange="updateInput('fotoProfil', this)">
            <button type="button" class="btn btn-upload position-absolute"
              style="right: 3px; top: 50%; transform: translateY(-50%); border-radius: 5px;"
              onclick="document.getElementById('fileFoto').click();">Unggah File</button>
          </div>
          @error('avatar')
          <div class="invalid-file">{{ $message }}</div>
      @enderror
        </form>
      </div>

      <div class="footer mb-5 text-center">
        <div class="main-menu row col-12 mx-0 justify-content-between d-flex ">
          <button type="button" id="submitForm" class="button w-100 d-flex align-items-center justify-content-center text-white shadow-sm" >
            <img src="{{asset('assets/img/icon/edit-profile.svg')}}" alt="Kirim"
              style="width: 20px; height: 20px; margin-right: 8px;" />
            <span class="text-white">Simpan dan Kirim Data</span>
          </button>
        </div>
      </div>

@endsection

@push('after-script')

<script>
    function updateInput(inputId, fileInput) {
      document.getElementById(inputId).value = fileInput.files[0] ? fileInput.files[0].name : "";
    }

    // Menggunakan format JS yang diberikan
    $(document).ready(function() {
        $('#submitForm').on('click', function(e) {
            e.preventDefault();
            
            Swal.fire({
                title: 'Konfirmasi Pengiriman Data',
                text: 'Apakah Anda yakin ingin mengirim data galang dana ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Kirim',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#galangDanaForm').submit();
                }
            });
        });
    });

    // Flash messages from session
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