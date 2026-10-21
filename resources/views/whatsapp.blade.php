<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div id="qr-box">
    <h3>Scan QR WhatsApp</h3>
    <img id="qr-img" src="" width="300">
</div>

<script>
    async function loadQR() {
        let res = await fetch("/api/whatsapp/get-status");
        let data = await res.json();

        if (data.status === "qr") {
            document.getElementById("qr-img").src = data.qr;
        }

        if (data.status === "connected") {
            document.getElementById("qr-box").innerHTML = "<h3>WhatsApp Connected!</h3>";
        }
    }

    setInterval(loadQR, 2000);
</script>

</body>
</html>