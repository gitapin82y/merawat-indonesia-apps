@extends('layouts.admin')
 
@section('title', 'Tambah Kabar Terbaru')

@push('after-style')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
@endpush

@section('content')
<div class="card mb-4">
    <div class="card-header bg-danger py-3 align-items-center justify-content-between row m-0">
        <div class="col-12 col-sm-6 p-0">
            <h4 class="m-0 font-weight-bold float-left text-white">Tambah Kabar Terbaru</h4>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('kabar-terbaru.store') }}" method="POST" id="kabarForm">
            @csrf
            
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group mb-3">
                        <label>Pilih Kampanye <span class="text-danger">*</span></label>
                        <select name="campaign_id" id="select2" class="form-control @error('campaign_id') is-invalid @enderror" required>
                            <option value="">Pilih Kampanye</option>
                            @foreach($campaigns as $campaign)
                                <option value="{{ $campaign->id }}" {{ old('campaign_id') == $campaign->id ? 'selected' : '' }}>
                                    {{ $campaign->title }} - {{ $campaign->admin->name }}
                                </option>
                            @endforeach
                        </select>                            
                        @error('campaign_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label>Judul <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                               value="{{ old('title') }}" placeholder="Masukkan judul kabar terbaru" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label>Deskripsi <span class="text-danger">*</span></label>
                        <textarea class="form-control summernote @error('description') is-invalid @enderror" 
                                  name="description" required>{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-danger">Simpan Kabar Terbaru</button>
                <a href="{{ route('kabar-terbaru.index') }}" class="btn btn-secondary">Kembali</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('after-script')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2
    $('#select2').select2({
        placeholder: 'Pilih Kampanye',
        allowClear: true,
        width: '100%',
    });

    // Initialize Summernote
    $('.summernote').summernote({
        height: 300,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['fontname', ['fontname']],
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
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Upload Gambar',
                    text: 'Terjadi kesalahan saat upload gambar',
                    timer: 3000,
                    showConfirmButton: false
                });
            }
        });
    }

    // Success/Error Messages
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session('success') }}',
            timer: 3000,
            showConfirmButton: false
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: '{{ session('error') }}',
            timer: 3000,
            showConfirmButton: false
        });
    @endif
});
</script>
@endpush