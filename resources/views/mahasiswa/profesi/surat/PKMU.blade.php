<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Surat Pernyataan Kesanggupan Membayar UKT - UBP Karawang</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body {
    background: #e8e0d4;
    font-family: 'Times New Roman', Times, serif;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    min-height: 100vh;
    padding: 40px 20px;
  }
  
  /* =========================================
     SETTINGAN LAYAR (HP & PC)
     ========================================= */
  .page {
    background: #fff;
    width: 100%; 
    max-width: 210mm; /* Maksimal selebar A4 */
    min-height: 297mm;
    padding: 20px; /* Padding dinamis buat layar HP */
    box-shadow: 0 4px 32px rgba(0,0,0,0.18);
    position: relative;
    overflow-x: hidden;
  }

  /* KOP SURAT */
  .kop-table { width: 100%; border-bottom: 3px solid black; padding-bottom: 10px; margin-bottom: 20px; }
  .kop-logo-td { width: 15%; text-align: left; vertical-align: middle; }
  .kop-logo-img { width: 60px; height: auto; } /* Logo dikecilin dikit di HP */
  .kop-text-td { width: 70%; text-align: center; vertical-align: middle; }
  .kop-text .univ { font-size: 14pt; font-weight: bold; letter-spacing: 0.03em; color: #1a1a6e; }
  .kop-text .fak { font-size: 11pt; font-weight: bold; color: #1a1a6e; }
  .kop-text .akred { font-size: 9pt; font-weight: bold; color: #1a1a6e; font-style: italic; }
  .kop-text .alamat { font-size: 8pt; color: #333; margin-top: 4px; }
  .kop-text .alamat a { color: #1a1a6e; text-decoration: none; word-break: break-all; }

  /* JUDUL */
  .judul { text-align: center; margin: 20px 0 16px; }
  .judul p { font-size: 12pt; font-weight: bold; line-height: 1.5; text-decoration: underline; }

  /* IDENTITAS */
  .identitas { margin: 14px 0 18px; }
  .identitas .row { display: flex; align-items: baseline; margin-bottom: 6px; flex-wrap: wrap; }
  .identitas .label { min-width: 170px; font-size: 11pt; }
  .identitas .titik-dua { margin-right: 8px; font-size: 11pt; }
  .identitas .garis { flex: 1; border-bottom: 1px solid #222; height: 1em; min-width: 100px; }

  /* ISI & PENUTUP */
  .isi { font-size: 11pt; line-height: 1.7; text-align: justify; margin-bottom: 14px; }
  .isi strong { font-weight: bold; }
  
  /* TABEL PROGRAM STUDI KHUSUS UKT */
  .prodi-table { margin: 14px 0 18px; padding-left: 10px; }
  .prodi-table .row { display: flex; align-items: baseline; margin-bottom: 8px; flex-wrap: wrap; }
  .prodi-table .label { min-width: 140px; font-size: 11pt; }
  .prodi-table .titik-dua { margin-right: 8px; font-size: 11pt; }
  .prodi-table .value { font-size: 11pt; font-weight: bold; }

  .penutup { font-size: 11pt; line-height: 1.7; text-align: justify; margin-bottom: 20px; }

  /* TANDA TANGAN */
  .ttd-block { text-align: right; margin-top: 10px; }
  .ttd-block .tempat-tanggal { font-size: 11pt; margin-bottom: 4px; }
  .ttd-block .yang-membuat { font-size: 11pt; margin-bottom: 60px; }
  .ttd-block .meterai {
    font-size: 9pt; color: #555; font-style: italic;
    border: 1px dashed #999; display: inline-block;
    padding: 3px 14px; margin-bottom: 55px;
  }
  .ttd-block .garis-nama {
    border-bottom: 1px solid #333;
    min-width: 150px; display: inline-block; height: 1px;
  }

  /* TOMBOL PRINT MELAYANG */
  .btn-print {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background-color: #1a1a6e;
    color: #fff;
    border: none;
    padding: 12px 20px;
    font-size: 11pt;
    font-weight: bold;
    border-radius: 8px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    transition: all 0.2s;
    font-family: Arial, sans-serif;
    z-index: 1000;
  }
  .btn-print:hover {
    background-color: #0d0d47;
    transform: translateY(-2px);
  }

  /* =========================================
     SETTINGAN KHUSUS MODE PRINT (KERTAS A4)
     ========================================= */
  
  /* Paksa browser pakai kertas A4 & hapus margin default browser */
  @page {
    size: A4;
    margin: 0; 
  }

  @media print {
    body { background: #fff; padding: 0; margin: 0; }
    
    .page { 
        width: 210mm;
        min-height: auto; /* Dibuang biar gak maksa nambah halaman kosong */
        height: 100vh;
        padding: 15mm 25mm 15mm 30mm; /* Padding aman standar dokumen cetak */
        box-shadow: none; 
        margin: 0;
        page-break-after: avoid; 
    }
    
    /* Rapatkan spasi teks khusus pas dicetak biar muat pas 1 lembar */
    .isi { line-height: 1.5; margin-bottom: 10px; } 
    .penutup { line-height: 1.5; margin-bottom: 15px; }
    
    /* Kurangin sedikit tinggi area tanda tangan */
    .ttd-block .yang-membuat { margin-bottom: 40px; } 
    .ttd-block .meterai { margin-bottom: 35px; }

    /* Kembalikan ukuran Kop Surat ke standar dokumen cetak asli */
    .kop-logo-img { width: 90px; }
    .kop-text .univ { font-size: 18px; }
    .kop-text .fak { font-size: 16px; }
    .kop-text .akred { font-size: 14px; }
    .kop-text .alamat { font-size: 12px; }
    .ttd-block .garis-nama { min-width: 220px; }

    /* Sembunyikan tombol biru pas proses cetak/save PDF */
    .btn-print { display: none !important; }
  }
</style>
</head>
<body>
<div class="page">
  <table class="kop-table">
        <tr>
            {{-- Kolom Logo mepet kiri --}}
            <td class="kop-logo-td">
                <img src="{{ asset('images/logo_ubp.png') }}" alt="Logo UBP" class="kop-logo-img">
            </td>
            
            {{-- Kolom Teks Center Sempurna --}}
            <td class="kop-text-td">
                <div class="kop-text">
                    <div class="univ">UNIVERSITAS BUANA PERJUANGAN KARAWANG</div>
                    <div class="fak">FAKULTAS FARMASI</div>
                    <div class="akred">TERAKREDITASI LAM-PTKes</div>
                    <div class="alamat">
                        Jl. H.S Ronggowaluyo Telukjambe Timur Karawang 41361 &nbsp; Tel./Fax. (0267) 8403140<br>
                        Site: <a href="https://farmasi.ubpkarawang.ac.id/">https://farmasi.ubpkarawang.ac.id/</a> &nbsp; Email: <a href="mailto:fakultasfarmasi@ubpkarawang.ac.id">fakultasfarmasi@ubpkarawang.ac.id</a>
                    </div>
                </div>
            </td>
            
            {{-- Penyeimbang kolom kanan --}}
            <td style="width: 15%;"></td>
        </tr>
    </table>

  <div class="judul">
    <p>SURAT PERNYATAAN<br>KESANGGUPAN MEMBAYAR UKT</p>
  </div>

  <div class="isi">Saya yang bertanda tangan di bawah ini:</div>

  <div class="identitas">
    <div class="row"><span class="label">Nama Lengkap</span><span class="titik-dua">:</span><span class="garis"></span></div>
    <div class="row"><span class="label">Tempat, Tanggal Lahir</span><span class="titik-dua">:</span><span class="garis"></span></div>
    <div class="row"><span class="label">No. Pendaftaran/Ujian</span><span class="titik-dua">:</span><span class="garis"></span></div>
    <div class="row"><span class="label">Alamat Rumah</span><span class="titik-dua">:</span><span class="garis"></span></div>
  </div>

  <div class="isi">
    Dengan ini menyatakan bahwa apabila saya diterima sebagai Calon Mahasiswa Baru pada:
  </div>

  <div class="prodi-table">
    <div class="row"><span class="label">Program Studi</span><span class="titik-dua">:</span><span class="value">Profesi Apoteker</span></div>
    <div class="row"><span class="label">Fakultas</span><span class="titik-dua">:</span><span class="value">Farmasi</span></div>
    <div class="row"><span class="label">Perguruan Tinggi</span><span class="titik-dua">:</span><span class="value">Universitas Buana Perjuangan Karawang</span></div>
  </div>

  <div class="isi">
    Saya <strong>bersedia dan sanggup untuk membayar seluruh biaya Uang Kuliah Tunggal
    (UKT)</strong> serta biaya pendidikan lainnya yang telah ditetapkan oleh pihak universitas secara penuh
    dan tepat waktu sesuai jadwal yang ditentukan.
  </div>

  <div class="isi">
    Apabila di kemudian hari saya melanggar pernyataan ini atau tidak memenuhi kewajiban
    pembayaran tersebut, saya bersedia menerima sanksi akademik maupun administratif sesuai aturan
    yang berlaku di Universitas Buana Perjuangan Karawang, termasuk pembatalan status saya
    sebagai mahasiswa.
  </div>

  <div class="penutup">
    Demikian surat pernyataan ini saya buat dengan kesadaran penuh, jujur, dan tanpa ada paksaan
    dari pihak mana pun untuk dipergunakan sebagaimana mestinya.
  </div>

  <div class="ttd-block">
    <div class="tempat-tanggal">Karawang, &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;, _____________________ 2026</div>
    <div class="yang-membuat">Yang membuat pernyataan,</div>
    <div class="meterai">(TTD Meterai Rp10.000)</div><br><br>
    <div>( <span class="garis-nama">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> )</div>
  </div>
</div>

  <button class="btn-print" onclick="window.print()">
    🖨️ Cetak / Simpan PDF
  </button>

  <script>
    window.onload = function() {
      setTimeout(function() {
        window.print();
      }, 500);
    }
  </script>
</body>
</html>