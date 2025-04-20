@forelse($kabarPencairan as $index => $pencairan)
<div class="accordion-item">
    <h2 class="accordion-header">
        <button class="accordion-button collapsed d-flex justify-content-between align-items-center"
            type="button" data-bs-toggle="collapse" data-bs-target="#collapsePencairan{{ $pencairan->id }}">
            <div class="d-flex align-items-center gap-3">
                @php
                    // Calculate the correct index number based on pagination
                    $itemNumber = ($kabarPencairan->currentPage() - 1) * $kabarPencairan->perPage() + $loop->iteration;
                @endphp
                <div class="circle-number">{{ $itemNumber }}</div>
                <div>
                    <div class="text-muted small mb-1">{{ $pencairan->created_at->format('d F Y') }}</div>
                    <div class="fw-bold text-danger">{{ $pencairan->title }}</div>
                </div>
            </div>
            <i class="fa-solid fa-chevron-down ms-auto circle-dropdown"></i>
        </button>
    </h2>
    <div id="collapsePencairan{{ $pencairan->id }}" class="accordion-collapse collapse"
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