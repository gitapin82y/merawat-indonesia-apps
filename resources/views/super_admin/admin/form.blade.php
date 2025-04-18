@extends('layouts.admin')
 
@section('title', 'Manajemen Admin')

@push('after-style')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
     .select2-container--bootstrap-5 .select2-selection {
        min-height: 58px;
        padding-top: 1.625rem;
        padding-bottom: 0.625rem;
    }
    
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        padding-left: 0;
        padding-top: 0.3rem;
    }
    
    .form-floating > .select2-container {
        width: 100% !important;
    }

    /* Form floating styles */
    .form-floating > .form-control {
        padding-top: 1.625rem;
        padding-bottom: 0.625rem;
    }
    
    .form-floating > .form-control-plaintext ~ label,
    .form-floating > .form-control:focus ~ label,
    .form-floating > .form-control:not(:placeholder-shown) ~ label {
        opacity: 0.65;
        transform: scale(0.85) translateY(-0.5rem) translateX(0.15rem);
    }
    
    .form-floating > .form-select {
        padding-top: 1.625rem;
        padding-bottom: 0.625rem;
    }
    
    .form-floating > textarea.form-control {
        height: 100px;
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
    
    .form-control[readonly] {
        color: black !important;
    }
    
    /* Fix for cropper modal */
    .modal {
        z-index: 1050 !important;
    }
    
    .cropper-container {
        z-index: 1060 !important;
    }
    
    .modal-backdrop {
        z-index: 1040 !important;
    }
</style>
@endpush

@section('content')
<div class="card mb-4">
    <div class="card-header bg-danger py-3 align-items-center justify-content-between row m-0">
        <div class="col-12 col-sm-6 p-0">
            <h4 class="m-0 font-weight-bold float-left text-white">{{ isset($admin->id) ? 'Edit Admin' : 'Tambah Admin Baru' }}</h4>
        </div>
    </div>
        <div class="card-body">
            <div class="alert alert-warning" role="alert">
                1 akun user hanya dapat memiliki 1 akun admin yayasan, belum punya akun user(donatur) untuk membuat akun admin? <a href="{{route('user.index')}}" class="alert-link">Tambah User</a>
            </div>
            <form action="{{ isset($admin->id) ? route('admin.update', $admin->id) : route('admin.store') }}" 
                    method="POST" enctype="multipart/form-data" id="adminForm">
                  @csrf
                  @if(isset($admin->id))
                      @method('PUT')
                  @endif
              
                <div class="row">
                    <div class="col-md-6">
                        <!-- Replace the user select with this -->
                    <div class="form-floating mb-3">
                        <select name="user_id" id="user_id" class="select2 form-select @error('user_id') is-invalid @enderror" required>
                            <option value="" selected disabled>Pilih User</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ (old('user_id', $admin->user_id ?? '') == $user->id) ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>       
                        <label for="user_id">User</label>                     
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                        <div class="form-floating mb-3">
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                            placeholder="Nama Galang Dana" value="{{ old('name', $admin->name ?? '') }}" required>
                            <label for="name">Nama Galang Dana</label>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" 
                            placeholder="Nomor Telepon" value="{{ old('phone', $admin->phone ?? '') }}" required>
                            <label for="phone">Nomor Telepon</label>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" name="leader_name" id="leader_name" class="form-control @error('leader_name') is-invalid @enderror" 
                            placeholder="Nama Pimpinan" value="{{ old('leader_name', $admin->leader_name ?? '') }}" required>
                            <label for="leader_name">Nama Pimpinan</label>
                            @error('leader_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-floating mb-3">
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" 
                            placeholder="Email" value="{{ old('email', $admin->email ?? '') }}" required>
                            <label for="email">Email</label>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">
                                Thumbnail
                                @if(isset($admin->id) && $admin->thumbnail)
                                    <a href="javascript:void(0)" class="ms-2 text-primary" onclick="previewImage('{{ asset('storage/'.$admin->thumbnail) }}', 'Thumbnail')">
                                        <small>(Lihat)</small>
                                    </a>
                                @endif
                            </label>
                            <input type="file" id="fileThumbnail" name="thumbnail" class="form-control @error('thumbnail') is-invalid @enderror" 
                                accept="image/*">
                            @error('thumbnail')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            
                            <!-- Hidden input for cropped data -->
                            <input type="hidden" name="thumbnail_cropped" id="thumbnail_cropped">
                            
                            <!-- Preview container will appear after cropping -->
                            <div id="thumbnailPreviewContainer" class="mt-2" style="display: none;">
                                <label class="form-label">Preview Thumbnail:</label>
                                <img id="croppedThumbnailPreview" src="#" alt="Thumbnail Preview" class="img-fluid rounded" style="max-height: 100px; object-fit: cover;">
                            </div>
                        </div>
                        
                        <div class="form-floating mb-3">
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="" disabled>Pilih Status</option>
                                <option value="menunggu" {{ old('status', $admin->status ?? '') == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                                <option value="disetujui" {{ old('status', $admin->status ?? '') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                                <option value="ditolak" {{ old('status', $admin->status ?? '') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                            </select>
                            <label for="status">Status</label>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">
                                Legalitas
                                @if(isset($admin->id) && $admin->legality)
                                    <a href="{{ asset('storage/'.$admin->legality) }}" class="ms-2 text-primary" target="_blank">
                                        <small>(Lihat Dokumen)</small>
                                    </a>
                                @endif
                            </label>
                            <input type="file" name="legality" class="form-control @error('legality') is-invalid @enderror" 
                                    value="{{ old('legality') }}">
                            @error('legality')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Avatar
                                @if(isset($admin->id) && $admin->avatar)
                                    <a href="javascript:void(0)" class="ms-2 text-primary" onclick="previewImage('{{ asset('storage/'.$admin->avatar) }}', 'Avatar')">
                                        <small>(Lihat)</small>
                                    </a>
                                @endif
                            </label>
                            <input type="file" id="fileAvatar" name="avatar" class="form-control @error('avatar') is-invalid @enderror" 
                                accept="image/*">
                            @error('avatar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            
                            <!-- Hidden input for cropped data -->
                            <input type="hidden" name="avatar_cropped" id="avatar_cropped">
                            
                            <!-- Preview container will appear after cropping -->
                            <div id="avatarPreviewContainer" class="mt-2" style="display: none;">
                                <label class="form-label">Preview Avatar:</label>
                                <div style="width: 100px; height: 100px; overflow: hidden; border-radius: 50%;">
                                    <img id="croppedAvatarPreview" src="#" alt="Avatar Preview" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                            </div>
                        </div>

                        <div class="form-floating mb-3">
                            <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror" 
                            placeholder="Alamat" required>{{ old('address', $admin->address ?? '') }}</textarea>
                            <label for="address">Alamat</label>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Media Sosial</label>
                            
                            <div class="form-floating mb-2">
                                <input type="text" class="form-control @error('social.instagram') is-invalid @enderror" 
                                    id="instagram" name="social[instagram]" placeholder="Instagram" 
                                    value="{{ old('social.instagram', $admin->social['instagram'] ?? '') }}" />
                                <label for="instagram">Instagram</label>
                                @error('social.instagram')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-floating mb-2">
                                <input type="text" class="form-control @error('social.facebook') is-invalid @enderror" 
                                    id="facebook" name="social[facebook]" placeholder="Facebook" 
                                    value="{{ old('social.facebook', $admin->social['facebook'] ?? '') }}" />
                                <label for="facebook">Facebook</label>
                                @error('social.facebook')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-floating mb-2">
                                <input type="text" class="form-control @error('social.youtube') is-invalid @enderror" 
                                    id="youtube" name="social[youtube]" placeholder="Youtube" 
                                    value="{{ old('social.youtube', $admin->social['youtube'] ?? '') }}" />
                                <label for="youtube">Youtube</label>
                                @error('social.youtube')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-floating mb-2">
                                <input type="text" class="form-control @error('social.tiktok') is-invalid @enderror" 
                                    id="tiktok" name="social[tiktok]" placeholder="Tiktok" 
                                    value="{{ old('social.tiktok', $admin->social['tiktok'] ?? '') }}" />
                                <label for="tiktok">Tiktok</label>
                                @error('social.tiktok')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

              

                <div class="form-group">
                    <button type="submit" class="btn btn-danger">Simpan Admin</button>
                    <a href="{{ route('admin.index') }}" class="btn btn-secondary">Kembali</a>
                </div>
            </form>
        </div>
</div>

<!-- Modal Crop Avatar -->
<div class="modal fade" id="cropAvatarModal" tabindex="-1" aria-labelledby="cropAvatarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cropAvatarModalLabel">Crop Avatar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div style="max-width: 100%; margin: 0 auto;">
                    <img id="previewAvatar" src="" style="display: block; max-width: 100%;" />
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" id="cropAvatarButton" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Crop Thumbnail -->
<div class="modal fade" id="cropThumbnailModal" tabindex="-1" aria-labelledby="cropThumbnailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cropThumbnailModalLabel">Crop Thumbnail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div style="max-width: 100%; margin: 0 auto;">
                    <img id="previewThumbnail" src="" style="display: block; max-width: 100%;" />
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" id="cropThumbnailButton" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('after-script')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Add these scripts in your after-script section -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: "Pilih User",
            allowClear: true,
            dropdownParent: $('#user_id').parent()
        });
        
        // Fix for Select2 with form-floating (adjust label position)
        $('.select2').on('select2:open', function() {
            $(this).parent().find('label').addClass('form-floating-select-label');
        });
    });
</script>
<script>
    // Debug helper
    function logStatus(message) {
        console.log(`[CROPPER DEBUG]: ${message}`);
    }

    // Global variables
    let avatarCropper = null;
    let thumbnailCropper = null;
    let avatarModal = null;
    let thumbnailModal = null;
    
    // Initialize Bootstrap modals
    document.addEventListener('DOMContentLoaded', function() {
        logStatus("DOM loaded");
        avatarModal = new bootstrap.Modal(document.getElementById('cropAvatarModal'));
        thumbnailModal = new bootstrap.Modal(document.getElementById('cropThumbnailModal'));
    });
    
    // Avatar Cropping (ratio 1:1)
    document.getElementById('fileAvatar').addEventListener('change', function(e) {
        logStatus("Avatar file selected");
        const file = e.target.files[0];
        if (!file) {
            logStatus("No file selected");
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(event) {
            logStatus("File read successfully");
            const imgElement = document.getElementById('previewAvatar');
            imgElement.src = event.target.result;
            
            // Show the modal
            if (avatarModal) {
                avatarModal.show();
                logStatus("Avatar modal shown");
                
                // Destroy previous cropper if exists
                if (avatarCropper) {
                    avatarCropper.destroy();
                    logStatus("Previous avatar cropper destroyed");
                }
                
                // Initialize cropper with a delay to ensure modal is visible
                setTimeout(() => {
                    avatarCropper = new Cropper(imgElement, {
                        aspectRatio: 1,
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 0.8,
                        responsive: true,
                        restore: false,
                        guides: true,
                        center: true,
                        highlight: false,
                        cropBoxMovable: true,
                        cropBoxResizable: true,
                        toggleDragModeOnDblclick: false,
                        ready: function() {
                            logStatus("Avatar cropper initialized");
                        }
                    });
                }, 500);
            } else {
                logStatus("ERROR: Avatar modal is not initialized");
                alert("Modal tidak tersedia. Mohon muat ulang halaman.");
            }
        };
        reader.readAsDataURL(file);
    });
    
    // Crop Avatar Button Click
    document.getElementById('cropAvatarButton').addEventListener('click', function() {
        logStatus("Crop avatar button clicked");
        
        if (!avatarCropper) {
            logStatus("ERROR: Avatar cropper instance not available");
            return;
        }
        
        try {
            // Get cropped canvas
            const canvas = avatarCropper.getCroppedCanvas({
                width: 300,
                height: 300,
                minWidth: 100,
                minHeight: 100,
                maxWidth: 1000,
                maxHeight: 1000,
                fillColor: '#fff',
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            });
            
            logStatus("Canvas generated successfully");
            
            // Convert to blob
            canvas.toBlob(function(blob) {
                logStatus("Blob created: " + (blob ? "success" : "failure"));
                
                if (blob) {
                    // Create file from blob
                    const croppedFile = new File([blob], "avatar.jpg", { type: "image/jpeg" });
                    
                    // Update file input
                    const fileInput = document.getElementById('fileAvatar');
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(croppedFile);
                    fileInput.files = dataTransfer.files;
                    
                    // Store data URL in hidden input
                    const dataUrl = canvas.toDataURL('image/jpeg');
                    document.getElementById('avatar_cropped').value = dataUrl;
                    
                    // Show preview
                    document.getElementById('croppedAvatarPreview').src = URL.createObjectURL(blob);
                    document.getElementById('avatarPreviewContainer').style.display = 'block';
                    
                    logStatus("Avatar preview updated");
                }
                
                // Hide modal
                if (avatarModal) {
                    avatarModal.hide();
                    logStatus("Avatar modal hidden");
                }
            }, 'image/jpeg', 0.9);
        } catch (error) {
            logStatus("ERROR during avatar cropping: " + error.message);
            console.error(error);
        }
    });
    
    // Thumbnail Cropping (ratio 2:1)
    document.getElementById('fileThumbnail').addEventListener('change', function(e) {
        logStatus("Thumbnail file selected");
        const file = e.target.files[0];
        if (!file) {
            logStatus("No file selected");
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(event) {
            logStatus("File read successfully");
            const imgElement = document.getElementById('previewThumbnail');
            imgElement.src = event.target.result;
            
            // Show the modal
            if (thumbnailModal) {
                thumbnailModal.show();
                logStatus("Thumbnail modal shown");
                
                // Destroy previous cropper if exists
                if (thumbnailCropper) {
                    thumbnailCropper.destroy();
                    logStatus("Previous thumbnail cropper destroyed");
                }
                
                // Initialize cropper with a delay to ensure modal is visible
                setTimeout(() => {
                    thumbnailCropper = new Cropper(imgElement, {
                        aspectRatio: 2 / 1,
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 0.8,
                        responsive: true,
                        restore: false,
                        guides: true,
                        center: true,
                        highlight: false,
                        cropBoxMovable: true,
                        cropBoxResizable: true,
                        toggleDragModeOnDblclick: false,
                        ready: function() {
                            logStatus("Thumbnail cropper initialized");
                        }
                    });
                }, 500);
            } else {
                logStatus("ERROR: Thumbnail modal is not initialized");
                alert("Modal tidak tersedia. Mohon muat ulang halaman.");
            }
        };
        reader.readAsDataURL(file);
    });
    
    // Crop Thumbnail Button Click
    document.getElementById('cropThumbnailButton').addEventListener('click', function() {
        logStatus("Crop thumbnail button clicked");
        
        if (!thumbnailCropper) {
            logStatus("ERROR: Thumbnail cropper instance not available");
            return;
        }
        
        try {
            // Get cropped canvas
            const canvas = thumbnailCropper.getCroppedCanvas({
                width: 600,
                height: 300,
                minWidth: 100,
                minHeight: 50,
                maxWidth: 2000,
                maxHeight: 1000,
                fillColor: '#fff',
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            });
            
            logStatus("Canvas generated successfully");
            
            // Convert to blob
            canvas.toBlob(function(blob) {
                logStatus("Blob created: " + (blob ? "success" : "failure"));
                
                if (blob) {
                    // Create file from blob
                    const croppedFile = new File([blob], "thumbnail.jpg", { type: "image/jpeg" });
                    
                    // Update file input
                    const fileInput = document.getElementById('fileThumbnail');
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(croppedFile);
                    fileInput.files = dataTransfer.files;
                    
                    // Store data URL in hidden input
                    const dataUrl = canvas.toDataURL('image/jpeg');
                    document.getElementById('thumbnail_cropped').value = dataUrl;
                    
                    // Show preview
                    document.getElementById('croppedThumbnailPreview').src = URL.createObjectURL(blob);
                    document.getElementById('thumbnailPreviewContainer').style.display = 'block';
                    
                    logStatus("Thumbnail preview updated");
                }
                
                // Hide modal
                if (thumbnailModal) {
                    thumbnailModal.hide();
                    logStatus("Thumbnail modal hidden");
                }
            }, 'image/jpeg', 0.9);
        } catch (error) {
            logStatus("ERROR during thumbnail cropping: " + error.message);
            console.error(error);
        }
    });
</script>

<script>
    $(document).ready(function() {
        // Sweet Alert Notifications
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
        
        // Validasi Form dengan Sweet Alert
        $('#adminForm').on('submit', function(e) {
            var form = this;
            e.preventDefault();
    
            Swal.fire({
                title: 'Konfirmasi Perubahan Admin',
                text: 'Apakah anda yakin ingin menyimpan data?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Simpan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

    // Add this to your JavaScript section
function previewImage(imageUrl, title) {
    Swal.fire({
        title: title,
        imageUrl: imageUrl,
        imageWidth: 400,
        imageHeight: title === 'Thumbnail' ? 200 : 400,
        imageAlt: title,
        confirmButtonText: 'Tutup',
        showClass: {
            popup: 'animate__animated animate__fadeInDown'
        },
        hideClass: {
            popup: 'animate__animated animate__fadeOutUp'
        }
    });
}
</script>
@endpush