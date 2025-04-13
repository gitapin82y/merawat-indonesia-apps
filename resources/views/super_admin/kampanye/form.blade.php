@extends('layouts.admin')
 
@section('title', 'Manajemen Kampanye')

@push('after-style')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/css/select2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/autonumeric"></script>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
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
                  </div>
          
                  <div class="col-md-6">
          
                      <div class="mb-3">
                        @if(isset($kampanye) && $kampanye->photo)
                        <label class="photo">Ubah Foto Thumbnail?</label>
                        <a href="javascript:void(0);" class="text-danger lihat-preview" data-image="{{ asset('storage/' . $kampanye->photo) }}">Lihat Foto</a>
                    @else
                        <label class="photo">Foto Thumbnail</label>
                    @endif
                    

                          <input type="file" name="photo" class="form-control @error('photo') is-invalid @enderror" accept="image/*" id="photo">
                          @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                      </div>
          
                      <div class="mb-3">
                        @if(isset($kampanye) && $kampanye->document_rab)
                        <label class="document_rab">Ubah Dokumen RAB?</label>
                        <a href="{{ asset('storage/' . $kampanye->document_rab) }}" target="_blank" class="text-danger">Lihat Dokumen</a>
                        @else
                        <label class="document_rab">Dokumen RAB</label>
                        @endif
                 
                          <input type="file" name="document_rab" class="form-control @error('document_rab') is-invalid @enderror" accept=".pdf, .doc, .docx, .xls, .xlsx" id="document_rab">
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
@endsection

@push('after-script')

<script>
    $(document).ready(function() {
        $('#kampanyeForm').on('submit', function(e) {
            var form = this;
            e.preventDefault();
    
            Swal.fire({
                title: 'Konfirmasi Simpan Kampanye',
                text: 'Apakah Anda yakin ingin menyimpan kampanye ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Simpan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    var rupiah = $('#jumlah_target_donasi').val();
                    var clean = rupiah.replace('Rp ', '').replace(/\./g, '').replace(',', '.');
                    $('#jumlah_target_donasi').val(clean);
                    form.submit();
                }
            });
        });
    });
</script>


<script>
    $(document).ready(function() {
        // Select2 untuk pencarian admin
        $('#adminSelect').select2({
            width: '100%',
            placeholder: "Pilih Admin",
            allowClear: true
        });

        // AutoNumeric untuk format rupiah
        new AutoNumeric('#jumlah_target_donasi', {
            digitGroupSeparator: '.',
            decimalCharacter: ',',
            currencySymbol: 'Rp ',
            unformatOnSubmit: true,
            decimalPlaces: 0
        });
    });
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

<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".lihat-preview").forEach(function(element) {
            element.addEventListener("click", function() {
                let imageUrl = this.getAttribute("data-image");
                Swal.fire({
                    imageUrl: imageUrl,
                    imageAlt: 'Thumbnail',
                    showCloseButton: true,
                    showConfirmButton: false,
                });
            });
        });
    });
</script>
    
@endpush