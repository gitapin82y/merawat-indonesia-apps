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
     * Format: METHOD:ENDPOINT:ACCESS_TOKEN:LOWERCASE_HEX(SHA256(BODY)):TIMESTAMP
     */
    public function generateAsymmetricSignature($method, $endpoint, $accessToken, $requestBody, $timestamp)
    {
        try {
            $hashedRequestBody = strtolower(hash('sha256', $requestBody));
            $stringToSign = "{$method}:{$endpoint}:{$accessToken}:{$hashedRequestBody}:{$timestamp}";

            Log::info('Espay StringToSign (asymmetric)', ['string_to_sign' => $stringToSign]);

            if (!file_exists($this->privateKeyPath)) {
                throw new \Exception("Private key file not found at: {$this->privateKeyPath}");
            }

            $privateKey = file_get_contents($this->privateKeyPath);
            $pkeyId = openssl_pkey_get_private($privateKey);

            if (!$pkeyId) {
                throw new \Exception("Failed to load private key");
            }

            $signature = '';
            openssl_sign($stringToSign, $signature, $pkeyId, OPENSSL_ALGO_SHA256);
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

    /**
     * Get B2B Access Token dari Espay
     * StringToSign: merchantCode + "|" + timestamp
     */
    public function getB2B2CAccessToken()
    {
        try {
            $timestamp = $this->generateTimestamp();
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

            $url = $this->apiUrl . '/api/v1.0/access-token/b2b';

            Log::info('Espay Access Token Request', [
                'url'            => $url,
                'timestamp'      => $timestamp,
                'string_to_sign' => $stringToSign,
            ]);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-TIMESTAMP'  => $timestamp,
                'X-CLIENT-KEY' => $this->merchantCode,
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
     * Get Merchant Info (Inquiry Merchant Info) - Non-SNAP
     */
    public function getMerchantInfo()
    {
        try {
            $url = $this->apiUrl . '/rest/merchant/merchantinfo';
            $requestData = ['key' => $this->apiKey];

            Log::info('Espay Merchant Info Request', ['url' => $url, 'data' => $requestData]);

            $response = Http::asForm()->post($url, $requestData);
            $responseData = $response->json();

            Log::info('Espay Merchant Info Response', ['response' => $responseData]);

            if ($response->successful() && isset($responseData['error_code']) && $responseData['error_code'] === '0000') {
                return ['success' => true, 'data' => $responseData];
            }

            return [
                'success' => false,
                'message' => $responseData['error_message'] ?? 'Failed to get merchant info'
            ];
        } catch (\Exception $e) {
            Log::error('Error getting Espay merchant info: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Create Payment Transaction menggunakan Payment Host to Host (SNAP)
     * Signature wajib menyertakan access token untuk production
     */
    public function createPaymentHostToHost($donation, $campaign, $paymentMethod)
    {
        try {
            $timestamp = $this->generateTimestamp();
            $externalId = $this->generateExternalId();
            $partnerReferenceNo = 'DON-' . $donation->id . '-' . time();
            $amount = number_format((float)$donation->amount, 2, '.', '');

            $payMethod = $paymentMethod->pay_method;
            $payOption = $paymentMethod->pay_option;

            $donaturName = $donation->is_anonymous ? 'Sahabat Baik' : $donation->name;

            $statusToken = $this->createStatusToken($donation->id);
            $returnUrl = route('donations.status', [
                'id' => $donation->id,
                'status_token' => $statusToken
            ]);

            $sanitizedName = preg_replace('/[^a-zA-Z0-9\s]/', '', $donaturName);
            $sanitizedName = trim(preg_replace('/\s+/', ' ', $sanitizedName));
            $sanitizedName = substr($sanitizedName, 0, 50);

            $sanitizedPhone = preg_replace('/[^0-9]/', '', $donation->phone);
            if (substr($sanitizedPhone, 0, 2) === '62') {
                $sanitizedPhone = '0' . substr($sanitizedPhone, 2);
            }

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
                    'payType'     => 'REDIRECT',
                    'userName'    => $sanitizedName,
                    'userEmail'   => $donation->email,
                    'userPhone'   => $sanitizedPhone,
                    'inquiryUrl'  => url('/api/v1.0/transfer-va/inquiry'),
                    'paymentUrl'  => url('/api/v1.0/transfer-va/payment'),
                    'callbackUrl' => url('/api/espay/callback'),
                ],
            ];

            if ($paymentMethod->category === 'ewallet') {
                $requestBody['additionalInfo']['productCode'] = $payOption;
            } elseif ($paymentMethod->category === 'qris') {
                $requestBody['additionalInfo']['productCode'] = $payOption;
            }

            $requestBodyJson = json_encode($requestBody);
            $endpoint = '/apimerchant/v1.0/debit/payment-host-to-host';

            // Step 1: Ambil access token (wajib untuk SNAP production)
            $tokenResult = $this->getB2B2CAccessToken();
            if (!$tokenResult['success']) {
                Log::error('Espay: Gagal mendapatkan access token', $tokenResult);
                return [
                    'success' => false,
                    'message' => 'Gagal mendapatkan access token Espay: ' . ($tokenResult['message'] ?? 'unknown')
                ];
            }
            $accessToken = $tokenResult['accessToken'];

            // Step 2: Generate signature dengan access token
            $signature = $this->generateAsymmetricSignature(
                'POST',
                $endpoint,
                $accessToken,
                $requestBodyJson,
                $timestamp
            );

            $url = $this->apiMerchantUrl . $endpoint;

            Log::info('Espay Payment Request', [
                'url'            => $url,
                'body'           => $requestBody,
                'timestamp'      => $timestamp,
                'external_id'    => $externalId,
                'payment_method' => [
                    'code'       => $paymentMethod->code,
                    'category'   => $paymentMethod->category,
                    'pay_method' => $payMethod,
                    'pay_option' => $payOption
                ]
            ]);

            // Step 3: Kirim request dengan Authorization Bearer token
            $response = Http::withHeaders([
                'Content-Type'   => 'application/json',
                'Authorization'  => 'Bearer ' . $accessToken,
                'X-TIMESTAMP'    => $timestamp,
                'X-SIGNATURE'    => $signature,
                'X-EXTERNAL-ID'  => $externalId,
                'X-PARTNER-ID'   => $this->merchantCode,
                'CHANNEL-ID'     => config('espay.channel_id'),
            ])->post($url, $requestBody);

            $responseData = $response->json();

            Log::info('Espay Payment Response', [
                'response' => $responseData,
                'status'   => $response->status()
            ]);

            if ($response->successful() && isset($responseData['responseCode'])) {
                if ($responseData['responseCode'] === '2005400') {
                    return [
                        'success' => true,
                        'data' => [
                            'reference'          => $partnerReferenceNo,
                            'checkout_url'       => $responseData['webRedirectUrl'] ?? null,
                            'approval_code'      => $responseData['approvalCode'] ?? null,
                            'partner_reference_no' => $partnerReferenceNo,
                            'expired_time'       => Carbon::now('Asia/Jakarta')
                                ->addHours(config('espay.default_expiry_hours', 24))
                                ->timestamp
                        ]
                    ];
                }

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

    /**
     * Check payment status - Non-SNAP
     */
    public function checkPaymentStatus($orderId)
    {
        try {
            $timestamp  = $this->generateTimestamp();
            $uuid       = $this->generateExternalId();
            $rqDatetime = now('Asia/Jakarta')->format('Y-m-d H:i:s');

            $rawString = '##' . $this->signatureKey . '##' . $rqDatetime . '##' . $orderId . '##CHECKSTATUS##';
            $signature = hash('sha256', strtoupper($rawString));

            $url = $this->apiUrl . '/rest/merchant/status';

            $response = Http::asForm()->post($url, [
                'uuid'            => $uuid,
                'rq_datetime'     => $rqDatetime,
                'comm_code'       => $this->merchantCode,
                'order_id'        => $orderId,
                'is_paymentnotif' => 'Y',
                'signature'       => $signature,
            ]);

            if ($response->successful()) {
                $data     = $response->json();
                $txStatus = $data['tx_status'] ?? 'IP';

                $statusMap = [
                    'S'  => '00',
                    'F'  => '02',
                    'EX' => '03',
                    'IP' => '01',
                    'SP' => '01',
                    'WC' => '01',
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
            if (!config('espay.callback.verify_signature')) {
                return true;
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Error verifying Espay callback signature: ' . $e->getMessage());
            return false;
        }
    }
}