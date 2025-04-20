<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\KabarTerbaru;
use Illuminate\Database\Seeder;

class KabarTerbaruTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $campaigns = Campaign::where('status', 'aktif')
                             ->orWhere('status', 'selesai')
                             ->get();
                             
        if ($campaigns->isEmpty()) {
            $this->command->info('No active or completed campaigns found. Please run CampaignsTableSeeder first.');
            return;
        }
        
        $updateTemplates = [
            [
                'title' => 'Penyaluran Bantuan Tahap 1',
                'description' => 'Alhamdulillah, kami telah mulai menyalurkan bantuan tahap pertama berupa sembako dan kebutuhan pokok kepada penerima manfaat. Terima kasih atas kepercayaan dan donasi yang diberikan.'
            ],
            [
                'title' => 'Kunjungan dan Asesmen Lokasi',
                'description' => 'Tim kami telah melakukan kunjungan dan asesmen ke lokasi untuk memastikan bantuan tepat sasaran. Kami telah mengidentifikasi kebutuhan utama dan prioritas penyaluran.'
            ],
            [
                'title' => 'Pembelian Alat dan Bahan',
                'description' => 'Hari ini kami telah melakukan pembelian alat dan bahan yang dibutuhkan. Semua berkas dan kwitansi akan kami laporkan secara transparan.'
            ],
            [
                'title' => 'Progres Pembangunan',
                'description' => 'Pembangunan telah mencapai 50%. Pondasi telah selesai dan saat ini sedang dalam tahap pemasangan dinding. Terima kasih atas dukungan para donatur.'
            ],
            [
                'title' => 'Pelatihan untuk Penerima Manfaat',
                'description' => 'Kami telah mengadakan pelatihan untuk para penerima manfaat. Mereka belajar tentang pengelolaan dan pemanfaatan bantuan yang diberikan agar lebih efektif dan berkelanjutan.'
            ],
            [
                'title' => 'Pencapaian Target Donasi',
                'description' => 'Alhamdulillah, berkat dukungan para donatur, kita telah mencapai 75% dari target donasi. Mari bersama-sama mencapai target penuh untuk memaksimalkan bantuan.'
            ],
            [
                'title' => 'Evaluasi Program',
                'description' => 'Tim telah melakukan evaluasi program dan mengidentifikasi area yang perlu ditingkatkan. Kami berkomitmen untuk terus meningkatkan efektivitas bantuan.'
            ],
            [
                'title' => 'Testimoni Penerima Manfaat',
                'description' => '"Terima kasih banyak atas bantuan yang diberikan. Ini sangat membantu kami di masa sulit ini." - Ini adalah salah satu testimoni dari penerima manfaat yang kami temui.'
            ],
            [
                'title' => 'Penambahan Sasaran Bantuan',
                'description' => 'Berdasarkan asesmen lanjutan, kami telah mengidentifikasi kelompok tambahan yang membutuhkan bantuan. Dengan izin para donatur, kami akan memperluas jangkauan bantuan.'
            ],
            [
                'title' => 'Persiapan Distribusi Akhir',
                'description' => 'Tim sedang mempersiapkan distribusi tahap akhir. Semua logistik dan transportasi telah diatur untuk memastikan penyaluran berjalan lancar.'
            ]
        ];
        
        foreach ($campaigns as $campaign) {
            // How many updates to create for this campaign
            $updateCount = min($campaign->total_kabar_terbaru, 10);
            $updateCount = max($updateCount, 1); // At least 1 update
            
            // Shuffle templates to get random ones
            shuffle($updateTemplates);
            
            for ($i = 0; $i < $updateCount; $i++) {
                $template = $updateTemplates[$i % count($updateTemplates)];
                
                KabarTerbaru::create([
                    'campaign_id' => $campaign->id,
                    'title' => $template['title'],
                    'description' => $template['description']
                ]);
            }
            
            // Update the campaign's total_kabar_terbaru count if needed
            if ($campaign->total_kabar_terbaru != $updateCount) {
                $campaign->update(['total_kabar_terbaru' => $updateCount]);
            }
        }
    }
}