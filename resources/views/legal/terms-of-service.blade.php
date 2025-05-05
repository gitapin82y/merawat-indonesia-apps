@extends('layouts.public')
 
@section('title', 'Syarat dan Ketentuan | Merawat Indonesia')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h1 class="h3 mb-0">Syarat dan Ketentuan</h1>
                </div>
                <div class="card-body">
                    <p class="lead">Terakhir diperbarui: {{ date('d F Y') }}</p>
                    
                    <h2 class="h4 mt-4">1. Penerimaan Syarat</h2>
                    <p>Dengan mengakses atau menggunakan platform Merawat Indonesia, Anda menyetujui untuk terikat oleh Syarat dan Ketentuan ini. Jika Anda tidak setuju dengan bagian apa pun dari syarat ini, Anda tidak boleh menggunakan layanan kami.</p>
                    
                    <h2 class="h4 mt-4">2. Definisi</h2>
                    <ul>
                        <li><strong>"Platform"</strong> mengacu pada situs web, aplikasi, atau layanan digital lainnya yang disediakan oleh Merawat Indonesia.</li>
                        <li><strong>"Pengguna"</strong> adalah individu yang mengakses atau menggunakan Platform.</li>
                        <li><strong>"Donatur"</strong> adalah Pengguna yang memberikan donasi melalui Platform.</li>
                        <li><strong>"Penggalang Dana"</strong> adalah Pengguna yang membuat dan mengelola kampanye di Platform.</li>
                    </ul>
                    
                    <h2 class="h4 mt-4">3. Penggunaan Layanan</h2>
                    <p>Anda setuju untuk menggunakan Platform kami hanya untuk tujuan yang sah dan sesuai dengan Syarat dan Ketentuan ini. Anda tidak boleh:</p>
                    <ul>
                        <li>Melanggar hukum atau peraturan yang berlaku</li>
                        <li>Menyalahgunakan Platform untuk aktivitas penipuan</li>
                        <li>Mengganggu operasi Platform</li>
                        <li>Mengumpulkan informasi pengguna tanpa izin</li>
                        <li>Mengunggah konten yang melanggar hukum atau tidak pantas</li>
                    </ul>
                    
                    <h2 class="h4 mt-4">4. Akun Pengguna</h2>
                    <p>Anda mungkin perlu membuat akun untuk menggunakan beberapa fitur Platform. Anda bertanggung jawab untuk:</p>
                    <ul>
                        <li>Menjaga kerahasiaan kata sandi Anda</li>
                        <li>Memberikan informasi yang akurat dan lengkap</li>
                        <li>Semua aktivitas yang terjadi di bawah akun Anda</li>
                    </ul>
                    
                    <h2 class="h4 mt-4">5. Donasi</h2>
                    <p>Ketika memberikan donasi melalui Platform kami:</p>
                    <ul>
                        <li>Anda menjamin bahwa Anda memiliki hak untuk menggunakan metode pembayaran yang dipilih</li>
                        <li>Anda memahami bahwa donasi mungkin tunduk pada biaya pemrosesan</li>
                        <li>Donasi bersifat final dan tidak dapat dikembalikan kecuali dalam keadaan luar biasa</li>
                    </ul>
                    
                    <h2 class="h4 mt-4">6. Kampanye Penggalangan Dana</h2>
                    <p>Bagi Penggalang Dana:</p>
                    <ul>
                        <li>Anda bertanggung jawab atas akurasi informasi kampanye Anda</li>
                        <li>Dana yang dikumpulkan harus digunakan sesuai dengan tujuan yang dinyatakan</li>
                        <li>Anda setuju untuk memberikan pembaruan dan transparansi tentang penggunaan dana</li>
                        <li>Merawat Indonesia berhak menolak atau menghapus kampanye yang melanggar ketentuan kami</li>
                    </ul>
                    
                    <h2 class="h4 mt-4">7. Hak Kekayaan Intelektual</h2>
                    <p>Semua konten yang disediakan oleh Platform, termasuk tetapi tidak terbatas pada teks, grafik, logo, dan perangkat lunak, adalah milik Merawat Indonesia atau pemberi lisensinya. Konten yang diunggah oleh pengguna tetap menjadi milik pengguna, tetapi Anda memberikan Merawat Indonesia lisensi non-eksklusif untuk menggunakan, menampilkan, dan mendistribusikan konten tersebut.</p>
                    
                    <h2 class="h4 mt-4">8. Penolakan Tanggung Jawab</h2>
                    <p>Platform disediakan "sebagaimana adanya" dan "sebagaimana tersedia" tanpa jaminan apa pun. Merawat Indonesia tidak bertanggung jawab atas konten yang diunggah oleh pengguna atau untuk tindakan penggalang dana setelah menerima donasi.</p>
                    
                    <h2 class="h4 mt-4">9. Batasan Tanggung Jawab</h2>
                    <p>Merawat Indonesia tidak akan bertanggung jawab atas kerugian tidak langsung, insidental, khusus, atau konsekuensial yang timbul dari penggunaan Platform.</p>
                    
                    <h2 class="h4 mt-4">10. Perubahan Syarat</h2>
                    <p>Kami berhak mengubah Syarat dan Ketentuan ini kapan saja. Perubahan signifikan akan diberitahukan kepada Anda melalui email atau pemberitahuan di Platform.</p>
                    
                    <h2 class="h4 mt-4">11. Hukum yang Berlaku</h2>
                    <p>Syarat dan Ketentuan ini diatur oleh hukum Republik Indonesia.</p>
                    
                    <h2 class="h4 mt-4">12. Hubungi Kami</h2>
                    <p>Jika Anda memiliki pertanyaan tentang Syarat dan Ketentuan kami, silakan hubungi kami di: <a href="mailto:merawatindonesia2@gmail.com">merawatindonesia2@gmail.com</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection