<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get yayasan users to create admin profiles for them
        $yayasanUsers = User::where('role', 'yayasan')->get();

        foreach ($yayasanUsers as $index => $user) {
            $status = ['menunggu', 'disetujui', 'ditolak'];
            $randomStatus = $index < 3 ? 'disetujui' : $status[array_rand($status)];
            
            Admin::create([
                'user_id' => $user->id,
                'name' => "Yayasan " . ($index + 1),
                'phone' => $user->phone,
                'leader_name' => "Pemimpin Yayasan " . ($index + 1),
                'address' => "Jl. Yayasan No. " . ($index + 1) . ", Jakarta",
                'legality' => "SK-" . rand(100000, 999999),
                'email' => $user->email,
                'avatar' => null,
                'bio' => "Yayasan yang bergerak di bidang " . ['pendidikan', 'kesehatan', 'lingkungan', 'sosial', 'kemanusiaan'][rand(0, 4)],
                'thumbnail' => null,
                'social' => json_encode([
                    'facebook' => "https://facebook.com/yayasan" . ($index + 1),
                    'tiktok' => "https://tiktok.com/@yayasan" . ($index + 1),
                    'youtube' => "https://youtube.com/c/yayasan" . ($index + 1)
                ]),
                'status' => $randomStatus,
                'log_activity' => now()
            ]);
        }

        // Create 5 more admin entries for demonstration
        for ($i = 1; $i <= 5; $i++) {
            $userId = User::create([
                'name' => "New Yayasan $i",
                'phone' => "08999888" . sprintf('%03d', $i),
                'email' => "new_yayasan$i@example.com",
                'password' => bcrypt('password123'),
                'role' => 'yayasan',
            ])->id;

            Admin::create([
                'user_id' => $userId,
                'name' => "New Yayasan $i",
                'phone' => "08999888" . sprintf('%03d', $i),
                'leader_name' => "Leader New Yayasan $i",
                'address' => "Jl. New Yayasan No. $i, Bandung",
                'legality' => "SK-NEW-" . rand(100000, 999999),
                'email' => "new_yayasan$i@example.com",
                'bio' => "Yayasan baru yang fokus pada " . ['bantuan bencana', 'pemberdayaan masyarakat', 'perlindungan anak', 'literasi', 'kesehatan mental'][rand(0, 4)],
                'status' => 'menunggu',
                'log_activity' => now()->subDays(rand(1, 30))
            ]);
        }
    }
}