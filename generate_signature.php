<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$timestamp = '2026-04-12T15:17:00+07:00';
$method = 'POST';
$endpoint = '/apimerchant/v1.0/debit/payment-host-to-host';
$body = json_encode([
    'partnerReferenceNo' => 'DD-NEG-002-' . time(),
    'merchantId'         => 'SGWYAYASANBINAMULIA',
    'subMerchantId'      => '478e6640ee7aab15364bf42569559a35',
    'amount'             => ['value' => '25000.00', 'currency' => 'IDR'],
    'urlParam'           => [
        'url'        => 'https://merawatindonesia.com/donations/status',
        'type'       => 'PAY_RETURN',
        'isDeeplink' => 'N',
    ],
    'validUpTo'          => '2026-04-13T15:17:00+07:00',
    'pointOfInitiation'  => 'Website',
    'payOptionDetails'   => [
        'payMethod'   => '014',
        'payOption'   => 'BCAATM',
        'transAmount' => ['value' => '25000.00', 'currency' => 'IDR'],
        'feeAmount'   => ['value' => '0.00', 'currency' => 'IDR'],
    ],
    'additionalInfo'     => [
        'payType'       => 'REDIRECT',
        'userId'        => '425666',
        'userName'      => 'Test Donatur',
        'userEmail'     => 'test@merawatindonesia.com',
        'userPhone'     => '081234567890',
        'buyerId'       => '12345678',
        'productCode'   => 'BCAATM',
        'balanceType'   => 'CASH',
        'bankCardToken' => 'ESP230929094046rRD5mCT1IZkrBhJb5',
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
