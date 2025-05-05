@extends('layouts.public')
 
@section('title', 'Fundraishing')

@push('after-style')

@endpush

@section('content')

    @include('includes.public.navbar-back', ['title' => 'Fundraishing'])

    @if($fundraisings->isEmpty())
    @include('donatur.fundraishing.not-available')

    @else
    @include('donatur.fundraishing.available',['fundraisings'=>$fundraisings,'totalCommission'=> $totalCommission])

    @endif
 
    @include('includes.public.menu')

@endsection

@push('after-script')
<script>
    document
      .querySelector(".btn-cairkandana")
      .addEventListener("click", function (e) {
        e.preventDefault();
        const komisi = $(this).data('komisi');
        if(komisi < 100000){

            Swal.fire({
                    icon: 'info',
                    text: 'Minimal pendapatan harus mencapai 100.000',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000
                });
        }else{
            document.getElementById("popupCairkanDana").style.display = "flex";
        }
      });
      
    
    // Close popup when clicking outside the popup content
    document
      .getElementById("popupCairkanDana")
      .addEventListener("click", function (e) {
        if (e.target === this) {
          this.style.display = "none";
        }
      });
    
        $(document).ready(function() {
            $('.btn-salin').click(function() {
                const link = $(this).data('link');
    
                const tempInput = document.createElement('input');
                tempInput.value = link;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand('copy');
                document.body.removeChild(tempInput);

                Swal.fire({
                    icon: 'success',
                    text: 'Link fundraising berhasil disalin ke clipboard.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000
                });
            });
            
            // Tampilkan pesan error atau success dari controller
            @if(session('success'))
                Swal.fire({
                    title: 'Berhasil!',
                    text: '{{ session('success') }}',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            @endif
            
            @if(session('error'))
                Swal.fire({
                    title: 'Perhatian!',
                    text: '{{ session('error') }}',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            @endif
        });
    </script>

@endpush

