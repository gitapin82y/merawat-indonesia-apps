 <!-- Bootstrap core JavaScript-->
 <script src="{{asset('vendor/jquery/jquery.min.js')}}"></script>
 <script src="{{asset('vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>

 <!-- Core plugin JavaScript-->
 <script src="{{asset('vendor/jquery-easing/jquery.easing.min.js')}}"></script>

 <!-- Custom scripts for all pages-->
 <script src="{{asset('js/sb-admin-2.min.js')}}"></script>

     <!-- Page level plugins -->
     <script src="{{asset('vendor/chart.js/Chart.min.js')}}"></script>

     <!-- Page level custom scripts -->
     <script src="{{asset('js/demo/chart-area-demo.js')}}"></script>
     <script src="{{asset('js/demo/chart-pie-demo.js')}}"></script>

 <script src="{{asset('vendor/datatables/jquery.dataTables.min.js')}}"></script>
 <script src="{{asset('vendor/datatables/dataTables.bootstrap4.min.js')}}"></script>

 <!-- Page level custom scripts -->
 <script src="{{asset('js/demo/datatables-demo.js')}}"></script>
 {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css"></script> --}}
 <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
 <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
 <script>
    document.getElementById('phone').addEventListener('input', function (e) {
    let value = e.target.value.replace(/\D/g, ''); // Hapus karakter non-digit

    if (value.startsWith('62')) {
        value = '0' + value.substring(2);
    }

    e.target.value = value;
});


 </script>
@if(session('toast'))
<script>
    Swal.fire({
        icon: '{{ session('toast')['type'] }}',
        text: '{{ session('toast')['message'] }}',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
    });
</script>
@endif