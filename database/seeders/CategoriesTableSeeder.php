<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Kesehatan', 'icon' => 'health.svg'],
            ['name' => 'Pendidikan', 'icon' => 'education.svg'],
            ['name' => 'Kemanusiaan', 'icon' => 'humanity.svg'],
            ['name' => 'Bencana Alam', 'icon' => 'disaster.svg'],
            ['name' => 'Rumah Ibadah', 'icon' => 'worship.svg'],
            ['name' => 'Bantuan Medis', 'icon' => 'medical.svg'],
            ['name' => 'Pemberdayaan', 'icon' => 'empowerment.svg'],
            ['name' => 'Disabilitas', 'icon' => 'disability.svg'],
            ['name' => 'Lingkungan', 'icon' => 'environment.svg'],
            ['name' => 'Anak Yatim', 'icon' => 'orphan.svg'],
            ['name' => 'Panti Asuhan', 'icon' => 'orphanage.svg'],
            ['name' => 'Wakaf', 'icon' => 'waqf.svg']
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}