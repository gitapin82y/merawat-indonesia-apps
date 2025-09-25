@extends('layouts.public')
 
@section('title', 'Pencairan Dana Kampanye')

@push('after-style')
<script src="https://cdn.jsdelivr.net/npm/autonumeric"></script>
<style>
  .btn-upload {
      background: #e0e0e0;
      border: none;
      padding: 5px 10px;
      height: 52px !important;
      min-width: 120px;
      background: #aca9a933;
  }

  .btn-upload:hover {
      background: #d6d6d6 !important;
      transform: scale(1.05);
      color: #333 !important;
  }


  .bg-opacity {
      background-color: rgba(255, 71, 71, 0.1);
  }

  .btn-second {
      background-color: var(--second-color);
      color: var(--second-color);
  }


  .btn-second:hover {
      background-color: var(--bs-danger);
      color: white;
  }

  .form-floating select.form-select {
      padding-left: 21px;
      /* Sesuaikan padding kiri */
      height: calc(3.5rem + 2px);
      /* Sesuaikan tinggi agar sama dengan input */
  }

  .form-control[readonly] {
      color: black !important;
  }
</style>
@endpush

@section('content')

        <div class="navbar-back col-12 align-items-center d-flex">
          <a href="{{ url()->current() == url()->previous() ? url('admin/kampanye/' . $slug . '/kabar-pencairan') : url()->previous() }}" class="bg-white">
              <i class="fa-solid fa-angle-left"></i>
          </a>
          <h1 class="text-white mb-0 ms-2">Pencairan Dana</h1>
        </div>

        <main class="container mt-3">
            <div class="pencairan-dana-container">
              <!-- div class="container mt-4 flex-grow-1"> -->
              <div class="alert alert-light shadow-sm d-flex align-items-center">
                <img src="{{asset('assets/img/icon/form-data.svg')}}" alt="Info" class="me-3" style="width: 120px; height: 120px;" />
                <div>
                  <h6 class="fw-bold">Ingin Mencairkan Dana?</h6>
                  <p class="mb-0">
                      Pencairan dana memerlukan persetujuan dari admin, pencairan dana yang sudah disetujui admin, otomatis akan muncul pada kabar pencairan dan donatur dapat mengunduh rincian penggunaan dana untuk tujuan transparansi.
                  </p>
                </div>
              </div>
          
              <form action="{{ route('kabar-pencairan.store') }}"  method="POST" enctype="multipart/form-data" id="formData" class="pb-5">
                @csrf
                <input type="hidden" name="campaign_id" value="{{$idKampanye}}">
                <input type="hidden" name="admin_id" value="{{Auth::user()->admin->id}}">

                <div class="form-floating mb-3">
                  <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror" id="payment_method">
                      <option value="">Nama Bank</option>
                      <option value="bca">Bank BCA</option>
                      <option value="mandiri">Bank Mandiri</option>
                      <option value="bni">Bank BNI</option>
                      <option value="bri">Bank BRI</option>
                      <option value="cimb">Bank CIMB Niaga</option>
                      <option value="btn">Bank BTN</option>
                      <option value="danamon">Bank Danamon</option>
                      <option value="permata">Bank Permata</option>
                      <option value="bsi">Bank Syariah Indonesia</option>
                  </select>
                  <label for="payment_method">Akun Bank</label>
                  @error('payment_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
                
                <div class="form-floating mb-3">
                  <input type="text" name="account_name" class="form-control @error('account_name') is-invalid @enderror" id="account_name"
                      value="{{ old('account_name') }}" placeholder="Nama Rekening">
                  <label for="account_name">Nama Rekening</label>
                  @error('account_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              <div class="form-floating mb-3">
                <input type="text" name="account_number" class="form-control @error('account_number') is-invalid @enderror" id="account_number"
                    value="{{ old('account_number') }}" placeholder="Nomor Rekening">
                <label for="account_number">Nomor Rekening</label>
                @error('account_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-floating mb-3">
              <input type="text" name="amount" class="form-control @error('amount') is-invalid @enderror" id="amount"
                  value="{{ old('amount') }}" placeholder="Jumlah Pencairan Dana">
              <label for="amount">Jumlah Pencairan Dana</label>
              @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
              <small class="mt-1">Tidak boleh melebihi jumlah donasi kampanye saat ini : <strong class="text-danger">{{$current_donation}}</strong></small>
          </div>
                
          <div class="mb-3 position-relative">
            <div class="form-floating">
                <input type="text" class="form-control bg-white" id="laporanRAB" placeholder="Laporan RAB"
                    style="padding-right: 120px; pointer-events: none;" readonly>
                <label for="laporanRAB">Laporan RAB</label>
            </div>
            <input type="file" id="fileRAB" name="document_rab" class="d-none @error('document_rab') is-invalid @enderror" onchange="updateInput('laporanRAB', this)">
            <button type="button"  class="btn btn-upload position-absolute"
                style="right: 3px; top: 50%; transform: translateY(-50%); border-radius: 5px;"
                onclick="event.preventDefault();document.getElementById('fileRAB').click();">Unggah File</button>
        </div>
            @error('document_rab')
            <div class="invalid-file">{{ $message }}</div>
        @enderror
                
                <div class="upload-info mb-3">
                  <p>Unggah rincian laporan penggunaan dana sesuai dengan jumlah pencairan dana PDF/excel, setelah dana dicarikan segera untuk membuat kabar terbaru beserta bukti berupa foto bahwa dana sudah tersalurkan.</p>
                </div>
              </form>
            </div>
        </main>

        <div class="footer mb-5 text-center">
          <div class="main-menu row col-12 mx-0 justify-content-between d-flex ">
              <button type="button" id="submitForm" class="button w-100 d-flex align-items-center justify-content-center text-white shadow-sm">
              <img src="{{asset('assets/img/icon/edit-profile.svg')}}" alt="Kirim"
                style="width: 20px; height: 20px; margin-right: 8px;" />
              <span class="text-white">Pencairan Dana</span>
            </button>
          </div>
      </div>


@endsection

@push('after-script')
<script>
  function updateInput(inputId, fileInput) {
      document.getElementById(inputId).value = fileInput.files[0] ? fileInput.files[0].name : "";
  }

  $(document).ready(function() {
    $('#submitForm').on('click', function(e) {
        e.preventDefault();
        
// Ambil nilai pencairan dari input (contoh: "Rp 901.452")
var rupiah = $('#amount').val() || '';
// Hapus semua yang bukan angka (menjadi "901452")
var cleanAmount = rupiah.replace(/[^0-9]/g, '');
// Ubah ke integer
var amountValue = parseInt(cleanAmount, 10);

// Ambil nilai current_donation dari Blade (contoh: "Rp 901.452")
var currentDonationRaw = "{{ $current_donation }}" || '';
var cleanDonation = currentDonationRaw.replace(/[^0-9]/g, '');
var currentDonation = parseInt(cleanDonation, 10);

// Debug
console.log('amountValue =>', amountValue, typeof amountValue);
console.log('currentDonation =>', currentDonation, typeof currentDonation);
          
// Validasi dasar: pastikan bukan NaN
if (isNaN(amountValue) || isNaN(currentDonation)) {
    Swal.fire({
        icon: 'error',
        title: 'Data Tidak Valid',
        text: 'Nilai pencairan atau saldo saat ini tidak valid.'
    });
    return false;
}

// CATATAN: gunakan ">" jika pencairan boleh sama dengan saldo.
// Jika Anda menggunakan ">=" maka nilai yang sama akan ditolak.
if (amountValue > currentDonation) {
    Swal.fire({
        icon: 'error',
        title: 'Jumlah Tidak Valid',
        text: 'Jumlah pencairan dana tidak boleh melebihi Rp ' + new Intl.NumberFormat('id-ID').format(currentDonation),
        confirmButtonText: 'OK'
    });
    return false;
}
        
        // Jika validasi berhasil, tampilkan konfirmasi
        Swal.fire({
            title: 'Konfirmasi Pengiriman Data',
            text: 'Apakah Anda yakin ingin mengirim request?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Kirim',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#amount').val(cleanAmount);
                $('#formData').submit();
            }
        });
    });
});

  new AutoNumeric('#amount', {
          digitGroupSeparator: '.',
          decimalCharacter: ',',
          currencySymbol: 'Rp ',
          unformatOnSubmit: true,
          decimalPlaces: 0
      });

      document.getElementById('account_number').addEventListener('input', function (e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

  // Flash messages from session
  @if(session('success'))
  Swal.fire({
    icon: 'success',
    title: 'Berhasil!',
    text: "{{ session('success') }}",
    timer: 3000
  });
  @endif

  @if(session('error'))
  Swal.fire({
    icon: 'error',
    title: 'Error!',
    text: "{{ session('error') }}",
    timer: 3000
  });
  @endif
</script>
@endpush

