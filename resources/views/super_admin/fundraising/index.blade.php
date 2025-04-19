@extends('layouts.admin')
 
@section('title', 'Semua Data Fundraising')

@push('after-style')

@endpush

@section('content')

    <!-- Page Heading -->
    <div class="card mb-4">
        <div class="card-header bg-white py-3 align-items-center justify-content-between row m-0">
            <div class="col-12 col-sm-6 p-0">
                <h4 class="m-0 font-weight-bold float-left text-danger">Semua Data Fundraising</h4>
            </div>
            <div class="col-12 col-sm-6">
                <a href="javascript:void(0)" class="btn btn-danger float-left me-2 mt-sm-0 float-sm-right shadow-sm btn-ubah-komisi">Ubah Persentase Komisi</a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered yajra-datatable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Total Komisi</th>
                            <th>Total Donatur</th>
                            <th>Jumlah Donasi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

@push('after-script')
<script>
$(function () {
    var table = $('.yajra-datatable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('fundraising.index') }}",
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'name', name: 'name'},
            {data: 'email', name: 'email'},
            {data: 'commission', name: 'commission'},
            {data: 'total_donatur', name: 'total_donatur'},
            {data: 'jumlah_donasi', name: 'jumlah_donasi'},
            {
                data: 'action', 
                name: 'action', 
                orderable: false, 
                searchable: false
            },
        ]
    });
}); 

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
                url: 'fundraising/' + id,  // Pastikan URL sesuai dengan route POST
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',  // Pastikan token CSRF disertakan
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
                        // Reload data tabel setelah berhasil menghapus
                        $('.yajra-datatable').DataTable().ajax.reload();
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


// Add this to your after-script section
$(document).ready(function() {
  
    $('.btn-ubah-komisi').click(function(e) {
    e.preventDefault();
    
    // Show loading while fetching current value
    Swal.fire({
        title: 'Loading...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Fetch current commission percentage
    $.ajax({
        url: "{{ route('commission.get') }}",
        type: 'GET',
        success: function(response) {
            if (response.success) {
                showCustomCommissionModal(response.data.amount);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Gagal memuat data persentase komisi'
                });
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Terjadi kesalahan saat memuat data persentase komisi'
            });
        }
    });
});

function showCustomCommissionModal(currentValue) {
    Swal.fire({
        title: 'Ubah Persentase Komisi',
        titleText: 'Ubah Persentase Komisi',
        html: `
            <div style="background-color: #FFEBE9; padding: 20px; border-radius: 8px; margin-bottom: 20px; text-align: left;">
                <p style="color: #E74C3C; font-size: 18px; margin-bottom: 5px; font-weight: bold;">Persentase Komisi Saat Ini : ${currentValue}%</p>
                <p style="color: #E74C3C; margin: 0;">Jika Mengubah Persentase Komisi Akan Berlaku Untuk Fundraising Selanjutnya</p>
            </div>
            <input id="swal-input-commission" class="swal2-input" placeholder="Masukan Persentase (jika ingin dirubah)" type="number" min="0" max="100" style="width: 100%; margin: 10px 0; padding: 12px; border-radius: 8px; border: 1px solid #ccc; box-sizing: border-box;">
        `,
        showCancelButton: false,
        showConfirmButton: true,
        confirmButtonText: 'Simpan Data',
        confirmButtonColor: '#E74C3C',
        customClass: {
            popup: 'custom-popup',
            title: 'custom-title',
            confirmButton: 'custom-confirm-button',
            closeButton: 'custom-close-button'
        },
        showCloseButton: true,
        preConfirm: () => {
            const value = document.getElementById('swal-input-commission').value;
            
            if (!value) {
                Swal.showValidationMessage('Persentase komisi tidak boleh kosong');
                return false;
            }
            
            if (value < 0 || value > 100) {
                Swal.showValidationMessage('Persentase komisi harus berada di antara 0-100%');
                return false;
            }
            
            return $.ajax({
                url: "{{ route('commission.update') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    amount: value
                }
            }).then(response => {
                if (!response.success) {
                    throw new Error(response.message || 'Gagal mengubah persentase komisi');
                }
                return response;
            }).catch(error => {
                Swal.showValidationMessage(
                    `Gagal: ${error.responseJSON?.message || error.message || 'Terjadi kesalahan'}`
                );
            });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Persentase komisi berhasil diubah',
                timer: 2000,
                showConfirmButton: false
            });
        }
    });
}
});


</script>


@endpush