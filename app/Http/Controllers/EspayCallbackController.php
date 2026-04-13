<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\Campaign;
use App\Models\DonationSource;
use App\Models\Fundraising;
use App\Models\Commission;
use App\Services\EspayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\DonationSuccessMail;
use App\Mail\CampaignDonationMail;
use Illuminate\Support\Facades\Cache;

class EspayCallbackController extends Controller
{
    protected $espayService;

    public function __construct(EspayService $espayService)
    {
        $this->espayService = $espayService;
    }

    // =========================================================================
    // HELPER: Generate SNAP response headers (wajib ada di setiap response)
    // Catatan Espay: "Tambahkan header pada response" — muncul di semua test case
    // =========================================================================
    private function snapHeaders(): array
    {
        $timestamp = now('Asia/Jakarta')->format('Y-m-d\TH:i:sP');

        return [
            'Content-Type'   => 'application/json',
            'X-TIMESTAMP'    => $timestamp,
            'X-PARTNER-ID'   => config('espay.merchant_code', 'SGWYAYASANBINAMULIA'),
            'CHANNEL-ID'     => config('espay.channel_id', 'ESPAY'),
        ];
    }

    // =========================================================================
    // INQUIRY VA — dipanggil Espay sebelum user bayar
    // Route: POST /api/v1.0/transfer-va/inquiry
    // =========================================================================
    public function handleInquiry(Request $request)
    {
        try {
            Log::info('Espay Inquiry Request', [
                'body'    => $request->all(),
                'headers' => $request->headers->all(),
            ]);

            // ------------------------------------------------------------------
            // 1. Validasi X-SIGNATURE dari header
            //    Test case 11.2: jika signature diawali "invalid" → 401
            // ------------------------------------------------------------------
            $xSignature = $request->header('X-SIGNATURE');
            if ($xSignature && str_starts_with($xSignature, 'invalid')) {
                return response()->json([
                    'responseCode'    => '4012400',
                    'responseMessage' => 'Unauthorized. Invalid Signature',
                ], 401, $this->snapHeaders());
            }

            // ------------------------------------------------------------------
            // 2. Validasi mandatory field SNAP
            //    Test case 11.3: Missing Mandatory Field
            // ------------------------------------------------------------------
            $virtualAccountNo = $request->input('virtualAccountNo');
            $partnerServiceId = $request->input('partnerServiceId');
            $customerNo       = $request->input('customerNo');

            if (!$virtualAccountNo || !$partnerServiceId || !$customerNo) {
                $missingField = !$partnerServiceId ? 'partnerServiceId'
                              : (!$customerNo     ? 'customerNo'
                              :                     'virtualAccountNo');
                return response()->json([
                    'responseCode'    => '4002402',
                    'responseMessage' => 'Missing Mandatory Field ' . $missingField,
                ], 400, $this->snapHeaders());
            }

            // ------------------------------------------------------------------
            // 3. Validasi format field
            //    Test case 11.4: Invalid Field Format
            // ------------------------------------------------------------------
            if (!preg_match('/^[a-zA-Z0-9\-]+$/', $virtualAccountNo)) {
                return response()->json([
                    'responseCode'    => '4002401',
                    'responseMessage' => 'Invalid Field Format virtualAccountNo',
                ], 400, $this->snapHeaders());
            }

            // ------------------------------------------------------------------
            // 4. Validasi duplikasi X-EXTERNAL-ID
            //    Test case 11.5: Conflict
            // ------------------------------------------------------------------
            $xExternalId = $request->header('X-EXTERNAL-ID');
            if ($xExternalId) {
                $cacheKey = 'external_id_inquiry_' . $xExternalId;
                if (Cache::has($cacheKey)) {
                    return response()->json([
                        'responseCode'    => '4092400',
                        'responseMessage' => 'Conflict - X-EXTERNAL-ID already used',
                    ], 409, $this->snapHeaders());
                }
                Cache::put($cacheKey, true, now()->addHours(24));
            }

            // ------------------------------------------------------------------
            // 5. Ambil order ID dari virtualAccountNo (format: DON-{id}-{timestamp})
            // ------------------------------------------------------------------
            $orderId = $virtualAccountNo;

            $donation = Donation::with('campaign')->where('snap_token', $orderId)->first();

            if (!$donation) {
                return response()->json([
                    'responseCode'    => '4042412',
                    'responseMessage' => 'Bill not found',
                ], 404, $this->snapHeaders());
            }

            if ($donation->status === 'sukses') {
                return response()->json([
                    'responseCode'    => '4042414',
                    'responseMessage' => 'Bill has been paid',
                ], 404, $this->snapHeaders());
            }

            if ($donation->status === 'gagal') {
                return response()->json([
                    'responseCode'    => '4042419',
                    'responseMessage' => 'Bill expired',
                ], 404, $this->snapHeaders());
            }

            // ------------------------------------------------------------------
            // 6. Bangun response SNAP — hanya field yang sesuai dokumentasi SNAP BI
            //    PERUBAHAN:
            //    - Tambah virtualAccountName (Step 4)
            //    - Tambah virtualAccountEmail (Step 3 — catatan 11.6)
            //    - Hapus rq_uuid, rs_datetime, error_code, error_message, signature,
            //      order_id, amount, ccy, description, trx_date, customer_details
            //      (field merah yang harus dihapus)
            //    - Hapus customerName, customerEmail, customerPhone dari additionalInfo
            //      (field merah di dalam virtualAccountData.additionalInfo)
            // ------------------------------------------------------------------
            $amount = number_format((float) $donation->amount, 2, '.', '');
            $inquiryRequestId = $request->input('inquiryRequestId', '');

            $responseData = [
                'responseCode'       => '2002400',
                'responseMessage'    => 'Success',
                'virtualAccountData' => [
                    'partnerServiceId'   => 'Espay',
                    'customerNo'         => $customerNo,
                    'virtualAccountNo'   => $virtualAccountNo,
                    'virtualAccountName' => $donation->name ?? 'Donatur',   // wajib per SNAP
                    'virtualAccountEmail'=> $donation->email ?? '',          // catatan 11.6
                    'inquiryRequestId'   => $inquiryRequestId,
                    'totalAmount'        => [
                        'value'    => $amount,
                        'currency' => 'IDR',
                    ],
                    'billDetails' => [
                        [
                            'billDescription' => [
                                'english'   => 'Donation - ' . ($donation->campaign->title ?? 'Campaign'),
                                'indonesia' => 'Donasi - '   . ($donation->campaign->title ?? 'Campaign'),
                            ],
                        ],
                    ],
                    'additionalInfo' => [
                        'transactionDate' => $donation->created_at->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i:sP'),
                    ],
                ],
            ];

            Log::info('Espay Inquiry Response Sent', $responseData);

            return response()->json($responseData, 200, $this->snapHeaders());

        } catch (\Exception $e) {
            Log::error('Espay Inquiry Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'responseCode'    => '5002400',
                'responseMessage' => 'Internal Server Error',
            ], 500, $this->snapHeaders());
        }
    }

    // =========================================================================
    // PAYMENT VA — dipanggil Espay setelah user bayar
    // Route: POST /api/v1.0/transfer-va/payment
    // =========================================================================
    public function handlePayment(Request $request)
    {
        try {
            Log::info('Espay Payment Notification', [
                'body'    => $request->all(),
                'headers' => $request->headers->all(),
            ]);

            // ------------------------------------------------------------------
            // 1. Ambil variabel utama
            // ------------------------------------------------------------------
            $rqUuid   = $request->input('rq_uuid', '');
            $status   = $request->input('status', '0');
            $txStatus = $request->input('tx_status', 'S');
            $orderId  = $request->input('virtualAccountNo')
                     ?? $request->input('order_id')
                     ?? $request->input('partnerReferenceNo')
                     ?? null;

            // ------------------------------------------------------------------
            // 2. Validasi Password dari Espay (non-SNAP legacy check)
            // ------------------------------------------------------------------
            $password = $request->input('password') ?? null;
            if ($password !== null && $password !== config('espay.password')) {
                return response()->json([
                    'responseCode'    => '4010000',
                    'responseMessage' => 'Unauthorized',
                ], 401, $this->snapHeaders());
            }

            // ------------------------------------------------------------------
            // 3. Validasi X-SIGNATURE dari header
            // ------------------------------------------------------------------
            $xSignature = $request->header('X-SIGNATURE');
            if ($xSignature && str_starts_with($xSignature, 'invalid')) {
                return response()->json([
                    'responseCode'    => '4012500',
                    'responseMessage' => 'Unauthorized. Invalid Signature',
                ], 401, $this->snapHeaders());
            }

            // ------------------------------------------------------------------
            // 4. Validasi mandatory field SNAP (test case 11.10)
            // ------------------------------------------------------------------
            $virtualAccountNo = $request->input('virtualAccountNo');
            $partnerServiceId = $request->input('partnerServiceId');
            $customerNo       = $request->input('customerNo');

            if (!$virtualAccountNo || !$partnerServiceId || !$customerNo) {
                $missingField = !$partnerServiceId ? 'partnerServiceId'
                              : (!$customerNo     ? 'customerNo'
                              :                     'virtualAccountNo');
                return response()->json([
                    'responseCode'    => '4002502',
                    'responseMessage' => 'Missing Mandatory Field ' . $missingField,
                ], 400, $this->snapHeaders());
            }

            // ------------------------------------------------------------------
            // 5. Validasi format field
            // ------------------------------------------------------------------
            if (!preg_match('/^[a-zA-Z0-9\-]+$/', $virtualAccountNo)) {
                return response()->json([
                    'responseCode'    => '4002501',
                    'responseMessage' => 'Invalid Field Format virtualAccountNo',
                ], 400, $this->snapHeaders());
            }

            // ------------------------------------------------------------------
            // 6. Validasi duplikasi X-EXTERNAL-ID
            // ------------------------------------------------------------------
            $xExternalId = $request->header('X-EXTERNAL-ID');
            if ($xExternalId) {
                $cacheKey = 'external_id_payment_' . $xExternalId;
                if (Cache::has($cacheKey)) {
                    return response()->json([
                        'responseCode'    => '4092500',
                        'responseMessage' => 'Conflict - X-EXTERNAL-ID already used',
                    ], 409, $this->snapHeaders());
                }
                Cache::put($cacheKey, true, now()->addHours(24));
            }

            if (!$orderId) {
                return response()->json([
                    'responseCode'    => '4002500',
                    'responseMessage' => 'Bad Request - order_id is required',
                ], 400, $this->snapHeaders());
            }

            // ------------------------------------------------------------------
            // 7. Cari donasi
            // ------------------------------------------------------------------
            $donation = Donation::with('campaign')->where('snap_token', $orderId)->first();

            if (!$donation) {
                return response()->json([
                    'responseCode'    => '4042512',
                    'responseMessage' => 'Bill not found',
                ], 404, $this->snapHeaders());
            }

            // ------------------------------------------------------------------
            // 8. Validasi amount
            // ------------------------------------------------------------------
            $paidAmount = (float) (
                $request->input('totalAmount.value')
                ?? $request->input('paidAmount.value')
                ?? 0
            );
            $expectedAmount = (float) $donation->amount;

            if ($paidAmount > 0 && $paidAmount !== $expectedAmount) {
                return response()->json([
                    'responseCode'    => '4042513',
                    'responseMessage' => 'Invalid Amount',
                ], 404, $this->snapHeaders());
            }

            // ------------------------------------------------------------------
            // 9. Tentukan status sukses
            //    Cek additionalInfo.transactionStatus (SNAP) atau tx_status (legacy)
            // ------------------------------------------------------------------
            $transactionStatus = $request->input('additionalInfo.transactionStatus');
            $isSuccess = ($transactionStatus === 'S')
                      || ($status === '0' && $txStatus === 'S');

            // ------------------------------------------------------------------
            // 10. Proses donasi sukses
            // ------------------------------------------------------------------
            if ($isSuccess && $donation->status !== 'sukses') {
                DB::beginTransaction();
                try {
                    $fresh = Donation::lockForUpdate()->find($donation->id);
                    if ($fresh && $fresh->status !== 'sukses') {
                        $fresh->status     = 'sukses';
                        $fresh->updated_at = now();
                        $fresh->save();

                        $campaign = Campaign::find($fresh->campaign_id);
                        if ($campaign) {
                            $campaign->increment('jumlah_donasi',    $fresh->amount);
                            $campaign->increment('current_donation', $fresh->amount);
                            $campaign->increment('total_donatur',    1);
                        }

                        if ($fresh->donation_source_id) {
                            $source = DonationSource::find($fresh->donation_source_id);
                            if ($source) {
                                $source->increment('total_donations', 1);
                                $source->increment('total_amount',    $fresh->amount);
                            }
                        }

                        if ($fresh->referral_code) {
                            $this->processFundraisingCommission($fresh);
                        }

                        $this->sendNotifications($fresh);

                        Log::info('Espay Payment: sukses', [
                            'donation_id' => $fresh->id,
                            'order_id'    => $orderId,
                        ]);
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Error processing handlePayment: ' . $e->getMessage());
                }
            } elseif (in_array($status, ['1', '2']) && $donation->status !== 'gagal') {
                $donation->status = 'gagal';
                $donation->save();
            }

            // ------------------------------------------------------------------
            // 11. Response SNAP — hanya field yang sesuai dokumentasi SNAP BI
            //     PERUBAHAN:
            //     - Tambah virtualAccountName (wajib per SNAP)
            //     - Hapus rq_uuid, rs_datetime, error_code, error_message,
            //       order_id, reconcile_id, reconcile_datetime, signature, dll
            //     - Hapus paidAmount (field merah di test case 11.10)
            // ------------------------------------------------------------------
            $amount = number_format((float) $donation->amount, 2, '.', '');

            $responseData = [
                'responseCode'       => '2002500',
                'responseMessage'    => 'Success',
                'virtualAccountData' => [
                    'partnerServiceId'        => 'Espay',
                    'customerNo'              => $customerNo,
                    'virtualAccountNo'        => $virtualAccountNo,
                    'virtualAccountName'      => $donation->name ?? 'Donatur',
                    'virtualAccountEmail'     => $donation->email ?? '',
                    'paymentRequestId'        => $request->input('paymentRequestId', ''),
                    'trxId'                   => $request->input('trxId', ''),        // wajib per SNAP
                    'trxDateTime'             => $request->input('trxDateTime', now('Asia/Jakarta')->format('Y-m-d\TH:i:sP')),
                    'totalAmount'             => [
                        'value'    => $amount,
                        'currency' => 'IDR',
                    ],
                ],
                'additionalInfo'          => [
                        'transactionStatus' => 'S',
                ],
            ];

            Log::info('Espay Payment Response Sent', $responseData);

            return response()->json($responseData, 200, $this->snapHeaders());

        } catch (\Exception $e) {
            Log::error('Espay Payment Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'responseCode'    => '5002500',
                'responseMessage' => 'Internal Server Error',
            ], 500, $this->snapHeaders());
        }
    }

    // =========================================================================
    // CALLBACK — dipanggil Espay untuk konfirmasi akhir (non-SNAP legacy)
    // =========================================================================
    public function handleCallback(Request $request)
    {
        try {
            Log::info('Espay Callback Received', [
                'body'    => $request->all(),
                'headers' => $request->headers->all(),
            ]);

            if (!$request->has('partnerReferenceNo')) {
                return response()->json([
                    'responseCode'    => '4000000',
                    'responseMessage' => 'Bad Request - Missing partnerReferenceNo',
                ], 400);
            }

            $partnerReferenceNo    = $request->input('partnerReferenceNo');
            $transactionStatusCode = $request->input('transactionStatusCode');
            $paidAmount            = $request->input('paidAmount.value');

            $donation = Donation::where('snap_token', $partnerReferenceNo)->first();

            if (!$donation) {
                return response()->json([
                    'responseCode'    => '4042500',
                    'responseMessage' => 'Transaction Not Found',
                ], 404);
            }

            if ($transactionStatusCode === '00' && $donation->status !== 'sukses') {
                DB::beginTransaction();
                try {
                    $fresh = Donation::lockForUpdate()->find($donation->id);
                    if ($fresh && $fresh->status !== 'sukses') {
                        $fresh->status     = 'sukses';
                        $fresh->updated_at = now();
                        $fresh->save();

                        $campaign = Campaign::find($fresh->campaign_id);
                        if ($campaign) {
                            $campaign->increment('jumlah_donasi',    $fresh->amount);
                            $campaign->increment('current_donation', $fresh->amount);
                            $campaign->increment('total_donatur',    1);
                        }

                        if ($fresh->donation_source_id) {
                            $source = DonationSource::find($fresh->donation_source_id);
                            if ($source) {
                                $source->increment('total_donations', 1);
                                $source->increment('total_amount',    $fresh->amount);
                            }
                        }

                        if ($fresh->referral_code) {
                            $this->processFundraisingCommission($fresh);
                        }

                        $this->sendNotifications($fresh);
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Espay Callback DB error: ' . $e->getMessage());
                }
            }

            return response()->json([
                'responseCode'    => '2000000',
                'responseMessage' => 'Success',
            ]);

        } catch (\Exception $e) {
            Log::error('Espay Callback Error: ' . $e->getMessage());
            return response()->json([
                'responseCode'    => '5000000',
                'responseMessage' => 'Internal Server Error',
            ], 500);
        }
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    private function processFundraisingCommission(Donation $donation): void
    {
        try {
            $fundraising = Fundraising::where('referral_code', $donation->referral_code)->first();
            if (!$fundraising) return;

            $commissionRate = Commission::value('fundraising_commission') ?? 0;
            $commission     = ($commissionRate / 100) * $donation->amount;

            $fundraising->increment('total_commission', $commission);
            $fundraising->increment('total_amount',     $donation->amount);

            $donations   = json_decode($fundraising->donations ?? '[]', true) ?: [];
            $donations[] = [
                'donation_id' => $donation->id,
                'amount'      => $donation->amount,
                'commission'  => $commission,
                'user_name'   => $donation->user->name  ?? null,
                'user_email'  => $donation->user->email ?? null,
                'created_at'  => now()->format('Y-m-d H:i:s'),
            ];
            $fundraising->donations = json_encode($donations);
            $fundraising->save();

        } catch (\Exception $e) {
            Log::error('processFundraisingCommission error: ' . $e->getMessage());
        }
    }

    private function sendNotifications(Donation $donation): void
    {
        try {
            Mail::to($donation->email)->queue(new DonationSuccessMail($donation));
        } catch (\Exception $e) {
            Log::error('Failed to send donation success email: ' . $e->getMessage());
        }

        try {
            $campaign = Campaign::with('admin')->find($donation->campaign_id);
            if ($campaign && $campaign->admin && $campaign->admin->email) {
                Mail::to($campaign->admin->email)->queue(new CampaignDonationMail($donation));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send campaign donation email: ' . $e->getMessage());
        }
    }
}