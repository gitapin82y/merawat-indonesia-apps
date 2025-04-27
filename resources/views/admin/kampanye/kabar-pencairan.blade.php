@extends('layouts.public')
 
@section('title', 'Kabar Pencairan Kampanye')

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

    <div class="navbar-back col-12 align-items-center d-flex">
        <a href="{{ url('admin/kampanye/' . $slug) }}" class="bg-white">
            <i class="fa-solid fa-angle-left"></i>
        </a>
        <h1 class="text-white mb-0 ms-2">Kabar Pencairan</h1>
    </div>

    <div class="container mt-4 flex-grow-1" style="padding-bottom:800px;">
        <div class="d-flex flex-column gap-3">
          <div class="accordion" id="kabarPencairanAccordion">
            @forelse($kabarPencairan as $index => $pencairan)
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <div class="accordion-button collapsed d-flex justify-content-between align-items-center"
                        type="button" data-bs-toggle="collapse" data-bs-target="#collapsePencairan{{ $index }}">
                        <div class="d-flex align-items-center gap-3">
                            <div class="circle-number">{{ $index + 1 }}</div>
                            <div class="d-flex align-items-center bg-opacity rounded-pill px-3 py-1">
                                <small class="text-second">{{$pencairan->status}}</small>
                            </div>
                            <div>
                                <div class="text-muted small mb-1">{{ $pencairan->created_at->format('d F Y') }}</div>
                                <div class="fw-bold text-danger">{{ $pencairan->title }}</div>
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-down ms-auto circle-dropdown"></i>
                    </div>
                </h2>
                <div id="collapsePencairan{{ $index }}" class="accordion-collapse collapse"
                    data-bs-parent="#kabarPencairanAccordion">
                    <div class="accordion-body">
                        <p>{!! $pencairan->description !!}</p>
                        <a href="{{ asset('storage/' . $pencairan->document_rab) }}" target="_blank" class="button my-3 d-block text-center">
                            <i class="fa-solid fa-download"></i> &nbsp; Laporan Penggunaan Dana
                        </a>
                    </div>
                </div>
            </div>
            @empty
                <div class="text-center">
                    <img src="{{ asset('assets/img/icon/success-data.svg') }}" alt="Not Found" class="mb-3" style="width: 150px; height: 150px;">
                    <p>Belum ada kabar pencairan</p>
                </div>
            @endforelse

            <div class="upload-info mb-3 mt-4">
                <p>Kabar pencairan akan di tampilkan di publik jika status pencairan dana sudah disetujui</p>
            </div>
        </div>
        </div>
      </div>

    @include('includes.public.footer-button', ['buttonTitle' => 'Ajukan Pencairan Dana', 'buttonLink' =>url('admin/kampanye/' . $slug .'/pencairan-dana')])


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

