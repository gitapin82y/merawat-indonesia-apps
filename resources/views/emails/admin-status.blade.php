<!-- resources/views/emails/admin-status.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pendaftaran Admin</title>
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
            background-color: {{ $isApproved ? '#28a745' : '#dc3545' }};
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
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Status Pendaftaran Admin</h1>
        </div>
        <div class="content">
            <p>Halo {{ $admin->name }},</p>
            
            @if ($isApproved)
                <p>Selamat! Pendaftaran Anda sebagai admin pada platform kami telah <strong>disetujui</strong>.</p>
                
                <p>Anda sekarang dapat masuk ke panel admin dan mulai membuat kampanye penggalangan dana.</p>
                
                <div class="details">
                    <p><strong>Email:</strong> {{ $admin->email }}</p>
                    <p><strong>Status:</strong> Disetujui</p>
                    <p><strong>Tanggal Persetujuan:</strong> {{ now()->format('d F Y H:i') }}</p>
                </div>
                
                <p>Silakan login menggunakan email dan password yang telah Anda daftarkan sebelumnya.</p>
                
                <div style="text-align: center;">
                    <a href="{{ route('login') }}" class="button" style="background-color: {{ $isApproved ? '#28a745' : '#6c757d' }};">Login Sekarang</a>
                </div>
            @else
                <p>Mohon maaf, pendaftaran Anda sebagai admin pada platform kami telah <strong>ditolak</strong>.</p>
                
                <div class="details">
                    <p><strong>Email:</strong> {{ $admin->email }}</p>
                    <p><strong>Status:</strong> Ditolak</p>
                    <p><strong>Tanggal Keputusan:</strong> {{ now()->format('d F Y H:i') }}</p>
                </div>
                
                <p>Jika Anda memiliki pertanyaan lebih lanjut, silakan hubungi tim dukungan kami.</p>
            @endif
            
        </div>
        <div class="footer">
            <p>Email ini dikirim secara otomatis. Mohon tidak membalas email ini.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>