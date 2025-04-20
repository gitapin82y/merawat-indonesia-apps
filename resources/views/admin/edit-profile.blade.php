@extends('layouts.public')
 
@section('title', 'Edit Profile Galang Dana')

@push('after-style')
<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
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
                <input type="hidden" name="email" value="{{ $admin->email }}">
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
                    onclick="event.preventDefault();document.getElementById('fileThumbnail').click();">Unggah File</button>
            </div>
            @error('thumbnail')
            <div class="invalid-file">{{ $message }}</div>
        @enderror
        <div id="thumbnailPreviewContainer" class="mb-3" style="display: none;">
            <label class="form-label">Preview Thumbnail:</label>
            <img id="croppedThumbnailPreview" src="#" alt="Thumbnail Preview" class="img-fluid rounded" style="max-height: 100px; object-fit: cover;">
        </div>
        
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
                    onclick="event.preventDefault();document.getElementById('fileAvatar').click();">Unggah File</button>
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
                <textarea class="form-control @error('bio') is-invalid @enderror" id="bio" name="bio" placeholder="Biography" style="height: 100px;">{{ old('bio', $admin->bio) }}</textarea>
                <label for="bio">Biography</label>
                @error('bio')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        
            <!-- Instagram -->
            <div class="form-floating mb-3">
                <input type="text" class="form-control @error('social.instagram') is-invalid @enderror" id="instagram" name="social[instagram]" placeholder="Instagram"   value="{{ old('social.instagram', $admin->social['instagram'] ?? '') }}" />
                <label for="instagram">Instagram</label>
                @error('social.instagram')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <!-- Facebook -->
            <div class="form-floating mb-3">
                <input type="text" class="form-control @error('social.facebook') is-invalid @enderror" id="facebook" name="social[facebook]" placeholder="Facebook"   value="{{ old('social.facebook', $admin->social['facebook'] ?? '') }}" />
                <label for="facebook">Facebook</label>
                @error('social.facebook')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        
            <!-- Youtube -->
            <div class="form-floating mb-3">
                <input type="text" class="form-control @error('social.youtube') is-invalid @enderror" id="youtube" name="social[youtube]" placeholder="Youtube"   value="{{ old('social.youtube', $admin->social['youtube'] ?? '') }}" />
                <label for="youtube">Youtube</label>
                @error('social.youtube')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        
            <!-- Tiktok -->
            <div class="form-floating mb-3">
                <input type="text" class="form-control @error('social.tiktok') is-invalid @enderror" id="tiktok" name="social[tiktok]" placeholder="Tiktok"   value="{{ old('social.tiktok', $admin->social['tiktok'] ?? '') }}" />
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


         <!-- Modal Crop Avatar -->
         <div class="modal fade" id="cropAvatarModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Crop Avatar Profil</h5>
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

  <script>
    // Cropper JS Implementation
    let avatarCropper;
    let thumbnailCropper;
    
    // Fungsi untuk Avatar (rasio 1:1)
    document.getElementById('fileAvatar').addEventListener('change', function (e) {
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
            const fileInput = document.getElementById('fileAvatar');
            const newFile = new File([blob], "avatar.jpg", { type: "image/jpeg" });
    
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(newFile);
            fileInput.files = dataTransfer.files;
    
            // Update input text
            document.getElementById('avatarProfil').value = "avatar.jpg";
    
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
  </script>
@endpush

