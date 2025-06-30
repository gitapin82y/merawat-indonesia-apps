@extends('layouts.admin')

@section('title', 'Detail Fundraising Campaign')

@section('content')
<div class="card mb-4">
    <div class="card-header bg-danger py-3 align-items-center justify-content-between row m-0">
        <div class="col-12 p-0">
            <h4 class="m-0 font-weight-bold text-white">{{ $campaign->title }}</h4>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Jumlah Donasi</th>
                        <th>Komisi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($campaign->fundraisings as $index => $fundraising)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $fundraising->user->name }}</td>
                        <td>{{ $fundraising->user->email }}</td>
                        <td>Rp {{ number_format($fundraising->jumlah_donasi, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($fundraising->commission, 0, ',', '.') }}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('fundraising.edit', $fundraising->id) }}" class="btn btn-info btn-sm">
                                    <i class="fa-solid fa-eye text-white"></i>
                                </a>
                                <button onclick="deleteFundraising({{ $fundraising->id }})" class="btn btn-danger btn-sm">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <a href="{{ route('fundraising.index') }}" class="btn btn-secondary mt-3">Kembali</a>
    </div>
</div>
@endsection

@push('after-script')
<script>
function deleteFundraising(id) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Anda tidak dapat mengembalikan data yang dihapus!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Hapus!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/super-admin/fundraising/' + id,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}',
                },
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: response.message,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                        location.reload();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: response.message,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                },
                error: function() {
                    Swal.fire(
                        'Error!',
                        'Terjadi kesalahan saat menghapus data',
                        'error'
                    );
                }
            });
        }
    });
}
</script>
@endpush