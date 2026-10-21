<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Virtual Account</title>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const expiryTime = new Date("{{ $va->expires_at }}").getTime();
            const timerElement = document.getElementById('countdown');

            const interval = setInterval(function() {
                const now = new Date().getTime();
                const distance = expiryTime - now;

                if (distance < 0) {
                    clearInterval(interval);
                    timerElement.innerHTML = "Waktu habis. Virtual account akan direset...";
                    setTimeout(() => {
                        location.reload(); // reload agar VA baru muncul
                    }, 3000);
                    return;
                }

                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                timerElement.innerHTML = minutes + "m " + seconds + "d";
            }, 1000);
        });
    </script>
</head>
<body class="p-6 font-sans">
    <h1>Nomor Virtual Account Anda</h1>
    <h2 style="font-size: 24px; color: blue;">{{ $va->va_number }}</h2>
    <p>Kadaluarsa pada: <strong>{{ $va->expires_at->format('H:i:s') }}</strong></p>
    <p id="countdown" style="font-size: 20px; color: red;"></p>
</body>
</html>
