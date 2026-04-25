<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EspayService
{
    protected $merchantCode;
    protected $apiKey;
    protected $signatureKey;
    protected $password;
    protected $apiUrl;
    protected $apiMerchantUrl;
    protected $privateKeyPath;

    public function __construct()
    {
        $this->merchantCode = config('espay.merchant_code');
        $this->apiKey = config('espay.api_key');
        $this->signatureKey = config('espay.signature_key');
        $this->password = config('espay.password');
        $this->apiUrl = config('espay.api_url');
        $this->apiMerchantUrl = config('espay.api_merchant_url');
        $this->privateKeyPath = config('espay.private_key_path');
    }

    /**
     * Generate timestamp in ISO 8601 format untuk Espay
     */
    public function generateTimestamp()
    {
        return Carbon::now('Asia/Jakarta')->format('Y-m-d\TH:i:sP');
    }

    /**
     * Generate external ID (unique ID untuk request)
     */
    public function generateExternalId()
    {
        return Str::uuid()->toString();
    }

    /**
     * Generate asymmetric signature untuk Espay menggunakan RSA
     * Dokumentasi: https://docs.espay.id/api-mandatory/snap/signature/
     */
    public function generateAsymmetricSignature($method, $endpoint, $accessToken, $requestBody, $timestamp)
    {
        try {
            // Format: HTTP_METHOD + ":" + RELATIVE_PATH + ":" + ACCESS_TOKEN + ":" + LOWERCASE_HEX(SHA256(REQUEST_BODY)) + ":" + TIMESTAMP
            $hashedRequestBody = hash('sha256', $requestBody);
            $hashedRequestBody = strtolower($hashedRequestBody);
            
            $stringToSign = "{$method}:{$endpoint}:{$accessToken}:{$hashedRequestBody}:{$timestamp}";
            
            // Load private key
            if (!file_exists($this->privateKeyPath)) {
                throw new \Exception("Private key file not found at: {$this->privateKeyPath}");
            }
            
            $privateKey = file_get_contents($this->privateKeyPath);
            $pkeyId = openssl_pkey_get_private($privateKey);
            
            if (!$pkeyId) {
                throw new \Exception("Failed to load private key");
            }
            
            // Sign the string
            $signature = '';
            openssl_sign($stringToSign, $signature, $pkeyId, OPENSSL_ALGO_SHA256);
            
            // Base64 encode
            $signatureBase64 = base64_encode($signature);
            
            openssl_free_key($pkeyId);
            
            return $signatureBase64;
        } catch (\Exception $e) {
            Log::error('Error generating asymmetric signature: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate Hash-Based Signature untuk Inquiry Merchant Info (Non-SNAP)
     * Formula: SHA256(CommCode + ApiKey + SignatureKey)
     */
    public function generateHashSignature()
    {
        $string = $this->merchantCode . $this->apiKey . $this->signatureKey;
        return hash('sha256', $string);
    }
public function getB2B2CAccessToken()
{
    try {
        $timestamp = $this->generateTimestamp();

        // StringToSign untuk access token = merchantCode + "|" + timestamp
        $stringToSign = $this->merchantCode . '|' . $timestamp;

        if (!file_exists($this->privateKeyPath)) {
            throw new \Exception("Private key not found at: {$this->privateKeyPath}");
        }

        $privateKey = file_get_contents($this->privateKeyPath);
        $pkeyId     = openssl_pkey_get_private($privateKey);

        if (!$pkeyId) {
            throw new \Exception("Failed to load private key: " . openssl_error_string());
        }

        $signature = '';
        openssl_sign($stringToSign, $signature, $pkeyId, OPENSSL_ALGO_SHA256);
        $signatureBase64 = base64_encode($signature);

        // Sandbox menggunakan /api/v1.0/access-token/b2b (bukan b2b2c)
        $url = $this->apiUrl . '/api/v1.0/access-token/b2b';

        Log::info('Espay Access Token Request', [
            'url'            => $url,
            'timestamp'      => $timestamp,
            'string_to_sign' => $stringToSign,
        ]);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-TIMESTAMP'  => $timestamp,
            'X-CLIENT-KEY' => $this->apiKey, // 478e6640ee7aab15364bf42569559a35
            'X-SIGNATURE'  => $signatureBase64,
        ])->post($url, ['grantType' => 'client_credentials']);

        Log::info('Espay Access Token Response', [
            'status'   => $response->status(),
            'response' => $response->body(),
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'success'     => true,
                'accessToken' => $data['accessToken'] ?? null,
                'expiresIn'   => $data['expiresIn'] ?? null,
                'tokenType'   => $data['tokenType'] ?? 'Bearer',
            ];
        }

        Log::error('Espay Access Token Error', ['response' => $response->body()]);
        return ['success' => false, 'message' => 'Failed to get access token: ' . $response->body()];

    } catch (\Exception $e) {
        Log::error('Error getting Espay access token: ' . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}


    /**
     * Get Merchant Info (Inquiry Merchant Info)
     * Endpoint untuk mendapatkan daftar payment methods yang aktif
     * Dokumentasi: https://sandbox-kit.espay.id/docs/v2/docespay/en/inquirymi.php
     */
    public function getMerchantInfo()
    {
        try {
            // FIXED: Endpoint yang benar
            $url = $this->apiUrl . '/rest/merchant/merchantinfo';
            
            // Request harus dalam format x-www-form-urlencoded, bukan JSON
            $requestData = [
                'key' => $this->apiKey,
            ];
            
            Log::info('Espay Merchant Info Request', [
                'url' => $url,
                'data' => $requestData
            ]);
            
            $response = Http::asForm()->post($url, $requestData);
            
            $responseData = $response->json();
            
            Log::info('Espay Merchant Info Response', ['response' => $responseData]);
            
            if ($response->successful() && isset($responseData['error_code']) && $responseData['error_code'] === '0000') {
                return [
                    'success' => true,
                    'data' => $responseData
                ];
            }
            
            return [
                'success' => false,
                'message' => $responseData['error_message'] ?? 'Failed to get merchant info'
            ];
        } catch (\Exception $e) {
            Log::error('Error getting Espay merchant info: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Create Payment Transaction menggunakan Payment Host to Host
     * Dokumentasi: https://docs.espay.id/pembayaran/direct-api/snap/payment-host-to-host/
     */
    public function createPaymentHostToHost($donation, $campaign, $paymentMethod)
    {
        try {
            $timestamp = $this->generateTimestamp();
            $externalId = $this->generateExternalId();
            $partnerReferenceNo = $donation->snap_token;
            $amount = number_format((float)$donation->amount, 2, '.', '');
            
            // Parse payment method code
            $payMethod = $paymentMethod->pay_method;
            $payOption = $paymentMethod->pay_option;
            
            $donaturName = $donation->is_anonymous ? 'Sahabat Baik' : $donation->name;
            
            // Generate status URL dengan token
            $statusToken = $this->createStatusToken($donation->id);
            $returnUrl = route('donations.status', [
                'id' => $donation->id,
                'status_token' => $statusToken
            ]);

            $sanitizedName = preg_replace('/[^a-zA-Z0-9\s]/', '', $donaturName);
$sanitizedName = trim(preg_replace('/\s+/', ' ', $sanitizedName)); // hapus spasi ganda
// Maksimal 50 karakter sesuai spec Espay
$sanitizedName = substr($sanitizedName, 0, 50);

$sanitizedPhone = preg_replace('/[^0-9]/', '', $donation->phone);
// Pastikan diawali 08 bukan 8 atau +628
if (substr($sanitizedPhone, 0, 2) === '62') {
    $sanitizedPhone = '0' . substr($sanitizedPhone, 2);
}
            
            // Base request body
            $requestBody = [
                'partnerReferenceNo' => $partnerReferenceNo,
                'merchantId' => $this->merchantCode,
                'subMerchantId' => $this->apiKey,
                'amount' => [
                    'value' => $amount,
                    'currency' => 'IDR'
                ],
                'urlParam' => [
                    'url' => $returnUrl,
                    'type' => 'PAY_RETURN',
                    'isDeeplink' => 'N'
                ],
                'validUpTo' => Carbon::now('Asia/Jakarta')
                    ->addHours(config('espay.default_expiry_hours', 24))
                    ->format('Y-m-d\TH:i:sP'),
                'pointOfInitiation' => 'Website',
                'payOptionDetails' => [
                    'payMethod' => $payMethod,
                    'payOption' => $payOption,
                    'transAmount' => [
                        'value' => $amount,
                        'currency' => 'IDR'
                    ],
                    'feeAmount' => [
                        'value' => number_format((float)$paymentMethod->fee_amount, 2, '.', ''),
                        'currency' => 'IDR'
                    ]
                ],
                'additionalInfo' => [
    'payType'      => 'REDIRECT',
    'userName'     => $sanitizedName,
    'userEmail'    => $donation->email,
    'userPhone'    => $sanitizedPhone,
    'inquiryUrl'   => url('/api/v1.0/transfer-va/inquiry'),
    'paymentUrl'   => url('/api/v1.0/transfer-va/payment'),
    'callbackUrl'  => url('/api/espay/callback'),
],

            ];
            
            // Special handling untuk kategori tertentu
            if ($paymentMethod->category === 'ewallet') {
                $requestBody['additionalInfo']['productCode'] = $payOption;
            } elseif ($paymentMethod->category === 'qris') {
                // Untuk QRIS, tambahkan productCode
                $requestBody['additionalInfo']['productCode'] = $payOption;
            }
            
            $requestBodyJson = json_encode($requestBody);
            $endpoint = '/apimerchant/v1.0/debit/payment-host-to-host';
            
            // Generate signature
            $signature = $this->generateSimpleSignature(
                'POST',
                $endpoint,
                $requestBodyJson,
                $timestamp
            );
            
            $url = $this->apiMerchantUrl . $endpoint;
            
            Log::info('Espay Payment Request', [
                'url' => $url,
                'body' => $requestBody,
                'timestamp' => $timestamp,
                'external_id' => $externalId,
                'payment_method' => [
                    'code' => $paymentMethod->code,
                    'category' => $paymentMethod->category,
                    'pay_method' => $payMethod,
                    'pay_option' => $payOption
                ]
            ]);
            
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-TIMESTAMP' => $timestamp,
                'X-SIGNATURE' => $signature,
                'X-EXTERNAL-ID' => $externalId,
                'X-PARTNER-ID' => $this->merchantCode,
                'CHANNEL-ID' => config('espay.channel_id'),
            ])->post($url, $requestBody);
            
            $responseData = $response->json();
            
            Log::info('Espay Payment Response', [
                'response' => $responseData,
                'status' => $response->status()
            ]);
            
            if ($response->successful() && isset($responseData['responseCode'])) {
                // Response code 2005400 = Success
                if ($responseData['responseCode'] === '2005400') {
                    return [
                        'success' => true,
                        'data' => [
                            'reference' => $partnerReferenceNo,
                            'checkout_url' => $responseData['webRedirectUrl'] ?? null,
                            'approval_code' => $responseData['approvalCode'] ?? null,
                            'partner_reference_no' => $partnerReferenceNo,
                              'qr_image'           => $responseData['qrContent'] ?? null,  
            'qr_content'         => $responseData['qrUrl'] ?? null, 
                            'expired_time' => Carbon::now('Asia/Jakarta')
                                ->addHours(config('espay.default_expiry_hours', 24))
                                ->timestamp
                        ]
                    ];
                }
                
                // Return error message dari Espay
                return [
                    'success' => false,
                    'message' => 'Espay Error: ' . ($responseData['responseMessage'] ?? 'Payment creation failed') . 
                                ' (Code: ' . ($responseData['responseCode'] ?? 'unknown') . ')'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Failed to create payment: ' . ($responseData['responseMessage'] ?? $response->body())
            ];
        } catch (\Exception $e) {
            Log::error('Error creating Espay payment: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'success' => false,
                'message' => 'System error: ' . $e->getMessage()
            ];
        }
    }

private function generateSimpleSignature($method, $endpoint, $requestBody, $timestamp)
{
    try {
        // Sesuai dokumentasi Espay:
        // StringToSign = HTTPMethod + ":" + RelativeUrl + ":" + Lowercase(SHA256(MinifyJson(Body))) + ":" + Timestamp

        // Step 1: Minify JSON lalu SHA256
        $minifiedBody = json_encode(json_decode($requestBody), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $hashedBody   = strtolower(hash('sha256', $minifiedBody));

        // Step 2: Build StringToSign (TANPA access token — ini non-SNAP payment endpoint)
        $stringToSign = $method . ':' . $endpoint . ':' . $hashedBody . ':' . $timestamp;

        Log::info('Espay StringToSign', ['string_to_sign' => $stringToSign]);

        // Step 3: Load private key
        if (!file_exists($this->privateKeyPath)) {
            throw new \Exception("Private key not found at: {$this->privateKeyPath}");
        }

        $privateKey = file_get_contents($this->privateKeyPath);
        $pkeyId     = openssl_pkey_get_private($privateKey);

        if (!$pkeyId) {
            $opensslError = openssl_error_string();
            throw new \Exception("Failed to load private key. OpenSSL error: {$opensslError}");
        }

        // Step 4: Sign dengan SHA256withRSA
        $signature = '';
        $signed    = openssl_sign($stringToSign, $signature, $pkeyId, OPENSSL_ALGO_SHA256);

        if (!$signed) {
            throw new \Exception('openssl_sign failed: ' . openssl_error_string());
        }

        // Step 5: Base64 encode
        return base64_encode($signature);

    } catch (\Exception $e) {
        Log::error('Error generating signature: ' . $e->getMessage());
        throw $e;
    }
}

    /**
     * Check payment status menggunakan Inquiry Status
     * Dokumentasi: https://docs.espay.id/api-opsional/snap/inquiry-status/
     */
public function checkPaymentStatus($orderId)
{
    try {
        $timestamp   = $this->generateTimestamp();
        $uuid        = $this->generateExternalId();
        $rqDatetime  = now('Asia/Jakarta')->format('Y-m-d H:i:s');

        // Formula signature: SHA256(UPPERCASE("##KEY##rq_datetime##order_id##CHECKSTATUS##"))
        $rawString = '##' . $this->signatureKey . '##' . $rqDatetime . '##' . $orderId . '##CHECKSTATUS##';
        $signature = hash('sha256', strtoupper($rawString));

        $url = $this->apiUrl . '/rest/merchant/status';

        $response = Http::asForm()->post($url, [
            'uuid'             => $uuid,
            'rq_datetime'      => $rqDatetime,
            'comm_code'        => $this->merchantCode,
            'order_id'         => $orderId,
            'is_paymentnotif'  => 'Y', // Trigger payment notif ke server kita
            'signature'        => $signature,
        ]);

        // Log::info('Espay Check Status Response', [
        //     'status'   => $response->status(),
        //     'response' => $response->json(),
        // ]);

        if ($response->successful()) {
            $data     = $response->json();
            $txStatus = $data['tx_status'] ?? 'IP';

            // Map tx_status ke format yang dipakai controller
            $statusMap = [
                'S'  => '00', // Success
                'F'  => '02', // Failed
                'EX' => '03', // Expired
                'IP' => '01', // In Process
                'SP' => '01', // Suspect → treat as pending
                'WC' => '01', // Waiting Correction
            ];

            return [
                'success' => true,
                'data'    => [
                    'transactionStatusCode' => $statusMap[$txStatus] ?? '01',
                    'tx_status'             => $txStatus,
                    'order_id'              => $data['order_id'] ?? $orderId,
                    'amount'                => $data['amount'] ?? null,
                    'raw'                   => $data,
                ],
            ];
        }

        return ['success' => false, 'message' => 'Failed to check status: ' . $response->body()];

    } catch (\Exception $e) {
        Log::error('Espay checkPaymentStatus error: ' . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

    /**
     * Create status token untuk validasi halaman status
     */
    private function createStatusToken($donationId)
    {
        return md5($donationId . time() . Str::random(10));
    }

    /**
     * Verify signature dari payment notification callback
     */
    public function verifyCallbackSignature($requestBody, $receivedSignature, $timestamp)
    {
        try {
            // Untuk callback, Espay mengirim signature yang perlu diverifikasi
            // Implementasi ini tergantung dokumentasi callback Espay
            // Untuk sekarang, return true jika verify signature disabled
            if (!config('espay.callback.verify_signature')) {
                return true;
            }
            
            // TODO: Implement actual signature verification sesuai docs Espay
            return true;
        } catch (\Exception $e) {
            Log::error('Error verifying Espay callback signature: ' . $e->getMessage());
            return false;
        }
    }


    public function createQrisPayment($donation, $campaign, $paymentMethod)
{
    try {
        $timestamp          = $this->generateTimestamp();
        $externalId         = $this->generateExternalId();
        $partnerReferenceNo = $donation->snap_token;
        $amount             = number_format((float) $donation->amount, 2, '.', '');

        $requestBody = [
            'partnerReferenceNo' => $partnerReferenceNo,
            'merchantId'         => $this->merchantCode,
            'amount'             => ['value' => $amount, 'currency' => 'IDR'],
            'additionalInfo'     => [
                'productCode' => $paymentMethod->pay_option, // e.g. SHOPEEQRPAY
            ],
            'validityPeriod' => Carbon::now('Asia/Jakarta')
                ->addMinutes(10)
                ->format('Y-m-d\TH:i:sP'),
        ];

        $requestBodyJson = json_encode($requestBody);
        $endpoint        = '/api/v1.0/qr/qr-mpm-generate';

        $signature = $this->generateSimpleSignature('POST', $endpoint, $requestBodyJson, $timestamp);

        $url = $this->apiUrl . $endpoint; // https://api.espay.id

        Log::info('Espay QRIS Request', ['url' => $url, 'body' => $requestBody]);

        $response = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'X-TIMESTAMP'   => $timestamp,
            'X-SIGNATURE'   => $signature,
            'X-EXTERNAL-ID' => $externalId,
            'X-PARTNER-ID'  => $this->merchantCode,
            'CHANNEL-ID'    => config('espay.channel_id'),
        ])->post($url, $requestBody);

        $responseData = $response->json();

        Log::info('Espay QRIS Response', ['response' => $responseData]);

        if ($response->successful() && ($responseData['responseCode'] ?? '') === '2004700') {
            return [
                'success' => true,
                'data'    => [
                    'reference'           => $partnerReferenceNo,
                    'checkout_url'        => null,
                    'qr_image'             => $responseData['qrContent'] ?? null, // SWAP: pakai qrContent
        'qr_content'           => $responseData['qrUrl']    ?? null,  // qrUrl sebagai fallback
        'partner_reference_no' => $partnerReferenceNo,

                    'expired_time'        => Carbon::now('Asia/Jakarta')->addMinutes(10)->timestamp,
                ],
            ];
        }

        return [
            'success' => false,
            'message' => 'QRIS Error: ' . ($responseData['responseMessage'] ?? 'Failed') .
                         ' (Code: ' . ($responseData['responseCode'] ?? 'unknown') . ')',
        ];

    } catch (\Exception $e) {
        Log::error('Error creating QRIS payment: ' . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
}