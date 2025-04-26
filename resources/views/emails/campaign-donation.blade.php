<!-- resources/views/emails/campaign-donation.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donasi Baru untuk Kampanye</title>
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
        .details {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
        .details-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            border-bottom: 1px solid #eeeeee;
            padding-bottom: 10px;
        }
        .details-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
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
        .highlight {
            font-size: 24px;
            font-weight: bold;
            color: #28a745;
            text-align: center;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Donasi Baru Diterima!</h1>
        </div>
        <div class="content">
            <p>Halo {{ $donation->campaign->admin->name }},</p>
            
            <p>Kabar baik! Kampanye Anda "{{ $donation->campaign->title }}" telah menerima donasi baru.</p>
            
            <div class="highlight">
                Rp {{ number_format($donation->amount) }}
            </div>
            
            <div class="details">
                <div class="details-row">
                    <strong>Nama Donatur:</strong>
                    <span>{{ $donation->is_anonymous ? 'Sahabat Baik' : $donation->name }}</span>
                </div>
                <div class="details-row">
                    <strong>Jumlah Donasi:</strong>
                    <span>Rp {{ number_format($donation->amount) }}</span>
                </div>
                <div class="details-row">
                    <strong>Metode Pembayaran:</strong>
                    <span>
                        @if($donation->payment_type == 'payment_gateway')
                            {{ $donation->payment_method }}
                        @else
                            Manual Transfer
                        @endif
                    </span>
                </div>
                <div class="details-row">
                    <strong>Tanggal Donasi:</strong>
                    <span>{{ $donation->updated_at->format('d F Y H:i') }}</span>
                </div>
                @if($donation->doa)
                <div class="details-row">
                    <strong>Doa/Pesan:</strong>
                    <span>{{ $donation->doa }}</span>
                </div>
                @endif
            </div>
            
            <p>Saat ini, total donasi untuk kampanye ini adalah <strong>Rp {{ number_format($donation->campaign->jumlah_donasi) }}</strong> dari target <strong>Rp {{ number_format($donation->campaign->jumlah_target_donasi) }}</strong>.</p>
            
            <div style="text-align: center;">
                <a href="{{ route('admin.campaign.detail', $donation->campaign->title) }}" class="button">Lihat Detail Kampanye</a>
            </div>
        </div>
        <div class="footer">
            <p>Email ini dikirim secara otomatis. Mohon tidak membalas email ini.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>