@extends('layouts.admin')
 
@section('title', 'Manajemen Admin')

@push('after-style')

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
                1 akun user hanya dapat memiliki 1 akun admin yayasan, belum punya akun user(donatur) untuk membuat akun admin? <a href="" class="alert-link">Tambah User</a>
            </div>
            <form action="{{ isset($admin->id) ? route('admin.update', $admin->id) : route('admin.store') }}" 
                    method="POST" enctype="multipart/form-data" id="adminForm">
                  @csrf
                  @if(isset($admin->id))
                      @method('PUT')
                  @endif
              
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label>User</label>
                            <select name="user_id" class="form-control @error('user_id') is-invalid @enderror" required>
                                <option value="">Pilih User</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ (old('user_id', $admin->user_id ?? '') == $user->id) ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>                            
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label>Nama Galang Dana</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                            value="{{ old('name', $admin->name ?? '') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label>Nomor Telepon</label>
                            <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" 
                            value="{{ old('phone', $admin->phone ?? '') }}" required>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label>Nama Pimpinan</label>
                            <input type="text" name="leader_name" class="form-control @error('leader_name') is-invalid @enderror" 
                            value="{{ old('leader_name', $admin->leader_name ?? '') }}" required>
                            @error('leader_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">

                        <div class="form-group mb-3">
                            <label>Legalitas</label>
                            <input type="file" name="legality" class="form-control @error('legality') is-invalid @enderror" 
                                   value="{{ old('legality') }}">
                            @error('legality')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                            value="{{ old('email', $admin->email ?? '') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label>Avatar</label>
                            <input type="file" name="avatar" class="form-control @error('avatar') is-invalid @enderror" 
                                   accept="image/*">
                            @error('avatar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label>Alamat</label>
                            <textarea name="address" class="form-control @error('address') is-invalid @enderror" required>{{ old('address', $admin->address ?? '') }} </textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">

                        <div class="form-group mb-3">
                            <label>Thumbnail</label>
                            <input type="file" name="thumbnail" class="form-control @error('thumbnail') is-invalid @enderror" 
                                   accept="image/*">
                            @error('thumbnail')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label>Status</label>
                            <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                                <option value="menunggu" {{ old('status', $admin->status ?? '') == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                                <option value="disetujui" {{ old('status', $admin->status ?? '') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                                <option value="ditolak" {{ old('status', $admin->status ?? '') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">

                        <div class="form-group mb-3">
                            <label>Media Sosial</label>
                            <div id="social-media-container">
                                <div class="social-media-group d-flex mb-2">
                                    <select name="social[platform][]" class="form-control me-2">
                                        <option value="facebook">Facebook</option>
                                        <option value="instagram">Instagram</option>
                                        <option value="tiktok">Tiktok</option>
                                        <option value="youtube">YouTube</option>
                                    </select>
                                    <input type="text" name="social[url][]" class="form-control" placeholder="URL Media Sosial">
                                    <button type="button" class="btn btn-danger ms-2 remove-social-media">-</button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-primary" id="add-social-media">Tambah Media Sosial</button>
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
@endsection

@push('after-script')
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
    $(document).ready(function() {
        // Tambah Media Sosial Dinamis
        $('#add-social-media').click(function() {
            var newSocialMedia = `
                <div class="social-media-group d-flex mb-2">
                    <select name="social[platform][]" class="form-control me-2">
                        <option value="facebook">Facebook</option>
                        <option value="twitter">Twitter</option>
                        <option value="instagram">Instagram</option>
                        <option value="linkedin">LinkedIn</option>
                        <option value="youtube">YouTube</option>
                    </select>
                    <input type="text" name="social[url][]" class="form-control" placeholder="URL Media Sosial">
                    <button type="button" class="btn btn-danger ms-2 remove-social-media">-</button>
                </div>
            `;
            $('#social-media-container').append(newSocialMedia);
        });
    
        // Hapus Media Sosial
        $(document).on('click', '.remove-social-media', function() {
            $(this).closest('.social-media-group').remove();
        });
    
        // Validasi Form dengan Sweet Alert
        $('#adminForm').on('submit', function(e) {
            var form = this;
            e.preventDefault();
    
            Swal.fire({
                title: 'Konfirmasi Tambah Admin',
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
    </script>
@endpush