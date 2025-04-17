<!-- resources/views/emails/fundraising-withdrawal-rejected.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pencairan Dana Fundraising Ditolak</title>
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
            background-color: #dc3545;
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
            color: #dc3545;
        }
        .footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #888;
            font-size: 12px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Pencairan Dana Fundraising Ditolak</h1>
        </div>
        <div class="content">
            <p>Halo {{ $withdrawal->user->name }},</p>
            
            <p>Mohon maaf, permintaan pencairan dana fundraising Anda <strong>DITOLAK</strong>.</p>
            
            <div class="withdrawal-info">
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
                    <strong>Status:</strong> <span style="color: #dc3545; font-weight: bold;">DITOLAK</span>
                </div>
            </div>
            
            <p>Untuk informasi lebih lanjut mengenai alasan penolakan, silakan hubungi tim dukungan kami.</p>
            <p>Anda dapat mengajukan kembali permintaan pencairan dana setelah memastikan informasi yang diberikan sudah benar dan lengkap.</p>
            
        </div>
        <div class="footer">
            <p>Email ini dikirim secara otomatis. Mohon tidak membalas email ini.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>