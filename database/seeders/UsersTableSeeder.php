<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Super Admin
        User::create([
            'name' => 'Super Admin',
            'phone' => '081234567890',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'bio' => 'Super Admin Platform',
            'social' => json_encode([
                'facebook' => 'https://facebook.com/superadmin',
                'tiktok' => 'https://tiktok.com/@superadmin',
                'youtube' => 'https://youtube.com/c/superadmin'
            ])
        ]);

        // Yayasan Users (5)
        for ($i = 1; $i <= 5; $i++) {
            User::create([
                'name' => "Yayasan User $i",
                'phone' => "08123456" . sprintf('%04d', $i),
                'email' => "yayasan$i@example.com",
                'password' => Hash::make('password123'),
                'role' => 'yayasan',
                'bio' => "Bio for yayasan user $i",
                'social' => json_encode([
                    'facebook' => "https://facebook.com/yayasan$i",
                    'tiktok' => "https://tiktok.com/@yayasan$i",
                    'youtube' => "https://youtube.com/c/yayasan$i"
                ])
            ]);
        }

        // Donatur Users (15)
        for ($i = 1; $i <= 15; $i++) {
            User::create([
                'name' => "Donatur $i",
                'phone' => "08765432" . sprintf('%04d', $i),
                'email' => "donatur$i@example.com",
                'password' => Hash::make('password123'),
                'role' => 'donatur',
                'bio' => "Bio for donatur $i",
                'social' => json_encode([
                    'facebook' => "https://facebook.com/donatur$i",
                    'tiktok' => "https://tiktok.com/@donatur$i",
                ])
            ]);
        }

        // Social Media Login Users (5)
        $providers = ['google', 'facebook'];
        for ($i = 1; $i <= 5; $i++) {
            $provider = $providers[array_rand($providers)];
            User::create([
                'name' => "Social User $i",
                'phone' => "08555666" . sprintf('%04d', $i),
                'email' => "social$i@example.com",
                'provider' => $provider,
                'provider_id' => "provider_id_$i",
                'role' => 'donatur',
                'bio' => "Bio for social media user $i"
            ]);
        }
    }
}