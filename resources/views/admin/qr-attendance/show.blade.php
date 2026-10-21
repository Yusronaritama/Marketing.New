<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Daftar Hadir</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            background: #fff;
            border-radius: 24px;
            padding: 36px 32px;
            max-width: 420px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        }
        .badge {
            display: inline-block;
            background: #ede9fe;
            color: #6d28d9;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 4px 14px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 16px;
        }
        .nama {
            font-size: 1.4rem;
            font-weight: 800;
            color: #1f2937;
            margin-bottom: 4px;
        }
        .sesi {
            font-size: 0.9rem;
            color: #6b7280;
            margin-bottom: 24px;
        }
        .qr-wrapper {
            background: #f8fafc;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 24px;
            display: inline-block;
            border: 2px dashed #e2e8f0;
        }
        .qr-wrapper svg { display: block; }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #d1fae5;
            color: #065f46;
            font-weight: 700;
            font-size: 0.85rem;
            padding: 8px 20px;
            border-radius: 20px;
            margin-bottom: 16px;
        }
        .status-dot {
            width: 8px; height: 8px;
            background: #10b981;
            border-radius: 50%;
            animation: pulse 1.2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.4); }
        }
        .expired-badge {
            background: #fee2e2;
            color: #991b1b;
        }
        .expired-dot { background: #ef4444; animation: none; }
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
            font-size: 0.85rem;
        }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: #9ca3af; }
        .info-value { color: #374151; font-weight: 600; }
        .expired-overlay {
            position: relative;
        }
        .expired-overlay::after {
            content: 'SUDAH DIGUNAKAN';
            position: absolute;
            inset: 0;
            background: rgba(239,68,68,0.85);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 800;
            font-size: 1.1rem;
            letter-spacing: 0.05em;
        }
        .footer-note {
            font-size: 0.75rem;
            color: #d1d5db;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="card">
    <div class="badge">{{ $qr->sesi }}</div>

    <div class="nama">{{ $mahasiswa?->nama_mahasiswa ?? 'Mahasiswa' }}</div>
    <div class="sesi">Daftar Hadir — Tunjukkan QR ini ke petugas</div>

    @if($qr->isActive())
        <div class="status-badge">
            <span class="status-dot"></span> QR Aktif & Siap Scan
        </div>
    @else
        <div class="status-badge expired-badge">
            <span class="status-dot expired-dot"></span> QR Tidak Aktif
        </div>
    @endif

    {{-- QR Image --}}
    <div class="qr-wrapper {{ !$qr->isActive() ? 'expired-overlay' : '' }}">
        {!! $qrImage !!}
    </div>

    {{-- Info singkat --}}
    <div style="text-align:left; margin-top:16px;">
        <div class="info-row">
            <span class="info-label">Nama</span>
            <span class="info-value">{{ $mahasiswa?->nama_mahasiswa ?? '-' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Email</span>
            <span class="info-value">{{ $mahasiswa?->email ?? '-' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Sesi</span>
            <span class="info-value">{{ $qr->sesi }}</span>
        </div>
        @if($qr->expired_at)
        <div class="info-row">
            <span class="info-label">Berlaku hingga</span>
            <span class="info-value">{{ $qr->expired_at->format('d/m/Y H:i') }}</span>
        </div>
        @endif
    </div>

    <p class="footer-note">QR ini hanya dapat digunakan <strong>sekali</strong>. Setelah di-scan, QR akan otomatis kadaluarsa.</p>
</div>

{{-- Auto-refresh setiap 30 detik jika masih aktif --}}
@if($qr->isActive())
<script>
    setTimeout(() => window.location.reload(), 30000);
</script>
@endif
</body>
</html>