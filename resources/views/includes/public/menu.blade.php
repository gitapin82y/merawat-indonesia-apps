<div class="container">
    <hr>
    <div class="row mt-5 pb-5 mb-3">
        <span class="text-center">
            <a href="{{ url('/') }}">Tentang Kitabisa</a> | 
            <a href="{{ route('terms.service') }}">Syarat & Ketentuan</a> | 
            <a href="{{ route('privacy.policy') }}">Pusat Bantuan</a>
        </span>
        <div class="d-flex justify-content-center gap-3 mt-4">
            <div class="social-icon d-flex align-items-center justify-content-center">
                <a href="#" target="_blank">
                    <img src="{{asset('assets/img/icon/instagram.svg')}}" alt="Instagram" class="img-fluid">
                </a>
            </div>
    
            <div class="social-icon d-flex align-items-center justify-content-center">
                <a href="#" target="_blank">
                    <img src="{{asset('assets/img/icon/youtube.svg')}}" alt="YouTube" class="img-fluid">
                </a>
            </div>
    
            <div class="social-icon d-flex align-items-center justify-content-center">
                <a href="#" target="_blank">
                    <img src="{{asset('assets/img/icon/facebook.svg')}}" alt="Facebook" class="img-fluid">
                </a>
            </div>
    
            <div class="social-icon d-flex align-items-center justify-content-center">
                <a href="#" target="_blank">
                    <img src="{{asset('assets/img/icon/tiktok.svg')}}" alt="TikTok" class="img-fluid">
                </a>
            </div>
        </div>
        <span class="text-center mt-4">
            Copyright © 2025 Merawat Indonesia. All Rights Reserved
        </span>
    </div>
</div>
<div class="footer">
    <div class="main-menu">
            <a href="{{url('/')}}"><img src="{{asset('assets/img/main-menu/beranda.svg')}}" alt="Beranda"><p>Beranda</p></a>
            <a href="{{url('galang-dana')}}"><img src="{{asset('assets/img/main-menu/galang dana.svg')}}" alt="Galang Dana"><p>Galang Dana</p></a>
            <a href="{{url('eksplore-kampanye')}}" class="donasi-menu"><img src="{{asset('assets/img/main-menu/donasi.svg')}}" alt="Donasi"><p>Donasi</p></a>
            <a href="{{url('leaderboard')}}"><img src="{{asset('assets/img/main-menu/leaderboard.svg')}}" alt="Leaderboard"><p>Leaderboard</p></a>
            <a href="{{url('profile')}}"><img src="{{asset('assets/img/main-menu/profile.svg')}}" alt="Profile"><p>Profile</p></a>
    </div>
</div>