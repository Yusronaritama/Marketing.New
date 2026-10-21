<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Hasil Pindai & Koreksi OCR</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; max-width: 900px; margin: auto; padding: 20px; background-color: #f4f7f6; }
        .form-container { background-color: #fff; border: 1px solid #ddd; padding: 25px; border-radius: 8px; margin-bottom: 25px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h2, h3, h4 { color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input[type="text"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 1rem; }
        pre { background-color: #2d2d2d; color: #f1f1f1; padding: 15px; border-radius: 5px; white-space: pre-wrap; word-wrap: break-word; font-size: 0.9rem; }
        .button-group { text-align: right; margin-top: 20px; }
        .btn { display: inline-block; text-align:center; text-decoration: none; color: white; padding: 12px 20px; border-radius: 4px; font-size: 16px; border: none; cursor: pointer; }
        .btn-save { background-color: #28a745; }
        .btn-save:hover { background-color: #218838; }
        .btn-rescan { background-color: #007bff; margin-left: 10px; }
        .btn-rescan:hover { background-color: #0056b3; }
    </style>
</head>
<body>
    <h2>Hasil Pindai Dokumen & Formulir Koreksi</h2>

    {{-- Aksi form diubah ke route 'ocr.save' untuk menangani penyimpanan data --}}
    <form action="{{ route('ocr.save') }}" method="POST">
        @csrf

        {{-- HASIL KARTU KELUARGA --}}
        @if(isset($results['kk']))
            <div class="form-container">
                <h3>Hasil Pindai: {{ $results['kk']['type'] }}</h3>
                <h4>Data yang Diekstrak (Dapat Dikoreksi):</h4>
                @forelse($results['kk']['data'] as $key => $value)
                    <div class="form-group">
                        <label for="kk_{{ str_replace(' ', '_', $key) }}">{{ $key }}:</label>
                        <input type="text" id="kk_{{ str_replace(' ', '_', $key) }}" name="kk[{{ str_replace(' ', '_', $key) }}]" value="{{ $value ?: '' }}">
                    </div>
                @empty
                    <p>Tidak ada data spesifik yang dapat diekstrak.</p>
                @endforelse
                
                <h4>Teks Lengkap (Dirapikan AI):</h4>
                <pre>{{ $results['kk']['raw_text'] }}</pre>
            </div>
        @endif

        {{-- HASIL IJAZAH --}}
        @if(isset($results['ijazah']))
            <div class="form-container">
                <h3>Hasil Pindai: {{ $results['ijazah']['type'] }}</h3>
                <h4>Data yang Diekstrak (Dapat Dikoreksi):</h4>
                @forelse($results['ijazah']['data'] as $key => $value)
                     <div class="form-group">
                        <label for="ijazah_{{ str_replace(' ', '_', $key) }}">{{ $key }}:</label>
                        <input type="text" id="ijazah_{{ str_replace(' ', '_', $key) }}" name="ijazah[{{ str_replace(' ', '_', $key) }}]" value="{{ $value ?: '' }}">
                    </div>
                @empty
                    <p>Tidak ada data spesifik yang dapat diekstrak.</p>
                @endforelse

                <h4>Teks Lengkap (Dirapikan AI):</h4>
                <pre>{{ $results['ijazah']['raw_text'] }}</pre>
            </div>
        @endif

        <div class="button-group">
            <button type="submit" class="btn btn-save">Simpan Koreksi</button>
            <a href="{{ url('/') }}" class="btn btn-rescan">Pindai Ulang</a>
        </div>
    </form>
</body>
</html>

