<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Upload Dokumen Ganda OCR</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: sans-serif; max-width: 600px; margin: auto; padding: 20px; }
        .file-input-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="file"] { width: 100%; box-sizing: border-box; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        button { padding: 12px 20px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background-color: #0056b3; }
    </style>
</head>
<body>
    <h2>Unggah & Pindai KK dan Ijazah Sekaligus</h2>

    {{-- Atribut target="__blank" telah dihapus dari form ini --}}
    <form action="{{ route('ocr.scan') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="file-input-group">
            <label for="file_kk">1. Unggah Kartu Keluarga (KK):</label>
            <input type="file" id="file_kk" name="file_kk" accept="image/*,application/pdf" required>
        </div>
        
        <div class="file-input-group">
            <label for="file_ijazah">2. Unggah Ijazah:</label>
            <input type="file" id="file_ijazah" name="file_ijazah" accept="image/*,application/pdf" required>
        </div>
        
        <button type="submit">Pindai Kedua Dokumen</button>
    </form>

    {{-- Script SweetAlert (tidak ada perubahan) --}}
    @if(session('error'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    icon: "error",
                    title: "Gagal",
                    text: @json(session('error'))
                });
            });
        </script>
    @endif
</body>
</html>
