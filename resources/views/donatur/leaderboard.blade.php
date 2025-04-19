@extends('layouts.public')
 
@section('title', 'Leaderboard')

@push('after-style')
<style>
  /* General Styles */
  body {
    background-color: #f5f5f5;
    font-family: Arial, sans-serif;
  }

  /* Leaderboard Header Styles */
  .leaderboard-title {
    margin: 15px;
    font-size: 20px;
    font-weight: bold;
  }

  .leaderboard-subtitle {
    margin: -10px 15px 15px;
    font-size: 14px;
    color: #666;
  }

  /* Leaderboard Item Styles */
  .leaderboard-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin: 15px;
    padding: 15px;
    background-color: white;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    margin-bottom: 10px;
  }

  .leaderboard-user {
    display: flex;
    align-items: center;
  }

  .avatar-container {
    position: relative;
    margin-right: 15px;
  }

  .avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
  }

  .top-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    width: 24px;
    height: 24px;
  }

  .user-details h4 {
    font-size: 18px;
    margin: 0;
    color: #ff4d4f;
    font-weight: bold;
  }

  .user-rank {
    font-size: 14px;
    color: #ff4d4f;
    background-color: rgba(255, 77, 79, 0.1);
    padding: 4px 10px;
    border-radius: 15px;
    margin-top: 6px;
    display: inline-block;
  }

  /* Donation Amount Styles */
  .donation-amount {
    text-align: right;
  }

  .donation-amount h3 {
    font-size: 18px;
    color: #ff4d4f;
    font-weight: bold;
    margin: 0;
    display: flex;
    align-items: center;
    justify-content: flex-end;
  }

  .donation-amount img {
    width: 22px;
    margin-right: 6px;
  }

  .donation-text {
    font-size: 14px;
    color: #666;
    margin-top: 6px;
  }

  /* Tab Button Styles */
  .btn-outline-danger {
    color: #ff4d4f;
    border-color: #ff4d4f;
  }

  .btn-outline-danger:hover,
  .btn-outline-danger.active {
    background-color: #ff4d4f;
    border-color: #ff4d4f;
    color: white;
  }

  /* Pengalang Dana Tab Styles */
  #pengalang-content .donation-amount h3 {
    color: #ff4d4f;
  }

  #pengalang-content .donation-amount img {
    width: 22px;
    margin-right: 6px;
  }

  /* Responsive Styles */
  @media (max-width: 480px) {
    /* Tab Navigation */
    .btn-outline-danger.tab-button {
      font-size: 14px;
      padding: 6px 10px;
    }

    /* Leaderboard Title and Subtitle */
    .leaderboard-title {
      font-size: 18px;
      margin: 12px 10px;
    }

    .leaderboard-subtitle {
      font-size: 12px;
      margin: -8px 10px 12px;
    }

    /* Leaderboard Items */
    .leaderboard-item {
      margin: 10px;
      padding: 12px 10px;
    }

    /* Avatar and Badge */
    .avatar {
      width: 45px;
      height: 45px;
    }

    .avatar-container {
      margin-right: 10px;
    }

    .top-badge {
      width: 20px;
      height: 20px;
      top: -6px;
      right: -6px;
    }

    /* User Details */
    .user-details h4 {
      font-size: 16px;
    }

    .user-rank {
      font-size: 12px;
      padding: 3px 8px;
      margin-top: 4px;
    }

    /* Donation Amount */
    .donation-amount h3 {
      font-size: 15px;
    }

    .donation-amount img {
      width: 18px;
      margin-right: 4px;
    }

    .donation-text {
      font-size: 12px;
      margin-top: 4px;
    }

    /* Content padding */
    .leaderboard-content {
      padding: 0 5px;
    }

    /* Pengalang Dana responsive adjustments */
    #pengalang-content .leaderboard-title {
      font-size: 18px;
      margin: 12px 10px;
    }

    #pengalang-content .leaderboard-subtitle {
      font-size: 12px;
      margin: -8px 10px 12px;
    }

    #pengalang-content .donation-amount h3 {
      font-size: 15px;
    }

    #pengalang-content .donation-amount img {
      width: 18px;
      margin-right: 4px;
    }
  }
</style>
@endpush

@section('content')

          <!-- Navbar Back -->

          @include('includes.public.navbar-back', ['title' => 'Leaderboard'])

  
          <!-- Tab Navigation -->
          <div class="d-flex justify-content-center gap-2 m-3">
            <button
              class="btn btn-outline-danger tab-button w-50 active"
              data-target="donatur"
            >
              Donatur
            </button>
            <button
              class="btn btn-outline-danger tab-button w-50"
              data-target="pengalang"
            >
              Penggalang Dana
            </button>
          </div>
  
          <!-- Donatur Leaderboard Content -->
          <div class="leaderboard-content" id="donatur-content" style="padding-bottom: 500px;">
            <h2 class="leaderboard-title">Top 10 Donasi Terbanyak</h2>
            <p class="leaderboard-subtitle">
              Diambil berdasarkan jumlah transaksi donasi terbanyak
            </p>
  
            @forelse($donaturLeaderboard as $donatur)
            <a href="{{route('profileDonatur',$donatur['name'] )}}" class="leaderboard-item">
              <div class="leaderboard-user">
                <div class="avatar-container">
                  <img
                    src="{{ asset('storage/' . $donatur['avatar']) }}"
                    alt="Lisa Bella"
                    class="avatar"
                  />
                  <img
                    src="{{asset('assets/img/icon/top1-donasi.svg')}}"
                    alt="Top 1"
                    class="top-badge"
                  />
                </div>
                <div class="user-details">
                  <h4>{{ $donatur['name'] }}</h4>
                  <span class="user-rank">Top{{ $loop->index + 1 }} Leaderboard</span>
                </div>
              </div>
              <div class="donation-amount">
                <h3>
                  <img src="{{asset('assets/img/icon/dompet.svg')}}" alt="Dompet" />
                  {{ $donatur['total_donation_formatted'] }}
                </h3>
                <p class="donation-text">Total Donasi</p>
              </div>
            </a>
            @empty
            <div class="text-center">
              <img src="{{ asset('assets/img/icon/success-data.svg') }}" alt="Not Found" class="mb-3" style="width: 150px; height: 150px;">
              <p>Belum ada leaderboard donatur</p>
          </div>
        @endforelse

            <!-- Add padding at the bottom for footer -->
            <div style="height: 80px"></div>
          </div>
  
          <!-- Penggalang Dana Leaderboard Content -->
          <div class="leaderboard-content" id="pengalang-content" style="padding-bottom: 500px;">
            <h2 class="leaderboard-title">
              Top 10 Penggalang Dana Terbaik
            </h2>
            <p class="leaderboard-subtitle">
              Diambil berdasarkan jumlah donatur terbanyak
            </p>

            @forelse($adminLeaderboard as $admin)
            <!-- Leaderboard Items -->
            <a href="{{route('galangDanaProfile',$admin['name'] )}}" class="leaderboard-item">
              <div class="leaderboard-user">
                <div class="avatar-container">
                  <img
                    src="{{ asset('storage/' . $admin['avatar']) }}"
                    alt="Merawat Indonesia"
                    class="avatar"
                  />
                </div>
                <div class="user-details">
                  <h4>{{ $admin['name'] }}</h4>
                  <span class="user-rank">Top {{ $loop->index + 1 }} Leaderboard</span>
                </div>
              </div>
              <div class="donation-amount">
                <h3>
                  <img src="{{asset('assets/img/icon/total-donatur.svg')}}" alt="Donatur" />
                  {{ $admin['total_donatur'] }}
                </h3>
                <p class="donation-text">Total Donatur</p>
              </div>
            </a>
            @empty
            <div class="text-center">
              <img src="{{ asset('assets/img/icon/success-data.svg') }}" alt="Not Found" class="mb-3" style="width: 150px; height: 150px;">
              <p>Belum ada leaderboard donatur</p>
          </div>
        @endforelse
  
      
  
            <!-- Add padding at the bottom for footer -->
            <div style="height: 80px"></div>
          </div>
 
    @include('includes.public.menu')

@endsection

@push('after-script')
<script>
  // Tab switching functionality
  document.addEventListener("DOMContentLoaded", function () {
    const tabButtons = document.querySelectorAll(".tab-button");
    const donaturContent = document.getElementById("donatur-content");
    const pengalangContent = document.getElementById("pengalang-content");

    // Hide pengalang content initially
    if (pengalangContent) {
      pengalangContent.style.display = "none";
    }

    tabButtons.forEach((button) => {
      button.addEventListener("click", function () {
        // Remove active class from all buttons
        tabButtons.forEach((btn) => btn.classList.remove("active"));

        // Add active class to clicked button
        this.classList.add("active");

        // Show/hide appropriate content based on the tab
        const target = this.getAttribute("data-target");
        if (target === "donatur") {
          donaturContent.style.display = "block";
          if (pengalangContent) {
            pengalangContent.style.display = "none";
          }
        } else if (target === "pengalang") {
          donaturContent.style.display = "none";
          if (pengalangContent) {
            pengalangContent.style.display = "block";
          }
        }
      });
    });
  });
</script>
@endpush

