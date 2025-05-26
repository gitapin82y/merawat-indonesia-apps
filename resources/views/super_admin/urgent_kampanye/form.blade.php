@extends('layouts.admin')
 
@section('title', 'Manajemen Pilihan Kampanye')

@push('after-style')
<style>
    .prioritas-badge {
        display: inline-block;
        padding: 3px 8px;
        margin: 2px;
        border-radius: 4px;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
    }
    
    .prioritas-badge.used {
        background-color: #dc3545;
        color: white;
        text-decoration: line-through;
    }
</style>
@endpush

@section('content')
<div class="card mb-4">
    <div class="card-header bg-danger py-3 align-items-center justify-content-between row m-0">
        <div class="col-12 col-sm-6 p-0">
            <h4 class="m-0 font-weight-bold float-left text-white">{{ isset($urgentKampanye->id) ? 'Edit Pilihan Kampanye' : 'Tambah Pilihan Kampanye' }}</h4>
        </div>
    </div>
    <div class="card-body">
        @if(count($campaigns) == 0)
            <div class="alert alert-warning">
                Semua kampanye sudah berada dalam daftar prioritas. <a href="{{ route('urgent-kampanye.index') }}">Kembali ke daftar</a>
            </div>
        @else
            <form action="{{ isset($urgentKampanye->id) ? route('urgent-kampanye.update', $urgentKampanye->id) : route('urgent-kampanye.store') }}" 
                    method="POST" enctype="multipart/form-data" id="kampanyeForm">
                @csrf
                @if(isset($urgentKampanye->id))
                    @method('PUT')
                @endif
              
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label>Kampanye</label>
                            <select name="campaign_id" class="form-control @error('campaign_id') is-invalid @enderror" required>
                                <option value="">Pilih Kampanye</option>
                                @foreach($campaigns as $campaign)
                                    <option value="{{ $campaign->id }}" {{ (old('campaign_id', $urgentKampanye->campaign_id ?? '') == $campaign->id) ? 'selected' : '' }}>
                                        {{ $campaign->title }}
                                    </option>
                                @endforeach
                            </select>                            
                            @error('campaign_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label>Prioritas</label>
                            <select name="prioritas" class="form-control @error('prioritas') is-invalid @enderror" required>
                                <option value="">Pilih Prioritas</option>
                                @for ($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}" 
                                        {{ (old('prioritas', $urgentKampanye->prioritas ?? '') == $i) ? 'selected' : '' }}
                                        {{ isset($usedPriorities) && in_array($i, $usedPriorities) ? 'disabled' : '' }}>
                                        {{ $i }}{{ isset($usedPriorities) && in_array($i, $usedPriorities) ? ' (Sudah digunakan)' : '' }}
                                    </option>
                                @endfor
                            </select>                            
                            @error('prioritas')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mt-3 mb-4">
                            <p><strong>Status Prioritas:</strong></p>
                            <div class="d-flex flex-wrap">
                                @for ($i = 1; $i <= 10; $i++)
                                    <span class="prioritas-badge {{ isset($usedPriorities) && in_array($i, $usedPriorities) ? 'used' : '' }}">
                                        {{ $i }}
                                    </span>
                                @endfor
                            </div>
                            <small class="text-muted">Nomor yang dicoret telah digunakan</small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-danger">Simpan Pilihan Kampanye</button>
                    <a href="{{ route('urgent-kampanye.index') }}" class="btn btn-secondary">Kembali</a>
                </div>
            </form>
        @endif
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
        $('#kampanyeForm').on('submit', function(e) {
            var form = this;
            e.preventDefault();
    
            Swal.fire({
                title: 'Konfirmasi Simpan Prioritas',
                text: 'Apakah Anda yakin ingin menyimpan prioritas kampanye ini?',
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