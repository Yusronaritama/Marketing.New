<!DOCTYPE html>
<html>
<head>
    <title>Pembayaran Berhasil</title>
</head>
<body>
    <h2>Halo, {{ $nama }}!</h2>
    <p>Terima kasih, kami ingin menginformasikan bahwa pembayaran untuk Virtual Account <strong>{{ $va_number }}</strong> telah <strong>BERHASIL</strong> kami terima.</p>
    
    <p>Anda sekarang dapat melanjutkan ke tahap berikutnya di sistem pendaftaran kami.</p>
    
    <p>Silakan login kembali ke dashboard Anda: <br>
    <a href="{{ url('/login') }}">Login Dashboard PMB</a></p>

    <p>Salam hangat,<br>
    Tim PMB UBP</p>
</body>
</html>