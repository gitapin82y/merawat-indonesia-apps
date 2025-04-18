@extends('layouts.admin')
 
@section('title', 'Manajemen User')

@push('after-style')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
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
</style>
@endpush

@section('content')
<div class="card mb-4">
    <div class="card-header bg-danger py-3 align-items-center justify-content-between row m-0">
        <div class="col-12 col-sm-6 p-0">
            <h4 class="m-0 font-weight-bold float-left text-white">{{ isset($user->id) ? 'Edit User' : 'Tambah User Baru' }}</h4>
        </div>
    </div>
        <div class="card-body">
            <form action="{{ isset($user->id) ? route('user.update', $user->id) : route('user.store') }}" 
                    method="POST" enctype="multipart/form-data" id="userForm">
                  @csrf
                  @if(isset($user->id))
                      @method('PUT')
                  @endif
              
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                            placeholder="Nama Lengkap" value="{{ old('name', $user->name ?? '') }}" required>
                            <label for="name">Nama Lengkap</label>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" 
                            placeholder="Nomor Telepon" value="{{ old('phone', $user->phone ?? '') }}" required>
                            <label for="phone">Nomor Telepon</label>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-floating mb-3">
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" 
                            placeholder="Email" value="{{ old('email', $user->email ?? '') }}" required>
                            <label for="email">Email</label>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-floating mb-3">
                            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" 
                            placeholder="Password" value="{{ old('password', $user->password ?? '') }}" required>
                            <label for="password">Password</label>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-floating mb-3">
                            <textarea name="bio" id="bio" class="form-control @error('bio') is-invalid @enderror" 
                            placeholder="Bio">{{ old('bio', $user->bio ?? '') }}</textarea>
                            <label for="bio">Bio</label>
                            @error('bio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Avatar</label>
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

                        <div class="mb-3">
                            <label class="form-label">Thumbnail</label>
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
                            <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                                <option value="" selected disabled>Pilih Peran</option>
                                <option value="yayasan" {{ (old('role', $user->role ?? '') == "yayasan") ? 'selected' : '' }}>
                                    Yayasan
                                </option>
                                <option value="donatur" {{ (old('role', $user->role ?? '') == "donatur") ? 'selected' : '' }}>
                                    Donatur
                                </option>
                            </select>
                            <label for="role">Peran User</label>                        
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Media Sosial</label>
                            
                            <div class="form-floating mb-2">
                                <input type="text" class="form-control @error('social.instagram') is-invalid @enderror" 
                                    id="instagram" name="social[instagram]" placeholder="Instagram" 
                                    value="{{ old('social.instagram', $user->social['instagram'] ?? '') }}" />
                                <label for="instagram">Instagram</label>
                                @error('social.instagram')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-floating mb-2">
                                <input type="text" class="form-control @error('social.facebook') is-invalid @enderror" 
                                    id="facebook" name="social[facebook]" placeholder="Facebook" 
                                    value="{{ old('social.facebook', $user->social['facebook'] ?? '') }}" />
                                <label for="facebook">Facebook</label>
                                @error('social.facebook')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-floating mb-2">
                                <input type="text" class="form-control @error('social.youtube') is-invalid @enderror" 
                                    id="youtube" name="social[youtube]" placeholder="Youtube" 
                                    value="{{ old('social.youtube', $user->social['youtube'] ?? '') }}" />
                                <label for="youtube">Youtube</label>
                                @error('social.youtube')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-floating mb-2">
                                <input type="text" class="form-control @error('social.tiktok') is-invalid @enderror" 
                                    id="tiktok" name="social[tiktok]" placeholder="Tiktok" 
                                    value="{{ old('social.tiktok', $user->social['tiktok'] ?? '') }}" />
                                <label for="tiktok">Tiktok</label>
                                @error('social.tiktok')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-danger">Simpan User</button>
                    <a href="{{ route('user.index') }}" class="btn btn-secondary">Kembali</a>
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
        
    $(document).on('click', '.remove-social-media', function() {
        $(this).closest('.social-media-group').remove();
    });
    
        // Validasi Form dengan Sweet Alert
        $('#userForm').on('submit', function(e) {
            var form = this;
            e.preventDefault();
    
            Swal.fire({
                title: 'Konfirmasi User',
                text: 'Apakah Anda yakin ingin simpan user?',
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
</script>
@endpush