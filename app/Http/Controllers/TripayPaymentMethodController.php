<?php

namespace App\Http\Controllers;

use App\Models\TripayPaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TripayPaymentMethodController extends Controller
{
    protected $apiKey;
    protected $apiUrl;

    public function __construct()
    {
        
        $this->apiKey = env('TRIPAY_API_KEY');
        $this->apiUrl = env('TRIPAY_API_URL', 'https://tripay.co.id/api-sandbox');
    }

    public function index()
    {
        $methods = TripayPaymentMethod::all();
        return response()->json($methods);
    }

    public function fetchFromTripay()
    {
        try {
            $apiUrl = rtrim($this->apiUrl, '/') . '/';
            $endpoint = 'merchant/payment-channel';
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey
            ])->get($apiUrl . $endpoint);
            
            if ($response->successful() && isset($response['success']) && $response['success']) {
                return response()->json($response['data']);
            }
            
            return response()->json(['error' => 'Failed to fetch payment methods from Tripay'], 500);
        } catch (\Exception $e) {
            Log::error('Error fetching Tripay payment methods: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function syncPaymentMethods()
    {
        try {
            $tripayMethods = $this->fetchFromTripay()->getData();
            
            if (isset($tripayMethods->error)) {
                return response()->json(['error' => $tripayMethods->error], 500);
            }
            
            // Get existing methods
            $existingMethods = TripayPaymentMethod::pluck('is_active', 'code')->toArray();
            
            foreach ($tripayMethods as $method) {
                TripayPaymentMethod::updateOrCreate(
                    ['code' => $method->code],
                    [
                        'name' => $method->name,
                        'is_active' => array_key_exists($method->code, $existingMethods) 
                            ? $existingMethods[$method->code] 
                            : true
                    ]
                );
            }
            
            return response()->json(['success' => true, 'message' => 'Payment methods synced successfully']);
        } catch (\Exception $e) {
            Log::error('Error syncing Tripay payment methods: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function toggleStatus(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'is_active' => 'required|boolean'
        ]);
        
        $method = TripayPaymentMethod::where('code', $request->code)->first();
        
        if (!$method) {
            return response()->json(['error' => 'Payment method not found'], 404);
        }
        
        $method->is_active = $request->is_active;
        $method->save();
        
        return response()->json(['success' => true, 'message' => 'Status updated successfully']);
    }
}