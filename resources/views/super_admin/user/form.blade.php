@extends('layouts.admin')
 
@section('title', 'Manajemen User')

@push('after-style')

@endpush

@section('content')
<div class="card mb-4">
    <div class="card-header bg-danger py-3 align-items-center justify-content-between row m-0">
        <div class="col-12 col-sm-6 p-0">
            <h4 class="m-0 font-weight-bold float-left text-white">Tambah User Baru</h4>
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
                        <div class="form-group mb-3">
                            <label>Nama Lengkap</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                            value="{{ old('name', $user->name ?? '') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label>Nomor Telepon</label>
                            <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" 
                            value="{{ old('phone', $user->phone ?? '') }}" required>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                            value="{{ old('email', $user->email ?? '') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label>Password</label>
                            <input type="text" name="password" class="form-control @error('password') is-invalid @enderror" 
                            value="{{ old('password', $user->password ?? '') }}" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label>Bio</label>
                            <textarea name="bio" class="form-control @error('bio') is-invalid @enderror" required>{{ old('bio') }}</textarea>
                            @error('bio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>

                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label>Avatar</label>
                            <input type="file" name="avatar" class="form-control @error('avatar') is-invalid @enderror" 
                                   accept="image/*">
                            @error('avatar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label>Thumbnail</label>
                            <input type="file" name="thumbnail" class="form-control @error('thumbnail') is-invalid @enderror" 
                                   accept="image/*">
                            @error('thumbnail')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label>Peran User</label>
                            <select name="role" class="form-control @error('role') is-invalid @enderror" required>
                                <option value="">Pilih Peran</option>
                                    <option value="yayasan" {{ (old('role', $user->role ?? '') == "yayasan") ? 'selected' : '' }}>
                                        Yayasan
                                    </option>
                                    <option value="donatur" {{ (old('role', $user->role ?? '') == "donatur") ? 'selected' : '' }}>
                                        Donatur
                                    </option>
                            </select>                            
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

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
                    <button type="submit" class="btn btn-danger">Simpan User</button>
                    <a href="{{ route('user.index') }}" class="btn btn-secondary">Kembali</a>
                </div>
            </form>
        </div>
</div>
@endsection

@push('after-script')
<script>
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
        $('#userForm').on('submit', function(e) {
            var form = this;
            e.preventDefault();
    
            Swal.fire({
                title: 'Konfirmasi Tambah User',
                text: 'Apakah Anda yakin ingin menambahkan user baru?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Tambahkan',
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