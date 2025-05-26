<ul class="navbar-nav bg-primary d-block d-md-none sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ url('/') }}">
        <img src="{{ asset('assets/img/merawat-indonesia-logo.png') }}" alt="logo lulus bersama" width="30px">
    </a>

    <hr class="sidebar-divider my-0">

    <!-- Dashboard -->
    <li class="nav-item {{ Request::is('super-admin') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('dashboard.index') }}">
            <i class="fas fa-tachometer-alt"></i>
            <span class="text-white">Dashboard</span>
        </a>
    </li>

    <hr class="sidebar-divider my-0">

    <!-- Donasi (Dropdown) -->
    <li class="nav-item {{ Request::is('super-admin/donasi-kampanye') || Request::is('super-admin/ceklis-donasi') ? 'active' : '' }}">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#donasiMenu"
            aria-expanded="{{ Request::is('super-admin/donasi-kampanye') || Request::is('super-admin/ceklis-donasi') ? 'true' : 'false' }}" 
            aria-controls="donasiMenu">
            <i class="fas fa-hand-holding-heart"></i>
            <span class="text-white">Donasi</span>
        </a>
        <div id="donasiMenu" class="collapse {{ Request::is('super-admin/donasi-kampanye') || Request::is('super-admin/ceklis-donasi') ? 'show' : '' }}" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item {{ Request::is('super-admin/donasi-kampanye') ? 'active' : '' }}" href="{{ route('donasi-kampanye.index') }}">Semua Donasi</a>
                <a class="collapse-item {{ Request::is('super-admin/ceklis-donasi') ? 'active' : '' }}" href="{{ route('ceklis-donasi.index') }}">Ceklis Donasi</a>
            </div>
        </div>
    </li>

    <hr class="sidebar-divider my-0">

    <!-- Pengguna (Dropdown) -->
    <li class="nav-item {{ Request::is('admin') || Request::is('user') ? 'active' : '' }}">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#penggunaMenu"
            aria-expanded="{{ Request::is('super-admin/admin') || Request::is('super-admin/user') ? 'true' : 'false' }}" 
            aria-controls="penggunaMenu">
            <i class="fas fa-users-cog"></i>
            <span class="text-white">Pengguna</span>
        </a>
        <div id="penggunaMenu" class="collapse {{ Request::is('super-admin/admin') || Request::is('super-admin/user') ? 'show' : '' }}" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item {{ Request::is('super-admin/user') ? 'active' : '' }}" href="{{ route('user.index') }}">User Donatur</a>
                <a class="collapse-item {{ Request::is('super-admin/admin') ? 'active' : '' }}" href="{{ route('admin.index') }}">Admin Yayasan</a>
            </div>
        </div>
    </li>

    <hr class="sidebar-divider my-0">

    <!-- Kampanye (Dropdown) -->
    <li class="nav-item {{ Request::is('kampanye') || Request::is('super-admin/prioritas-kampanye') ? 'active' : '' }}">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#kampanyeMenu"
            aria-expanded="{{ Request::is('super-admin/kampanye') || Request::is('super-admin/prioritas-kampanye') ? 'true' : 'false' }}" 
            aria-controls="kampanyeMenu">
            <i class="fas fa-bullhorn"></i>
            <span class="text-white">Kampanye</span>
        </a>
        <div id="kampanyeMenu" class="collapse {{ Request::is('super-admin/kampanye') || Request::is('super-admin/prioritas-kampanye') ? 'show' : '' }}" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item {{ Request::is('super-admin/kampanye') ? 'active' : '' }}" href="{{ route('kampanye.index') }}">Lihat Kampanye</a>
                <a class="collapse-item {{ Request::is('super-admin/prioritas-kampanye') ? 'active' : '' }}" href="{{ route('prioritas-kampanye.index') }}">Promosi Kampanye</a>
                <a class="collapse-item {{ Request::is('super-admin/urgent-kampanye') ? 'active' : '' }}" href="{{ route('urgent-kampanye.index') }}">Pilihan Kampanye</a>
            </div>
        </div>
    </li>

    <hr class="sidebar-divider my-0">

    <!-- Kabar Terbaru -->
    <li class="nav-item {{ Request::is('kabar-terbaru') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('kabar-terbaru.index') }}">
            <i class="fas fa-newspaper"></i>
            <span class="text-white">Kabar Terbaru</span>
        </a>
    </li>

    <hr class="sidebar-divider my-0">

    <!-- Fundraising -->
    <li class="nav-item {{ Request::is('super-admin/fundraising') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('fundraising.index') }}">
            <i class="fas fa-hand-holding-usd"></i>
            <span class="text-white">Fundraising</span>
        </a>
    </li>

    <hr class="sidebar-divider my-0">

    <!-- Pencairan Dana (Dropdown) -->
    <li class="nav-item {{ Request::is('super-admin/pencairan-kampanye') || Request::is('super-admin/pencairan-fundraising') ? 'active' : '' }}">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#pencairanMenu"
            aria-expanded="{{ Request::is('super-admin/pencairan-kampanye') || Request::is('super-admin/pencairan-fundraising') ? 'true' : 'false' }}" 
            aria-controls="pencairanMenu">
            <i class="fas fa-wallet"></i>
            <span class="text-white">Pencairan Dana</span>
        </a>
        <div id="pencairanMenu" class="collapse {{ Request::is('super-admin/pencairan-kampanye') || Request::is('super-admin/pencairan-fundraising') ? 'show' : '' }}" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item {{ Request::is('super-admin/pencairan-kampanye') ? 'active' : '' }}" href="{{ route('pencairan-kampanye.index') }}">Kampanye</a>
                <a class="collapse-item {{ Request::is('super-admin/pencairan-fundraising') ? 'active' : '' }}" href="{{ route('pencairan-fundraising.index') }}">Fundraising</a>
            </div>
        </div>
    </li>

    <hr class="sidebar-divider my-0">

    <!-- Sidebar Toggler -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>