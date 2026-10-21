<!DOCTYPE html>
<html>
<head>
    <title>Pendaftaran Berhasil</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <h2>Selamat Datang, {{ $user->name }}!</h2>
    
    <p>Terima kasih telah mendaftar. Berikut adalah informasi akun Anda untuk login ke sistem:</p>
    
    <div style="background: #f4f4f4; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <p><strong>URL Login:</strong> <a href="{{ route('login') }}">{{ route('login') }}</a></p>
        <p><strong>Username / ID Bayar:</strong> {{ $masterUser->trx_id }}</p>
        <p><strong>Password:</strong> {{ $plainPassword }}</p>
        <p><strong>Status Pembayaran:</strong> <span style="color: green; font-weight: bold;">LUNAS (PAID)</span></p>
    </div>

    <p>Silakan simpan informasi ini dengan baik. Jika Anda mengalami kendala, silakan hubungi admin.</p>
    
    <p>Salam hangat,<br>Panitia PMB</p>
</body>
</html>