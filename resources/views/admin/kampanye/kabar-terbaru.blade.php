@extends('layouts.public')
 
@section('title', 'Kabar Terbaru Kampanye')

@push('after-style')
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
      box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.1);
  }

</style>
@endpush

@section('content')

    @include('includes.public.navbar-back', ['title' => 'Kabar Terbaru'])

    <div class="container mt-4 flex-grow-1" style="padding-bottom:800px;">
      <div class="d-flex flex-column gap-3">
          <div class="accordion" id="penyaluranDanaAccordion">
            @foreach($kabarTerbaru as $index => $kabar)
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <div class="accordion-button collapsed d-flex justify-content-between align-items-center"
                        type="button" data-bs-toggle="collapse" data-bs-target="#collapseKabarTerbaru{{ $index }}">
                        <div class="d-flex align-items-center gap-3">
                            <div class="circle-number">{{ $index + 1 }}</div>
                            <div>
                                <div class="text-muted small mb-1">{{ $kabar->created_at->format('d F Y') }}</div>
                                <div class="fw-bold text-danger">{{ $kabar->title }}</div>
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-down ms-auto circle-dropdown"></i>
                    </div>
                </h2>
                <div id="collapseKabarTerbaru{{ $index }}" class="accordion-collapse collapse"
                    data-bs-parent="#penyaluranDanaAccordion">
                    <div class="accordion-body">
                        {!! $kabar->description !!}
                        @if($kabar->image)
                            <img src="{{ asset($kabar->image) }}" alt="{{ $kabar->title }}" class="img-fluid rounded">
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

          <div class="upload-info mb-3">
            <p>Donatur sedang menunggu kabar terbaru dari kampanye. Pastikan Anda selalu mengupdate kabar hingga semua dana sudah tersalurkan.</p>
          </div>


      </div>
  </div>
        
        @include('includes.public.footer-button', ['buttonTitle' => 'Buat Kabar Terbaru', 'buttonLink' => url('admin/kampanye/' . $title .'/buat-kabar')])

@endsection

@push('after-script')
<script>
  // Toggle icon on accordion collapse
  document.querySelectorAll(".accordion-button").forEach((button) => {
      button.addEventListener("click", () => {
          const icon = button.querySelector("i");
          icon.classList.toggle("fa-chevron-up");
          icon.classList.toggle("fa-chevron-down");
      });
  });
</script>
@endpush

