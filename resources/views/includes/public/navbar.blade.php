<style>
.filter-popup {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    display: flex;
    justify-content: center;
    align-items: center;
}

.filter-container {
    background-color: white;
    border-radius: 10px;
    padding: 30px;
    width: 90%;
    max-width: 600px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.form-check {
    margin-bottom: 10px;
}

.form-check-input {
    width: 20px;
    height: 20px;
    margin-right: 10px;
}

.btn-danger {
    background-color: #F05454;
    border: none;
    padding: 10px 40px;
    font-size: 18px;
    border-radius: 8px;
    color: white;
    font-weight: 500;
}
</style>

<div class="navbar col-12 justify-content-between">
    <div class="col-1 px-0">
        <a href="{{url('/')}}">
            <img src="{{asset('assets/img/merawat-indonesia.png')}}" alt="logo merawat indonesia" width="100%" height="auto">
        </a>
    </div>
    <div class="col-7 col-md-8 px-0">
        <div class="search">
            <img src="{{asset('assets/img/icon/search.svg')}}" alt="">
            <input type="text" placeholder="Cari Program, Bantu Bersama" id="searchInput" value="{{ request()->input('title', '') }}">
        </div>
    </div>
    <div class="text-end">
        <a href="#" class="btn-rectangle" id="filterBtn"><img src="{{asset('assets/img/icon/filter.svg')}}" style="transform: translateY(-2px);" alt=""></a>
    </div>
    <div class="text-end" style="padding-top: 2px;">
        <a href="{{url('notifikasi')}}" class="btn-rectangle" style="position:relative;">
            @auth
            <p class="count-notification">{{ Auth::user()->notifications()->unread()->count() }}</p>
            @endauth
            <img src="{{asset('assets/img/icon/notification.svg')}}" style="transform: translateY(2px);"  alt="">
        </a>
    </div>
</div>

<!-- Filter Popup -->
<div class="filter-popup" id="filterPopup" style="display: none;">
    <div class="filter-container">
        <div class="row">
            <div class="col-md-6">
                <h4>Kategori</h4>
                <div class="filter-options">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="category[]" value="Kesehatan" id="kategoriKesehatan" 
                        {{ in_array('Kesehatan', (array)request()->input('category', [])) ? 'checked' : '' }}>
                        <label class="form-check-label" for="kategoriKesehatan">Kesehatan</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="category[]" value="Pendidikan" id="kategoriPendidikan"
                        {{ in_array('Pendidikan', (array)request()->input('category', [])) ? 'checked' : '' }}>
                        <label class="form-check-label" for="kategoriPendidikan">Pendidikan</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="category[]" value="Bencana" id="kategoriBencana"
                        {{ in_array('Bencana', (array)request()->input('category', [])) ? 'checked' : '' }}>
                        <label class="form-check-label" for="kategoriBencana">Bencana</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="category[]" value="Zakat" id="kategoriZakat"
                        {{ in_array('Zakat', (array)request()->input('category', [])) ? 'checked' : '' }}>
                        <label class="form-check-label" for="kategoriZakat">Zakat</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="category[]" value="Keagamaan" id="kategoriKeagamaan"
                        {{ in_array('Keagamaan', (array)request()->input('category', [])) ? 'checked' : '' }}>
                        <label class="form-check-label" for="kategoriKeagamaan">Keagamaan</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="category[]" value="Sosial" id="kategoriSosial"
                        {{ in_array('Sosial', (array)request()->input('category', [])) ? 'checked' : '' }}>
                        <label class="form-check-label" for="kategoriSosial">Sosial</label>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <h4>Filter Lainnya</h4>
                <div class="filter-options">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="filter[]" value="populer" id="filterPopuler"
                        {{ in_array('populer', (array)request()->input('filter', [])) ? 'checked' : '' }}>
                        <label class="form-check-label" for="filterPopuler">Populer</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="filter[]" value="terbaru" id="filterTerbaru"
                        {{ in_array('terbaru', (array)request()->input('filter', [])) ? 'checked' : '' }}>
                        <label class="form-check-label" for="filterTerbaru">Terbaru</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="filter[]" value="hampir_tercapai" id="filterHampirTercapai"
                        {{ in_array('hampir_tercapai', (array)request()->input('filter', [])) ? 'checked' : '' }}>
                        <label class="form-check-label" for="filterHampirTercapai">Target Hampir Tercapai</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-center mt-3">
            <button type="button" id="cariSekarangBtn" class="btn btn-danger">Cari Sekarang</button>
        </div>
    </div>
</div>