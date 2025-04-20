@extends('layouts.public')
 
@section('title', 'Menu Lainnya')

@push('after-style')

@endpush

@section('content')

    @include('includes.public.navbar-back', ['title' => 'Menu Lainnya'])

    <ul class="list-group pt-2" style="padding-bottom:600px;">
        <a href="{{url('eksplore-kampanye')}}" class="list-group-item py-0 my-2 d-flex align-items-center">
            <div class="icon-card me-2">
                <img src="{{asset('assets/img/kategori/donasi.svg')}}" alt="Donasi" width="40px">
            </div>
            <span>Donasi</span>
        </a>
        <a href="{{url('galang-dana')}}" class="list-group-item py-0 my-2 d-flex align-items-center">
            <div class="icon-card me-2">
                <img src="{{asset('assets/img/kategori/galang dana.svg')}}" alt="Galang Dana" width="40px">
            </div>
            <span>Galang Dana</span>
        </a>
        <a href="{{url('kalkulator-zakat')}}" class="list-group-item py-0 my-2 d-flex align-items-center">
            <div class="icon-card me-2">
                <img src="{{asset('assets/img/kategori/kalkulator zakat.svg')}}" alt="Kalkulator Zakat" width="40px">
            </div>
            <span>Kalkulator Zakat</span>
        </a>
        @foreach($categories as $category)
                <a href="{{ url('/eksplore') }}?category={{ urlencode($category->name) }}" class="list-group-item py-0 my-2 d-flex align-items-center">
                    <div class="icon-card me-2">
                        <img src="{{ asset('storage/' . $category->icon) }}" alt="{{ $category->name }}" width="40px">
                    </div>
                    <span>{{ $category->name }}</span>
                </a>
        @endforeach
    </ul>

 
    @include('includes.public.menu')

@endsection

@push('after-script')
   
@endpush

