<!DOCTYPE html>
<html>
<head>
    <title>Surat Kelulusan</title>
    <style>
        /* Mengatur Margin Kertas agar Area Tulis Lebih Luas */
        @page { 
            size: A4; 
            margin: 2cm 2cm 2cm 2cm; /* Margin Atas-Kanan-Bawah-Kiri 2cm (Standar Surat Resmi) */
        }

        body { 
            font-family: 'Times New Roman', Times, serif; 
            font-size: 11pt; /* Ukuran font dikecilkan sedikit */
            line-height: 1.2; /* Spasi antar baris diperpadat */
            margin: 0; 
            padding: 0; 
        }
        
        /* KOP SURAT */
        .header-table { width: 100%; border-bottom: 3px solid #000; padding-bottom: 5px; margin-bottom: 10px; }
        .logo { width: 85px; } /* Logo diperkecil sedikit */
        .kop-text { text-align: center; }
        .kop-univ { font-size: 14pt; font-weight: bold; text-transform: uppercase; margin: 0; }
        .kop-detail { font-size: 9pt; margin: 0; }
        .kop-web { font-size: 9pt; color: blue; text-decoration: underline; }

        /* ISI SURAT */
        .content { width: 100%; }
        
        /* Tabel Nomor & Tanggal */
        .nomor-hal { width: 100%; margin-bottom: 10px; }
        .nomor-hal td { vertical-align: top; }

        .tujuan { margin-bottom: 10px; }
        
        .lulus-box { text-align: center; margin: 15px 0; }
        .kata-lulus { font-size: 14pt; font-weight: bold; text-transform: uppercase; margin-bottom: 2px; display: block;}
        .prodi-text { font-size: 12pt; font-weight: bold; }

        .list-poin { margin-left: 15px; text-align: justify; }
        .list-poin ol { padding-left: 20px; margin-top: 5px; margin-bottom: 5px; }
        .list-poin li { margin-bottom: 3px; padding-left: 5px; } /* Jarak antar poin diperkecil */

        /* TANDA TANGAN */
        /* Menggunakan Page Break Avoid agar TTD tidak pisah halaman */
        .signature-section { 
            float: right; 
            /* 1. Width diperkecil dari 45% ke 35% supaya wadahnya pas dengan teks */
            width: 35%; 
            
            text-align: left; 
            margin-top: 20px; 
            
            /* 2. Margin Right Negatif untuk menarik paksa ke kanan */
            margin-right: -30px; 
            
            page-break-inside: avoid;
        }
        
        .ttd-img { 
            height: 60px; 
            margin: 2px 0; 
            /* Pastikan gambar tidak melebihi lebar wadah */
            max-width: 100%; 
            object-fit: contain;
        }
        
        .rektor-name { 
            font-weight: bold; 
            text-decoration: underline; 
            /* Mencegah nama turun baris jika terlalu panjang */
            white-space: nowrap; 
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td width="15%" valign="top">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" class="logo">
                @else
                    [LOGO]
                @endif
            </td>
            <td width="85%" class="kop-text">
                <h1 class="kop-univ">UNIVERSITAS BUANA PERJUANGAN KARAWANG</h1>
                <p class="kop-detail">TERAKREDITASI BAN-PT dan LAM-PTKes</p>
                <p class="kop-detail">Jl. H.S Ronggowaluyo Telukjambe Timur Karawang 41361 Tel./Fax. (0267) 8403140</p>
                <p class="kop-detail">Site: <span class="kop-web">http://ubpkarawang.ac.id</span> Email: info@ubpkarawang.ac.id</p>
            </td>
        </tr>
    </table>

    <div class="content">
        <table class="nomor-hal">
            <tr>
                <td width="12%">Nomor</td>
                <td width="48%">: 001/PMB/{{ date('Y') }}</td>
                <td width="40%" align="right">Karawang, {{ $tanggal }}</td>
            </tr>
            <tr>
                <td>Hal</td>
                <td colspan="2">: Hasil Seleksi Calon Mahasiswa Baru {{ date('Y') }}</td>
            </tr>
        </table>

        <div class="tujuan">
            Kepada Yth.<br>
            <strong>{{ strtoupper($nama) }}</strong><br>
            {{-- No. Daftar: {{ $nim }} --}} di Tempat
        </div>

        <p style="text-align: justify; margin-bottom: 10px;">
            Berdasarkan Hasil Ujian Saringan Masuk (USM) Universitas Buana Perjuangan Karawang, saudara dinyatakan :
        </p>

        <div class="lulus-box">
            <span class="kata-lulus">Lulus</span>
            Pada program studi <span class="prodi-text">{{ $prodi }}</span>
        </div>

        <p style="margin-bottom: 5px;">Selanjutnya, Saudara diwajibkan mengikuti beberapa tahapan selanjutnya sebagai berikut:</p>

        <div class="list-poin">
            <ol>
                <li><strong>Melakukan Pembayaran Tahap 1</strong> :
                    <br>- Dapat dilihat dimenu pembayaran.
                </li>
                <li>Mahasiswa Mengikuti Pengenalan Kehidupan Kampus Mahasiswa Baru (PKKMB), jadwal pelaksanaan PKKMB dapat dilihat pada laman : <u>http://penmabar.ubpkarawang.ac.id/</u></li>
                <li>Peserta Mengikuti Kegiatan Latihan Dasar Kepemimpinan dan Kedisiplinan (LDKK) Sesuai Tanggal dan Ketentuan Yang Tertera di Beranda Sistem.</li>
                <li>Melakukan Pengisian Kartu Rencana Studi (KRS), pada laman : <u>http://sipt.ubpkarawang.ac.id/</u></li>
                <li>Mahasiswa Mendapatkan Kelas dan Dosen Wali (Catatan: Apabila belum mendapatkan kelas & Dosen Wali Segera Hubungi TU Prodi masing - masing).</li>
                <li>Mahasiswa Mengikuti Perkuliahan.</li>
                <li>Informasi Jas LAB (Teknik Mesin, Teknik Industri, Teknik Informatika, Sistem Informasi dan Farmasi) Silahkan hubungi Tata Usaha Fakultas.</li>
            </ol>
        </div>

        <p style="margin-top: 10px;">Demikian untuk diketahui, atas perhatiannya diucapkan terima kasih.</p>

        <div class="signature-section">
            <p style="margin-bottom: 5px;">Wakil Rektor III<br>Bidang Kemahasiswaan, Alumni, Humas<br>dan Marketing.</p>
                    
            {{-- Lebar bungkus dikunci 180px biar presisi buat DOMPDF --}}
            <div style="position: relative; display: inline-block; margin: 8px 0; text-align: center; width: 180px;">
                
                {{-- 1. Gambar Tanda Tangan (Posisi Normal) --}}
                @if($ttdBase64)
                    <img src="{{ $ttdBase64 }}" style="width: 180px; height: auto; display: block; position: relative; z-index: 1;">
                @else
                    <br><br><br> 
                @endif
        
                {{-- 2. Gambar Cap UBP (Posisi Absolute, nimpa TTD) --}}
                @if($capBase64)
                    {{-- 'left' diubah jadi 15px biar cap masuk numpuk ke area tanda tangan --}}
                    <img src="{{ $capBase64 }}" style="position: absolute; left: 15px; top: 25px; width: 110px; height: auto; z-index: 2; opacity: 0.85;">
                @endif
        
            </div>
                    
            <p style="margin-top: 0;">
                <span class="rektor-name">Dr. apt. Dedy Frianto, S.Far., M.M.</span><br>
                NIK. 416200134
            </p>
        </div>
        
        <div style="clear: both;"></div>
    </div>

</body>
</html>