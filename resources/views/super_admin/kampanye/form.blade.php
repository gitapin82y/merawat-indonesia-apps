@extends('layouts.admin')
 
@section('title', 'Manajemen Kampanye')

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
    <div class="card-header bg-danger py-3 align-items-center justify-content-between row m-0">
        <div class="col-12 col-sm-6 p-0">
            <h4 class="m-0 font-weight-bold float-left text-white">{{ isset($kampanye->id) ? 'Edit Kampanye' : 'Tambah Kampanye Baru' }}</h4>
        </div>
    </div>
        <div class="card-body">
            <form action="{{ isset($kampanye->id) ? route('kampanye.update', $kampanye->id) : route('kampanye.store') }}" 
                method="POST" enctype="multipart/form-data" id="kampanyeForm">
              @csrf
              @if(isset($kampanye->id))
                  @method('PUT')
              @endif
          
              <div class="row">
                  <div class="col-md-6">
                        <div class="form-group mb-3">
                            <select name="admin_id" id="adminSelect" class="form-select select2 @error('admin_id') is-invalid @enderror">
                                <option value="">Pilih Admin</option>
                                @foreach($admins as $admin)
                                    <option value="{{ $admin->id }}" {{ old('admin_id', $kampanye->admin_id ?? '') == $admin->id ? 'selected' : '' }}>
                                        {{ $admin->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('admin_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
          
                      <div class="form-floating mb-3">
                          <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" id="category_id">
                              <option value="">Pilih Kategori</option>
                              @foreach($categories as $category)
                                  <option value="{{ $category->id }}" {{ (old('category_id', $kampanye->category_id ?? '') == $category->id) ? 'selected' : '' }}>
                                      {{ $category->name }}
                                  </option>
                              @endforeach
                          </select>
                          <label for="category_id">Kategori</label>
                          @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                      </div>

                     
                      <div class="form-floating mb-3">
                          <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" id="title"
                              value="{{ old('title', $kampanye->title ?? '') }}" placeholder="Judul">
                          <label for="title">Judul</label>
                          @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                      </div>

                      <div class="form-floating mb-3">
                        <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" id="slug"
                            value="{{ old('slug', $kampanye->slug ?? '') }}" placeholder="Slug URL">
                        <label for="slug">Slug URL (opsional)</label>
                        <small class="form-text text-muted">URL khusus untuk kampanye ini. Jika dikosongkan, sistem akan generate otomatis dari judul. Format: my-campaign-name</small>
                        @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
          
                      <div class="form-floating mb-3">
                          <input type="date" name="deadline" class="form-control @error('deadline') is-invalid @enderror" id="deadline"
                              value="{{ old('deadline', isset($kampanye) && $kampanye->deadline ? $kampanye->deadline->format('Y-m-d') : '') }}">
                          <label for="deadline">Deadline</label>
                          @error('deadline')<div class="invalid-feedback">{{ $message }}</div>@enderror
                      </div>

                      <div class="form-floating mb-3">
                        <input type="text" id="jumlah_target_donasi" name="jumlah_target_donasi" class="form-control @error('jumlah_target_donasi') is-invalid @enderror" value="{{ old('jumlah_target_donasi', $kampanye->jumlah_target_donasi ?? '') }}">
                        <label for="jumlah_target_donasi">Target Donasi</label>
                        @error('jumlah_target_donasi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label>Status</label>
                        <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                            <option value="aktif" {{ old('status', $kampanye->status ?? '') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="selesai" {{ old('status', $kampanye->status ?? '') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                            <option value="ditolak" {{ old('status', $kampanye->status ?? '') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                            <option value="validasi" {{ old('status', $kampanye->status ?? '') == 'validasi' ? 'selected' : '' }}>Validasi</option>
                            {{-- <option value="berakhir" {{ old('status', $kampanye->status ?? '') == 'berakhir' ? 'selected' : '' }}>Berakhir</option> --}}
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
          
                  </div>
          
                  <div class="col-md-6">
                      <div class="mb-3">
                        <label class="form-label">
                            Foto Thumbnail
                            @if(isset($kampanye) && $kampanye->photo)
                                <a href="javascript:void(0)" class="ms-2 text-primary" onclick="previewImage('{{ asset('storage/'.$kampanye->photo) }}', 'Thumbnail')">
                                    <small>(Lihat)</small>
                                </a>
                            @endif
                        </label>
                        <input type="file" id="fileThumbnail" name="photo" class="form-control @error('photo') is-invalid @enderror" accept="image/*">
                        @error('photo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        
                        <!-- Hidden input for cropped data -->
                        <input type="hidden" name="photo_cropped" id="photo_cropped">
                        
                        <!-- Preview container will appear after cropping -->
                        <div id="thumbnailPreviewContainer" class="mt-2" style="display: none;">
                            <label class="form-label">Preview Thumbnail:</label>
                            <img id="croppedThumbnailPreview" src="#" alt="Thumbnail Preview" class="img-fluid rounded" style="max-height: 150px; object-fit: cover;">
                        </div>
                      </div>
          
                      <div class="mb-3">
                        <label class="form-label">
                            Dokumen RAB
                            @if(isset($kampanye) && $kampanye->document_rab)
                                <a href="{{ asset('storage/'.$kampanye->document_rab) }}" class="ms-2 text-primary" target="_blank">
                                    <small>(Lihat)</small>
                                </a>
                            @endif
                        </label>
                        <input type="file" name="document_rab" class="form-control @error('document_rab') is-invalid @enderror" accept=".pdf, .doc, .docx, .xls, .xlsx" id="document_rab" @if(!isset($admin->id)) required @endif>
                        @error('document_rab')<div class="invalid-feedback">{{ $message }}</div>@enderror
                      </div>
          
                      <div class="form-floating mb-3">
                        <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror">{{ old('description', $kampanye->description ?? '') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                  </div>
              </div>
          
              <div class="d-flex gap-2">
                  <button type="submit" class="btn btn-danger">Simpan Kampanye</button>
                  <a href="{{ route('kampanye.index') }}" class="btn btn-secondary">Kembali</a>
              </div>
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
    // Debug helper
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
        // Select2 untuk pencarian admin
        $('#adminSelect').select2({
            width: '100%',
            placeholder: "Pilih Admin",
            allowClear: true
        });

        // Make sure AutoNumeric is properly initialized with explicit variable
        const autoNumericInstance = new AutoNumeric('#jumlah_target_donasi', {
            digitGroupSeparator: '.',
            decimalCharacter: ',',
            currencySymbol: 'Rp ',
            currencySymbolPlacement: 'p',
            unformatOnSubmit: false,
            decimalPlaces: 0
        });

        // Check if initialization worked
        console.log('AutoNumeric initialized:', autoNumericInstance);
        
        // Add an event listener to manually handle the form submission
        $('#kampanyeForm').on('submit', function(e) {
            e.preventDefault();
            const form = this;
            
            Swal.fire({
                title: 'Konfirmasi Simpan Kampanye',
                text: 'Apakah Anda yakin ingin menyimpan kampanye ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Simpan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Manually clean the currency value
                    const targetDonasi = $('#jumlah_target_donasi').val();
                    const cleanValue = targetDonasi.replace(/[^\d,-]/g, '').replace(/\./g, '').replace(',', '.');
                    console.log('Original value:', targetDonasi);
                    console.log('Cleaned value:', cleanValue);
                    
                    // Set the cleaned value
                    $('#jumlah_target_donasi').val(cleanValue);
                    
                    // Submit the form
                    form.submit();
                }
            });
        });
    });
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
@endpush