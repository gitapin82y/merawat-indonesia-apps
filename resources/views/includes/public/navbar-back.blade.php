<div class="navbar-back col-12 align-items-center d-flex">
    <a href="{{ $url ?? url()->previous() }}" class="bg-white">
        <i class="fa-solid fa-angle-left"></i>
    </a>
    <h1 class="text-white mb-0 ms-2">{{ $title ?? 'Judul Default' }}</h1>
</div>
