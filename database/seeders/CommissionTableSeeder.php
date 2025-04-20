<?php

namespace Database\Seeders;

use App\Models\Commission;
use Illuminate\Database\Seeder;

class CommissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Setting default commission percentage/amount
        Commission::create([
            'amount' => 5 // 5% commission
        ]);
    }
}