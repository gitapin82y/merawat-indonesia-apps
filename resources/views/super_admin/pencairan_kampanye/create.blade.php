@extends('layouts.admin')
 
@section('title', 'Tambah Pencairan Dana Kampanye')

@push('after-style')
@endpush

@section('content')
<div class="card mb-4">
    <div class="card-header bg-danger py-3 align-items-center justify-content-between row m-0">
        <div class="col-12 col-sm-6 p-0">
            <h4 class="m-0 font-weight-bold float-left text-white">Tambah Pencairan Dana Kampanye</h4>
        </div>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Super admin dapat langsung menambahkan pencairan dana tanpa perlu menunggu pengajuan dari yayasan. Pencairan akan langsung <strong>DISETUJUI</strong> dan mengurangi saldo kampanye.
        </div>

        <form action="{{ route('pencairan-kampanye.store') }}" method="POST" enctype="multipart/form-data" id="pencairanForm">
            @csrf
            
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group mb-3">
                        <label>Pilih Kampanye <span class="text-danger">*</span></label>
                        <select name="campaign_id" id="select2Campaign" class="form-control @error('campaign_id') is-invalid @enderror" required>
                            <option value="">Pilih Kampanye</option>
                            @foreach($campaigns as $campaign)
                                <option value="{{ $campaign->id }}" 
                                        data-donation="{{ $campaign->current_donation_real }}"
                                        data-admin="{{ $campaign->admin_id }}"
                                        {{ old('campaign_id') == $campaign->id ? 'selected' : '' }}>
                                    {{ $campaign->title }} - {{ $campaign->admin->name }} (Saldo: Rp {{ number_format($campaign->current_donation_real, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                        @error('campaign_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Hanya kampanye dengan saldo donasi tersedia yang ditampilkan</small>
                    </div>

                    <!-- Hidden admin_id will be filled by JavaScript -->
                    <input type="hidden" name="admin_id" id="admin_id" value="{{ old('admin_id') }}">

                    <div class="alert alert-warning d-none" id="saldoAlert">
                        <i class="fas fa-exclamation-triangle"></i> <strong>Saldo Kampanye:</strong> <span id="saldoKampanye">Rp 0</span>
                    </div>

                   <div class="form-group mb-3">
    <label>Jumlah Pencairan <span class="text-danger">*</span></label>
    <input type="text" name="amount_display" id="amount_display" class="form-control @error('amount') is-invalid @enderror" 
           value="{{ old('amount') ? 'Rp ' . number_format(old('amount'), 0, ',', '.') : '' }}" 
           placeholder="Rp 0" autocomplete="off">
    <input type="hidden" name="amount" id="amount" value="{{ old('amount') }}">
    @error('amount')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="text-muted">Jumlah pencairan tidak boleh melebihi saldo kampanye</small>
</div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>Bank <span class="text-danger">*</span></label>
                                <select name="payment_method" class="form-control @error('payment_method') is-invalid @enderror" required>
                                    <option value="">Pilih Bank</option>
                                    <option value="bca" {{ old('payment_method') == 'bca' ? 'selected' : '' }}>Bank BCA</option>
                                    <option value="mandiri" {{ old('payment_method') == 'mandiri' ? 'selected' : '' }}>Bank Mandiri</option>
                                    <option value="bni" {{ old('payment_method') == 'bni' ? 'selected' : '' }}>Bank BNI</option>
                                    <option value="bri" {{ old('payment_method') == 'bri' ? 'selected' : '' }}>Bank BRI</option>
                                    <option value="cimb" {{ old('payment_method') == 'cimb' ? 'selected' : '' }}>Bank CIMB Niaga</option>
                                    <option value="btn" {{ old('payment_method') == 'btn' ? 'selected' : '' }}>Bank BTN</option>
                                    <option value="danamon" {{ old('payment_method') == 'danamon' ? 'selected' : '' }}>Bank Danamon</option>
                                    <option value="permata" {{ old('payment_method') == 'permata' ? 'selected' : '' }}>Bank Permata</option>
                                    <option value="bsi" {{ old('payment_method') == 'bsi' ? 'selected' : '' }}>Bank Syariah Indonesia</option>
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>Nama Pemilik Rekening <span class="text-danger">*</span></label>
                                <input type="text" name="account_name" class="form-control @error('account_name') is-invalid @enderror" 
                                       value="{{ old('account_name') }}" placeholder="Nama pemilik rekening" required>
                                @error('account_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label>Nomor Rekening <span class="text-danger">*</span></label>
                        <input type="text" name="account_number" class="form-control @error('account_number') is-invalid @enderror" 
                               value="{{ old('account_number') }}" placeholder="Nomor rekening" required>
                        @error('account_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label>Dokumen RAB <span class="text-danger">*</span></label>
                        <input type="file" name="document_rab" class="form-control @error('document_rab') is-invalid @enderror" 
                               accept=".pdf,.doc,.docx,.xls,.xlsx" required>
                        @error('document_rab')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Format: PDF, DOC, DOCX, XLS, XLSX (Max: 5MB)</small>
                    </div>

                    <div class="form-group mb-3">
                        <label>Bukti Pencairan <span class="text-danger">*</span></label>
                        <input type="file" name="bukti_pencairan" class="form-control @error('bukti_pencairan') is-invalid @enderror" 
                               accept="image/*,.pdf" required>
                        @error('bukti_pencairan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Upload bukti transfer/pencairan dana (Format: JPG, PNG, PDF - Max: 2MB)</small>
                    </div>
                </div>
            </div>

            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> <strong>Perhatian:</strong>
                <ul class="mb-0 mt-2">
                    <li>Pencairan dana oleh super admin akan langsung <strong>DISETUJUI</strong></li>
                    <li>Saldo kampanye akan langsung berkurang</li>
                    <li>Kabar pencairan akan otomatis dibuat dengan status <strong>DISETUJUI</strong></li>
                    <li>Pastikan semua data sudah benar sebelum menyimpan</li>
                </ul>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-save"></i> Simpan dan Setujui Pencairan
                </button>
                <a href="{{ route('pencairan-kampanye.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('after-script')
<script>
$(document).ready(function() {
    // Initialize Select2
    $('#select2Campaign').select2({
        placeholder: 'Pilih Kampanye',
        allowClear: true,
        width: '100%',
    });

    // Format Rupiah untuk input amount
    function formatRupiah(angka, prefix = 'Rp ') {
        var number_string = angka.replace(/[^,\d]/g, '').toString(),
            split = number_string.split(','),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return prefix + rupiah;
    }

    // Auto format saat typing
    $('#amount_display').on('keyup', function(e) {
        var value = $(this).val();
        $(this).val(formatRupiah(value));
        
        // Update hidden input dengan angka murni
        var numberValue = value.replace(/[^0-9]/g, '');
        $('#amount').val(numberValue);
        
        // Trigger validation
        $('#amount').trigger('input');
    });

    // Paste event
    $('#amount_display').on('paste', function(e) {
        setTimeout(function() {
            var value = $('#amount_display').val();
            $('#amount_display').val(formatRupiah(value));
            var numberValue = value.replace(/[^0-9]/g, '');
            $('#amount').val(numberValue);
            $('#amount').trigger('input');
        }, 100);
    });

    // Update saldo dan admin_id saat campaign dipilih
    $('#select2Campaign').on('change', function() {
        var selected = $(this).find('option:selected');
        var saldo = selected.data('donation');
        var adminId = selected.data('admin');
        
        if (saldo !== undefined) {
            $('#saldoKampanye').text('Rp ' + new Intl.NumberFormat('id-ID').format(saldo));
            $('#saldoAlert').removeClass('d-none');
            $('#admin_id').val(adminId);
            
            // Set max amount
            $('#amount').attr('max', saldo);
        } else {
            $('#saldoAlert').addClass('d-none');
            $('#admin_id').val('');
            $('#amount').removeAttr('max');
        }
    });

// Validasi amount tidak melebihi saldo
    $('#amount').on('input', function() {
        var amount = parseInt($(this).val()) || 0;
        var saldo = parseInt($('#select2Campaign').find('option:selected').data('donation')) || 0;
        
        // Tampilkan error di amount_display
        if (amount > saldo) {
            $('#amount_display').addClass('is-invalid');
            $('#amount_display').siblings('.invalid-feedback').remove();
            $('#amount_display').after('<div class="invalid-feedback d-block">Jumlah pencairan tidak boleh melebihi saldo kampanye (Rp ' + new Intl.NumberFormat('id-ID').format(saldo) + ')</div>');
        } else if (amount == 0 || isNaN(amount)) {
            $('#amount_display').addClass('is-invalid');
            $('#amount_display').siblings('.invalid-feedback').remove();
            $('#amount_display').after('<div class="invalid-feedback d-block">Jumlah pencairan harus diisi</div>');
        } else {
            $('#amount_display').removeClass('is-invalid');
            $('#amount_display').siblings('.invalid-feedback').remove();
        }
    });

// Form validation before submit
    $('#pencairanForm').on('submit', function(e) {
        var amount = parseInt($('#amount').val()) || 0;
        var saldo = parseInt($('#select2Campaign').find('option:selected').data('donation')) || 0;
        
        if (amount <= 0 || isNaN(amount)) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal',
                text: 'Jumlah pencairan harus diisi!',
                timer: 3000,
                showConfirmButton: false
            });
            $('#amount_display').focus();
            return false;
        }
        
        if (amount > saldo) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal',
                text: 'Jumlah pencairan tidak boleh melebihi saldo kampanye!',
                timer: 3000,
                showConfirmButton: false
            });
            $('#amount_display').focus();
            return false;
        }
    });

    // Success/Error Messages
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session('success') }}',
            timer: 3000,
            showConfirmButton: false
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: '{{ session('error') }}',
            timer: 3000,
            showConfirmButton: false
        });
    @endif
});
</script>
@endpush