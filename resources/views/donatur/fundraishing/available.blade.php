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

    /* Styling untuk info summary */
    .filter-summary-card {
      background: linear-gradient(135deg, var(--second-color, #007bff) 0%, rgba(0, 123, 255, 0.8) 100%);
      color: white;
      border-radius: 12px;
      margin-bottom: 20px;
    }

    .summary-item {
      background: rgba(255, 255, 255, 0.1);
      border-radius: 8px;
      padding: 15px;
      text-align: center;
    }

    .summary-number {
      font-size: 24px;
      font-weight: bold;
      line-height: 1.2;
    }

    .summary-label {
      font-size: 12px;
      opacity: 0.9;
      margin-top: 5px;
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
      .summary-number {
        font-size: 18px;
      }
      .summary-label {
        font-size: 10px;
      }
    }
  </style>
  @include('donatur.fundraishing.filter-form', ['filterData' => $filterData])

        <!-- Summary Card untuk Filter -->
        @if(($filterData['filter_type'] ?? 'all') != 'all' && ($filterData['has_results'] ?? false))
        <div class="container mt-3">
            <div class="card filter-summary-card border-0">
                <div class="card-body p-3">
                    <h6 class="mb-3 text-white">
                        <i class="fas fa-chart-line me-2"></i>
                        Ringkasan Perolehan 
                        @if(($filterData['filter_type'] ?? '') == 'daily' && $filterData['date'])
                            Harian ({{ \Carbon\Carbon::parse($filterData['date'])->format('d/m/Y') }})
                        @elseif(($filterData['filter_type'] ?? '') == 'monthly' && $filterData['month'])
                            Bulanan ({{ \Carbon\Carbon::parse($filterData['month'])->format('F Y') }})
                        @elseif(($filterData['filter_type'] ?? '') == 'range' && $filterData['start_date'] && $filterData['end_date'])
                            ({{ \Carbon\Carbon::parse($filterData['start_date'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($filterData['end_date'])->format('d/m/Y') }})
                        @endif
                    </h6>
                    <div class="row g-2">
                        <div class="col-4">
                            <div class="summary-item">
                                <div class="summary-number">{{ $fundraisings->count() }}</div>
                                <div class="summary-label">Campaign Aktif</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="summary-item">
                                <div class="summary-number">{{ $fundraisings->sum('total_donatur') }}</div>
                                <div class="summary-label">Total Donatur</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="summary-item">
                                <div class="summary-number">Rp {{ number_format($fundraisings->sum('jumlah_donasi')) }}</div>
                                <div class="summary-label">Total Donasi</div>
                            </div>
                        </div>
                    </div>
                    <div class="row g-2 mt-2">
                        <div class="col-12">
                            <div class="summary-item" style="background: rgba(255, 255, 255, 0.2);">
                                <div class="summary-number">Rp {{ number_format($totalCommission) }}</div>
                                <div class="summary-label">Total Komisi Periode Ini</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Fundraising Kampanye -->
        <div class="container mt-4">
            <div class="row kampanye mt-4">
              <div class="justify-content-between d-flex mb-3">
                  <h2 id="kategoriTitle" class="text-danger">
                    @if(($filterData['filter_type'] ?? 'all') != 'all')
                        Fundraising - Periode Filter
                    @else
                        Fundraising Kampanye Anda
                    @endif
                </h2>
                  @if(($filterData['filter_type'] ?? 'all') != 'all')
                <small class="text-muted align-self-center">
                    ({{ $fundraisings->count() }} hasil untuk periode ini)
                </small>
            @else
                <small class="text-muted align-self-center">
                    ({{ $fundraisings->count() }} total fundraising)
                </small>
            @endif
              
              </div>
              
              @forelse($fundraisings as $fundraising)
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
                      @if(($filterData['filter_type'] ?? 'all') != 'all')
                          <p class="mb-0 donasi-info">
                              <i class="fas fa-calendar-day text-primary"></i>
                              Donatur periode ini: <b>{{ $fundraising->total_donatur }}</b>
                          </p>
                          <p class="mb-0 donasi-info">
                              <i class="fas fa-money-bill-wave text-success"></i>
                              Donasi periode ini: <b>Rp {{ number_format($fundraising->jumlah_donasi) }}</b>
                          </p>
                          <p class="mb-2 donasi-info">
                              <i class="fas fa-percentage text-warning"></i>
                              Komisi periode ini: <b>Rp {{ number_format($fundraising->commission) }}</b>
                          </p>
                      @else
                          <p class="mb-0 donasi-info">Berhasil mengajak <b>{{ $fundraising->total_donatur }}</b> donatur</p>
                          <p class="mb-0 donasi-info">Total donasi <b>Rp {{ number_format($fundraising->jumlah_donasi) }}</b></p>
                          <p class="mb-2 donasi-info">
                              Pendapatan dari total donasi <b>Rp {{ number_format($fundraising->commission) }}</b>
                          </p>
                      @endif
                    <button type="button" class="btn btn-salin btn-action d-inline" data-link="{{ route('campaign.referral', ['slug' => $fundraising->campaign->slug, 'code' => $fundraising->code_link]) }}">Salin Link</button>
                  </div>
              </div>
              @empty
        <div class="col-12 text-center py-5">
            <div class="text-muted">
                <i class="fas fa-search fa-3x mb-3"></i>
                @if(($filterData['filter_type'] ?? 'all') != 'all')
                    <p>Tidak ada donasi masuk pada periode yang dipilih.</p>
                    <small>Coba ubah filter tanggal atau pilih "Semua Donasi" untuk melihat keseluruhan data.</small>
                @else
                    <p>Tidak ada data fundraising ditemukan.</p>
                @endif
                <br>
                <a href="{{ route('profile.fundraising.index') }}" class="btn btn-sm btn-primary mt-2">
                    Lihat Semua Data
                </a>
            </div>
        </div>
        @endforelse
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
                @if(($filterData['filter_type'] ?? 'all') != 'all')
                    <h2>Pendapatan Periode: Rp {{ number_format($totalCommission) }}</h2>
                    <p class="mb-1 text-muted small">
                        @if(($filterData['filter_type'] ?? '') == 'daily' && $filterData['date'])
                            Perolehan tanggal {{ \Carbon\Carbon::parse($filterData['date'])->format('d F Y') }}
                        @elseif(($filterData['filter_type'] ?? '') == 'monthly' && $filterData['month'])
                            Perolehan bulan {{ \Carbon\Carbon::parse($filterData['month'])->format('F Y') }}
                        @elseif(($filterData['filter_type'] ?? '') == 'range' && $filterData['start_date'] && $filterData['end_date'])
                            Perolehan {{ \Carbon\Carbon::parse($filterData['start_date'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($filterData['end_date'])->format('d/m/Y') }}
                        @endif
                    </p>
                    <p class="mb-3">
                        <small class="text-info">
                            <i class="fas fa-info-circle"></i>
                            Ini adalah komisi dari donasi yang masuk pada periode tersebut. 
                            <a href="{{ route('profile.fundraising.index') }}" class="text-primary">Lihat total keseluruhan</a>
                        </small>
                    </p>
                @else
                    <h2>Total Pendapatan Rp {{ number_format($totalCommission) }}</h2>
                    <p class="mb-3">
                        Anda dapat mencairkan semua pendapatan fundraising minimal Rp 100.000
                    </p>
                @endif
                    <a href="javascript:void(0)" data-komisi="{{$totalCommission}}" class="btn-cairkandana btn-action">
                        @if(($filterData['filter_type'] ?? 'all') != 'all')
                            Lihat Pencairan
                        @else
                            Cairkan Dana
                        @endif
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
                @if(($filterData['filter_type'] ?? 'all') != 'all')
                    <h3>Info Pencairan Dana</h3>
                    <div class="alert alert-info p-3 mb-3 rounded">
                        <i class="fas fa-info-circle"></i>
                        <p class="mb-2"><strong>Pendapatan Periode Filter:</strong><br>
                        Rp {{ number_format($totalCommission) }}</p>
                        <small>Untuk mencairkan dana, silakan lihat halaman "Semua Data" untuk total keseluruhan pendapatan Anda.</small>
                    </div>
                    <a href="{{ route('profile.fundraising.index') }}" class="btn btn-primary w-100">
                        Lihat Total Keseluruhan
                    </a>
                @else
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
                    <option value="bni" {{ old('payment_method') == 'bni' ? 'selected' : '' }}>Bank BNI</option>
                    <option value="cimb" {{ old('payment_method') == 'cimb' ? 'selected' : '' }}>Bank CIMB Niaga</option>
                    <option value="btn" {{ old('payment_method') == 'btn' ? 'selected' : '' }}>Bank BTN</option>
                    <option value="danamon" {{ old('payment_method') == 'danamon' ? 'selected' : '' }}>Bank Danamon</option>
                    <option value="permata" {{ old('payment_method') == 'permata' ? 'selected' : '' }}>Bank Permata</option>
                    <option value="bsi" {{ old('payment_method') == 'bsi' ? 'selected' : '' }}>Bank Syariah Indonesia</option>
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
              @endif
              </div>
            </div>
          </div>