<?php

namespace Database\Seeders;

use App\Models\TripayPaymentMethod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TripayPaymentMethodSeeder extends Seeder
{
    protected $apiKey;
    protected $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.tripay.api_key');
        $this->apiUrl = config('services.tripay.api_url');
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            // Pastikan URL berakhir dengan slash
            $apiUrl = rtrim($this->apiUrl, '/') . '/';
            $endpoint = 'merchant/payment-channel';
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey
            ])->get($apiUrl . $endpoint);
            
            if ($response->successful() && isset($response['success']) && $response['success']) {
                $methods = $response['data'];
                
                foreach ($methods as $method) {
                    TripayPaymentMethod::updateOrCreate(
                        ['code' => $method['code']],
                        [
                            'name' => $method['name'],
                            'is_active' => true // Aktifkan semua secara default
                        ]
                    );
                }
                
                $this->command->info('Tripay payment methods seeded successfully!');
            } else {
                $this->command->error('Failed to fetch payment methods from Tripay API');
            }
        } catch (\Exception $e) {
            Log::error('Error seeding Tripay payment methods: ' . $e->getMessage());
            $this->command->error('Error seeding Tripay payment methods: ' . $e->getMessage());
        }
    }
}