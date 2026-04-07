<?php

namespace App\Http\Controllers;

use App\Models\EspayPaymentMethod;
use App\Services\EspayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EspayPaymentMethodController extends Controller
{
    protected $espayService;

    public function __construct(EspayService $espayService)
    {
        $this->espayService = $espayService;
    }

    /**
     * Get all payment methods
     */
    public function index()
    {
        $methods = EspayPaymentMethod::all();
        return response()->json($methods);
    }

    /**
     * Fetch payment methods from Espay API
     */
    public function fetchFromEspay()
    {
        try {
            $merchantInfo = $this->espayService->getMerchantInfo();
            
            if (!isset($merchantInfo['success']) || !$merchantInfo['success']) {
                return response()->json([
                    'error' => $merchantInfo['message'] ?? 'Failed to fetch payment methods from Espay'
                ], 500);
            }
            
            return response()->json($merchantInfo['data']);
        } catch (\Exception $e) {
            Log::error('Error fetching Espay payment methods: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Sync payment methods from Espay
     */
    public function syncPaymentMethods()
    {
        try {
            $merchantInfo = $this->espayService->getMerchantInfo();
            
            if (!isset($merchantInfo['success']) || !$merchantInfo['success']) {
                return response()->json([
                    'error' => $merchantInfo['message'] ?? 'Failed to sync payment methods'
                ], 500);
            }
            
            // Get existing methods
            $existingMethods = EspayPaymentMethod::pluck('is_active', 'code')->toArray();
            
            $synced = 0;
            
            // FIXED: Response format dari Espay sebenarnya adalah:
            // data: array of payment methods dengan format flat:
            // [{ "bankCode": "014", "productCode": "BCAATM", "productName": "BCA VA Online" }]
            
            $paymentMethods = $merchantInfo['data']['data'] ?? [];
            
            if (empty($paymentMethods)) {
                return response()->json([
                    'error' => 'No payment methods returned from Espay. Please check your credentials.'
                ], 500);
            }
            
            foreach ($paymentMethods as $method) {
                $bankCode = $method['bankCode'];
                $productCode = $method['productCode'];
                $productName = $method['productName'];
                
                // Code format: bankCode_productCode
                $code = $bankCode . '_' . $productCode;
                
                // Tentukan kategori berdasarkan product code
                $category = $this->determineCategory($productCode);
                
                // Tentukan nama bank berdasarkan bank code
                $bankName = $this->getBankName($bankCode);
                
                EspayPaymentMethod::updateOrCreate(
                    ['code' => $code],
                    [
                        'name' => $productName, // Gunakan productName dari Espay
                        'pay_method' => $bankCode,
                        'pay_option' => $productCode,
                        'category' => $category,
                        'is_active' => array_key_exists($code, $existingMethods) 
                            ? $existingMethods[$code] 
                            : true,
                        'icon_url' => null, // Espay tidak provide icon URL di response ini
                        'description' => $bankName
                    ]
                );
                
                $synced++;
            }
            
            return response()->json([
                'success' => true, 
                'message' => "Successfully synced {$synced} payment methods"
            ]);
        } catch (\Exception $e) {
            Log::error('Error syncing Espay payment methods: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get bank name from bank code
     */
    private function getBankName($bankCode)
    {
        $bankNames = [
            '002' => 'BRI',
            '008' => 'Mandiri',
            '009' => 'BNI',
            '011' => 'Danamon',
            '013' => 'Permata',
            '014' => 'BCA',
            '016' => 'Maybank',
            '022' => 'CIMB Niaga',
            '213' => 'BTPN',
            '503' => 'OVO',
            '911' => 'LinkAja',
        ];
        
        return $bankNames[$bankCode] ?? 'Bank ' . $bankCode;
    }

    /**
     * Toggle payment method status
     */
    public function toggleStatus(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'is_active' => 'required|boolean'
        ]);
        
        $method = EspayPaymentMethod::where('code', $request->code)->first();
        
        if (!$method) {
            return response()->json(['error' => 'Payment method not found'], 404);
        }
        
        $method->is_active = $request->is_active;
        $method->save();
        
        return response()->json([
            'success' => true, 
            'message' => 'Status updated successfully'
        ]);
    }

    /**
     * Determine category based on product code
     */
    private function determineCategory($productCode)
    {
        $productCode = strtoupper($productCode);
        
        // QRIS
        if (strpos($productCode, 'QR') !== false || strpos($productCode, 'QRIS') !== false) {
            return 'qris';
        }
        
        // E-Wallet
        $ewallets = ['OVO', 'GOPAY', 'DANA', 'SHOPEE', 'LINKAJA', 'LINKAJAAPPLINK', 'JENIUS'];
        foreach ($ewallets as $ewallet) {
            if (strpos($productCode, $ewallet) !== false) {
                return 'ewallet';
            }
        }
        
        // Credit Card
        if (strpos($productCode, 'CARD') !== false || 
            strpos($productCode, 'CC') !== false ||
            strpos($productCode, 'CREDIT') !== false) {
            return 'credit_card';
        }
        
        // Virtual Account (ATM/VA patterns)
        if (strpos($productCode, 'VA') !== false || 
            strpos($productCode, 'ATM') !== false ||
            strpos($productCode, 'IBANK') !== false) {
            return 'virtual_account';
        }
        
        // Bank Transfer
        if (strpos($productCode, 'TRANSFER') !== false) {
            return 'bank_transfer';
        }
        
        return 'other';
    }
}