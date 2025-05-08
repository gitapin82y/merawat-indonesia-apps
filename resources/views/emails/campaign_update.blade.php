<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kabar Terbaru Kampanye</title>
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
        .update-title {
            font-size: 22px;
            font-weight: bold;
            color: #dc3545;
            margin-bottom: 15px;
        }
        .update-description {
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
            background-color: #dc3545;
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
            <h1>Kabar Terbaru Kampanye</h1>
        </div>
        <div class="content">
            <p>Halo {{ $donor['name'] ?? 'Donatur' }},</p>
            
            <p>Terima kasih atas dukungan Anda untuk kampanye "<strong>{{ $campaign->title }}</strong>".</p>
            
            <p>Kami memiliki kabar terbaru untuk kampanye ini:</p>
            
            <div class="update-title">{{ $kabarTerbaru->title }}</div>
            
            <div class="update-description">
                {!! $kabarTerbaru->description !!}
            </div>
            
            <p>Donasi Anda membuat perbedaan nyata. Terima kasih telah menjadi bagian dari perjalanan ini.</p>
            
            <div style="text-align: center;">
                <a href="{{ url('/kampanye/' . $campaign->slug) }}" class="button">Lihat Kampanye</a>
            </div>
        </div>
        <div class="footer">
            <p>Email ini dikirim secara otomatis. Mohon tidak membalas email ini.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>