{{-- resources/views/super_admin/legal_documents/index.blade.php --}}
@extends('layouts.admin')
 
@section('title', 'Manage Legal Documents')

@push('after-style')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Pengaturan Dokumen Legal</h1>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-danger">Kebijakan Privasi</h6>
                </div>
                <div class="card-body">
                    <p>Terakhir diperbarui: 
                        @if(isset($privacyPolicy) && is_object($privacyPolicy) && is_object($privacyPolicy->last_updated) && method_exists($privacyPolicy->last_updated, 'format'))
                            {{ $privacyPolicy->last_updated->format('d F Y H:i:s') }}
                        @else
                            Belum Ada
                        @endif
                    </p>
                    <a href="#" class="btn btn-danger btn-edit-document" data-toggle="modal" data-target="#editDocumentModal" data-type="{{ App\Models\LegalDocument::PRIVACY_POLICY }}" data-title="Kebijakan Privasi">
                        <i class="fas fa-edit fa-sm"></i> Edit Kebijakan Privasi
                    </a>
                    <a href="{{ route('privacy.policy') }}" class="btn btn-secondary" target="_blank">
                        <i class="fas fa-eye fa-sm"></i> Lihat Halaman
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-danger">Syarat dan Ketentuan</h6>
                </div>
                <div class="card-body">
                    <p>Terakhir diperbarui: {{ $termsOfService ? $termsOfService->last_updated->format('d F Y H:i:s') : 'Belum Ada' }}</p>
                    <a href="#" class="btn btn-danger btn-edit-document" data-toggle="modal" data-target="#editDocumentModal" data-type="{{ App\Models\LegalDocument::TERMS_OF_SERVICE }}" data-title="Syarat dan Ketentuan">
                        <i class="fas fa-edit fa-sm"></i> Edit Syarat dan Ketentuan
                    </a>
                    <a href="{{ route('terms.service') }}" class="btn btn-secondary" target="_blank">
                        <i class="fas fa-eye fa-sm"></i> Lihat Halaman
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Document Modal -->
<div class="modal fade" id="editDocumentModal" tabindex="-1" role="dialog" aria-labelledby="editDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="editDocumentModalLabel">Edit Dokumen</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="documentForm">
                    <input type="hidden" id="document_type" name="type">
                    <div class="form-group">
                        <textarea id="document_content" name="content" class="form-control summernote"></textarea>
                        <div class="invalid-feedback" id="error-content"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="saveDocumentBtn">Simpan</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('after-script')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
<script>
    $(document).ready(function() {
        // Init Summernote
        $('.summernote').summernote({
            height: 500,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });

        // Handle edit document button click
        $('.btn-edit-document').click(function() {
            const type = $(this).data('type');
            const title = $(this).data('title');
            
            $('#document_type').val(type);
            $('#editDocumentModalLabel').text('Edit ' + title);
            
            // Load document content
            $.ajax({
                url: `/super-admin/legal-documents/${type}`,
                method: 'GET',
                success: function(response) {
                    if (response.document) {
                        $('#document_content').summernote('code', response.document.content);
                    } else {
                        $('#document_content').summernote('code', '');
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat dokumen.',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            });
        });

        // Handle save document button click
        $('#saveDocumentBtn').click(function() {
            const type = $('#document_type').val();
            const content = $('#document_content').summernote('code');
            
            $.ajax({
                url: `/super-admin/legal-documents/${type}`,
                method: 'POST',
                data: {
                    content: content,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#editDocumentModal').modal('hide');
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    }).then(function() {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        if (xhr.responseJSON.errors.content) {
                            $('#document_content').addClass('is-invalid');
                            $('#error-content').text(xhr.responseJSON.errors.content[0]);
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal menyimpan dokumen.',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                }
            });
        });
    });
</script>
@endpush