<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="shortcut icon" sizes="16x16 32x32 48x48" href="{{asset('assets/img/merawat-indonesia-logo.png')}}" />
    <meta name="description" content="">
    <meta name="author" content="">

    <title>@yield('title')</title>

    @include('includes.style')
    @stack('after-style')
</head>

<body>

        @yield('content')

    @include('includes.script')
    @include('sweetalert::alert')
    @stack('after-script')
</body>

</html>