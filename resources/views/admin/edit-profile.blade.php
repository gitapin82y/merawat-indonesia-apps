@extends('layouts.public')
 
@section('title', 'Edit Profile Galang Dana')

@push('after-style')
<style>

    .profile-container {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .header {
        background-color: #ff4d4d;
        color: white;
        padding: 15px;
        border-radius: 10px 10px 0 0;
        font-weight: bold;
    }

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

    .box-container {
        background-color: white;
        padding: 15px;
        border-radius: 10px;
    }

    .bg-second {
        background-color: var(--second-color);
    }

    .btn-second {
        background-color: var(--second-color);
    }

    .btn-second:hover {
        background-color: var(--bs-danger);
        color: white;
    }
    .form-control[readonly] {
        color: black !important;
    }
</style>
@endpush

@section('content')

    @include('includes.public.navbar-back', ['title' => 'Edit Profile Galang Dana'])

        <!-- Konten -->
        <?php
            use App\Models\Admin;
            $admin = Admin::where('user_id',Auth::user()->id)->first();
        ?>
        <div class="container mt-4 flex-grow-1" style="padding-bottom: 170px">
            <form id="formData" action="{{ route('admin.update', $admin->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <input type="hidden" name="user_id" value="{{ $admin->user_id }}">
                <input type="hidden" name="leader_name" value="{{ $admin->leader_name }}">
                
            <!-- Thumbnail Profil -->
            <div class="mb-3 position-relative">
                <div class="form-floating">
                    <input type="text" class="form-control bg-white @error('thumbnail') is-invalid @enderror" id="thumbnailProfil"
                        placeholder="Thumbnail Profil" style="padding-right: 120px; pointer-events: none;" readonly>
                    <label for="thumbnailProfil">Thumbnail Profil</label>
                </div>
                <input type="file" id="fileThumbnail" name="thumbnail" class="d-none @error('thumbnail') is-invalid @enderror"
                    onchange="updateInput('thumbnailProfil', this)">
                <button class="btn btn-upload position-absolute"
                    style="right: 3px; top: 50%; transform: translateY(-50%); border-radius: 5px;"
                    onclick="document.getElementById('fileThumbnail').click();">Unggah File</button>
            </div>
            @error('thumbnail')
            <div class="invalid-file">{{ $message }}</div>
        @enderror
        
            <!-- Foto Profil -->
            <div class="mb-3 position-relative">
                <div class="form-floating">
                    <input type="text" class="form-control bg-white @error('avatar') is-invalid @enderror" id="avatarProfil" placeholder="Avatar Profil"
                        style="padding-right: 120px; pointer-events: none;" readonly>
                    <label for="avatarProfil">Avatar Profil</label>
                </div>
                <input type="file" id="fileAvatar" name="avatar" class="d-none @error('avatar') is-invalid @enderror" onchange="updateInput('avatarProfil', this)">
                <button class="btn btn-upload position-absolute"
                    style="right: 3px; top: 50%; transform: translateY(-50%); border-radius: 5px;"
                    onclick="document.getElementById('fileAvatar').click();">Unggah File</button>
            </div>
            @error('avatar')
            <div class="invalid-file">{{ $message }}</div>
        @enderror
        
            <!-- Nama -->
            <div class="form-floating mb-3">
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" placeholder="Nama" required value="{{ old('name',$admin->name) }}" />
                <label for="name">Nama</label>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        
            <!-- Nomor Telepon -->
            <div class="form-floating mb-3">
                <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" placeholder="Nomor Telepon" required value="{{ old('phone',$admin->phone) }}" />
                <label for="phone">Nomor Whatsapp</label>
                @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        
            <!-- Biography -->
            <div class="form-floating mb-3">
                <textarea class="form-control @error('bio') is-invalid @enderror" id="bio" name="bio" placeholder="Biography" style="height: 100px;" required>{{ old('bio', $admin->bio) }}</textarea>
                <label for="bio">Biography</label>
                @error('bio')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        
            <!-- Instagram -->
            <div class="form-floating mb-3">
                <input type="text" class="form-control @error('social.instagram') is-invalid @enderror" id="instagram" name="social[instagram]" placeholder="Instagram" required  value="{{ old('social.instagram', $admin->social['instagram'] ?? '') }}" />
                <label for="instagram">Instagram</label>
                @error('social.instagram')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <!-- Facebook -->
            <div class="form-floating mb-3">
                <input type="text" class="form-control @error('social.facebook') is-invalid @enderror" id="facebook" name="social[facebook]" placeholder="Facebook" required  value="{{ old('social.facebook', $admin->social['facebook'] ?? '') }}" />
                <label for="facebook">Facebook</label>
                @error('social.facebook')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        
            <!-- Youtube -->
            <div class="form-floating mb-3">
                <input type="text" class="form-control @error('social.youtube') is-invalid @enderror" id="youtube" name="social[youtube]" placeholder="Youtube" required  value="{{ old('social.youtube', $admin->social['youtube'] ?? '') }}" />
                <label for="youtube">Youtube</label>
                @error('social.youtube')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        
            <!-- Tiktok -->
            <div class="form-floating mb-3">
                <input type="text" class="form-control @error('social.tiktok') is-invalid @enderror" id="tiktok" name="social[tiktok]" placeholder="Tiktok" required  value="{{ old('social.tiktok', $admin->social['tiktok'] ?? '') }}" />
                <label for="tiktok">Tiktok</label>
                @error('social.tiktok')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            </form>

        </div>
        
        <div class="footer mb-5 text-center">
            <div class="main-menu row col-12 mx-0 justify-content-between d-flex ">
              <button type="button" id="submitForm" class="button w-100 d-flex align-items-center justify-content-center text-white shadow-sm">
                <img src="{{asset('assets/img/icon/edit-profile.svg')}}" alt="Kirim"
                  style="width: 20px; height: 20px; margin-right: 8px;" />
                <span class="text-white">Simpan Data</span>
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
                text: 'Apakah Anda yakin ingin mengganti profile admin galang dana?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Kirim',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#formData').submit();
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

