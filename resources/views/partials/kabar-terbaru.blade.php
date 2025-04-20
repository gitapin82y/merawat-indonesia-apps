@forelse($kabarTerbaru as $index => $kabar)
<div class="accordion-item">
    <h2 class="accordion-header">
        <button class="accordion-button collapsed d-flex justify-content-between align-items-center"
            type="button" data-bs-toggle="collapse" data-bs-target="#collapseKabarTerbaru{{ $kabar->id }}">
            <div class="d-flex align-items-center gap-3">
                @php
                // Calculate the correct index number based on pagination
                $itemNumber = ($kabarTerbaru->currentPage() - 1) * $kabarTerbaru->perPage() + $loop->iteration;
            @endphp
            <div class="circle-number">{{ $itemNumber }}</div>
                <div>
                    <div class="text-muted small mb-1">{{ $kabar->created_at->format('d F Y') }}</div>
                    <div class="fw-bold text-danger">{{ $kabar->title }}</div>
                </div>
            </div>
            <i class="fa-solid fa-chevron-down ms-auto circle-dropdown"></i>
        </button>
    </h2>
    <div id="collapseKabarTerbaru{{ $kabar->id }}" class="accordion-collapse collapse"
        data-bs-parent="#penyaluranDanaAccordion">
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