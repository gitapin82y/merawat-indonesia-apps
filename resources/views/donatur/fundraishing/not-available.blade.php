<style>
    .fundraising-title {
      font-size: 1rem !important;
      font-weight: bold;
      color: var(--title-color);
    }

    .step-circle {
      background-color: var(--primary-light);
      color: var(--second-color);
      padding: 8px 12px;
      border-radius: 50%;
      font-weight: bold;
    }

    .step-title {
      font-weight: bold;
      color: var(--second-color);
    }

    .cta-button {
      background-color: var(--second-color);
      color: white;
      font-size: 14px;
      padding: 12px;
      border-radius: 8px;
      font-weight: bold;
      text-decoration: none;
      display: flex;
      align-items: center;
      justify-content: center;
      width: 100%;
      transition: background-color 0.3s ease;
    }

    .cta-button:hover {
      background-color: #e63e3e;
      color: white;
    }

    .cta-button img {
      margin-right: 10px;
    }

    @media (max-width: 480px) {
      .cta-button {
        font-size: 12px !important;
        padding: 10px;
      }
    }
  </style>
 <!-- Main Content -->
 <div class="container mt-4 flex-grow-1">
    <!-- Hero Image -->
    <div class="text-center">
      <img
        src="{{asset('assets/img/fundraishing.png')}}"
        alt="Fundraising"
        class="img-fluid"
        style="width: 60%; height: auto"
      />
    </div>

    <!-- Fundraising Guide Title -->
    <h6 class="fundraising-title text-start mt-4 mb-3">
      Panduan Fundraising
    </h6>

    <!-- Step 1 -->
    <div class="card border-0 p-3 mb-3 rounded-3 shadow-sm">
      <div class="d-flex align-items-center">
        <div class="step-circle me-3">1</div>
        <div>
          <h6 class="step-title mb-1">Simpan Link Fundraising</h6>
          <p class="m-0 text-muted">
            Sebarkan kebaikan melalui link fundraising dari salah satu
            program kampanye yang ingin Anda bantu.
          </p>
        </div>
      </div>
    </div>

    <!-- Step 2 -->
    <div class="card border-0 p-3 mb-3 rounded-3 shadow-sm">
      <div class="d-flex align-items-center">
        <div class="step-circle me-3">2</div>
        <div>
          <h6 class="step-title mb-1">Pendapatan Komisi</h6>
          <p class="m-0 text-muted">
            Setiap donatur yang klik link Anda melalui platform apa pun,
            Anda akan mendapatkan komisi sebesar 10% per transaksi.
          </p>
        </div>
      </div>
    </div>

    <!-- Step 3 -->
    <div class="card border-0 p-3 mb-4 rounded-3 shadow-sm">
      <div class="d-flex align-items-center">
        <div class="step-circle me-3">3</div>
        <div>
          <h6 class="step-title mb-1">Pencairan Dana Fundraising</h6>
          <p class="m-0 text-muted">
            Dapatkan hingga puluhan juta hanya dengan menyebarkan kebaikan
            dengan mengajak orang donasi melalui fundraising.
          </p>
        </div>
      </div>
    </div>

    <!-- CTA Button -->
    <div class="text-center mt-4 mb-5">
      <a href="{{url('/eksplore-kampanye')}}" class="cta-button">
        <img
          src="{{asset('assets/img/icon/fundraising.svg')}}"
          alt="Tangan Dollar"
          width="24"
        />
        Cari Kampanye dan Jadilah Fundraising
      </a>
    </div>
  </div>
