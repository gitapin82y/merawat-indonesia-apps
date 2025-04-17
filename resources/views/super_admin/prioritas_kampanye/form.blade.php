@extends('layouts.admin')
 
@section('title', 'Manajemen Kampanye')

@push('after-style')

@endpush

@section('content')
<div class="card mb-4">
    <div class="card-header bg-danger py-3 align-items-center justify-content-between row m-0">
        <div class="col-12 col-sm-6 p-0">
            <h4 class="m-0 font-weight-bold float-left text-white">{{ isset($prioritasKampanye->id) ? 'Edit Prioritas Kampanye' : 'Tambah Prioritas Kampanye' }}</h4>
        </div>
    </div>
        <div class="card-body">
            <form action="{{ isset($prioritasKampanye->id) ? route('prioritas-kampanye.update', $prioritasKampanye->id) : route('prioritas-kampanye.store') }}" 
                    method="POST" enctype="multipart/form-data" id="kampanyeForm">
                  @csrf
                  @if(isset($prioritasKampanye->id))
                      @method('PUT')
                  @endif
              
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label>Kampanye</label>
                            <select name="campaign_id" class="form-control @error('campaign_id') is-invalid @enderror" required>
                                <option value="">Pilih Kampanye</option>
                                @foreach($campaigns as $campaign)
                                    <option value="{{ $campaign->id }}" {{ (old('campaign_id', $prioritasKampanye->id ?? '') == $campaign->id) ? 'selected' : '' }}>
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
                                    <option value="{{ $i }}" {{ (old('prioritas', $prioritasKampanye->prioritas ?? '') == $i) ? 'selected' : '' }}>
                                        {{ $i }}
                                    </option>
                                @endfor
                            </select>                            
                            @error('prioritas')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-danger">Simpan Kampanye</button>
                    <a href="{{ route('prioritas-kampanye.index') }}" class="btn btn-secondary">Kembali</a>
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
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
