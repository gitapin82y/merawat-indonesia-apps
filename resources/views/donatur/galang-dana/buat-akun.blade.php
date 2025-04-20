@extends('layouts.public')
 
@section('title', 'Buat Galang Dana')

@push('after-style')
<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
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
    
    .invalid-file {
      width: 100%;
      margin-top: 0.25rem;
      font-size: 0.875em;
      color: #dc3545;
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
              onclick="event.preventDefault();document.getElementById('fileLegalitas').click();">Unggah File</button>
          </div>
          @error('legality')
            <div class="invalid-file">{{ $message }}</div>
          @enderror

          <div class="mb-3 position-relative">
            <div class="form-floating ">
              <input type="text" required class="form-control bg-white @error('thumbnail') is-invalid @enderror" id="thumbnailProfil"
                placeholder="Thumbnail Profil Galang Dana" style="padding-right: 120px; pointer-events: none;" readonly>
              <label for="thumbnailProfil">Thumbnail Profil Galang Dana</label>
            </div>
            <input type="file" required id="fileThumbnail" name="thumbnail" class="d-none @error('thumbnail') is-invalid @enderror" onchange="updateInput('thumbnailProfil', this)">
            <button type="button" class="btn btn-upload position-absolute"
              style="right: 3px; top: 50%; transform: translateY(-50%); border-radius: 5px;"
              onclick="event.preventDefault();document.getElementById('fileThumbnail').click();">Unggah File</button>
          </div>
          @error('thumbnail')
            <div class="invalid-file">{{ $message }}</div>
          @enderror
          <div id="thumbnailPreviewContainer" class="mb-3" style="display: none;">
            <label class="form-label">Preview Thumbnail:</label>
            <img id="croppedThumbnailPreview" src="#" alt="Thumbnail Preview" class="img-fluid rounded" style="max-height: 100px; object-fit: cover;">
          </div>

          <div class="mb-3 position-relative">
            <div class="form-floating">
              <input type="text" required class="form-control bg-white @error('avatar') is-invalid @enderror" id="fotoProfil" placeholder="Foto Profil Galang Dana"
                style="padding-right: 120px; pointer-events: none;" readonly>
              <label for="fotoProfil">Foto Profil Galang Dana</label>
            </div>
            <input type="file" required id="fileFoto" name="avatar" class="d-none @error('avatar') is-invalid @enderror" onchange="updateInput('fotoProfil', this)">
            <button type="button" class="btn btn-upload position-absolute"
              style="right: 3px; top: 50%; transform: translateY(-50%); border-radius: 5px;"
              onclick="event.preventDefault();document.getElementById('fileFoto').click();">Unggah File</button>
          </div>
          @error('avatar')
            <div class="invalid-file">{{ $message }}</div>
          @enderror
          <div id="avatarPreviewContainer" class="mb-3" style="display: none;">
            <div class="d-flex align-items-center">
              <label class="form-label">Preview Avatar:</label>
              <div style="width: 100px; height: 100px; overflow: hidden; border-radius: 50%;">
                <img id="croppedAvatarPreview" src="#" alt="Avatar Preview" style="width: 100%; height: 100%; object-fit: cover;">
              </div>
            </div>
          </div>
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

      <!-- Modal Crop Avatar -->
      <div class="modal fade" id="cropAvatarModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Crop Foto Profil</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <!-- Container dengan ukuran tetap 300px -->
              <div style="width: 300px; height: 300px; margin: 0 auto;">
                <img id="previewAvatar" style="max-width: 100%; display: block;" />
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" id="cropAvatarButton" class="btn btn-primary">Simpan</button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Modal Crop Thumbnail -->
      <div class="modal fade" id="cropThumbnailModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Crop Thumbnail Profil</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <!-- Container dengan ukuran tetap 300px -->
              <div style="width: 300px; height: 300px; margin: 0 auto;">
                <img id="previewThumbnail" style="max-width: 100%; display: block;" />
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" id="cropThumbnailButton" class="btn btn-primary">Simpan</button>
            </div>
          </div>
        </div>
      </div>

@endsection

@push('after-script')

<script>
    function updateInput(inputId, fileInput) {
      document.getElementById(inputId).value = fileInput.files[0] ? fileInput.files[0].name : "";
    }

    // Cropper untuk Avatar dan Thumbnail
    let avatarCropper;
    let thumbnailCropper;
    
    // Fungsi untuk Avatar (rasio 1:1)
    document.getElementById('fileFoto').addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (!file) return;
    
        const reader = new FileReader();
        reader.onload = function (event) {
            const img = document.getElementById('previewAvatar');
            img.src = event.target.result;
    
            // Reset & init cropper
            if (avatarCropper) avatarCropper.destroy();
            const cropModal = new bootstrap.Modal(document.getElementById('cropAvatarModal'));
            cropModal.show();
    
            document.getElementById('cropAvatarModal').addEventListener('shown.bs.modal', function () {
                avatarCropper = new Cropper(img, {
                    aspectRatio: 1 / 1, // Rasio 1:1 untuk avatar bulat
                    viewMode: 1,
                    autoCropArea: 0.8,
                    width: 300,
                    height: 300,
                    minContainerWidth: 300,
                    minContainerHeight: 300,
                    minCropBoxWidth: 100,
                    minCropBoxHeight: 100
                });
            }, { once: true });
        };
        reader.readAsDataURL(file);
    });
    
    document.getElementById('cropAvatarButton').addEventListener('click', function () {
        if (!avatarCropper) return;
    
        avatarCropper.getCroppedCanvas({
            width: 300,
            height: 300, // Ukuran 1:1
        }).toBlob(function (blob) {
            const fileInput = document.getElementById('fileFoto');
            const newFile = new File([blob], "avatar.jpg", { type: "image/jpeg" });
    
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(newFile);
            fileInput.files = dataTransfer.files;
    
            // Update input text
            document.getElementById('fotoProfil').value = "avatar.jpg";
    
            // Show preview
            const previewURL = URL.createObjectURL(blob);
            document.getElementById('croppedAvatarPreview').src = previewURL;
            document.getElementById('avatarPreviewContainer').style.display = 'block';
    
            // Tutup modal crop
            const cropModal = bootstrap.Modal.getInstance(document.getElementById('cropAvatarModal'));
            cropModal.hide();
        }, 'image/jpeg', 0.9);
    });
    
    // Fungsi untuk Thumbnail (rasio 2:1)
    document.getElementById('fileThumbnail').addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (!file) return;
    
        const reader = new FileReader();
        reader.onload = function (event) {
            const img = document.getElementById('previewThumbnail');
            img.src = event.target.result;
    
            // Reset & init cropper
            if (thumbnailCropper) thumbnailCropper.destroy();
            const cropModal = new bootstrap.Modal(document.getElementById('cropThumbnailModal'));
            cropModal.show();
    
            document.getElementById('cropThumbnailModal').addEventListener('shown.bs.modal', function () {
                thumbnailCropper = new Cropper(img, {
                    aspectRatio: 2 / 1, // Rasio 2:1 untuk thumbnail
                    viewMode: 1,
                    autoCropArea: 0.8,
                    width: 300,
                    height: 150, // Sesuai dengan aspect ratio 2:1
                    minContainerWidth: 300,
                    minContainerHeight: 300,
                    minCropBoxWidth: 100,
                    minCropBoxHeight: 50
                });
            }, { once: true });
        };
        reader.readAsDataURL(file);
    });
    
    document.getElementById('cropThumbnailButton').addEventListener('click', function () {
        if (!thumbnailCropper) return;
    
        thumbnailCropper.getCroppedCanvas({
            width: 600,
            height: 300, // Ukuran dengan aspect ratio 2:1
        }).toBlob(function (blob) {
            const fileInput = document.getElementById('fileThumbnail');
            const newFile = new File([blob], "thumbnail.jpg", { type: "image/jpeg" });
    
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(newFile);
            fileInput.files = dataTransfer.files;
    
            // Update input text
            document.getElementById('thumbnailProfil').value = "thumbnail.jpg";
    
            // Show preview
            const previewURL = URL.createObjectURL(blob);
            document.getElementById('croppedThumbnailPreview').src = previewURL;
            document.getElementById('thumbnailPreviewContainer').style.display = 'block';
    
            // Tutup modal crop
            const cropModal = bootstrap.Modal.getInstance(document.getElementById('cropThumbnailModal'));
            cropModal.hide();
        }, 'image/jpeg', 0.9);
    });

    // Validasi form sebelum submit
    $(document).ready(function() {
        $('#submitForm').on('click', function(e) {
            e.preventDefault();
            
            // Cek apakah file-file wajib sudah diisi
            const legalityFile = document.getElementById('fileLegalitas').files.length;
            const thumbnailFile = document.getElementById('fileThumbnail').files.length;
            const avatarFile = document.getElementById('fileFoto').files.length;
            
            // Array untuk pesan kesalahan
            let errors = [];
            
            if (!legalityFile) {
                errors.push('File Legalitas Organisasi wajib diunggah');
            }
            
            if (!thumbnailFile) {
                errors.push('File Thumbnail Profil wajib diunggah');
            }
            
            if (!avatarFile) {
                errors.push('File Foto Profil wajib diunggah');
            }
            
            if (errors.length > 0) {
                // Tampilkan pesan kesalahan dengan SweetAlert
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    html: 'Lengkapi semua input dan upload file',
                    timer: 3000
                });
                return;
            }
            
            // Jika validasi berhasil, tampilkan konfirmasi
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