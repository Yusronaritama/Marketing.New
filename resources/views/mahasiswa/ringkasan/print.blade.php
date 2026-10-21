@php
    // --- 1. SETUP LOGIKA DATA ---
    $statusText  = $statusUjian ?? 'Belum Ujian';
    $statusUpper = strtoupper($statusText);
    $isLulus     = ($statusUpper === 'LULUS');
    $isGagal     = ($statusUpper === 'TIDAK LULUS' || $statusUpper === 'GAGAL' || $statusUpper === 'REJECTED');
    $lunas       = (isset($slikBillingStatus) && $slikBillingStatus == 'paid');

    // --- 2. FORMAT BULAN INDONESIA ---
    $bulanIndo = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    $todayFormatted = date('d') . ' ' . $bulanIndo[(int)date('m')] . ' ' . date('Y');

    // Format Tanggal Lahir
    $tglLahirFormatted = '-';
    $rawTgl = $dataFinal->tanggal_lahir ?? ($biodata->tanggal_lahir ?? null);
    if ($rawTgl) {
        try {
            $cDate = $rawTgl instanceof \Carbon\Carbon ? $rawTgl : \Carbon\Carbon::parse($rawTgl);
            $tglLahirFormatted = $cDate->format('d') . ' ' . $bulanIndo[(int)$cDate->format('m')] . ' ' . $cDate->format('Y');
        } catch (\Exception $e) {
            $tglLahirFormatted = (string)$rawTgl;
        }
    }

    // Format Foto Profil
    $fotoProfil = null;
    if ($user && $user->profile_photo_path) {
        $fotoProfil = Str::startsWith($user->profile_photo_path, 'http') 
            ? $user->profile_photo_path 
            : asset('storage/' . $user->profile_photo_path);
    }

    // Format Alamat
    $alamatLengkap = '-';
    if ($dataFinal && $dataFinal->jalan) {
        $parts = [
            $dataFinal->jalan,
            $dataFinal->rt ? 'RT ' . $dataFinal->rt : '',
            $dataFinal->rw ? 'RW ' . $dataFinal->rw : '',
            $dataFinal->kelurahan ? 'Kel. ' . $dataFinal->kelurahan : '',
            $dataFinal->id_wilayah ? $dataFinal->id_wilayah : ''
        ];
        $alamatLengkap = implode(', ', array_filter($parts));
    } elseif ($biodata && $biodata->alamat) {
        $alamatLengkap = $biodata->alamat;
    }
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Registrasi PMB - {{ optional($dataFinal)->nama_mahasiswa ?? $biodata->nama_lengkap ?? $user->name }}</title>
    
    {{-- Google Fonts: Inter & Outfit --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* ========================================================= */
        /* RESET & SETUP CETAK A4                                    */
        /* ========================================================= */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        @page {
            size: A4 portrait;
            margin: 10mm 12mm;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.45;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .sheet {
            background: #ffffff;
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            padding: 12mm 15mm;
            position: relative;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        @media print {
            body {
                background: none !important;
                padding: 0 !important;
            }
            .sheet {
                width: 100% !important;
                min-height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border: none !important;
            }
            .no-print {
                display: none !important;
            }
        }

        /* WATERMARK LOGO RESMI UBP */
        .watermark-bg {
            position: absolute;
            inset: 0;
            background-image: url('{{ asset("images/logo_ubp.png") }}');
            background-repeat: no-repeat;
            background-position: center 52%;
            background-size: 320px;
            opacity: 0.035;
            pointer-events-none;
        }

        /* TABLE GRID STYLING */
        .academic-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5px;
        }
        .academic-table tr {
            border-bottom: 1px solid #f1f5f9;
        }
        .academic-table tr:last-child {
            border-bottom: none;
        }
        .academic-table td {
            padding: 4px 6px;
            vertical-align: top;
        }
        .academic-table td.field-label {
            width: 130px;
            color: #64748b;
            font-weight: 600;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .academic-table td.field-colon {
            width: 10px;
            color: #94a3b8;
            font-weight: bold;
            text-align: center;
        }
        .academic-table td.field-value {
            color: #0f172a;
            font-weight: 700;
        }

        .mono {
            font-family: 'JetBrains Mono', monospace;
        }

        /* STEMPEL ACCEPTED / REJECTED */
        .stamp-accept {
            border: 3px solid #16a34a;
            color: #16a34a;
            background-color: rgba(22, 163, 74, 0.06);
            padding: 4px 14px;
            font-size: 15px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 2px;
            display: inline-block;
            border-radius: 6px;
            transform: rotate(-6deg);
        }
        .stamp-reject {
            border: 3px solid #dc2626;
            color: #dc2626;
            background-color: rgba(220, 38, 38, 0.06);
            padding: 4px 14px;
            font-size: 15px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 2px;
            display: inline-block;
            border-radius: 6px;
            transform: rotate(-6deg);
        }
    </style>
</head>
<body>

    {{-- ========================================================= --}}
    {{-- FLOATING ACTION TOOLBAR (LAYAR SCREEN ONLY)               --}}
    {{-- ========================================================= --}}
    <div class="no-print" style="position: fixed; top: 16px; right: 16px; z-index: 9999; display: flex; flex-direction: column; align-items: flex-end; gap: 6px;">
        <div style="display: flex; gap: 8px;">
            <button onclick="window.print()" style="background: linear-gradient(135deg, #1e40af, #3b82f6); color: white; border: none; padding: 10px 20px; border-radius: 9999px; font-weight: 700; font-size: 13px; cursor: pointer; box-shadow: 0 4px 14px rgba(30, 64, 175, 0.35); display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-print"></i> Cetak / Simpan PDF
            </button>
            <button onclick="window.close()" style="background: #334155; color: white; border: none; padding: 10px 18px; border-radius: 9999px; font-weight: 600; font-size: 13px; cursor: pointer; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                Tutup
            </button>
        </div>
        <div style="background: rgba(15, 23, 42, 0.9); backdrop-filter: blur(8px); color: #e2e8f0; font-size: 11px; padding: 6px 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.15); box-shadow: 0 4px 12px rgba(0,0,0,0.2); max-width: 320px; text-align: right; line-height: 1.4;">
            💡 <strong style="color: #93c5fd;">Tips:</strong> Di jendela print, ganti <strong>Tujuan / Destination</strong> dari <em>OneNote</em> ke <strong>"Save as PDF"</strong> (Simpan sebagai PDF).
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- SHEET KERTAS CETAK RESMI A4                               --}}
    {{-- ========================================================= --}}
    <div class="sheet">
        
        {{-- Watermark Logo UBP --}}
        <div class="watermark-bg"></div>

        {{-- ===================================================== --}}
        {{-- 1. KOP SURAT RESMI UNIVERSITAS BUANA PERJUANGAN       --}}
        {{-- ===================================================== --}}
        <div style="position: relative; z-index: 10; margin-bottom: 12px;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    {{-- Logo Kampus Asli Berwarna --}}
                    <td style="width: 75px; vertical-align: middle;">
                        <img src="{{ asset('images/logo_ubp.png') }}" alt="Logo UBP Karawang" style="width: 72px; height: auto; object-fit: contain;">
                    </td>
                    {{-- Identitas Lembaga --}}
                    <td style="vertical-align: middle; padding-left: 14px;">
                        <span style="font-size: 9.5px; font-weight: 800; color: #475569; letter-spacing: 1.5px; text-transform: uppercase; display: block;">
                            Yayasan Pembina Perguruan Tinggi Pangkal Perjuangan
                        </span>
                        <h1 style="font-size: 18px; font-weight: 900; color: #0f2952; text-transform: uppercase; letter-spacing: -0.3px; line-height: 1.15; margin: 2px 0;">
                            Universitas Buana Perjuangan Karawang
                        </h1>
                        <span style="font-size: 10.5px; font-weight: 700; color: #1e3a8a; letter-spacing: 0.5px; text-transform: uppercase; display: block;">
                            Panitia Penerimaan Mahasiswa Baru (PMB) Tahun Akademik 2026/2027
                        </span>
                        <p style="font-size: 9px; color: #64748b; margin-top: 3px; line-height: 1.3;">
                            Jl. H.S. Ronggowaluyo, Telukjambe Timur, Karawang, Jawa Barat 41361<br>
                            Telp: (0267) 8403140 • Website: <strong style="color: #1e3a8a;">pmb.ubpkarawang.ac.id</strong> • Email: pmb@ubpkarawang.ac.id
                        </p>
                    </td>
                    {{-- QR Code Verifikasi --}}
                    <td style="width: 80px; text-align: right; vertical-align: middle;">
                        <div style="border: 1px solid #cbd5e1; padding: 4px; border-radius: 8px; background: #ffffff; display: inline-block; text-align: center;">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=110x110&data={{ urlencode(url()->current()) }}" 
                                 alt="QR Verify" 
                                 style="width: 58px; height: 58px; display: block;">
                            <span style="font-size: 7px; font-family: 'JetBrains Mono', monospace; font-weight: 800; color: #64748b; text-transform: uppercase; display: block; margin-top: 2px;">
                                VERIFIED
                            </span>
                        </div>
                    </td>
                </tr>
            </table>

            {{-- Double Line Kop Surat (Navy + Gold) --}}
            <div style="border-bottom: 2.5px solid #0f2952; margin-top: 8px;"></div>
            <div style="border-bottom: 1px solid #d97706; margin-top: 1.5px;"></div>
        </div>

        {{-- ===================================================== --}}
        {{-- 2. JUDUL DOKUMEN & NOMOR REGISTRASI                   --}}
        {{-- ===================================================== --}}
        <div style="position: relative; z-index: 10; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: flex-end;">
            <div>
                <h2 style="font-size: 13px; font-weight: 900; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px;">
                    Bukti Registrasi & Manifest Penerimaan
                </h2>
                <p style="font-size: 9.5px; color: #64748b; font-weight: 500;">
                    Dokumen sah tanda bukti pendaftaran & seleksi administrasi mahasiswa baru
                </p>
            </div>
            <div style="text-align: right;">
                <span style="font-size: 9px; color: #64748b; font-weight: 700; text-transform: uppercase; display: block;">No. Registrasi (Trx ID)</span>
                <span class="mono" style="font-size: 12px; font-weight: 900; color: #1e3a8a; letter-spacing: 0.5px;">
                    #{{ $ereg->trx_id ?? 'PENDING' }}
                </span>
            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- 3. HERO ACCEPTANCE HIGHLIGHT RIBBON (JIKA LULUS)      --}}
        {{-- ===================================================== --}}
        <div style="position: relative; z-index: 10; margin-bottom: 14px;">
            @if($isLulus)
                <div style="background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%); border: 1.5px solid #86efac; border-radius: 8px; padding: 9px 14px; display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="background: #16a34a; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 900;">
                            <i class="fas fa-check"></i>
                        </div>
                        <div>
                            <span style="font-size: 8.5px; font-weight: 800; color: #166534; text-transform: uppercase; letter-spacing: 1px; display: block;">
                                HASIL SELEKSI: RESMI DINYATAKAN LULUS
                            </span>
                            <span style="font-size: 12.5px; font-weight: 900; color: #0f2952; text-transform: uppercase; display: block;">
                                Program Studi: {{ $jurusanDiterima ?: ($ereg->jurusan1_nama ?? '-') }}
                            </span>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <span class="stamp-accept">ACCEPTED</span>
                    </div>
                </div>
            @elseif($isGagal)
                <div style="background: #fef2f2; border: 1.5px solid #fca5a5; border-radius: 8px; padding: 9px 14px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <span style="font-size: 8.5px; font-weight: 800; color: #991b1b; text-transform: uppercase; letter-spacing: 1px; display: block;">
                            STATUS SELEKSI: BELUM MEMENUHI SYARAT
                        </span>
                        <span style="font-size: 11px; font-weight: 800; color: #7f1d1d;">
                            Silakan menghubungi Panitia PMB untuk informasi gelombang pendaftaran berikutnya.
                        </span>
                    </div>
                    <span class="stamp-reject">REJECTED</span>
                </div>
            @else
                <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 9px 14px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <span style="font-size: 8.5px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 1px; display: block;">
                            STATUS TAHAPAN SELEKSI
                        </span>
                        <span style="font-size: 12px; font-weight: 800; color: #1e3a8a;">
                            {{ strtoupper($statusText) }}
                        </span>
                    </div>
                    <span style="font-size: 10px; font-weight: 800; color: #475569; background: #e2e8f0; padding: 4px 10px; border-radius: 4px; text-transform: uppercase;">
                        PROSES SELEKSI
                    </span>
                </div>
            @endif
        </div>

        {{-- ===================================================== --}}
        {{-- 4. KONTEN DUA KOLOM: FOTO + RINCIAN DATA LENGKAP     --}}
        {{-- ===================================================== --}}
        <div style="position: relative; z-index: 10; display: flex; gap: 14px;">

            {{-- ================================================= --}}
            {{-- KOLOM KIRI (LEBAR: 145px): FOTO, ALMET, BILLING   --}}
            {{-- ================================================= --}}
            <div style="width: 145px; flex-shrink: 0;">
                
                {{-- PAS FOTO RESMI (3x4) --}}
                <div style="width: 100%; aspect-ratio: 3/4; border: 2px solid #cbd5e1; border-radius: 6px; overflow: hidden; background: #f8fafc; position: relative; box-shadow: 0 1px 3px rgba(0,0,0,0.06); margin-bottom: 8px;">
                    @if($fotoProfil)
                        <img src="{{ $fotoProfil }}" alt="Foto Calon Mahasiswa" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                    @else
                        <div style="width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #f1f5f9; color: #94a3b8;">
                            <i class="fas fa-user" style="font-size: 40px; margin-bottom: 4px;"></i>
                            <span style="font-size: 9px; font-weight: 700; text-transform: uppercase;">Pas Foto 3x4</span>
                        </div>
                    @endif
                </div>

                {{-- KOTAK ALOKASI ALMAMATER --}}
                <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 6px; text-align: center; margin-bottom: 8px;">
                    <span style="font-size: 8px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; display: block;">
                        Jas Almamater
                    </span>
                    <span style="font-size: 15px; font-weight: 900; color: #1e3a8a; display: block; margin: 1px 0;">
                        {{ $ereg->ukuran_almamater ? strtoupper($ereg->ukuran_almamater) : 'BELUM PILIH' }}
                    </span>
                    <span style="font-size: 7.5px; color: #94a3b8; display: block;">
                        Tunjukkan saat pengambilan
                    </span>
                </div>

                {{-- KOTAK STATUS KEUANGAN & VA --}}
                <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 7px; margin-bottom: 8px;">
                    <span style="font-size: 8px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 2px;">
                        Status Keuangan
                    </span>
                    @if($lunas)
                        <span style="font-size: 11px; font-weight: 900; color: #16a34a; background: #dcfce7; padding: 2px 6px; border-radius: 4px; display: inline-block;">
                            <i class="fas fa-check-circle"></i> LUNAS / PAID
                        </span>
                    @else
                        <span style="font-size: 10px; font-weight: 900; color: #ea580c; background: #ffedd5; padding: 2px 6px; border-radius: 4px; display: inline-block;">
                            <i class="fas fa-clock"></i> TAGIHAN
                        </span>
                    @endif

                    @if($billing && $billing->virtual_account)
                        <div style="margin-top: 5px; padding-top: 4px; border-top: 1px dashed #e2e8f0;">
                            <span style="font-size: 7.5px; color: #64748b; font-weight: bold; text-transform: uppercase; display: block;">No. Virtual Account</span>
                            <span class="mono" style="font-size: 9.5px; font-weight: 800; color: #0f172a;">{{ $billing->virtual_account }}</span>
                        </div>
                    @endif
                </div>

                {{-- RINGKASAN JALUR & GELOMBANG --}}
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px; font-size: 9px;">
                    <div style="margin-bottom: 3px;">
                        <span style="color: #64748b; font-size: 8px; display: block; text-transform: uppercase;">Jalur Masuk:</span>
                        <strong style="color: #0f172a;">{{ $ereg->jalur_pendaftaran ?? 'Reguler' }}</strong>
                    </div>
                    <div>
                        <span style="color: #64748b; font-size: 8px; display: block; text-transform: uppercase;">Gelombang:</span>
                        <strong style="color: #0f172a;">{{ $ereg->masterUser->gelombang ?? ($ereg->gelombang ?? 'Gelombang 1') }}</strong>
                    </div>
                </div>

            </div>

            {{-- ================================================= --}}
            {{-- KOLOM KANAN (LEBAR: FLEX 1): RINCIAN TABULAR DATA --}}
            {{-- ================================================= --}}
            <div style="flex: 1; min-width: 0;">

                {{-- SECTION 1: DATA PRIBADI --}}
                <div style="margin-bottom: 11px;">
                    <div style="background: #f1f5f9; padding: 4px 8px; border-left: 3.5px solid #1e3a8a; border-radius: 0 4px 4px 0; margin-bottom: 5px;">
                        <h3 style="font-size: 10px; font-weight: 800; color: #0f2952; text-transform: uppercase; letter-spacing: 0.5px;">
                            I. Data Pribadi Calon Mahasiswa
                        </h3>
                    </div>
                    <table class="academic-table">
                        <tr>
                            <td class="field-label">Nama Lengkap</td>
                            <td class="field-colon">:</td>
                            <td class="field-value" style="font-size: 12px; color: #0f2952;">
                                {{ optional($dataFinal)->nama_mahasiswa ?? $biodata->nama_lengkap ?? $user->name }}
                            </td>
                        </tr>
                        <tr>
                            <td class="field-label">Nomor Induk (NIK)</td>
                            <td class="field-colon">:</td>
                            <td class="field-value mono">
                                {{ optional($dataFinal)->nik ?? $biodata->nik ?? '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="field-label">Tempat, Tgl Lahir</td>
                            <td class="field-colon">:</td>
                            <td class="field-value">
                                {{ optional($dataFinal)->tempat_lahir ?? $biodata->tempat_lahir ?? '-' }}, {{ $tglLahirFormatted }}
                            </td>
                        </tr>
                        <tr>
                            <td class="field-label">Jenis Kelamin</td>
                            <td class="field-colon">:</td>
                            <td class="field-value">
                                {{ $biodata->jenis_kelamin ?? '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="field-label">Alamat Domisili</td>
                            <td class="field-colon">:</td>
                            <td class="field-value" style="font-weight: 600; line-height: 1.35;">
                                {{ $alamatLengkap }}
                            </td>
                        </tr>
                        <tr>
                            <td class="field-label">Kontak (HP / WA)</td>
                            <td class="field-colon">:</td>
                            <td class="field-value mono">
                                {{ optional($dataFinal)->handphone ?? $ereg->phone ?? '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="field-label">Alamat Email</td>
                            <td class="field-colon">:</td>
                            <td class="field-value mono" style="font-size: 10px; font-weight: 600;">
                                {{ $user->email }}
                            </td>
                        </tr>
                    </table>
                </div>

                {{-- SECTION 2: DATA PENDIDIKAN & PROGRAM STUDI --}}
                <div style="margin-bottom: 11px;">
                    <div style="background: #f1f5f9; padding: 4px 8px; border-left: 3.5px solid #1e3a8a; border-radius: 0 4px 4px 0; margin-bottom: 5px;">
                        <h3 style="font-size: 10px; font-weight: 800; color: #0f2952; text-transform: uppercase; letter-spacing: 0.5px;">
                            II. Riwayat Pendidikan & Program Studi
                        </h3>
                    </div>
                    <table class="academic-table">
                        <tr>
                            <td class="field-label">Asal Sekolah</td>
                            <td class="field-colon">:</td>
                            <td class="field-value">
                                {{ $biodata->asal_sekolah ?? '-' }} 
                                @if($biodata && $biodata->tahun_lulus)
                                    <span style="color: #64748b; font-weight: normal;">(Lulus Tahun {{ $biodata->tahun_lulus }})</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="field-label">Pilihan Program Studi 1</td>
                            <td class="field-colon">:</td>
                            <td class="field-value">
                                {{ $ereg->jurusan1_nama ?? '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="field-label">Pilihan Program Studi 2</td>
                            <td class="field-colon">:</td>
                            <td class="field-value" style="color: #64748b;">
                                {{ $ereg->jurusan2_nama ?? '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="field-label" style="color: #166534;">Diterima Pada Prodi</td>
                            <td class="field-colon">:</td>
                            <td class="field-value" style="color: #166534; font-size: 11.5px;">
                                @if($isLulus)
                                    <strong style="text-decoration: underline;">{{ $jurusanDiterima ?: ($ereg->jurusan1_nama ?? '-') }}</strong>
                                @else
                                    <span style="color: #94a3b8;">{{ $statusText }}</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>

                {{-- SECTION 3: DATA ORANG TUA / WALI --}}
                <div style="margin-bottom: 8px;">
                    <div style="background: #f1f5f9; padding: 4px 8px; border-left: 3.5px solid #1e3a8a; border-radius: 0 4px 4px 0; margin-bottom: 5px;">
                        <h3 style="font-size: 10px; font-weight: 800; color: #0f2952; text-transform: uppercase; letter-spacing: 0.5px;">
                            III. Data Orang Tua / Wali
                        </h3>
                    </div>
                    <table class="academic-table">
                        <tr>
                            <td class="field-label">Nama Ayah Kandung</td>
                            <td class="field-colon">:</td>
                            <td class="field-value">
                                {{ $biodata->nama_ayah ?? '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="field-label">Nama Ibu Kandung</td>
                            <td class="field-colon">:</td>
                            <td class="field-value">
                                {{ $biodata->nama_ibu ?? '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="field-label">No. Telepon Orang Tua</td>
                            <td class="field-colon">:</td>
                            <td class="field-value mono">
                                {{ $biodata->dataFinal->handphone ?? ($biodata->no_hp_ortu ?? '-') }}
                            </td>
                        </tr>
                    </table>
                </div>

            </div>

        </div>

        {{-- ===================================================== --}}
        {{-- 5. LEGALITAS, DISCLAIMER & PENGESAHAN DOKUMEN         --}}
        {{-- ===================================================== --}}
        <div style="position: relative; z-index: 10; margin-top: 14px; border-top: 1.5px solid #0f2952; padding-top: 10px;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    {{-- Petunjuk & Catatan Penting --}}
                    <td style="vertical-align: top; width: 62%; padding-right: 20px;">
                        <span style="font-size: 9px; font-weight: 800; color: #0f2952; text-transform: uppercase; display: block; margin-bottom: 3px;">
                            <i class="fas fa-info-circle"></i> Catatan & Ketentuan Registrasi:
                        </span>
                        <ol style="font-size: 8.5px; color: #475569; padding-left: 14px; line-height: 1.45;">
                            <li>Dokumen ini merupakan bukti sah pendaftaran & verifikasi seleksi penerimaan mahasiswa baru UBP Karawang.</li>
                            <li>Kebenaran data yang tertera di atas sepenuhnya menjadi tanggung jawab pendaftar yang bersangkutan.</li>
                        </ol>
                        <p style="font-size: 8px; color: #94a3b8; font-family: 'JetBrains Mono', monospace; margin-top: 6px;">
                            Sistem Informasi Admisi PMB Online UBP Karawang • Dicetak pada: {{ date('d-m-Y H:i:s') }}
                        </p>
                    </td>

                    {{-- Kolom Pengesahan Tanda Tangan Panitia --}}
                    <td style="vertical-align: top; width: 38%; text-align: center;">
                        <p style="font-size: 9.5px; color: #334155; font-weight: 600;">
                            Karawang, {{ $todayFormatted }}
                        </p>
                        <p style="font-size: 9.5px; font-weight: 800; color: #0f2952; text-transform: uppercase;">
                            Panitia Penerimaan Mahasiswa Baru
                        </p>

                        {{-- Area Stempel & Tanda Tangan --}}
                        <div style="height: 52px; display: flex; align-items: center; justify-content: center; position: relative;">
                            {{-- Stempel Digital Kampus --}}
                            <div style="border: 2px dashed #94a3b8; border-radius: 50%; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; opacity: 0.65;">
                                <i class="fas fa-stamp" style="font-size: 18px; color: #0f2952;"></i>
                            </div>
                        </div>

                        <p style="font-size: 10px; font-weight: 900; color: #0f172a; text-decoration: underline; text-transform: uppercase;">
                            TIM ADMISI PMB UBP
                        </p>
                        <p style="font-size: 8px; font-family: 'JetBrains Mono', monospace; color: #64748b;">
                            NIP/ID. 2026.PMB.UBP.001
                        </p>
                    </td>
                </tr>
            </table>
        </div>

    </div>

</body>
</html>