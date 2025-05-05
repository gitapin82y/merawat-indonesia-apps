@extends('layouts.public')
 
@section('title', 'Kebijakan Privasi | Merawat Indonesia')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h1 class="h3 mb-0">Kebijakan Privasi</h1>
                </div>
                <div class="card-body">
                    <p class="lead">Terakhir diperbarui: {{ date('d F Y') }}</p>
                    
                    <h2 class="h4 mt-4">1. Pengantar</h2>
                    <p>Kami di Merawat Indonesia menghargai privasi Anda. Kebijakan Privasi ini menjelaskan bagaimana kami mengumpulkan, menggunakan, mengungkapkan, dan melindungi informasi pribadi Anda saat Anda menggunakan layanan kami, termasuk situs web dan aplikasi mobile kami.</p>
                    
                    <h2 class="h4 mt-4">2. Informasi yang Kami Kumpulkan</h2>
                    <p>Kami dapat mengumpulkan informasi berikut:</p>
                    <ul>
                        <li><strong>Informasi Identitas Pribadi</strong>: Nama, alamat email, nomor telepon, alamat, dan informasi identifikasi lainnya.</li>
                        <li><strong>Informasi Donasi</strong>: Detail pembayaran, jumlah donasi, dan kampanye yang didukung.</li>
                        <li><strong>Informasi Login Media Sosial</strong>: Saat Anda masuk menggunakan akun media sosial seperti Facebook, kami dapat mengumpulkan informasi profil dasar.</li>
                        <li><strong>Informasi Penggunaan</strong>: Data tentang bagaimana Anda berinteraksi dengan platform kami.</li>
                    </ul>
                    
                    <h2 class="h4 mt-4">3. Bagaimana Kami Menggunakan Informasi Anda</h2>
                    <p>Kami menggunakan informasi Anda untuk:</p>
                    <ul>
                        <li>Memproses donasi dan transaksi Anda</li>
                        <li>Mengelola akun Anda</li>
                        <li>Memberikan layanan pelanggan</li>
                        <li>Mengirimkan pembaruan tentang kampanye</li>
                        <li>Meningkatkan platform dan layanan kami</li>
                        <li>Mematuhi kewajiban hukum</li>
                    </ul>
                    
                    <h2 class="h4 mt-4">4. Pembagian Informasi</h2>
                    <p>Kami dapat membagikan informasi Anda dengan:</p>
                    <ul>
                        <li>Penyelenggara kampanye (hanya informasi yang diperlukan)</li>
                        <li>Penyedia layanan yang membantu operasional kami</li>
                        <li>Pihak berwenang jika diwajibkan oleh hukum</li>
                    </ul>
                    <p>Kami tidak menjual informasi pribadi Anda kepada pihak ketiga.</p>
                    
                    <h2 class="h4 mt-4">5. Keamanan</h2>
                    <p>Kami mengimplementasikan langkah-langkah keamanan untuk melindungi informasi pribadi Anda, termasuk enkripsi dan akses terbatas.</p>
                    
                    <h2 class="h4 mt-4">6. Hak Privasi Anda</h2>
                    <p>Anda memiliki hak untuk:</p>
                    <ul>
                        <li>Mengakses informasi pribadi Anda</li>
                        <li>Memperbarui atau memperbaiki informasi yang tidak akurat</li>
                        <li>Meminta penghapusan data Anda</li>
                        <li>Membatasi pemrosesan data Anda</li>
                        <li>Menarik persetujuan Anda</li>
                    </ul>
                    
                    <h2 class="h4 mt-4">7. Penggunaan Cookie</h2>
                    <p>Kami menggunakan cookie dan teknologi pelacakan serupa untuk meningkatkan pengalaman Anda dan mengumpulkan informasi tentang penggunaan situs web kami.</p>
                    
                    <h2 class="h4 mt-4">8. Perubahan pada Kebijakan Privasi</h2>
                    <p>Kami dapat memperbarui Kebijakan Privasi ini dari waktu ke waktu. Kami akan memberi tahu Anda tentang perubahan signifikan melalui email atau pemberitahuan di platform kami.</p>
                    
                    <h2 class="h4 mt-4">9. Hubungi Kami</h2>
                    <p>Jika Anda memiliki pertanyaan tentang Kebijakan Privasi kami, silakan hubungi kami di: <a href="mailto:merawatindonesia2@gmail.com">merawatindonesia2@gmail.com</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection