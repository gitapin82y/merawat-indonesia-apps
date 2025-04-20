<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Campaign;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CampaignsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get approved admins
        $admins = Admin::where('status', 'disetujui')->get();
        if ($admins->isEmpty()) {
            $this->command->info('No approved admins found. Please run AdminsTableSeeder first.');
            return;
        }
        
        $categories = Category::all();
        if ($categories->isEmpty()) {
            $this->command->info('No categories found. Please run CategoriesTableSeeder first.');
            return;
        }

        $campaignData = [
            [
                'title' => 'Bantu Korban Banjir di Kalimantan Selatan',
                'description' => 'Banjir bandang telah melanda beberapa kabupaten di Kalimantan Selatan. Ribuan rumah terendam dan banyak warga kehilangan tempat tinggal. Mari bantu saudara-saudara kita yang terdampak bencana ini.',
                'deadline' => Carbon::now()->addMonths(3),
                'jumlah_target_donasi' => 100000000,
                'status' => 'aktif'
            ],
            [
                'title' => 'Bangun Sekolah untuk Anak Pedalaman Papua',
                'description' => 'Masih banyak anak-anak di pedalaman Papua yang belum mendapatkan akses pendidikan layak. Melalui kampanye ini, kami berencana membangun sekolah dan menyediakan fasilitas belajar yang memadai.',
                'deadline' => Carbon::now()->addMonths(6),
                'jumlah_target_donasi' => 250000000,
                'status' => 'aktif'
            ],
            [
                'title' => 'Pengobatan Gratis untuk Lansia',
                'description' => 'Kampanye ini bertujuan untuk memberikan layanan kesehatan dan pengobatan gratis bagi lansia kurang mampu di berbagai wilayah Indonesia.',
                'deadline' => Carbon::now()->addMonths(2),
                'jumlah_target_donasi' => 75000000,
                'status' => 'aktif'
            ],
            [
                'title' => 'Renovasi Masjid Al-Ihsan',
                'description' => 'Masjid Al-Ihsan yang telah berusia lebih dari 50 tahun membutuhkan renovasi menyeluruh untuk menampung jamaah yang semakin bertambah.',
                'deadline' => Carbon::now()->addMonths(4),
                'jumlah_target_donasi' => 350000000,
                'status' => 'aktif'
            ],
            [
                'title' => 'Beasiswa untuk Anak Yatim',
                'description' => 'Berikan kesempatan pendidikan bagi anak-anak yatim dengan menyediakan beasiswa dari SD hingga SMA.',
                'deadline' => Carbon::now()->addMonths(12),
                'jumlah_target_donasi' => 500000000,
                'status' => 'aktif'
            ],
            [
                'title' => 'Bantuan Medis untuk Penderita Kanker',
                'description' => 'Kumpulkan dana untuk membantu biaya pengobatan pasien kanker dari keluarga kurang mampu.',
                'deadline' => Carbon::now()->addMonths(3),
                'jumlah_target_donasi' => 200000000,
                'status' => 'aktif'
            ],
            [
                'title' => 'Pembangunan Perpustakaan Desa',
                'description' => 'Membangun perpustakaan di desa terpencil untuk meningkatkan minat baca dan akses terhadap buku-buku berkualitas.',
                'deadline' => Carbon::now()->addMonths(5),
                'jumlah_target_donasi' => 150000000,
                'status' => 'validasi'
            ],
            [
                'title' => 'Bantuan untuk Difabel',
                'description' => 'Menyediakan alat bantu dan fasilitas pendukung bagi penyandang disabilitas agar dapat beraktivitas secara mandiri.',
                'deadline' => Carbon::now()->addMonths(4),
                'jumlah_target_donasi' => 120000000,
                'status' => 'validasi'
            ],
            [
                'title' => 'Program Air Bersih untuk Desa Kekeringan',
                'description' => 'Membangun sumur dan instalasi air bersih di desa-desa yang mengalami kekeringan panjang.',
                'deadline' => Carbon::now()->addMonths(8),
                'jumlah_target_donasi' => 300000000,
                'status' => 'selesai'
            ],
            [
                'title' => 'Bantuan Laptop untuk Siswa Kurang Mampu',
                'description' => 'Menyediakan laptop bagi siswa kurang mampu untuk mendukung pembelajaran daring.',
                'deadline' => Carbon::now()->subMonths(1),
                'jumlah_target_donasi' => 150000000,
                'status' => 'berakhir'
            ],
            [
                'title' => 'Pemberdayaan UMKM Pasca Pandemi',
                'description' => 'Program pelatihan dan modal usaha untuk membantu UMKM bangkit setelah pandemi.',
                'deadline' => Carbon::now()->addMonths(6),
                'jumlah_target_donasi' => 250000000,
                'status' => 'aktif'
            ],
            [
                'title' => 'Renovasi Panti Asuhan Harapan',
                'description' => 'Panti Asuhan Harapan yang menampung lebih dari 50 anak membutuhkan renovasi bangunan dan fasilitas.',
                'deadline' => Carbon::now()->addMonths(3),
                'jumlah_target_donasi' => 175000000,
                'status' => 'aktif'
            ]
        ];

        foreach ($campaignData as $index => $data) {
            $admin = $admins->random();
            $category = $categories->random();
            
            // Simulate some progress for active campaigns
            $jumlahDonasi = 0;
            $totalDonatur = 0;
            $totalKabarTerbaru = 0;
            $totalPencairanDana = 0;
            $jumlahPencairanDana = 0;
            
            if ($data['status'] === 'aktif' || $data['status'] === 'selesai') {
                $progress = rand(10, 90) / 100;
                $jumlahDonasi = round($data['jumlah_target_donasi'] * $progress);
                $totalDonatur = rand(20, 100);
                $totalKabarTerbaru = rand(1, 5);
                
                if ($data['status'] === 'selesai') {
                    $progress = 1;
                    $jumlahDonasi = $data['jumlah_target_donasi'];
                    $totalPencairanDana = rand(1, 3);
                    $jumlahPencairanDana = round($jumlahDonasi * 0.8); // 80% of total donations withdrawn
                }
            }
            
            Campaign::create([
                'admin_id' => $admin->id,
                'category_id' => $category->id,
                'photo' => 'campaign-' . ($index + 1) . '.jpg',
                'title' => $data['title'],
                'slug' => Str::slug($data['title']),
                'description' => $data['description'],
                'status' => $data['status'],
                'deadline' => $data['deadline'],
                'total_donatur' => $totalDonatur,
                'total_kabar_terbaru' => $totalKabarTerbaru,
                'total_pencairan_dana' => $totalPencairanDana,
                'jumlah_pencairan_dana' => $jumlahPencairanDana,
                'jumlah_pencarian' => rand(50, 500),
                'current_donation' => $jumlahDonasi,
                'jumlah_donasi' => $jumlahDonasi,
                'jumlah_target_donasi' => $data['jumlah_target_donasi'],
                'document_rab' => 'rab-campaign-' . ($index + 1) . '.pdf'
            ]);
        }
    }
}