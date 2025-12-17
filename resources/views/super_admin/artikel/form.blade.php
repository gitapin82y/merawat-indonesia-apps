@extends('layouts.admin')

@section('title', isset($article) ? 'Edit Salur Dana' : 'Tambah Salur Dana')
@push('after-style')
<link  href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/autonumeric"></script>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
<style>
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
    <div class="card-header bg-white py-3">
        <h4 class="m-0 font-weight-bold text-danger">{{ isset($article) ? 'Edit' : 'Tambah' }} Salur Dana</h4>
    </div>
    <div class="card-body">
        <form action="{{ isset($article) ? route('artikel.update', $article->id) : route('artikel.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if(isset($article))
                @method('PUT')
            @endif
            <div class="mb-3">
                <label class="form-label">Judul</label>
                <input type="text" name="title" class="required form-control @error('title') is-invalid @enderror" value="{{ old('title', $article->title ?? '') }}" required>
               @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Konten</label>
                <textarea name="content" id="description" class=" form-control @error('description') is-invalid @enderror" rows="5" required>{{ old('content', $article->content ?? '') }}</textarea>
                 @error('content')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
              <div class="col-md-6">
                    <div class="mb-3">
                    <label class="form-label">
                        Foto Thumbnail
                        @if(isset($article) && $article->photo)
                            <a href="javascript:void(0)" class="ms-2 text-primary" onclick="previewImage('{{ asset('storage/'.$article->image) }}', 'Thumbnail')">
                                <small>(Lihat)</small>
                            </a>
                        @endif
                    </label>
                    <input type="file" id="fileThumbnail" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                    @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    
                    <!-- Hidden input for cropped data -->
                    <input type="hidden" name="photo_cropped" id="photo_cropped">
                    
                    <!-- Preview container will appear after cropping -->
                    <div id="thumbnailPreviewContainer" class="mt-2" style="display: none;">
                        <label class="form-label">Preview Thumbnail:</label>
                        <img id="croppedThumbnailPreview" src="#" alt="Thumbnail Preview" class="img-fluid rounded" style="max-height: 150px; object-fit: cover;">
                    </div>
                    

                     @if(isset($article->image))
                        <img src="/storage/{{ $article->image }}" class="img-fluid my-3" style="border-radius:10px;">
                    @endif
                    </div>
            </div>

            <button type="submit" class="btn btn-danger">Simpan</button>
        </form>
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="cancelThumbnailButton">Batal</button>
                <button type="button" id="cropThumbnailButton" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('after-script')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
<script>
      function logStatus(message) {
        console.log(`[CROPPER DEBUG]: ${message}`);
    }
        // Global variables
    let thumbnailCropper = null;
    let thumbnailModal = null;
    
     // Initialize Bootstrap modals
    document.addEventListener('DOMContentLoaded', function() {
        logStatus("DOM loaded");
        thumbnailModal = new bootstrap.Modal(document.getElementById('cropThumbnailModal'));
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

    document.getElementById('cancelThumbnailButton').addEventListener('click', function() {
    logStatus("Cancel thumbnail button clicked");
    if (thumbnailModal) {
        thumbnailModal.hide();
        logStatus("Thumbnail modal hidden");
    }
});

// Event handler untuk tombol silang pada modal thumbnail
document.querySelector('#cropThumbnailModal .btn-close').addEventListener('click', function() {
    logStatus("Close thumbnail button clicked");
    if (thumbnailModal) {
        thumbnailModal.hide();
        logStatus("Thumbnail modal hidden");
    }
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
                    document.getElementById('photo_cropped').value = dataUrl;
                    
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

    // Function to preview existing images
    function previewImage(imageUrl, title) {
        Swal.fire({
            title: title,
            imageUrl: imageUrl,
            imageWidth: 600,
            imageHeight: 300,
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


<script>
    
    $(document).ready(function() {
        // Initialize Summernote
        $('#description').summernote({
            height: 300,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            callbacks: {
                onImageUpload: function(files) {
                    for(let i = 0; i < files.length; i++) {
                        uploadSummernoteImage(files[i], this);
                    }
                }
            }
        });

        function uploadSummernoteImage(file, editor) {
            let formData = new FormData();
            formData.append('file', file);
            formData.append('_token', '{{ csrf_token() }}');
            
            $.ajax({
                url: '{{ route("image.upload") }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(data) {
                    $(editor).summernote('insertImage', data.location);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error(textStatus + ": " + errorThrown);
                    alert('Failed to upload image: ' + errorThrown);
                }
            });
        }
    });
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (validateRequiredFields()) {
            this.submit();
        }
    });
    
    function validateRequiredFields() {
        let isValid = true;
        let errorMessages = [];
        
        // Validasi Judul (required)
        const title = document.querySelector('input[name="title"]').value.trim();
        if (!title) {
            errorMessages.push('• Judul harus diisi');
            isValid = false;
        }
        
        // Validasi Konten (required) 
        const content = $('#description').summernote('code');
        // Hapus semua tag HTML dan whitespace untuk cek konten sebenarnya
        const textContent = content.replace(/<[^>]*>/g, '').replace(/&nbsp;/g, '').trim();
        if (!textContent || textContent === '' || content === '<p><br></p>') {
            errorMessages.push('• Konten harus diisi');
            isValid = false;
        }
        
        // Validasi Gambar (required untuk tambah data)
        const isEditMode = document.querySelector('input[name="_method"]');
        const imageInput = document.querySelector('input[name="image"]');
        const hasExistingImage = document.querySelector('img[src*="/storage/"]');
        
        if (!isEditMode && (!imageInput.files || imageInput.files.length === 0)) {
            errorMessages.push('• Foto thumbnail harus dipilih');
            isValid = false;
        }
        
        // Tampilkan SweetAlert jika ada field yang kosong
        if (!isValid) {
            Swal.fire({
                icon: 'error',
                title: 'Form Belum Lengkap!',
                html: errorMessages.join('<br>'),
                confirmButtonText: 'OK',
                confirmButtonColor: '#dc3545'
            });
        }
        
        return isValid;
    }
});
</script>
@endpush
