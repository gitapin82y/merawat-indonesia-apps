<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

    <!-- Sidebar Toggle (Topbar) -->
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>

    <a class="navbar-nav d-none d-md-flex" href="{{url('/')}}" style="text-decoration: none;">
            <img src="{{asset('assets/img/merawat-indonesia-logo.png')}}" alt="logo lulus bersama" height="40px">
            <h5 class="text-danger align-self-center mb-0 ms-2">
                Merawat Indonesia
            </h5>
    </a>

    <div class="navbar-nav mx-auto d-none d-md-flex align-items-center">
        <a href="{{ route('dashboard.index') }}" class="nav-item nav-link {{ Request::is('super-admin') ? 'active' : '' }}">Dashboard</a>
    
        <!-- Dropdown Donasi -->
        <div class="nav-item dropdown">
            <a class="nav-link dropdown-toggle {{ Request::is('super-admin/donasi-kampanye') || Request::is('super-admin/ceklis-donasi') ? 'active' : '' }}" href="#" id="donasiDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Donasi
            </a>
            <div class="dropdown-menu" aria-labelledby="donasiDropdown">
                <a class="dropdown-item" href="{{ route('donasi-kampanye.index') }}">Semua Donasi</a>
                <a class="dropdown-item" href="{{ route('ceklis-donasi.index') }}">Ceklis Donasi</a>
            </div>
        </div>

        <div class="nav-item dropdown">
            <a class="nav-link dropdown-toggle {{ Request::is('super-admin/admin') || Request::is('super-admin/user') ? 'active' : '' }}" href="#" id="donasiDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Pengguna
            </a>
            <div class="dropdown-menu" aria-labelledby="donasiDropdown">
                <a class="dropdown-item" href="{{ route('user.index') }}">User Donatur</a>
                <a class="dropdown-item" href="{{ route('admin.index') }}">Admin Yayasan</a>
            </div>
        </div>
    
        <!-- Dropdown Kampanye -->
        <div class="nav-item dropdown">
            <a class="nav-link dropdown-toggle {{ Request::is('super-admin/kampanye') || Request::is('super-admin/prioritas-kampanye') ? 'active' : '' }}" href="#" id="kampanyeDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Kampanye
            </a>
            <div class="dropdown-menu" aria-labelledby="kampanyeDropdown">
                <a class="dropdown-item" href="{{ route('kampanye.index') }}">Lihat Kampanye</a>
                <a class="dropdown-item" href="{{ route('prioritas-kampanye.index') }}">Promosi Kampanye</a>
            </div>
        </div>
    
        <a href="{{ route('kabar-terbaru.index') }}" class="nav-item nav-link {{ Request::is('super-admin/kabar-terbaru') ? 'active' : '' }}">Kabar Terbaru</a>
        <a href="{{ route('fundraising.index') }}" class="nav-item nav-link {{ Request::is('super-admin/fundraising') ? 'active' : '' }}">Fundraising</a>
    
        <!-- Dropdown Pencairan Dana -->
        <div class="nav-item dropdown">
            <a class="nav-link dropdown-toggle {{ Request::is('super-admin/pencairan-fundraising') ||  Request::is('super-admin/pencairan-kampanye') ? 'active' : '' }}" href="#" id="pencairanDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Pencairan Dana
            </a>
            <div class="dropdown-menu w-100" aria-labelledby="pencairanDropdown">
                <a class="dropdown-item" href="{{ route('pencairan-kampanye.index') }}">Kampanye</a>
                <a class="dropdown-item" href="{{ route('pencairan-fundraising.index') }}">Fundraising</a>
            </div>
        </div>
    </div>


    <!-- Topbar Navbar -->
    <ul class="navbar-nav ml-auto">
        <!-- Nav Item - User Information -->
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <img class="img-profile rounded-circle"
                    src="{{asset('img/undraw_profile.svg')}}">
                    <span class="ml-2 d-none d-lg-inline text-gray-600 small">Wiliam</span>
            </a>
            <!-- Dropdown - User Information -->
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="userDropdown">
                {{-- <div class="dropdown-divider"></div> --}}
                <a class="dropdown-item" href="{{url('/akun')}}">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    Akun
                </a>
                <a class="dropdown-item" href="{{url('/keamanan')}}">
                    <i class="fas fa-shield-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    Keamanan dan Privasi
                </a>
                <a class="dropdown-item" href="{{url('/logout')}}">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    Logout
                </a>
                
            </div>
        </li>

    </ul>

</nav>