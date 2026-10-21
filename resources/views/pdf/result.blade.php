<!DOCTYPE html>
<html>
<head>
    <title>Hasil Scan</title>
    @if (app()->environment('local', 'development'))
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
</head>
<body class="p-10">
    <h1 class="text-xl font-bold mb-4">Hasil Scan Kartu Keluarga</h1>
    
    <div class="space-y-2">
        <p><strong>Nama Kepala Keluarga:</strong> {{ $nama_kk }}</p>
        <p><strong>NIK:</strong> {{ $nik }}</p>
        <p><strong>Alamat:</strong> {{ $alamat }}</p>
    </div>

    <h2 class="mt-6 font-semibold">Isi Lengkap PDF:</h2>
    <pre class="bg-gray-100 p-4 rounded">{{ $raw_text }}</pre>
</body>
</html>
