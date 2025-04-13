<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <link rel="shortcut icon" sizes="16x16 32x32 48x48" href="{{asset('assets/img/merawat-indonesia-logo.png')}}" />
  <title>@yield('title')</title>

  @include('includes.public.style')
  @stack('after-style')
</head>

<body>

  <section class="col-12 justify-content-center d-flex">
    <div class="col-12 bg-white col-md-6">
  <!-- start body -->
  @yield('content')
  <!-- end body -->
    </div>
  </section>

  @include('includes.public.script')
  @include('sweetalert::alert')
  @stack('after-script')
</body>

</html>