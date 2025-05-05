<style>
    .fundraising-title {
      font-size: 1rem !important;
      font-weight: bold;
      color: var(--title-color);
    }

    .btn-action {
      font-size: 14px;
      padding: 5px 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 5px;
    }

    .btn-salin {
      width: 94px;
      height: 31px;
      background-color: var(--second-color);
      color: white;
      border: none;
    }

    .btn-cairkandana {
      width: 129px;
      height: 31px;
      background-color: var(--second-color);
      color: white;
      border: none;
      text-decoration: none;
    }

    .btn-cairkandana:hover {
      background-color: var(--second-color) !important;
      color: white !important;
      opacity: 0.9 !important;
    }

    .fundraising-section h2 {
      font-size: 18px;
    }

    .fundraising-section p {
      font-size: 14px;
    }

    .fundraising-section .button {
      font-size: 14px;
      padding: 8px 16px;
    }

    .popup {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }

    .popup-content {
      background: white;
      padding: 20px;
      border-radius: 10px;
      width: 90%;
      max-width: 400px;
      text-align: center;
      position: relative;
    }

    .popup-content h3,
    .saldo {
      text-align: left;
      display: block;
    }

    .saldo {
      font-size: 18px;
      font-weight: bold;
      color: var(--second-color);
      background: var(--primary-light);
      padding: 5px 10px;
      border-radius: 5px;
      width: fit-content;
    }

    .input-field {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    .info {
      font-size: 12px;
      color: #ff901a;
      background: var(--primary-light);
      padding: 10px;
      border-radius: 5px;
    }

    .btn-submit {
      background: var(--second-color);
      color: white;
      padding: 10px;
      width: 100%;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    .step-circle {
      background-color: #ffeded;
      color: var(--second-color);
      padding: 8px 12px;
      border-radius: 50%;
      font-weight: bold;
    }

    .step-title {
      font-weight: bold;
      color: var(--second-color);
    }

    select {
      padding-right: 35px;
      appearance: none;
      background-image: url('data:image/svg+xml;charset=UTF-8,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%23888888"%3E%3Cpath d="M7 10l5 5 5-5H7z"/%3E%3C/svg%3E');
      background-repeat: no-repeat;
      background-position: right 10px center;
      background-size: 20px;
    }

    .kampanye-item {
      text-decoration: none;
      color: inherit;
      display: flex;
      margin-bottom: 20px;
    }

    .kampanye-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .donasi-info {
        font-size: 14px;
      }

    @media (max-width: 480px) {
      .donasi-header {
        font-size: 11px;
      }
      .donasi-info {
        font-size: 9px;
      }
      .btn-salin,
      .btn-cairkandana {
        width: 80px;
        height: 25px;
        font-size: 10px;
        padding: 4px 8px;
      }
      .fundraising-section h2 {
        font-size: 13px;
      }
      .fundraising-section p {
        font-size: 11px;
      }
      .fundraising-section .button {
        font-size: 12px;
        padding: 6px 12px;
      }
    }
  </style>

        <!-- Fundraising Kampanye -->
        <div class="container mt-4">
            <div class="row kampanye mt-4">
              <div class="justify-content-between d-flex">
                <h2 id="kategoriTitle">Fundraising Kampanye Anda</h2>
              </div>
              
              @foreach($fundraisings as $fundraising)
              <div class="col-12 row kampanye-item">
                  <a href="{{ route('campaign.detail', $fundraising->campaign->slug) }}" class="col-6 align-self-center">
                      <img
                          src="{{ asset('storage/' . $fundraising->campaign->photo) }}"
                          class="image-campaign-card"
                          alt="{{ $fundraising->campaign->title }}"
                      />
                  </a>
                  <div class="col-6 p-0 align-self-center">
                      <h3 class="mb-2 donasi-header">
                          {{ $fundraising->campaign->title }}
                      </h3>
                      <p class="mb-0 donasi-info">Berhasil mengajak <b>{{ $fundraising->total_donatur }}</b> donatur</p>
                      <p class="mb-0 donasi-info">Total donasi <b>{{ number_format($fundraising->jumlah_donasi) }}</b></p>
                      <p class="mb-2 donasi-info">
                          Pendapatan dari total donasi <b>{{ number_format($fundraising->commission) }}</b>
                      </p>
                    <button type="button" class="btn btn-salin btn-action d-inline" data-link="{{ route('campaign.referral', ['slug' => $fundraising->campaign->slug, 'code' => $fundraising->code_link]) }}">Salin Link</button>
                  </div>
              </div>
              @endforeach
            </div>
        </div>
  
          <!-- Line spacing sebelum Fundraising Section -->
          <div class="line-spacing"></div>
  
          <div class="container">
            <!-- Fundraising Section -->
            <div
              class="row col-12 mx-0 pb-md-2 pt-md-0 pb-5 pt-4 mb-3 fundraising-section"
            >
              <div class="col-5 align-self-center">
                <img
                  src="{{asset('assets/img/fundraishing.png')}}"
                  width="100%"
                  alt="Fundraising Illustration"
                />
              </div>
              <div class="col-7 align-self-center">
                <h2>Total Pendapatan {{ number_format($totalCommission) }}</h2>
                <p class="mb-3">
                    Anda dapat mencairkan semua pendapatan fundraising minimal 100.000
                </p>
                    <a href="javascript:void(0)" data-komisi="{{$totalCommission}}" class="btn-cairkandana btn-action">
                        Cairkan Dana
                    </a>
              </div>
            </div>
          </div>
  
          <!-- Line spacing setelah Fundraising Section -->
          <div class="line-spacing"></div>
  
          <div class="container">
            <!-- Panduan Fundraising -->
            <h6 class="fundraising-title text-start mt-3">Panduan Fundraising</h6>
  
            <!-- Langkah 1 -->
            <div class="card fundraising-card border-0 p-3 mb-3 rounded-3">
              <div class="d-flex align-items-center">
                <div class="step-circle me-3">1</div>
                <div>
                  <h6 class="step-title">Simpan Link Fundraising</h6>
                  <p class="m-0 text-muted">
                    Sebarkan kebaikan melalui link fundraising dari salah satu
                    program kampanye yang ingin Anda bantu.
                  </p>
                </div>
              </div>
            </div>
  
            <!-- Langkah 2 -->
            <div class="card fundraising-card border-0 p-3 mb-3 rounded-3">
              <div class="d-flex align-items-center">
                <div class="step-circle me-3">2</div>
                <div>
                  <h6 class="step-title">Pendapatan Komisi</h6>
                  <p class="m-0 text-muted">
                    Setiap donatur yang klik link Anda melalui platform apa pun,
                    Anda akan mendapatkan komisi sebesar {{$commission}}% per transaksi.
                  </p>
                </div>
              </div>
            </div>
  
            <!-- Langkah 3 -->
            <div class="card fundraising-card border-0 p-3 rounded-3 mb-5">
              <div class="d-flex align-items-center">
                <div class="step-circle me-3">3</div>
                <div>
                  <h6 class="step-title">Pencairan Dana Fundraising</h6>
                  <p class="m-0 text-muted">
                    Dapatkan hingga puluhan juta hanya dengan menyebarkan kebaikan
                    dengan mengajak orang donasi melalui fundraising.
                  </p>
                </div>
              </div>
            </div>
  
            <!-- Popup Cairkan Dana -->
            <div id="popupCairkanDana" class="popup">
              <div class="popup-content">
                <h3>Pencairan Dana Fundraising</h3>
                <p class="saldo">Rp {{ number_format($totalCommission) }}</p>
                   <!-- Menampilkan pesan error/success -->
    @if(session('error'))
    <div class="alert alert-danger mb-3">
      {{ session('error') }}
    </div>
    @endif
    
    @if(session('success'))
    <div class="alert alert-success mb-3">
      {{ session('success') }}
    </div>
    @endif
                <form action="{{ route('fundraising.withdraw') }}" method="POST">
                  @csrf

                  <input type="hidden" name="amount" value="{{$totalCommission}}">

                  <select class="input-field @error('payment_method') is-invalid @enderror" name="payment_method" id="bank-select">
                    <option value="">Pilih Bank</option>
                    <option value="bca" {{ old('payment_method') == 'bca' ? 'selected' : '' }}>Bank BCA</option>
                    <option value="bri" {{ old('payment_method') == 'bri' ? 'selected' : '' }}>Bank BRI</option>
                    <option value="mandiri" {{ old('payment_method') == 'mandiri' ? 'selected' : '' }}>Bank Mandiri</option>
                  </select>
                  @error('payment_method')
                    <small class="text-danger">{{ $message }}</small>
                  @enderror
  
                  <input
                  type="text" name="account_name"
                  class="input-field @error('account_name') is-invalid @enderror"
                  placeholder="Nama Rekening"
                  id="nama-rekening"
                  value="{{ old('account_name') }}"
                />
                @error('account_name')
                  <small class="text-danger">{{ $message }}</small>
                @enderror

                <input name="account_number"
                type="text"
                class="input-field @error('account_number') is-invalid @enderror"
                placeholder="Nomor Rekening"
                id="nomor-rekening"
                value="{{ old('account_number') }}"
              />
              @error('account_number')
                <small class="text-danger">{{ $message }}</small>
              @enderror
  
                <p class="info">
                  Terimakasih telah berpartisipasi dalam program fundraising,
                  proses pencairan dana ini berlangsung maksimal 1x24 Jam, bukti
                  transfer akan dikirim via inbox atau notifikasi.
                </p>
  
                <button type="submit" class="btn-submit">Kirim Request Pencairan Dana</button>
              </form>
              </div>
            </div>
          </div>

