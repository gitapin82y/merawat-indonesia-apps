@extends('layouts.public')
 
@section('title', 'Penghapusan Data | Merawat Indonesia')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h1 class="h3 mb-0">Permintaan Penghapusan Data</h1>
                </div>
                <div class="card-body">
                    <p class="lead">Terakhir diperbarui: {{ date('d F Y') }}</p>
                    
                    <h2 class="h4 mt-4">Hak Penghapusan Data Anda</h2>
                    <p>Merawat Indonesia menghormati privasi dan hak Anda atas data pribadi. Sesuai dengan kebijakan privasi kami dan peraturan perlindungan data yang berlaku, Anda memiliki hak untuk meminta penghapusan data pribadi Anda dari sistem kami.</p>
                    
                    <h2 class="h4 mt-4">Apa yang Dihapus</h2>
                    <p>Ketika Anda meminta penghapusan data, kami akan menghapus atau menganonimkan:</p>
                    <ul>
                        <li>Akun pengguna Anda dan informasi profil</li>
                        <li>Alamat email dan informasi kontak</li>
                        <li>Riwayat donasi (kecuali yang diperlukan untuk tujuan pajak atau audit)</li>
                        <li>Data yang dikumpulkan melalui login media sosial</li>
                    </ul>
                    
                    <p><strong>Catatan penting:</strong> Kami mungkin tetap menyimpan beberapa informasi jika diharuskan oleh kewajiban hukum atau kepentingan bisnis yang sah, seperti catatan transaksi untuk tujuan pajak dan laporan keuangan.</p>
                    
                    <h2 class="h4 mt-4">Cara Meminta Penghapusan Data</h2>
                    <p>Anda dapat meminta penghapusan data Anda dengan salah satu cara berikut:</p>
                    
                    <div class="card my-4">
                        <div class="card-body">
                            <h3 class="h5">Opsi 1: Melalui Akun Anda</h3>
                            <p>Jika Anda memiliki akun aktif, Anda dapat:</p>
                            <ol>
                                <li>Masuk ke akun Anda</li>
                                <li>Pergi ke Pengaturan > Privasi</li>
                                <li>Pilih "Hapus Akun & Data Saya"</li>
                                <li>Ikuti petunjuk verifikasi</li>
                            </ol>
                        </div>
                    </div>
                    
                    <div class="card my-4">
                        <div class="card-body">
                            <h3 class="h5">Opsi 2: Melalui Email</h3>
                            <p>Anda dapat mengirim email permintaan penghapusan data ke <a href="mailto:suport@merawatindonesia.com">suport@merawatindonesia.com</a> dengan subjek "Permintaan Penghapusan Data". Sertakan:</p>
                            <ul>
                                <li>Alamat email yang terkait dengan akun Anda</li>
                                <li>Nama lengkap Anda</li>
                                <li>Alasan permintaan (opsional)</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card my-4">
                        <div class="card-body">
                            <h3 class="h5">Opsi 3: Melalui Formulir di Bawah</h3>
                            <p>Gunakan formulir di bawah ini untuk mengajukan permintaan penghapusan data:</p>
                            
                            <form action="{{ route('data.deletion.request') }}" method="POST" class="mt-3">
                                @csrf
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nama Lengkap *</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="reason" class="form-label">Alasan Penghapusan (Opsional)</label>
                                    <textarea class="form-control" id="reason" name="reason" rows="3"></textarea>
                                </div>
                                
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="confirm" name="confirm" required>
                                    <label class="form-check-label" for="confirm">Saya mengonfirmasi bahwa saya ingin data pribadi saya dihapus dari Merawat Indonesia *</label>
                                </div>
                                
                                <button type="submit" class="btn btn-danger">Kirim Permintaan</button>
                            </form>
                        </div>
                    </div>
                    
                    <h2 class="h4 mt-4">Proses dan Waktu</h2>
                    <p>Setelah menerima permintaan Anda:</p>
                    <ol>
                        <li>Kami akan memverifikasi identitas Anda untuk memastikan keamanan data</li>
                        <li>Kami akan memproses permintaan Anda dalam waktu 30 hari</li>
                        <li>Anda akan menerima konfirmasi saat data Anda telah dihapus</li>
                    </ol>
                    
                    <h2 class="h4 mt-4">Data Facebook dan Platform Lainnya</h2>
                    <p>Jika Anda masuk menggunakan Facebook atau platform sosial lainnya, penghapusan data dari sistem kami tidak akan menghapus data Anda dari platform tersebut. Anda perlu menghubungi platform tersebut secara terpisah untuk mengelola data Anda di sana.</p>
                    
                    <h2 class="h4 mt-4">Pertanyaan Tambahan</h2>
                    <p>Jika Anda memiliki pertanyaan lain tentang penghapusan data atau privasi Anda, silakan hubungi kami di <a href="mailto:suport@merawatindonesia.com">suport@merawatindonesia.com</a>.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection