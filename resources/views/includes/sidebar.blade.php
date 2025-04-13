<ul class="navbar-nav bg-primary d-block d-md-none sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ url('/') }}">
        <img src="{{ asset('assets/img/merawat-indonesia-logo.png') }}" alt="logo lulus bersama" width="30px">
    </a>

    <hr class="sidebar-divider my-0">

    <!-- Dashboard -->
    <li class="nav-item {{ Request::is('dashboard') ? 'active' : '' }}">
        <a class="nav-link" href="{{ url('dashboard') }}">
            <i class="fas fa-tachometer-alt"></i>
            <span class="text-white">Dashboard</span>
        </a>
    </li>

    <hr class="sidebar-divider my-0">

    <!-- Donasi (Dropdown) -->
    <li class="nav-item {{ Request::is('donasi*') ? 'active' : '' }}">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#donasiMenu"
            aria-expanded="false" aria-controls="donasiMenu">
            <i class="fas fa-hand-holding-heart"></i>
            <span class="text-white">Donasi</span>
        </a>
        <div id="donasiMenu" class="collapse {{ Request::is('donasi*') ? 'show' : '' }}" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="{{ url('donasi/semua-donasi') }}">Semua Donasi</a>
                <a class="collapse-item" href="{{ url('donasi/ceklis-donasi') }}">Ceklis Donasi</a>
            </div>
        </div>
    </li>

    <hr class="sidebar-divider my-0">

    <!-- Admin Yayasan -->
    <li class="nav-item {{ Request::is('pengguna') ? 'active' : '' }}">
        <a class="nav-link" href="{{ url('pengguna') }}">
            <i class="fas fa-users-cog"></i>
            <span class="text-white">Admin Yayasan</span>
        </a>
    </li>

    <hr class="sidebar-divider my-0">

    <!-- Kampanye (Dropdown) -->
    <li class="nav-item {{ Request::is('kampanye*') ? 'active' : '' }}">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#kampanyeMenu"
            aria-expanded="false" aria-controls="kampanyeMenu">
            <i class="fas fa-bullhorn"></i>
            <span class="text-white">Kampanye</span>
        </a>
        <div id="kampanyeMenu" class="collapse {{ Request::is('kampanye*') ? 'show' : '' }}" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="{{ url('kampanye/lihat-kampanye') }}">Lihat Kampanye</a>
                <a class="collapse-item" href="{{ url('kampanye/promosi-kampanye') }}">Promosi Kampanye</a>
            </div>
        </div>
    </li>

    <hr class="sidebar-divider my-0">

    <!-- Kabar Terbaru -->
    <li class="nav-item {{ Request::is('setsoal') || Request::is('soal/*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ url('setsoal') }}">
            <i class="fas fa-newspaper"></i>
            <span class="text-white">Kabar Terbaru</span>
        </a>
    </li>

    <hr class="sidebar-divider my-0">

    <!-- Pencairan Dana (Dropdown) -->
    <li class="nav-item {{ Request::is('pencairan*') ? 'active' : '' }}">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#pencairanMenu"
            aria-expanded="false" aria-controls="pencairanMenu">
            <i class="fas fa-wallet"></i>
            <span class="text-white">Pencairan Dana</span>
        </a>
        <div id="pencairanMenu" class="collapse {{ Request::is('pencairan*') ? 'show' : '' }}" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="{{ url('pencairan/kampanye') }}">Kampanye</a>
                <a class="collapse-item" href="{{ url('pencairan/fundraising') }}">Fundraising</a>
            </div>
        </div>
    </li>

    <hr class="sidebar-divider my-0">

    <!-- Sidebar Toggler -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
