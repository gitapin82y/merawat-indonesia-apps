<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LegalDocument;
use Carbon\Carbon;

class LegalDocumentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Privacy Policy initial content
        $privacyPolicyContent = '<h2 class="h4 mt-4">1. Pengantar</h2>
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
        <p>Jika Anda memiliki pertanyaan tentang Kebijakan Privasi kami, silakan hubungi kami di: <a href="mailto:suport@merawatindonesia.com">suport@merawatindonesia.com</a></p>';

        // Terms of Service initial content
        $termsOfServiceContent = '<h2 class="h4 mt-4">1. Penerimaan Syarat</h2>
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
        <p>Jika Anda memiliki pertanyaan tentang Syarat dan Ketentuan kami, silakan hubungi kami di: <a href="mailto:suport@merawatindonesia.com">suport@merawatindonesia.com</a></p>';

        // Create privacy policy document
        LegalDocument::create([
            'type' => LegalDocument::PRIVACY_POLICY,
            'content' => $privacyPolicyContent,
            'last_updated' => Carbon::now(),
        ]);

        // Create terms of service document
        LegalDocument::create([
            'type' => LegalDocument::TERMS_OF_SERVICE,
            'content' => $termsOfServiceContent,
            'last_updated' => Carbon::now(),
        ]);
    }
}