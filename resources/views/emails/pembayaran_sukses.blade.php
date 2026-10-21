<!DOCTYPE html>
<html>
<head>
    <title>Pembayaran Berhasil</title>
</head>
<body>
    <h1>Pembayaran Diterima</h1>
<p>Halo {{ $nama }},</p>
<p>Pembayaran pendaftaran Anda telah kami terima dengan sukses. Terima kasih.</p>
    
    <p>Anda sekarang dapat melanjutkan ke tahap berikutnya di sistem pendaftaran kami.</p>
    
    <p>Silakan login kembali ke dashboard Anda: <br>
    <a href="{{ url('/login') }}">Login Dashboard PMB</a></p>

    <p>Salam hangat,<br>
    Tim PMB UBP</p>
</body>
</html>