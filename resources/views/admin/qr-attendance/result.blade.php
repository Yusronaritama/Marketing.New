<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Scan QR</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f1f5f9;
            min-height: 100vh;
            padding: 24px 16px;
        }

        /* ── Status Banner ── */
        .banner {
            border-radius: 16px;
            padding: 24px 28px;
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
            max-width: 680px;
            margin-left: auto;
            margin-right: auto;
        }
        .banner-success  { background: #d1fae5; border: 2px solid #6ee7b7; }
        .banner-warning  { background: #fef3c7; border: 2px solid #fcd34d; }
        .banner-error    { background: #fee2e2; border: 2px solid #fca5a5; }
        .banner-icon { font-size: 2.5rem; }
        .banner-title {
            font-size: 1.3rem;
            font-weight: 800;
        }
        .banner-success .banner-title  { color: #065f46; }
        .banner-warning .banner-title  { color: #92400e; }
        .banner-error   .banner-title  { color: #991b1b; }
        .banner-msg { font-size: 0.9rem; margin-top: 4px; color: #6b7280; }

        /* ── Card Biodata ── */
        .biodata-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.07);
            max-width: 680px;
            margin: 0 auto 24px;
            overflow: hidden;
        }
        .biodata-header {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: #fff;
            padding: 24px 28px;
        }
        .biodata-header h2 { font-size: 1.25rem; font-weight: 800; }
        .biodata-header p  { font-size: 0.85rem; opacity: 0.8; margin-top: 4px; }

        .biodata-body { padding: 24px 28px; }

        .section-title {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #9ca3af;
            margin: 20px 0 10px;
        }
        .section-title:first-child { margin-top: 0; }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            border: 1px solid #f3f4f6;
            border-radius: 10px;
            overflow: hidden;
        }
        .field {
            padding: 10px 14px;
            border-bottom: 1px solid #f3f4f6;
            border-right: 1px solid #f3f4f6;
        }
        .field:nth-child(even) { border-right: none; }
        .field:nth-last-child(-n+2) { border-bottom: none; }
        .field-label { font-size: 0.75rem; color: #9ca3af; margin-bottom: 2px; }
        .field-value { font-size: 0.88rem; font-weight: 600; color: #1f2937; }

        .grid-1 {
            border: 1px solid #f3f4f6;
            border-radius: 10px;
            overflow: hidden;
        }
        .grid-1 .field {
            border-right: none;
        }
        .grid-1 .field:last-child { border-bottom: none; }

        /* Kelulusan badge */
        .lulus-badge {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 700;
        }
        .lulus-badge.lulus   { background:#d1fae5; color:#065f46; }
        .lulus-badge.belum   { background:#fee2e2; color:#991b1b; }

        /* Scan info */
        .scan-info {
            background: #f8fafc;
            border-radius: 10px;
            padding: 14px 18px;
            font-size: 0.82rem;
            color: #6b7280;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
            max-width: 680px;
            margin: 0 auto;
        }

        @media (max-width: 520px) {
            .grid-2 { grid-template-columns: 1fr; }
            .field { border-right: none; }
            .field:nth-last-child(-n+2) { border-bottom: 1px solid #f3f4f6; }
            .field:last-child { border-bottom: none; }
        }
    </style>
</head>
<body>

{{-- ── Status Banner ── --}}
@php
    $bannerClass = match($status) {
        'success'        => 'banner-success',
        'already_scanned'=> 'banner-warning',
        default          => 'banner-error',
    };
    $bannerIcon = match($status) {
        'success'        => '✅',
        'already_scanned'=> '⚠️',
        'expired'        => '⏰',
        default          => '❌',
    };
    $bannerTitle = match($status) {
        'success'        => 'Kehadiran Tercatat!',
        'already_scanned'=> 'QR Sudah Digunakan',
        'expired'        => 'QR Kadaluarsa',
        default          => 'QR Tidak Valid',
    };
@endphp

<div class="banner {{ $bannerClass }}">
    <div class="banner-icon">{{ $bannerIcon }}</div>
    <div>
        <div class="banner-title">{{ $bannerTitle }}</div>
        <div class="banner-msg">{{ $message }}</div>
    </div>
</div>

{{-- ── Biodata Lengkap ── --}}
@if($data)
<div class="biodata-card">
    <div class="biodata-header">
        <h2>{{ $data->nama_mahasiswa ?? $data->user_name ?? 'Data Mahasiswa' }}</h2>
        <p>
            {{ $data->nama_prodi_1 ?? '-' }}
            @if($data->jenjang_prodi_1) ({{ $data->jenjang_prodi_1 }}) @endif
            &nbsp;·&nbsp; Gelombang {{ $data->gelombang ?? '-' }}
        </p>
    </div>

    <div class="biodata-body">

        {{-- Identitas Pribadi --}}
        <div class="section-title">Identitas Pribadi</div>
        <div class="grid-2">
            <div class="field"><div class="field-label">Nama Lengkap</div><div class="field-value">{{ $data->nama_mahasiswa ?? '-' }}</div></div>
            <div class="field"><div class="field-label">Jenis Kelamin</div><div class="field-value">{{ $data->jenis_kelamin ?? '-' }}</div></div>
            <div class="field"><div class="field-label">Tempat Lahir</div><div class="field-value">{{ $data->tempat_lahir ?? '-' }}</div></div>
            <div class="field"><div class="field-label">Tanggal Lahir</div><div class="field-value">{{ $data->tanggal_lahir ? \Carbon\Carbon::parse($data->tanggal_lahir)->format('d/m/Y') : '-' }}</div></div>
            <div class="field"><div class="field-label">NIK</div><div class="field-value">{{ $data->nik ?? '-' }}</div></div>
            <div class="field"><div class="field-label">NISN</div><div class="field-value">{{ $data->nisn ?? '-' }}</div></div>
            <div class="field"><div class="field-label">Agama</div><div class="field-value">{{ $data->id_agama ?? '-' }}</div></div>
            <div class="field"><div class="field-label">Kewarganegaraan</div><div class="field-value">{{ $data->kewarganegaraan ?? '-' }}</div></div>
        </div>

        {{-- Kontak & Alamat --}}
        <div class="section-title">Kontak & Alamat</div>
        <div class="grid-2">
            <div class="field"><div class="field-label">Email</div><div class="field-value">{{ $data->email ?? '-' }}</div></div>
            <div class="field"><div class="field-label">Handphone</div><div class="field-value">{{ $data->handphone ?? $data->phone ?? '-' }}</div></div>
            <div class="field"><div class="field-label">Telepon</div><div class="field-value">{{ $data->telepon ?? '-' }}</div></div>
            <div class="field"><div class="field-label">Jenis Tinggal</div><div class="field-value">{{ $data->id_jenis_tinggal ?? '-' }}</div></div>
            <div class="field"><div class="field-label">Jalan / RT RW</div><div class="field-value">{{ $data->jalanrtrw ?? '-' }}</div></div>
            <div class="field"><div class="field-label">Dusun</div><div class="field-value">{{ $data->dusun ?? '-' }}</div></div>
            <div class="field"><div class="field-label">Kelurahan</div><div class="field-value">{{ $data->kelurahan ?? '-' }}</div></div>
            <div class="field"><div class="field-label">Kode Pos</div><div class="field-value">{{ $data->kode_pos ?? '-' }}</div></div>
            <div class="field"><div class="field-label">Wilayah</div><div class="field-value">{{ $data->id_wilayah ?? '-' }}</div></div>
            <div class="field"><div class="field-label">Alat Transportasi</div><div class="field-value">{{ $data->id_alat_transportasi ?? '-' }}</div></div>
        </div>

        {{-- Data Orang Tua - Ayah --}}
        <div class="section-title">Data Ayah</div>
        <div class="grid-2">
            <div class="field"><div class="field-label">Nama Ayah</div><div class="field-value">{{ $data->nama_ayah ?? '-' }}</div></div>
            <div class="field"><div class="field-label">NIK Ayah</div><div class="field-value">{{ $data->nik_ayah ?? '-' }}</div></div>
            <div class="field"><div class="field-label">Tanggal Lahir</div><div class="field-value">{{ $data->tanggal_lahir_ayah ? \Carbon\Carbon::parse($data->tanggal_lahir_ayah)->format('d/m/Y') : '-' }}</div></div>
            <div class="field"><div class="field-label">Pendidikan</div><div class="field-value">{{ $data->id_jenjang_pendidikan_ayah ?? '-' }}</div></div>
            <div class="field"><div class="field-label">Pekerjaan</div><div class="field-value">{{ $data->id_pekerjaan_ayah ?? '-' }}</div></div>
            <div class="field"><div class="field-label">Penghasilan</div><div class="field-value">{{ $data->id_penghasilan_ayah ?? '-' }}</div></div>
        </div>

        {{-- Data Orang Tua - Ibu --}}
        <div class="section-title">Data Ibu</div>
        <div class="grid-2">
            <div class="field"><div class="field-label">Nama Ibu</div><div class="field-value">{{ $data->nama_ibu_kandung ?? '-' }}</div></div>
            <div class="field"><div class="field-label">NIK Ibu</div><div class="field-value">{{ $data->nik_ibu ?? '-' }}</div></div>
            <div class="field"><div class="field-label">Tanggal Lahir</div><div class="field-value">{{ $data->tanggal_lahir_ibu ? \Carbon\Carbon::parse($data->tanggal_lahir_ibu)->format('d/m/Y') : '-' }}</div></div>
            <div class="field"><div class="field-label">Pendidikan</div><div class="field-value">{{ $data->id_jenjang_pendidikan_ibu ?? '-' }}</div></div>
            <div class="field"><div class="field-label">Pekerjaan</div><div class="field-value">{{ $data->id_pekerjaan_ibu ?? '-' }}</div></div>
            <div class="field"><div class="field-label">Penghasilan</div><div class="field-value">{{ $data->id_penghasilan_ibu ?? '-' }}</div></div>
        </div>

        {{-- Data Wali --}}
        <div class="section-title">Data Wali</div>
        <div class="grid-2">
            <div class="field"><div class="field-label">Nama Wali</div><div class="field-value">{{ $data->nama_wali ?? '-' }}</div></div>
            <div class="field"><div class="field-label">Tanggal Lahir</div><div class="field-value">{{ $data->tanggal_lahir_wali ? \Carbon\Carbon::parse($data->tanggal_lahir_wali)->format('d/m/Y') : '-' }}</div></div>
            <div class="field"><div class="field-label">Pendidikan</div><div class="field-value">{{ $data->id_jenjang_pendidikan_wali ?? '-' }}</div></div>
            <div class="field"><div class="field-label">Pekerjaan</div><div class="field-value">{{ $data->id_pekerjaan_wali ?? '-' }}</div></div>
        </div>

        {{-- Data Pendaftaran & Kelulusan --}}
        <div class="section-title">Pendaftaran & Kelulusan</div>
        <div class="grid-2">
            <div class="field"><div class="field-label">Jalur Pendaftaran</div><div class="field-value">{{ $data->jalur_pendaftaran ?? '-' }}</div></div>
            <div class="field"><div class="field-label">Gelombang</div><div class="field-value">{{ $data->gelombang ?? '-' }}</div></div>
            <div class="field"><div class="field-label">Prodi Pilihan 1</div><div class="field-value">{{ $data->nama_prodi_1 ?? '-' }} {{ $data->jenjang_prodi_1 ? '('.$data->jenjang_prodi_1.')' : '' }}</div></div>
            <div class="field"><div class="field-label">Prodi Pilihan 2</div><div class="field-value">{{ $data->nama_prodi_2 ?? '-' }} {{ $data->jenjang_prodi_2 ? '('.$data->jenjang_prodi_2.')' : '' }}</div></div>
            <div class="field"><div class="field-label">Prodi Diterima</div><div class="field-value">{{ $data->nama_prodi_lulus ?? '-' }}</div></div>
            <div class="field"><div class="field-label">Nilai Quiz</div><div class="field-value">{{ $data->nilai_quiz ?? '-' }}</div></div>
            <div class="field">
                <div class="field-label">Status Kelulusan</div>
                <div class="field-value">
                    @if($data->status_kelulusan)
                        <span class="lulus-badge {{ strtolower($data->status_kelulusan) === 'lulus' ? 'lulus' : 'belum' }}">
                            {{ $data->status_kelulusan }}
                        </span>
                    @else
                        <span>-</span>
                    @endif
                </div>
            </div>
            <div class="field"><div class="field-label">Status Pendaftaran</div><div class="field-value">{{ $data->status_pendaftaran ?? '-' }}</div></div>
        </div>

    </div>
</div>

{{-- Info scan --}}
@if($qr && $status === 'success')
<div class="scan-info">
    <span>🕐 Scan pada: <strong>{{ $qr->scanned_at?->format('d/m/Y H:i:s') }}</strong></span>
    <span>📡 IP: <strong>{{ $qr->scanned_by ?? '-' }}</strong></span>
    <span>📋 Sesi: <strong>{{ $qr->sesi }}</strong></span>
</div>
@endif

@endif {{-- end if $data --}}

</body>
</html>