<?php

namespace Database\Seeders;

use App\Models\ManualPaymentMethod;
use Illuminate\Database\Seeder;

class ManualPaymentMethodsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentMethods = [
            [
                'name' => 'Bank BCA',
                'account_number' => '1234567890',
                'account_name' => 'Yayasan Donasi',
                'is_active' => true,
                'icon' => 'bca.png',
                'instructions' => 'Transfer ke rekening BCA kami dan upload bukti transfer.'
            ],
            [
                'name' => 'Bank Mandiri',
                'account_number' => '9876543210',
                'account_name' => 'Yayasan Donasi',
                'is_active' => true,
                'icon' => 'mandiri.png',
                'instructions' => 'Transfer ke rekening Mandiri kami dan upload bukti transfer.'
            ],
            [
                'name' => 'Bank BNI',
                'account_number' => '0123456789',
                'account_name' => 'Yayasan Donasi',
                'is_active' => true,
                'icon' => 'bni.png',
                'instructions' => 'Transfer ke rekening BNI kami dan upload bukti transfer.'
            ],
            [
                'name' => 'Bank BRI',
                'account_number' => '9870123456',
                'account_name' => 'Yayasan Donasi',
                'is_active' => true,
                'icon' => 'bri.png',
                'instructions' => 'Transfer ke rekening BRI kami dan upload bukti transfer.'
            ],
            [
                'name' => 'GoPay',
                'account_number' => '08123456789',
                'account_name' => 'Yayasan Donasi',
                'is_active' => true,
                'icon' => 'gopay.png',
                'instructions' => 'Transfer ke GoPay kami dan upload bukti transfer.'
            ],
            [
                'name' => 'OVO',
                'account_number' => '08123456780',
                'account_name' => 'Yayasan Donasi',
                'is_active' => true,
                'icon' => 'ovo.png',
                'instructions' => 'Transfer ke OVO kami dan upload bukti transfer.'
            ],
            [
                'name' => 'DANA',
                'account_number' => '08123456781',
                'account_name' => 'Yayasan Donasi',
                'is_active' => true,
                'icon' => 'dana.png',
                'instructions' => 'Transfer ke DANA kami dan upload bukti transfer.'
            ],
            [
                'name' => 'LinkAja',
                'account_number' => '08123456782',
                'account_name' => 'Yayasan Donasi',
                'is_active' => true,
                'icon' => 'linkaja.png',
                'instructions' => 'Transfer ke LinkAja kami dan upload bukti transfer.'
            ],
            [
                'name' => 'CIMB Niaga',
                'account_number' => '0987654321',
                'account_name' => 'Yayasan Donasi',
                'is_active' => false,
                'icon' => 'cimb.png',
                'instructions' => 'Transfer ke rekening CIMB kami dan upload bukti transfer.'
            ],
            [
                'name' => 'Bank Permata',
                'account_number' => '1122334455',
                'account_name' => 'Yayasan Donasi',
                'is_active' => false,
                'icon' => 'permata.png',
                'instructions' => 'Transfer ke rekening Permata kami dan upload bukti transfer.'
            ],
        ];

        foreach ($paymentMethods as $method) {
            ManualPaymentMethod::create($method);
        }
    }
}