@extends('layouts.public')
 
@section('title', 'Buat Kabar Terbaru')

@push('after-style')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
@endpush

@section('content')

    <div class="navbar-back col-12 align-items-center d-flex">
      <a href="{{ url()->current() == url()->previous() ? url('admin/kampanye/' . $slug . '/kabar-terbaru') : url()->previous() }}" class="bg-white">
          <i class="fa-solid fa-angle-left"></i>
      </a>
      <h1 class="text-white mb-0 ms-2">Buat Kabar Terbaru</h1>
    </div>

    <main class="container mt-3 px-4">
      <div class="lengkapi-data-form">
          <div class="alert alert-light shadow-sm d-flex align-items-center">
            <img src="{{asset('assets/img/icon/form-data.svg')}}" alt="Info" class="me-3" style="width: 120px; height: 120px;" />
            <div>
              <h6 class="fw-bold">Lengkapi Data Form</h6>
              <p class="mb-0">
                  Pastikan Mengisi Data Dengan Transparan dan Lengkap Sesuai Kondisi Terbaru, Data Kabar Pencarian Tidak Bisa Diedit, Jadi Pastikan Data Anda Sudah Benar
              </p>
            </div>
          </div>
    
        <form action="{{ route('kabar-terbaru.store') }}"  method="POST" enctype="multipart/form-data" id="formData" class="pb-5">
          @csrf
          <input type="hidden" name="campaign_id" value="{{$idKampanye}}">
          <div class="form-floating mb-3">
            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" id="title"
                value="{{ old('title') }}" placeholder="Judul">
            <label for="title">Judul</label>
            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
          
          <div class="form-floating mb-3">
            <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
            @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="alert alert-warning">
          Pada deskripsi kabar anda dapat menyertakan bukti foto khususnya ketika memberikan kabar penyaluran dana, agar donatur kampanye lebih percaya
        </div>
        </form>
      </div>
    </main>

    <div class="footer mb-5 text-center">
      <div class="main-menu row col-12 mx-0 justify-content-between d-flex ">
          <button type="button" id="submitForm" class="button w-100 d-flex align-items-center justify-content-center text-white shadow-sm">
          <img src="{{asset('assets/img/icon/edit-profile.svg')}}" alt="Kirim"
            style="width: 20px; height: 20px; margin-right: 8px;" />
          <span class="text-white">Simpan Data dan Kirim Data</span>
        </button>
      </div>
  </div>


@endsection

@push('after-script')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
<script>
    $(document).ready(function() {
        $('#submitForm').on('click', function(e) {
            e.preventDefault();
            
            Swal.fire({
                title: 'Menambahkan Kabar Terbaru',
                text: 'Apakah Anda yakin menambahkan kabar terbaru kampanye ini?',
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

