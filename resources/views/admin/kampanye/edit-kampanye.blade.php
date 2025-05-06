@extends('layouts.public')
 
@section('title', 'Edit Kampanye')

@push('after-style')
<link  href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/autonumeric"></script>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
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


    .bg-opacity {
        background-color: rgba(255, 71, 71, 0.1);
    }

    .btn-second {
        background-color: var(--second-color);
        color: var(--second-color);
    }


    .btn-second:hover {
        background-color: var(--bs-danger);
        color: white;
    }

    .form-floating select.form-select {
        padding-left: 21px;
        /* Sesuaikan padding kiri */
        height: calc(3.5rem + 2px);
        /* Sesuaikan tinggi agar sama dengan input */
    }

    .form-control[readonly] {
        color: black !important;
    }
</style>
@endpush

@section('content')

    @include('includes.public.navbar-back', ['title' => 'Edit Kampanye'])

        <!-- Konten -->
        <div class="container mt-4 flex-grow-1">
            <div class="alert alert-light shadow-sm d-flex align-items-center bg-white">
                <img src="{{asset('assets/img/icon/form-data.svg')}}" alt="Info" class="me-3"
                    style="width: 120px; height: 120px;" />
                <div>
                    <h6 class="fw-bold">Terdapat Kesalahan Input?</h6>
                    <p class="mb-0">
                        Anda dapat mengedit atau memperbarui form yang tersedia
                    </p>
                </div>
            </div>

            <form action="{{route('kampanye.update', $kampanye->id)}}" 
            method="POST" enctype="multipart/form-data" id="formData">
          @csrf
          @method('PUT')
          <input type="hidden" name="admin_id" value="{{Auth::user()->admin->id}}">
          <div class="form-floating mb-3">
            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" id="title"
                value="{{ old('title', $kampanye->title ?? '') }}" placeholder="Judul">
            <label for="title">Judul</label>
            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-floating mb-3">
            <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" id="slug"
                value="{{ old('slug', $kampanye->slug ?? '') }}" placeholder="Slug URL (opsional)">
            <label for="slug">Slug URL (opsional)</label>
            <small class="form-text text-muted">Jika kosong, slug akan otomatis dibuat dari judul kampanye. Contoh: my-campaign-name</small>
            @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-floating mb-3">
            <input type="text" id="jumlah_target_donasi" name="jumlah_target_donasi" class="form-control @error('jumlah_target_donasi') is-invalid @enderror" value="{{ old('jumlah_target_donasi', $kampanye->jumlah_target_donasi ?? '') }}">
            <label for="jumlah_target_donasi">Target Donasi</label>
            @error('jumlah_target_donasi')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
            <div class="form-floating mb-3">
                <input type="date" name="deadline" class="form-control @error('deadline') is-invalid @enderror" id="deadline"
                    value="{{ old('deadline', isset($kampanye) && $kampanye->deadline ? $kampanye->deadline->format('Y-m-d') : '') }}">
                <label for="deadline">Deadline</label>
                @error('deadline')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3 position-relative">
                <div class="form-floating">
                    <input type="text" class="form-control bg-white" id="photo"
                        placeholder="Ubah Thumbnail Kampanye?" style="padding-right: 120px; pointer-events: none;"
                        readonly>
                    <label for="photo">Ubah Thumbnail Kampanye?</label>
                </div>
                <input type="file" id="filePhoto" name="photo" class="d-none @error('photo') is-invalid @enderror"
                    onchange="updateInput('photo', this)">
                <button type="button"  class="btn btn-upload position-absolute"
                    style="right: 3px; top: 50%; transform: translateY(-50%); border-radius: 5px;"
                    onclick="event.preventDefault();document.getElementById('filePhoto').click();">Unggah File</button>
            </div>
            <div id="previewContainer" class="mb-3" style="display: none;">
                <label class="form-label">Preview Thumbnail:</label>
                <img id="croppedPreview" src="#" alt="Preview" class="img-fluid rounded" style="max-height: 100px; object-fit: cover;">
            </div>
            @error('photo')
                <div class="invalid-file">{{ $message }}</div>
            @enderror
            <?php
                use App\Models\Category;
                $categories = Category::all();
            ?>
            
            <div class="form-floating mb-3">
                <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" id="category_id" disabled>
                    <option value="">Pilih Kategori</option>
                    {{-- @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ (old('category_id', $kampanye->category_id ?? '') == $category->id) ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach --}}
                </select>
                <label for="category_id">Kategori</label>
            </div>

            <div class="mb-3 position-relative">
                <div class="form-floating">
                    <input type="text" class="form-control" id="laporanRAB" placeholder="Laporan RAB"
                        style="padding-right: 120px; pointer-events: none;background-color:#e9ece;" disabled>
                    <label for="laporanRAB">Laporan RAB</label>
                </div>
                <input type="file" id="fileRAB" name="document_rab" class="d-none" disabled>
                <button type="button" class="btn btn-upload position-absolute"
                    style="right: 3px; top: 50%; transform: translateY(-50%); border-radius: 5px;">Unggah File</button>
            </div>

            <div class="form-floating mb-3">
                <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror">{{ old('description', $kampanye->description ?? '') }}</textarea>
                @error('description')
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
              </a>
            </div>
        </div>

            <!-- Modal Crop -->
<div class="modal fade" id="cropModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Crop Thumbnail Kampanye</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div style="width: 300px; height: 300px; margin: 0 auto;">
                <img id="previewImage" style="max-width: 100%; display: block;" />
              </div>
        </div>
        <div class="modal-footer">
          <button type="button" id="cropButton" class="btn btn-primary">Simpan</button>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('after-script')
<script>
    let cropper;
    
    document.getElementById('filePhoto').addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (!file) return;
    
        const reader = new FileReader();
        reader.onload = function (event) {
            const img = document.getElementById('previewImage');
            img.src = event.target.result;
    
            // Reset & init cropper
            if (cropper) cropper.destroy();
            const cropModal = new bootstrap.Modal(document.getElementById('cropModal'));
            cropModal.show();
    
            document.getElementById('cropModal').addEventListener('shown.bs.modal', function () {
                cropper = new Cropper(img, {
                    aspectRatio: 2 / 1,
                viewMode: 1,
                autoCropArea: 0.8,
                width: 300,
                height: 150, // Aspect ratio 2:1
                minContainerWidth: 300,
                minContainerHeight: 300,
                minCropBoxWidth: 100,
                minCropBoxHeight: 50
                });
            }, { once: true });
        };
        reader.readAsDataURL(file);
    });
    
    document.getElementById('cropButton').addEventListener('click', function () {
    if (!cropper) return;

    cropper.getCroppedCanvas({
        width: 1280,
        height: 720,
    }).toBlob(function (blob) {
        const fileInput = document.getElementById('filePhoto');
        const newFile = new File([blob], "thumbnail.jpg", { type: "image/jpeg" });

        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(newFile);
        fileInput.files = dataTransfer.files;

        // Update input text
        document.getElementById('photo').value = "thumbnail.jpg";

        // Show preview
        const previewURL = URL.createObjectURL(blob);
        document.getElementById('croppedPreview').src = previewURL;
        document.getElementById('previewContainer').style.display = 'block';

        // Tutup modal crop
        const cropModal = bootstrap.Modal.getInstance(document.getElementById('cropModal'));
        cropModal.hide();
    }, 'image/jpeg', 0.9);
});

</script>
<script>
    function updateInput(inputId, fileInput) {
        document.getElementById(inputId).value = fileInput.files[0] ? fileInput.files[0].name : "";
    }

    // Menggunakan format JS yang diberikan
    $(document).ready(function() {
        $('#submitForm').on('click', function(e) {
            e.preventDefault();
            
            Swal.fire({
                title: 'Konfirmasi ubah kampanye',
                text: 'Apakah Anda yakin ingin mengubah informasi kampanye?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Kirim',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    var rupiah = $('#jumlah_target_donasi').val();
                    var clean = rupiah.replace('Rp ', '').replace(/\./g, '').replace(',', '.');
                    $('#jumlah_target_donasi').val(clean);
                    $('#formData').submit();
                }
            });
        });
    });

    new AutoNumeric('#jumlah_target_donasi', {
            digitGroupSeparator: '.',
            decimalCharacter: ',',
            currencySymbol: 'Rp ',
            unformatOnSubmit: true,
            decimalPlaces: 0
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
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
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

