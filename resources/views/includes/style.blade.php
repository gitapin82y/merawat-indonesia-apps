<link href="{{asset('css/sb-admin-2.min.css')}}" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
<link href="{{asset('vendor/datatables/dataTables.bootstrap4.min.css')}}" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    :root {
    /* Warna Utama dan Sekunder */
    --main-color: #FE0101;
    --second-color: #FF4747;
  
    /* Warna Teks */
    --title-color: #202020;
    --desc-color: #606060;
  
    /* Tipografi */
    --font-family-title: 'Poppins', sans-serif;
    --font-family-desc: 'Poppins', sans-serif;
  
    /* Ukuran Font */
    --font-size-title: 16px;
    --font-size-desc: 15px;
  
    /* Gaya Font */
    --font-weight-bold: bold;
    --font-weight-regular: normal;
  
    /* Button */
    --border-radius: 6px;
    --button-height: 50px;
  
    /* shadow */
    --box-shadow-default: 0px 10px 30px rgba(0, 0, 0, 0.1);
  }
    .swal2-popup{
        width: auto !important;
    }
    .nav-dashboard .active{
        color: rgb(50, 70, 201) !important;
    }
    body{
        background-color: #FFF9F9 !important;
    }
    .table td, .table th{
        vertical-align: middle;
    }
    .styleEyePassword{
        position: absolute;
        right:15px;
        transform: translateY(-33px);
        cursor: pointer;
    }

    .navbar-nav .nav-link {
        color: #333 !important; /* Warna teks */
        font-weight: 500; /* Menyamakan ketebalan */
    }

    .btn-group .btn{
        margin: 0px 8px;
        border-radius: 4px !important;
    }
    .navbar-nav .nav-link:hover, 
    .navbar-nav .nav-link.active, .text-danger {
        color: #FF4747 !important; /* Warna saat hover atau aktif */
    }

    .badge.bg-danger{
        background-color: rgba(255, 71, 71, 0.1) !important;
        color: rgba(255, 71, 71, 1) !important;
        border-radius: 20px;
        padding: 5px 15px;
    }
    .badge.bg-success{
        background-color: rgba(108, 221, 51, 0.1) !important;
        color: rgba(108, 221, 51, 1) !important; 
        border-radius: 20px;
        padding: 5px 15px;
    }
    .badge.bg-warning{
        background-color: rgba(255, 149, 43, 0.1) !important;
        color: rgba(255, 149, 43, 1) !important;
        border-radius: 20px;
        padding: 5px 15px;
    }
    .badge.bg-primary{
        background-color: rgba(51, 115, 255, 0.1) !important;
        color: rgba(51, 115, 255, 1) !important;
        border-radius: 6px;
        padding: 10px;
        font-size: 16px;
    }

    .dataTables_length .form-control-sm {
        padding: .25rem 1.5rem !important;
    }

    .bg-danger, .btn-danger{
        background-color: #FF4747;
    }

    .page-item.active .page-link {
        z-index: 3;
        color: #fff;
        background-color: #FF4747;
        border-color: #FF4747;
    }

    .shadow{
        box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.1) !important;
    }
</style>

<style>
     .circle-dropdown {
    color: white;
    background-color: var(--second-color);
    padding: 5px;
    border-radius: 20px;
  }
    .circle-number {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: #FF4747;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}
    .select2-container .select2-selection--single {
    height: 55px !important; /* Sesuaikan tinggi */
    display: flex !important;
    align-items: center !important; /* Tengahkan teks */
    padding: 10px !important;
    font-size: 16px;
}

.select2-container .select2-selection__arrow {
    top: 50% !important;
    transform: translateY(-50%) !important;
    right: 10px !important; /* Sesuaikan posisi ke kanan */
    position: absolute !important;
}

</style>