@extends('layouts.admin')
 
@section('title', 'Dashboard')

@push('after-style')

@endpush


@section('content')
<div class="card mb-4">
    <div class="card-body">
    <div class="row justify-content-center pb-4">
        <div class="col-4 col-md-2 col-lg-4">
            <a class="w-100 btn btn-danger btn-adsense" data-toggle="modal" data-target="#adsenseModal"
            data-id="{{ optional($adsense)->id }}"
            data-tiktok_pixel="{{ optional($adsense)->tiktok_pixel }}"
            data-facebook_pixel="{{ optional($adsense)->facebook_pixel }}"
            data-google_analytics_tag="{{ optional($adsense)->google_analytics_tag }}"
            data-meta_token="{{ optional($adsense)->meta_token }}"
            data-meta_endpoint="{{ optional($adsense)->meta_endpoint }}"
            data-google_ads_id="{{ optional($adsense)->google_ads_id }}"
            data-google_ads_label="{{ optional($adsense)->google_ads_label }}"
            data-tiktok_token="{{ optional($adsense)->tiktok_token }}"
            data-tiktok_endpoint="{{ optional($adsense)->tiktok_endpoint }}"
            >
                Pengaturan Iklan
            </a>
        </div>
        <div class="col-4 col-md-2 col-lg-4">
            <a class="w-100 btn btn-danger btn-kategori" data-toggle="modal" data-target="#manageKategoriModal">
                Manage Kategori
            </a>
        </div>
        <div class="col-4 col-md-2 col-lg-4">
            <a class="w-100 btn btn-danger" data-toggle="modal" data-target="#manageBannerModal"">
                Manage Slider
            </a>
        </div>
        <div class="col-4 col-md-2 col-lg-4">
            <a class="w-100 btn btn-danger" data-toggle="modal" data-target="#managePayment"">
                Manage Methods Payment Manual
            </a>
        </div>

       <!-- Modal Untuk Manajemen Metode Pembayaran Manual -->
<div class="modal fade" id="managePayment" tabindex="-1" aria-labelledby="paymentLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="paymentLabel">Manage Metode Pembayaran Manual</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <!-- Tampilkan list payment methods -->
          <div id="paymentList">
            <!-- Akan diisi dengan AJAX -->
            <div class="text-center py-3">
              <div class="spinner-border text-danger" role="status">
                <span class="sr-only">Loading...</span>
              </div>
            </div>
          </div>
          <button type="button" class="btn btn-danger mt-3 w-100 btn-tambah-payment">Tambah Metode Pembayaran</button>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Modal Form Tambah/Edit Metode Pembayaran -->
  <div class="modal fade" id="paymentFormModal" tabindex="-1" aria-labelledby="paymentFormModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="paymentFormModalLabel">Tambah Metode Pembayaran</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="paymentForm" enctype="multipart/form-data">
            <input type="hidden" id="payment_id" name="payment_id">
            
            <div class="form-group mb-3">
              <label for="payment_name">Nama Bank/E-Wallet <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="payment_name" name="name" required>
              <div id="error-name" class="invalid-feedback"></div>
            </div>
            
            <div class="form-group mb-3">
              <label for="account_number">Nomor Rekening/ID <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="account_number" name="account_number" required>
              <div id="error-account_number" class="invalid-feedback"></div>
            </div>
            
            <div class="form-group mb-3">
              <label for="account_name">Atas Nama <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="account_name" name="account_name" required>
              <div id="error-account_name" class="invalid-feedback"></div>
            </div>
            
            <div class="form-group mb-3">
              <label for="instructions">Instruksi Tambahan</label>
              <textarea class="form-control" id="instructions" name="instructions" rows="3"></textarea>
              <div id="error-instructions" class="invalid-feedback"></div>
            </div>
            
            <div class="form-group mb-3">
              <label for="payment_icon">Logo/Icon</label>
              <input type="file" class="form-control" id="payment_icon" name="icon" accept="image/*">
              <small class="form-text text-muted">Format: JPG, PNG, GIF (Max. 2MB)</small>
              <div id="error-icon" class="invalid-feedback"></div>
            </div>
            
            <div class="form-group mb-3" id="icon_preview_container">
              <img id="icon_preview" src="" alt="Preview" class="img-thumbnail mt-2" style="max-height: 100px; display: none;">
            </div>
            
            <div class="form-group form-check mb-3">
              <input type="checkbox" class="form-check-input" id="is_active" name="is_active" checked>
              <label class="form-check-label" for="is_active">Aktif</label>
            </div>
            
            <div class="text-end">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
              <button type="submit" class="btn btn-danger">Simpan</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

@include('modal_dashboard.adsense')
@include('modal_dashboard.kategori')
@include('modal_dashboard.form_kategori')
@include('modal_dashboard.banner')
@include('modal_dashboard.form_banner')
</div>

   <!-- Content Row -->
<div class="row">

    <!-- Card Template -->
    <?php 
        $cards = [
            ["title" => "Jumlah Donasi", "value" => "900.000.000"],
            ["title" => "Pencairan Donasi", "value" => "90.000.000"],
            ["title" => "Sisa Donasi Saat Ini", "value" => "810.000.000"],
            ["title" => "Pencairan Fundraising", "value" => "1.000.000"],
            ["title" => "Total Kampanye", "value" => "2920"],
            ["title" => "Donasi dari Google Ads", "value" => "30x"],
            ["title" => "Donasi dari FB Ads", "value" => "50x"],
            ["title" => "Donasi dari Tiktok Ads", "value" => "90x"],
            ["title" => "Total Donasi dari Ads", "value" => "170x"],
            ["title" => "Jumlah Donasi Dari Ads", "value" => "800.000.000"],
            ["title" => "Donasi Hari Ini", "value" => "30x"],
            ["title" => "Donasi Bulan Ini", "value" => "320x"],
            ["title" => "Jumlah Donasi Hari Ini", "value" => "10.000.000"],
            ["title" => "Jumlah Donasi Bulan Ini", "value" => "90.000.000"],
            ["title" => "Total Akun Admin", "value" => "320"]
        ];
    ?>

    <?php foreach ($cards as $card): ?>
        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card-sm shadow border-0 h-100 p-3">
                    <div class="text-danger mb-1" style="font-size: 12px;">
                        <?= $card['title']; ?>
                    </div>
                    <div class="h6 font-weight-bold text-danger" style="font-size: 18px;">
                        <?= $card['value']; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

</div>

    <!-- Content Row -->

    <div class="row">

        <!-- Area Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card mb-4">
                <!-- Card Header - Dropdown -->
                <div
                    class="card-header  bg-danger text-white py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-main">Grafik Penjualan 2024</h6>
                </div>
                <!-- Card Body -->
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="donasiChart"></canvas>
                    </div>
                </div>
                
            </div>
        </div>

        <!-- Pie Chart -->
        <div class="col-xl-4 col-lg-5">
            <div class="card mb-4">
                <div class="card-header bg-danger text-white py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-main">Persentase Akun</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-0 pb-2">
                        <canvas id="myPieChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <span class="mr-2">
                            <i class="fas fa-circle text-primary"></i> Zakat
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-success"></i> Kesehatan
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-danger"></i> Kebencanaan
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-warning"></i> Sosial
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-info"></i> Pendidikan
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-secondary"></i> Keagamaan
                        </span>
                    </div>
                </div>
                
            </div>
        </div>
        
    </div>
</div>
</div>

@endsection

@push('after-script')
<script>
var ctx = document.getElementById("donasiChart").getContext("2d");
var ctx = document.getElementById("donasiChart").getContext("2d");
var myBarChart = new Chart(ctx, {
    type: 'bar',  // Ubah dari 'line' menjadi 'bar'
    data: {
        labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
        datasets: [{
            label: "Total Donasi",
            backgroundColor: "#FF4747",  // Warna merah
            borderColor: "#FF4747",  // Warna border merah
            borderWidth: 1,
            hoverBackgroundColor: "rgba(255, 71, 71, 0.8)",
            hoverBorderColor: "#FF4747",
            data: [100000, 120000, 90000, 150000, 200000, 180000, 220000, 250000, 230000, 260000, 280000, 300000], // Data donasi
        }],
    },
    options: {
        maintainAspectRatio: false,
        scales: {
            x: {
                grid: { display: false }
            },
            y: {
                ticks: {
                    callback: function(value) {
                        return 'Rp ' + value.toLocaleString();
                    }
                },
                grid: { color: "rgb(234, 236, 244)", zeroLineColor: "rgb(234, 236, 244)" }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(tooltipItem) {
                        return 'Rp ' + tooltipItem.raw.toLocaleString();
                    }
                }
            }
        }
    }
});



// PIE CHART
// Data donasi dalam kategori (bisa diambil dari backend jika perlu)
var donationData = {
    "Zakat": 400000000,
    "Kesehatan": 200000000,
    "Kebencanaan": 100000000,
    "Sosial": 80000000,
    "Pendidikan": 150000000,
    "Keagamaan": 70000000
};

// Hitung total donasi
var totalDonasi = Object.values(donationData).reduce((a, b) => a + b, 0);

// Hitung persentase setiap kategori
var categoryLabels = Object.keys(donationData);
var categoryValues = Object.values(donationData).map(value => ((value / totalDonasi) * 100).toFixed(2));

// Warna untuk setiap kategori
var categoryColors = ['#4e73df', '#1cc88a', '#e74a3b', '#f6c23e', '#36b9cc', '#858796'];

var ctxPie = document.getElementById("myPieChart").getContext('2d');
var myPieChart = new Chart(ctxPie, {
    type: 'doughnut',
    data: {
        labelItems: categoryLabels,
        datasets: [{
            data: categoryValues,
            backgroundColor: categoryColors,
            hoverBackgroundColor: categoryColors.map(color => shadeColor(color, -20)),
            hoverBorderColor: "rgba(234, 236, 244, 1)",
        }],
    },
    options: {
        maintainAspectRatio: false,
        cutoutPercentage: 80,
        tooltips: {
            callbacks: {
                label: function(tooltipItem, chart) {
                    var label = chart.labelItems[tooltipItem.index];
                    var value = chart.datasets[0].data[tooltipItem.index];
                    return `${label}: ${value}%`;
                }
            }
        }
    },
});

// Fungsi untuk menggelapkan warna hover
function shadeColor(color, percent) {
    var num = parseInt(color.replace("#", ""), 16),
        amt = Math.round(2.55 * percent),
        R = (num >> 16) + amt,
        G = (num >> 8 & 0x00FF) + amt,
        B = (num & 0x0000FF) + amt;
    return `rgb(${R < 255 ? R : 255}, ${G < 255 ? G : 255}, ${B < 255 ? B : 255})`;
}

</script>

{{-- modal adsense --}}
<script>

$(document).ready(function () {
    $.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});


    $('#adsenseForm').on('submit', function (e) {
        e.preventDefault();
        
        let id = $('#adsense_id').val();
        let url = id ? `/super-admin/adsense/${id}` : '/super-admin/adsense';
        let method = id ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            type: method,
            data: {
                id: $('#adsense_id').val(),
                tiktok_pixel: $('#tiktok_pixel').val(),
                facebook_pixel: $('#facebook_pixel').val(),
                google_analytics_tag: $('#google_analytics_tag').val(),
                meta_token: $('#meta_token').val(),
                meta_endpoint: $('#meta_endpoint').val(),
                google_ads_id: $('#google_ads_id').val(),
                google_ads_label: $('#google_ads_label').val(),
                tiktok_token: $('#tiktok_token').val(),
                tiktok_endpoint: $('#tiktok_endpoint').val(),
            },
            dataType: 'json',
            beforeSend: function () {
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').text('');
            },
            success: function (response) {
                Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: response.message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 1000
            }).then(() => {
                // Setelah Toast tertutup, baru reload
                $('#adsenseModal').modal('hide');
                $('#adsenseForm')[0].reset();
                $('#adsense_id').val('');
                location.reload();
            });
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    $.each(errors, function (key, value) {
                        $('#' + key).addClass('is-invalid');
                        $('#error-' + key).text(value[0]);
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: errorMessage,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            }
        });
    });

    // Saat klik edit
    $('.btn-adsense').click(function () {
        let data = $(this).data(); 
        $('#adsense_id').val(data.id);

        Object.keys(data).forEach(function (key) {
            $('#' + key).val(data[key]);
        });

        $('#adsenseModal').modal('show');
    });

    // Reset form saat modal ditutup
    $('#adsenseModal').on('hidden.bs.modal', function () {
        $('#adsenseForm')[0].reset();
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        $('#adsense_id').val('');
    });
});
</script>

{{-- manage kategori --}}
<script>
$(document).ready(function() {
    // Load kategori saat modal "Manage Kategori" dibuka
    $('#manageKategoriModal').on('show.bs.modal', function() {
        loadKategori();
    });

    function loadKategori() {
        $.ajax({
            url: '/super-admin/categories',
            method: 'GET',
            success: function(response) {
                let rows = '';
                response.forEach(function(kategori) {
                    rows += `
                       <div class="row d-flex mb-2 justify-content-around mx-0">
    <div class="col-auto ps-0 align-self-center">
        <img src="/storage/${kategori.icon}" width="40">
    </div>
    <div class="col-auto align-self-center">
        ${kategori.name}
    </div>
    <div class="col-auto align-self-center">
        <button class="btn btn-warning btn-edit-kategori" data-id="${kategori.id}" data-name="${kategori.name}" data-icon="${kategori.icon}">
            Edit
        </button>
    </div>
    <div class="col-auto pe-0 align-self-center">
        <button class="btn btn-danger btn-delete-kategori" data-id="${kategori.id}">
            Delete
        </button>
    </div>
</div>
                    `;
                });
                $('#kategoriList').html(rows);
            }
        });
    }


    $(document).on('click', '.btn-delete-kategori', function() {
    let kategoriId = $(this).data('id');

    Swal.fire({
        title: "Apakah Anda yakin?",
        text: "Kategori ini akan dihapus secara permanen!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Ya, Hapus!"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/super-admin/categories/${kategoriId}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Kategori berhasil dihapus!',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    loadKategori(); // Refresh data setelah delete
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal menghapus kategori!',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            });
        }
    });
});



    // Tambah Kategori
    $('.btn-tambah-kategori').click(function() {
        $('#kategoriForm')[0].reset();
        $('#kategori_id').val('');
        $('#icon_preview').hide();
        $('#kategoriFormModalLabel').text('Tambah Kategori');
        $('#kategoriFormModal').modal('show');
    });

    // Edit Kategori
    $(document).on('click', '.btn-edit-kategori', function() {
        let id = $(this).data('id');
        let name = $(this).data('name');
        let icon = $(this).data('icon');

        $('#kategori_id').val(id);
        $('#kategori_name').val(name);

        if (icon) {
            $('#icon_preview').attr('src', '/storage/' + icon).show();
        } else {
            $('#icon_preview').hide();
        }

        $('#kategoriFormModalLabel').text('Edit Kategori');
        $('#kategoriFormModal').modal('show');
    });

    // Upload gambar preview
    $('#kategori_icon').change(function(e) {
        let reader = new FileReader();
        reader.onload = function(event) {
            $('#icon_preview').attr('src', event.target.result).show();
        }
        reader.readAsDataURL(e.target.files[0]);
    });

    $('#kategoriForm').submit(function(e) {
    e.preventDefault();

    let formData = new FormData();
    formData.append('name', $('#kategori_name').val());
    if ($('#kategori_icon')[0].files.length > 0) {
    formData.append('icon', $('#kategori_icon')[0].files[0]);
    }

    let id = $('#kategori_id').val();
    let url = id ? `/super-admin/categories/${id}` : '/super-admin/categories';
    let method = id ? 'POST' : 'POST'; // Pakai POST, lalu override dengan _method=PUT


    // Reset error messages
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').text('');

    $.ajax({
        url: url,
        method: method,
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: response.message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000
            }).then(() => {
                $('#kategoriFormModal').modal('hide');
                loadKategori();
            });
        },
        error: function(xhr) {
            let errors = xhr.responseJSON?.errors;
            if (errors) {
                if (errors.name) {
                    $('#kategori_name').addClass('is-invalid');
                    $('#error-name').text(errors.name[0]);
                }
                if (errors.icon) {
                    $('#kategori_icon').addClass('is-invalid');
                    $('#error-icon').text(errors.icon[0]);
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Terjadi kesalahan!',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            }
        }
    });
});

});

</script>


{{-- manage banner --}}
<script>
    $(document).ready(function() {
        // Load banner saat modal "Manage Banner" dibuka
        $('#manageBannerModal').on('show.bs.modal', function() {
            loadBanner();
        });
    
        function loadBanner() {
            $.ajax({
                url: '/super-admin/banner',
                method: 'GET',
                success: function(response) {
                    let rows = '';
                    response.forEach(function(banner) {
                        rows += `
                           <div class="row d-flex mb-2 justify-content-around mx-0">
                            <div class="col-auto ps-0 align-self-center">
                                <img src="/storage/${banner.photo}" width="40">
                            </div>
                            <div class="col-auto pe-0 align-self-center">
                                <button class="btn btn-danger btn-delete-banner" data-id="${banner.id}">
                                    Delete
                                </button>
                            </div>
                        </div>
                        `;
                    });
                    $('#bannerList').html(rows);
                }
            });
        }
    
    
        $(document).on('click', '.btn-delete-banner', function() {
        let bannerId = $(this).data('id');
    
        Swal.fire({
            title: "Apakah Anda yakin?",
            text: "Banner ini akan dihapus secara permanen!",
            photo: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Ya, Hapus!"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/super-admin/banner/${bannerId}`,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Banner berhasil dihapus!',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                        loadBanner(); // Refresh data setelah delete
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal menghapus banner!',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                });
            }
        });
    });
    
    
    
        // Tambah Banner
        $('.btn-tambah-banner').click(function() {
            $('#bannerForm')[0].reset();
            $('#banner_id').val('');
            $('#photo_preview').hide();
            $('#bannerFormModalLabel').text('Tambah Banner');
            $('#bannerFormModal').modal('show');
        });
    
        // Upload gambar preview
        $('#banner_photo').change(function(e) {
            let reader = new FileReader();
            reader.onload = function(event) {
                $('#photo_preview').attr('src', event.target.result).show();
            }
            reader.readAsDataURL(e.target.files[0]);
        });
    
        $('#bannerForm').submit(function(e) {
        e.preventDefault();
    
        let formData = new FormData();
        if ($('#banner_photo')[0].files.length > 0) {
        formData.append('photo', $('#banner_photo')[0].files[0]);
        }
    
        let id = $('#banner_id').val();
        let url = id ? `/super-admin/banner/${id}` : '/super-admin/banner';
        let method = id ? 'POST' : 'POST'; // Pakai POST, lalu override dengan _method=PUT
    
    
        // Reset error messages
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    
        $.ajax({
            url: url,
            method: method,
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.message,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000
                }).then(() => {
                    $('#bannerFormModal').modal('hide');
                    loadBanner();
                });
            },
            error: function(xhr) {
                let errors = xhr.responseJSON?.errors;
                if (errors) {
                    if (errors.photo) {
                        $('#banner_photo').addClass('is-invalid');
                        $('#error-photo').text(errors.photo[0]);
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: 'Terjadi kesalahan!',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            }
        });
    });
    
    });
    
</script>
<!-- Tambahkan kode ini di bagian @push('after-script') pada dashboard.blade.php -->
<script>
    // Fungsi untuk memuat daftar metode pembayaran
    function loadPaymentMethods() {
        $.ajax({
            url: '/super-admin/manual-payment-methods',
            method: 'GET',
            beforeSend: function() {
                $('#paymentList').html(`
                    <div class="text-center py-3">
                        <div class="spinner-border text-danger" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                `);
            },
            success: function(response) {
                let html = '';
                
                if (response.length === 0) {
                    html = `
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle me-2"></i> Belum ada metode pembayaran manual yang ditambahkan.
                        </div>
                    `;
                } else {
                    response.forEach(function(method) {
                        const statusBadge = method.is_active 
                            ? '<span class="badge bg-success text-white">Aktif</span>' 
                            : '<span class="badge bg-secondary text-white">Nonaktif</span>';
                            
                        html += `
                            <div class="card mb-2">
                                <div class="card-body p-3">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            ${method.icon 
                                                ? `<img src="/storage/${method.icon}" alt="${method.name}" height="40">` 
                                                : `<div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 40px; height: 40px"><i class="fa fa-building text-muted"></i></div>`
                                            }
                                        </div>
                                        <div class="col">
                                            <h6 class="mb-0">${method.name} ${statusBadge}</h6>
                                            <small class="text-muted">${method.account_number} (${method.account_name})</small>
                                        </div>
                                        <div class="col-auto">
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-warning btn-edit-payment" 
                                                    data-id="${method.id}" 
                                                    data-name="${method.name}"
                                                    data-account-number="${method.account_number}"
                                                    data-account-name="${method.account_name}"
                                                    data-instructions="${method.instructions || ''}"
                                                    data-icon="${method.icon || ''}"
                                                    data-is-active="${method.is_active ? 1 : 0}">
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger btn-delete-payment" data-id="${method.id}">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                }
                
                $('#paymentList').html(html);
            },
            error: function() {
                $('#paymentList').html(`
                    <div class="alert alert-danger">
                        <i class="fa fa-exclamation-circle me-2"></i> Gagal memuat data metode pembayaran.
                    </div>
                `);
            }
        });
    }
    
    $(document).ready(function() {
        // Setup AJAX headers
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        // Load payment methods ketika modal dibuka
        $('#managePayment').on('show.bs.modal', function() {
            loadPaymentMethods();
        });
        
        // Tambah metode pembayaran
        $('.btn-tambah-payment').click(function() {
            $('#paymentForm')[0].reset();
            $('#payment_id').val('');
            $('#icon_preview').hide();
            $('#paymentFormModalLabel').text('Tambah Metode Pembayaran');
            $('#is_active').prop('checked', true);
            $('#paymentFormModal').modal('show');
        });
        
        // Edit metode pembayaran
        $(document).on('click', '.btn-edit-payment', function() {
            const data = $(this).data();
            
            $('#payment_id').val(data.id);
            $('#payment_name').val(data.name);
            $('#account_number').val(data.accountNumber);
            $('#account_name').val(data.accountName);
            $('#instructions').val(data.instructions);
            $('#is_active').prop('checked', data.isActive === 1);
            
            if (data.icon) {
                $('#icon_preview').attr('src', '/storage/' + data.icon).show();
            } else {
                $('#icon_preview').hide();
            }
            
            $('#paymentFormModalLabel').text('Edit Metode Pembayaran');
            $('#paymentFormModal').modal('show');
        });
        
        // Preview image saat memilih file
        $('#payment_icon').change(function(e) {
            if (this.files && this.files[0]) {
                let reader = new FileReader();
                reader.onload = function(event) {
                    $('#icon_preview').attr('src', event.target.result).show();
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
        
        // Submit form metode pembayaran
        $('#paymentForm').submit(function(e) {
            e.preventDefault();
            
            let formData = new FormData(this);
            formData.append('is_active', $('#is_active').is(':checked') ? 1 : 0);
            
            const id = $('#payment_id').val();
            const url = id 
                ? `/super-admin/manual-payment-methods/${id}` 
                : '/super-admin/manual-payment-methods';
            const method = id ? 'PUT' : 'POST';
            
            // Reset error messages
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').text('');
            
            $.ajax({
                url: url,
                method: method,
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        $('#paymentFormModal').modal('hide');
                        loadPaymentMethods();
                    });
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON?.errors;
                    
                    if (errors) {
                        Object.keys(errors).forEach(function(key) {
                            $(`#${key}`).addClass('is-invalid');
                            $(`#error-${key}`).text(errors[key][0]);
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan sistem.',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                }
            });
        });
        
        // Delete metode pembayaran
        $(document).on('click', '.btn-delete-payment', function() {
            const id = $(this).data('id');
            
            Swal.fire({
                title: 'Hapus Metode Pembayaran',
                text: 'Apakah Anda yakin ingin menghapus metode pembayaran ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/super-admin/manual-payment-methods/${id}`,
                        method: 'DELETE',
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message,
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 2000
                            });
                            loadPaymentMethods();
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: 'Terjadi kesalahan saat menghapus metode pembayaran.',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
