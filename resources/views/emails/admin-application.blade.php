<!-- resources/views/emails/admin-application.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Admin Baru</title>
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
            background-color: #17a2b8;
            color: #ffffff;
            padding: 20px;
            border-radius: 5px 5px 0 0;
            text-align: center;
        }
        .content {
            padding: 20px;
        }
        .admin-info {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
        .admin-detail {
            margin-bottom: 10px;
            border-bottom: 1px solid #eeeeee;
            padding-bottom: 10px;
        }
        .admin-detail:last-child {
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
            background-color: #dc3545;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            display: block;
            margin: 0 auto 15px;
            border: 3px solid #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Pendaftaran Admin Baru</h1>
        </div>
        <div class="content">
            <p>Halo Super Admin,</p>
            
            <p>Ada pendaftaran admin baru yang memerlukan persetujuan Anda.</p>
            
            <div class="admin-info">        
                <div class="admin-detail">
                    <strong>Nama Admin:</strong> {{ $admin->name }}
                </div>
                <div class="admin-detail">
                    <strong>Email:</strong> {{ $admin->email }}
                </div>
                <div class="admin-detail">
                    <strong>Telepon:</strong> {{ $admin->phone }}
                </div>
                <div class="admin-detail">
                    <strong>Nama Pimpinan:</strong> {{ $admin->leader_name }}
                </div>
                <div class="admin-detail">
                    <strong>Alamat:</strong> {{ $admin->address }}
                </div>
                <div class="admin-detail">
                    <strong>Tanggal Pendaftaran:</strong> {{ $admin->created_at->format('d F Y H:i') }}
                </div>
                <div class="admin-detail">
                    <strong>Status:</strong> <span style="color: #17a2b8; font-weight: bold;">Menunggu</span>
                </div>
            </div>
            
            <p>Silakan tinjau aplikasi ini dan berikan persetujuan atau penolakan melalui panel admin.</p>
            
            <div style="text-align: center;">
                <a href="{{url('/super-admin')}}" class="button">Kelola Admin</a>
            </div>
        </div>
        <div class="footer">
            <p>Email ini dikirim secara otomatis. Mohon tidak membalas email ini.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>