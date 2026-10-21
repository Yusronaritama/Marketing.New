<!DOCTYPE html>
<html>
<head>
    <title>Password Berhasil Diubah</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .header { background-color: #f8f9fa; padding: 10px; text-align: center; border-bottom: 1px solid #ddd; }
        .content { padding: 20px 0; }
        .footer { font-size: 12px; color: #777; text-align: center; margin-top: 20px; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>{{ $appName }}</h2>
        </div>
        <div class="content">
            <p>Halo <strong>{{ $name }}</strong>,</p>
            
            <p>Password untuk akun Anda baru saja berhasil diubah.</p>
            
            <p>Sekarang Anda dapat login menggunakan password baru Anda.</p>
            
            <div style="background-color: #fff3cd; color: #856404; padding: 10px; border-radius: 4px; margin-top: 15px;">
                <strong>Penting:</strong> Jika Anda merasa tidak melakukan perubahan ini, harap segera hubungi administrator atau layanan pelanggan kami untuk mengamankan akun Anda.
            </div>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ $appName }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>