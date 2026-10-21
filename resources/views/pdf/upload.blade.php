<!DOCTYPE html>
<html>
<head>
    <title>Upload Kartu Keluarga</title>
    @if (app()->environment('local', 'development'))
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
</head>
<body class="p-10">
    <h1 class="text-xl font-bold mb-4">Upload PDF Kartu Keluarga</h1>
    <form action="{{ route('pdf.scan') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <input type="file" name="file" accept="application/pdf" class="border p-2">
        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded">Scan PDF</button>
    </form>
</body>
</html>
