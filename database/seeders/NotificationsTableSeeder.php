<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use App\Models\Campaign;
use App\Models\Donation;
use Illuminate\Database\Seeder;

class NotificationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        
        if ($users->isEmpty()) {
            $this->command->info('No users found. Please run UsersTableSeeder first.');
            return;
        }
        
        $notificationTypes = ['system', 'campaign', 'donation', 'withdrawal'];
        $campaigns = Campaign::all();
        
        foreach ($users as $user) {
            // Generate 3-10 notifications per user
            $notificationCount = rand(3, 10);
            
            for ($i = 0; $i < $notificationCount; $i++) {
                $type = $notificationTypes[array_rand($notificationTypes)];
                $readAt = rand(0, 2) === 0 ? now()->subHours(rand(1, 48)) : null; // 1/3 chance of being read
                $isSentEmail = (bool)rand(0, 1);
                $data = null;
                
                // Build notification based on type
                switch ($type) {
                    case 'system':
                        $systemNotifications = [
                            [
                                'title' => 'Selamat Datang di Platform Donasi',
                                'message' => 'Terima kasih telah bergabung dengan platform donasi kami. Mulai bantu sesama dengan berdonasi atau membuat kampanye.'
                            ],
                            [
                                'title' => 'Update Aplikasi',
                                'message' => 'Kami telah meluncurkan pembaruan aplikasi dengan fitur-fitur baru. Silakan update aplikasi Anda untuk pengalaman terbaik.'
                            ],
                            [
                                'title' => 'Verifikasi Email',
                                'message' => 'Mohon verifikasi email Anda untuk mengaktifkan seluruh fitur platform.'
                            ]
                        ];
                        
                        $notification = $systemNotifications[array_rand($systemNotifications)];
                        $title = $notification['title'];
                        $message = $notification['message'];
                        break;
                        
                    case 'campaign':
                        if ($campaigns->isEmpty()) {
                            continue 2; // Skip to next iteration if no campaigns
                        }
                        
                        $campaign = $campaigns->random();
                        $campaignNotifications = [
                            [
                                'title' => 'Kampanye Baru: ' . $campaign->title,
                                'message' => 'Kampanye baru telah dibuat. Lihat dan dukung kampanye ini sekarang.'
                            ],
                            [
                                'title' => 'Update Kampanye: ' . $campaign->title,
                                'message' => 'Ada kabar terbaru dari kampanye yang Anda ikuti. Klik untuk melihat detailnya.'
                            ],
                            [
                                'title' => 'Kampanye Mendekati Deadline',
                                'message' => 'Kampanye ' . $campaign->title . ' akan berakhir dalam 3 hari. Bantu sekarang sebelum terlambat.'
                            ]
                        ];
                        
                        $notification = $campaignNotifications[array_rand($campaignNotifications)];
                        $title = $notification['title'];
                        $message = $notification['message'];
                        $data = json_encode(['campaign_id' => $campaign->id]);
                        break;
                        
                    case 'donation':
                        $donationAmount = rand(5, 100) * 10000;
                        $title = 'Terima Kasih Atas Donasi Anda';
                        $message = 'Donasi Anda sebesar Rp ' . number_format($donationAmount, 0, ',', '.') . ' telah kami terima. Terima kasih atas kebaikan Anda.';
                        $data = json_encode(['amount' => $donationAmount]);
                        break;
                        
                    case 'withdrawal':
                        $withdrawalAmount = rand(10, 50) * 10000;
                        $title = 'Pencairan Dana Berhasil';
                        $message = 'Pencairan dana sebesar Rp ' . number_format($withdrawalAmount, 0, ',', '.') . ' telah diproses dan sedang dalam perjalanan ke rekening Anda.';
                        $data = json_encode(['amount' => $withdrawalAmount]);
                        break;
                }
                
                Notification::create([
                    'user_id' => $user->id,
                    'title' => $title,
                    'message' => $message,
                    'image_path' => $type === 'system' ? 'notifications/system.png' : null,
                    'read_at' => $readAt,
                    'type' => $type,
                    'data' => $data,
                    'is_sent_email' => $isSentEmail,
                    'created_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 23))
                ]);
            }
        }
    }
}