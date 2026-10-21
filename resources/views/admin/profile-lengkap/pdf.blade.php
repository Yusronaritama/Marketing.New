<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Profile Lengkap Mahasiswa - {{ $user->biodata->nama_lengkap ?? $user->name }}</title>
    <style>
        @page {
            margin: 20px;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        .header h1 {
            font-size: 18px;
            margin: 0;
            color: #2c3e50;
        }
        
        .header p {
            font-size: 11px;
            margin: 5px 0 0 0;
            color: #7f8c8d;
        }
        
        .section {
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        
        .section-title {
            background-color: #f8f9fa;
            padding: 8px 10px;
            font-size: 14px;
            font-weight: bold;
            color: #2c3e50;
            border-left: 4px solid #3498db;
            margin-bottom: 10px;
        }
        
        .info-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-label {
            display: table-cell;
            padding: 6px 10px;
            font-weight: bold;
            width: 35%;
            vertical-align: top;
            border-bottom: 1px solid #eee;
        }
        
        .info-value {
            display: table-cell;
            padding: 6px 10px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }
        
        .berkas-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .berkas-table th {
            background-color: #f8f9fa;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ddd;
            font-size: 11px;
        }
        
        .berkas-table td {
            padding: 8px;
            border: 1px solid #ddd;
            font-size: 11px;
            vertical-align: top;
        }
        
        .berkas-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .status-verified {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-unverified {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 10px;
            color: #7f8c8d;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .qr-code {
            text-align: center;
            margin: 20px 0;
        }
        
        .qr-code img {
            max-width: 150px;
            height: auto;
        }
        
        .watermark {
            position: fixed;
            bottom: 10px;
            right: 10px;
            opacity: 0.1;
            font-size: 40px;
            color: #ccc;
            transform: rotate(-45deg);
        }
        
        .logo {
            text-align: center;
            margin-bottom: 10px;
        }
        
        .logo img {
            max-width: 150px;
            height: auto;
        }
        
        .no-berkas {
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-style: italic;
        }
    </style>
</head>
<body>
    @if(!isset($preview))
    <div class="watermark">
        DRAFT
    </div>
    @endif
    
    <div class="header">
        <div class="logo">
            <!-- Tambahkan logo kampus di sini jika ada -->
            <h2>UNIVERSITAS BUANA PERJUANGAN KARAWANG</h2>
        </div>
        <h1>PROFILE LENGKAP MAHASISWA BARU</h1>
        <p>Dokumen ini dicetak pada: {{ $today }}</p>
    </div>
    
    <!-- Informasi Pribadi -->
    <div class="section">
        <div class="section-title">INFORMASI PRIBADI</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Nama Lengkap</div>
                <div class="info-value">{{ $user->biodata->nama_lengkap ?? $user->name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Email</div>
                <div class="info-value">{{ $user->email }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Telepon/HP</div>
                <div class="info-value">{{ $user->phone ?? ($user->biodata->no_telepon ?? '-') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Role</div>
                <div class="info-value">{{ $user->role }}</div>
            </div>
            @if($biodata)
            <div class="info-row">
                <div class="info-label">Agama</div>
                <div class="info-value">{{ $biodata->agama ?? '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Alamat</div>
                <div class="info-value">{{ $biodata->alamat ?? '-' }}</div>
            </div>
            @endif
        </div>
    </div>
    
    <!-- Informasi Akademik -->
    <div class="section">
        <div class="section-title">INFORMASI AKADEMIK</div>
        <div class="info-grid">
            @if($ereg)
            <div class="info-row">
                <div class="info-label">Jalur Pendaftaran</div>
                <div class="info-value">{{ $ereg->jalur_pendaftaran ?? '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Status Pekerjaan</div>
                <div class="info-value">{{ $ereg->status_pekerjaan ?? '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Jurusan Lulusan</div>
                <div class="info-value">{{ $ereg->jurusan_lulusan ?? '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Prodi Pilihan 1</div>
                <div class="info-value">{{ $ereg->jurusan1 ?? '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Prodi Pilihan 2</div>
                <div class="info-value">{{ $ereg->jurusan2 ?? '-' }}</div>
            </div>
            @if($ereg->alasan_jurusan_1)
            <div class="info-row">
                <div class="info-label">Alasan Pilihan 1</div>
                <div class="info-value">{{ $ereg->alasan_jurusan_1 }}</div>
            </div>
            @endif
            @if($ereg->alasan_jurusan_2)
            <div class="info-row">
                <div class="info-label">Alasan Pilihan 2</div>
                <div class="info-value">{{ $ereg->alasan_jurusan_2 }}</div>
            </div>
            @endif
            @endif
            
            @if($kelulusan)
            <div class="info-row">
                <div class="info-label">Status Kelulusan</div>
                <div class="info-value">
                    <strong>LULUS</strong> - {{ $kelulusan->nama_prodi_lulus ?? 'N/A' }}
                    @if($kelulusan->nilai_quiz)
                    (Nilai: {{ number_format($kelulusan->nilai_quiz, 2) }})
                    @endif
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Tanggal Kelulusan</div>
                <div class="info-value">
                    {{ $kelulusan->created_at ? $kelulusan->created_at->format('d F Y H:i') : '-' }}
                </div>
            </div>
            @endif
        </div>
    </div>
    
    <!-- Status Verifikasi -->
    <div class="section">
        <div class="section-title">STATUS VERIFIKASI</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Status Registrasi</div>
                <div class="info-value">
                    @if($ereg)
                        @php
                            $statusLabels = [
                                'completed' => 'Selesai',
                                'in_progress' => 'Dalam Proses',
                                'not_started' => 'Belum Mulai'
                            ];
                            $statusColors = [
                                'completed' => '#d4edda',
                                'in_progress' => '#fff3cd',
                                'not_started' => '#f8d7da'
                            ];
                            $currentStatus = $ereg->status ?? 'not_started';
                        @endphp
                        <span style="background-color: {{ $statusColors[$currentStatus] ?? '#e9ecef' }}; 
                                   color: #000; 
                                   padding: 3px 8px; 
                                   border-radius: 3px;">
                            {{ $statusLabels[$currentStatus] ?? 'Unknown' }}
                        </span>
                    @else
                        Tidak Ada Data
                    @endif
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Status Verifikasi Admin</div>
                <div class="info-value">
                    @if($verifikasi)
                        <span class="status-badge status-verified">✓ TERVERIFIKASI</span>
                        @if($user->masterUsersVerified && $user->masterUsersVerified->count() > 0)
                            @php
                                $latestVerification = $user->masterUsersVerified->sortByDesc('verified_at')->first();
                            @endphp
                            <br>
                            <small>Oleh: {{ $latestVerification->verified_by ?? 'System' }}</small>
                            <br>
                            <small>Pada: {{ $latestVerification->verified_at ? $latestVerification->verified_at->format('d F Y H:i') : '-' }}</small>
                        @endif
                    @else
                        <span class="status-badge status-unverified">BELUM DIVERIFIKASI</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    @if(count($berkasList) > 0)
    <div class="section">
        <div class="section-title">BERKAS UPLOAD ({{ $totalBerkas }} File)</div>
        
        <table class="berkas-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="35%">Jenis Berkas</th>
                    <th width="30%">Nama File</th>
                    <th width="15%">Status File</th>
                    <th width="15%">Tautan</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $counter = 1; // Inisialisasi counter manual
                @endphp
                @foreach($berkasList as $field => $berkas)
                <tr>
                    <td>{{ $counter++ }}</td> <!-- Gunakan counter, bukan $index + 1 -->
                    <td>{{ $berkas['label'] }}</td>
                    <td>
                        @php
                            $filename = basename($berkas['path']);
                            // Potong nama file jika terlalu panjang
                            if(strlen($filename) > 30) {
                                $filename = substr($filename, 0, 27) . '...';
                            }
                        @endphp
                        {{ $filename }}
                    </td>
                    <td>
                        @if($berkas['exists'])
                            <span style="color: #28a745;">✓ Tersedia</span>
                        @else
                            <span style="color: #dc3545;">✗ Tidak Ditemukan</span>
                        @endif
                    </td>
                    <td>
                        @if($berkas['exists'])
                            <span style="color: #007bff;">Lihat di Sistem</span>
                        @else
                            <span style="color: #6c757d;">-</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div style="margin-top: 15px; font-size: 10px; color: #6c757d;">
            <p><strong>Catatan:</strong> Untuk melihat file asli, silakan login ke sistem admin dan buka detail user.</p>
        </div>
    </div>
    @else
    <div class="section">
        <div class="section-title">BERKAS UPLOAD</div>
        <div class="no-berkas">
            <p>Tidak ada berkas yang diupload oleh mahasiswa ini.</p>
        </div>
    </div>
    @endif
    
    <!-- Informasi Tambahan -->
    <div class="section">
        <div class="section-title">INFORMASI TAMBAHAN</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">ID User</div>
                <div class="info-value">{{ $user->id }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Tanggal Registrasi</div>
                <div class="info-value">{{ $user->created_at->format('d F Y H:i') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Terakhir Diupdate</div>
                <div class="info-value">{{ $user->updated_at->format('d F Y H:i') }}</div>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <p>Dokumen ini dicetak secara otomatis dari Sistem PMB Universitas BUANA PERJUANGAN KARAWANG</p>
        <p>Halaman 1 dari 1 | User ID: {{ $user->id }} | Tanggal Cetak: {{ $today }}</p>
    </div>
</body>
</html>