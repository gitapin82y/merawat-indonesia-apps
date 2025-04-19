@extends('layouts.admin')
 
@section('title', 'Lihat Kabar Terbaru')

@push('after-style')
<style>
    .accordion-button::after {
        display: none;
    }
    .accordion-item {
        border-radius: 12px;
        margin-bottom: 10px;
        box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.1);
    }
    /* Make sure cursor shows it's clickable */
    .accordion-button {
        cursor: pointer;
    }
    
</style>
@endpush

@section('content')
<div class="card mb-4">
    <div class="card-header bg-danger py-3 align-items-center justify-content-between row m-0">
        <div class="col-12 col-sm-6 p-0">
            <h4 class="m-0 font-weight-bold float-left text-white">Detail Kabar Terbaru</h4>
        </div>
    </div>
    <div class="card-body">
        @forelse($kabarTerbaru as $index => $kabar)
        <div class="accordion-item">
            <h2 class="accordion-header" id="heading{{ $index }}">
                <button class="accordion-button collapsed d-flex justify-content-between align-items-center"
                    type="button" 
                    data-bs-toggle="collapse" 
                    data-bs-target="#collapseKabarTerbaru{{ $index }}" 
                    aria-expanded="false" 
                    aria-controls="collapseKabarTerbaru{{ $index }}">
                    <div class="d-flex align-items-center gap-3">
                        <div class="circle-number">{{ $index + 1 }}</div>
                        <div>
                            <div class="text-muted small mb-1">{{ $kabar->created_at->format('d F Y') }}</div>
                            <div class="fw-bold text-danger">{{ $kabar->title }}</div>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-down ms-auto circle-dropdown"></i>
                </button>
            </h2>
            <div id="collapseKabarTerbaru{{ $index }}" class="accordion-collapse collapse" 
                aria-labelledby="heading{{ $index }}">
                <div class="accordion-body">
                    {!! $kabar->description !!}
                    @if($kabar->image)
                        <img src="{{ asset($kabar->image) }}" alt="{{ $kabar->title }}" class="img-fluid rounded">
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="text-center">
            <img src="{{ asset('assets/img/icon/success-data.svg') }}" alt="Not Found" class="mb-3" style="width: 150px; height: 150px;">
            <p>Belum ada kabar terbaru</p>
        </div>
        @endforelse
        <div class="form-group">
            <a href="{{ route('kabar-terbaru.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </div>
</div>
@endsection

@push('after-script')
<script>
    // Make sure Bootstrap is fully loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Manual toggle approach if Bootstrap's built-in toggle isn't working
        document.querySelectorAll(".accordion-button").forEach((button) => {
            button.addEventListener("click", function(e) {
                // Stop event propagation
                e.preventDefault();
                e.stopPropagation();
                
                // Get the target from data-bs-target
                const targetId = this.getAttribute('data-bs-target');
                const targetElement = document.querySelector(targetId);
                
                // Toggle the 'show' class
                if (targetElement) {
                    targetElement.classList.toggle('show');
                    
                    // Toggle aria-expanded
                    const expanded = this.getAttribute('aria-expanded') === 'true';
                    this.setAttribute('aria-expanded', !expanded);
                    
                    // Toggle icon
                    const icon = this.querySelector("i");
                    icon.classList.toggle("fa-chevron-up");
                    icon.classList.toggle("fa-chevron-down");
                }
            });
        });
    });
</script>
@endpush