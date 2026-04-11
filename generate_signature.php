<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$timestamp = '2026-04-11T21:16:00+07:00';
$method = 'POST';
$endpoint = '/apimerchant/v1.0/debit/payment-host-to-host';
$body = json_encode([
    'partnerReferenceNo' => 'QR-POS-006-' . time(),
    'merchantId'         => 'SGWYAYASANBINAMULIA',
    'subMerchantId'      => '478e6640ee7aab15364bf42569559a35',
    'amount'             => ['value' => '25000.00', 'currency' => 'IDR'],
    'urlParam'           => [
        'url'        => 'https://merawatindonesia.com/donations/status',
        'type'       => 'PAY_RETURN',
        'isDeeplink' => 'N',
    ],
    'validUpTo'          => '2026-04-12T21:16:00+07:00',
    'pointOfInitiation'  => 'Website',
    'payOptionDetails'   => [
        'payMethod'   => '008',
        'payOption'   => 'SALDOMUQR',
        'transAmount' => ['value' => '25000.00', 'currency' => 'IDR'],
        'feeAmount'   => ['value' => '0.00', 'currency' => 'IDR'],
    ],
    'additionalInfo'     => [
        'payType'    => 'REDIRECT',
        'userName'   => 'Test Donatur',
        'userEmail'  => 'test@merawatindonesia.com',
        'userPhone'  => '081234567890',
        'inquiryUrl' => 'https://merawatindonesia.com/api/v1.0/transfer-va/inquiry',
        'paymentUrl' => 'https://merawatindonesia.com/api/v1.0/transfer-va/payment',
        'callbackUrl'=> 'https://merawatindonesia.com/api/espay/callback',
        'productCode'=> 'SALDOMUQR',
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

$hashedBody = strtolower(hash('sha256', $body));
$stringToSign = $method . ':' . $endpoint . ':' . $hashedBody . ':' . $timestamp;

$privateKey = file_get_contents(storage_path('app/keys/espay/private_key.pem'));
$pkeyId = openssl_pkey_get_private($privateKey);
$signature = '';
openssl_sign($stringToSign, $signature, $pkeyId, OPENSSL_ALGO_SHA256);

echo "Signature: " . base64_encode($signature) . "\n";
echo "Body: " . $body . "\n";