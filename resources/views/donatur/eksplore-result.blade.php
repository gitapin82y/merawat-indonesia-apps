@extends('layouts.public')
 
@section('title', 'Kampanye')

@push('after-style')

<style>
    .not-found-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 400px;
        width: 100%;
    }

    .not-found-message {
        text-align: center;
        padding: 30px;
        background-color: #f8f9fa;
        border-radius: 10px;
        max-width: 500px;
    }

    .not-found-icon {
        width: 80px;
        height: 80px;
        margin-bottom: 20px;
    }

    .not-found-message h3 {
        color: #F05454;
        margin-bottom: 15px;
        font-weight: 600;
    }

    .not-found-message p {
        color: #6c757d;
        margin-bottom: 5px;
    }

    .btn-primary {
        background-color: #F05454;
        border-color: #F05454;
        padding: 8px 20px;
        border-radius: 5px;
    }

    .btn-primary:hover {
        background-color: #d64545;
        border-color: #d64545;
    }
</style>
<style>
    
    .accordion-button::after {
  display: none;
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
.accordion-item {
  border-radius: 12px;
  margin-bottom: 10px;
  box-shadow: 0px 10px 30px rgba(0,0,0,0.1);
}

</style>

<style>
    .active-filters {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
    }
    
    .filter-badge {
        background-color: #F05454;
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        margin-right: 10px;
        margin-bottom: 10px;
        display: inline-flex;
        align-items: center;
    }
    
    .badge-close {
        margin-left: 8px;
        color: white;
        font-size: 18px;
        font-weight: bold;
        text-decoration: none;
    }
    
    .badge-close:hover {
        color: #f8f9fa;
    }
    </style>
@endpush

@section('content')

@include('includes.public.navbar')

<div class="container">

  <div class="row kampanye py-4 px-4">
       @if(!empty($activeFilters['title']) || !empty($activeFilters['categories']) || !empty($activeFilters['filters']))
       @if(!empty($activeFilters['title']))
            <div class="filter-badge">
                Pencarian: {{ $activeFilters['title'] }}
                <a href="{{ request()->fullUrlWithQuery(['title' => null]) }}" class="badge-close">×</a>
            </div>
        @endif

        @if(!empty($activeFilters['categories']))
        @foreach((array)$activeFilters['categories'] as $category)
            <div class="filter-badge">
                Kategori: {{ $category }}
                <a href="{{ request()->fullUrlWithQuery(['category' => array_diff((array)request()->input('category', []), [$category])]) }}" class="badge-close">×</a>
            </div>
        @endforeach
    @endif
    
    @if(!empty($activeFilters['filters']))
        @foreach((array)$activeFilters['filters'] as $filter)
            <div class="filter-badge">
                @if($filter == 'populer')
                    Populer
                @elseif($filter == 'terbaru')
                    Terbaru
                @elseif($filter == 'hampir_tercapai')
                    Target Hampir Tercapai
                @endif
                <a href="{{ request()->fullUrlWithQuery(['filter' => array_diff((array)request()->input('filter', []), [$filter])]) }}" class="badge-close">×</a>
            </div>
        @endforeach
    @endif

    @if($notFound)
<div class="not-found-container">
    <div class="not-found-message">
            <img src="{{ asset('assets/img/icon/success-data.svg') }}" alt="Not Found" class="mb-3" style="width: 150px; height: 150px;">
        <h3>Pencarian Tidak Ditemukan</h3>
        <p>Maaf, kami tidak menemukan campaign yang sesuai dengan kriteria pencarian Anda.</p>
        <p>Silahkan coba dengan kata kunci atau filter yang berbeda.</p>
        <a href="{{ url('/eksplore-kampanye') }}" class="btn btn-primary mt-3">Lihat Semua Kampanye</a>
    </div>
</div>

@else
<div id="campaigns-container">
    @foreach ($campaigns as $campaign)
@include('includes.campaign-card', ['campaign' => $campaign])
@endforeach

</div>
@endif

@else
<div class="not-found-container">
    <div class="not-found-message">
            <img src="{{ asset('assets/img/icon/success-data.svg') }}" alt="Not Found" class="mb-3" style="width: 150px; height: 150px;">
        <h3>Pencarian Tidak Ditemukan</h3>
        <p>Maaf, kami tidak menemukan campaign yang sesuai dengan kriteria pencarian Anda.</p>
        <p>Silahkan coba dengan kata kunci atau filter yang berbeda.</p>
        <a href="{{ url('/eksplore-kampanye') }}" class="btn btn-primary mt-3">Lihat Semua Kampanye</a>
    </div>
</div>
@endif
      

      @if ($campaigns->hasMorePages())
      <button id="load-more" data-next-page="{{ $campaigns->nextPageUrl() }}" class="btn btn-primary mt-4">
          Lihat Lebih Banyak
      </button>
  @endif
  </div>

  </div>

    @include('includes.public.menu')

@endsection

@push('after-script')
<script>
    $(document).ready(function() {
        // Saat tombol "Lihat Lebih Banyak" diklik
        $('#load-more').click(function() {
            var nextPageUrl = $(this).data('next-page'); // Ambil URL untuk halaman berikutnya

            // Lakukan AJAX untuk memuat kampanye berikutnya
            $.ajax({
                url: nextPageUrl,
                type: 'GET',
                success: function(data) {
                    // Tambahkan kampanye yang baru ke dalam container kampanye
                    $('#campaigns-container').append(data.campaigns);

                    // Jika tidak ada lagi kampanye, sembunyikan tombol
                    if (!data.hasMorePages) {
                        $('#load-more').hide();
                    } else {
                        // Update URL untuk tombol "Lihat Lebih Banyak"
                        $('#load-more').data('next-page', data.nextPageUrl);
                    }
                },
                error: function() {
                    // Menangani error jika terjadi kesalahan pada AJAX
                    alert("Terjadi kesalahan, coba lagi.");
                }
            });
        });
    });
</script>
@endpush

