<!-- resources/views/emails/campaign-withdrawal-approved.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pencairan Dana Kampanye Disetujui</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #28a745;
            color: #ffffff;
            padding: 20px;
            border-radius: 5px 5px 0 0;
            text-align: center;
        }
        .content {
            padding: 20px;
        }
        .withdrawal-info {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
        .withdrawal-detail {
            margin-bottom: 10px;
            border-bottom: 1px solid #eeeeee;
            padding-bottom: 10px;
        }
        .withdrawal-detail:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .amount {
            font-size: 24px;
            font-weight: bold;
            color: #28a745;
        }
        .footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #888;
            font-size: 12px;
            text-align: center;
        }
        .button {
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .bukti-transfer {
            margin: 20px 0;
            text-align: center;
        }
        .bukti-transfer img {
            max-width: 100%;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Pencairan Dana Kampanye Disetujui</h1>
        </div>
        <div class="content">
            <p>Halo {{ $withdrawal->admin->name }},</p>
            
            <p>Permintaan pencairan dana kampanye Anda telah <strong>DISETUJUI</strong>.</p>
            
            <div class="withdrawal-info">
                <div class="withdrawal-detail">
                    <strong>Kampanye:</strong> {{ $withdrawal->campaign->title }}
                </div>
                <div class="withdrawal-detail">
                    <strong>Jumlah Dana:</strong> <span class="amount">Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}</span>
                </div>
                <div class="withdrawal-detail">
                    <strong>Metode Pembayaran:</strong> {{ strtoupper($withdrawal->payment_method) }}
                </div>
                <div class="withdrawal-detail">
                    <strong>Nama Rekening:</strong> {{ $withdrawal->account_name }}
                </div>
                <div class="withdrawal-detail">
                    <strong>Nomor Rekening:</strong> {{ $withdrawal->account_number }}
                </div>
                <div class="withdrawal-detail">
                    <strong>Tanggal Permintaan:</strong> {{ $withdrawal->created_at->format('d F Y H:i') }}
                </div>
                <div class="withdrawal-detail">
                    <strong>Status:</strong> <span style="color: #28a745; font-weight: bold;">DISETUJUI</span>
                </div>
            </div>
            
            @if(isset($bukti_pencairan_url))
            <div class="bukti-transfer">
                <h3>Bukti Transfer</h3>
                <img src="{{ $bukti_pencairan_url }}" alt="Bukti Transfer" style="max-width: 100%;">
            </div>
            @endif
            
            <p>Dana telah ditransfer ke rekening yang Anda berikan. Silakan periksa akun bank Anda.</p>
            <p>Terima kasih telah menggunakan platform kami untuk kegiatan sosial Anda.</p>
            
            <div style="text-align: center;">
                <a href="{{ route('kabar-pencairan', $withdrawal->campaign->title) }}" class="button">Lihat Kabar Pencairan</a>
            </div>
        </div>
        <div class="footer">
            <p>Email ini dikirim secara otomatis. Mohon tidak membalas email ini.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>