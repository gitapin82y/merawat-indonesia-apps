@extends('layouts.public')

@section('title', 'Salur Dana')

@section('content')
  @include('includes.public.navbar')
  
<div class="container p-3">
    <h5 class="mb-3">Salur Dana Terbaru</h5>
    @forelse($articles as $article)
   <a href="{{ url('artikel/'. $article->slug) }}" class="col-12 row mt-2">
    <div class="foto col-6 col-sm-6 col-md-4 align-items-center position-relative">
        <img src="{{ asset('storage/' . $article->image) }}" class="image-article-card w-100" alt="{{ $article->title }}">
    </div>

    <div class="col-6 col-sm-6 col-md-8 p-0 align-self-center">
        <h2 class="text-danger">{{ $article->title }}</h2>
        <div class="row col-12 m-0 p-0">
            <div class="col-6 p-0 text-start">
                <small>{{ $article->created_at->format('d M Y') }}</small>
            </div>
        </div>
    </div>
</a>

   @empty
                <div class="d-flex align-items-center justify-content-center">
                  <img src="{{ asset('assets/img/icon/success-data.svg') }}" alt="Not Found" class="mb-3 pe-3" style="width: 50px; height: 50px;">
                  <p>Belum ada artikel</p>
              </div>
            @endforelse
    {{ $articles->links() }}
</div>
@include('includes.public.menu')
@endsection