<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Surat Pernyataan Praktek Kerja Apoteker - UBP Karawang</title>
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
  .identitas .row { display: flex; align-items: baseline; margin-bottom: 8px; flex-wrap: wrap; }
  .identitas .label { min-width: 170px; font-size: 11pt; }
  .identitas .titik-dua { margin-right: 8px; font-size: 11pt; }
  .identitas .garis { flex: 1; border-bottom: 1px solid #222; height: 1em; min-width: 100px; }

  /* ISI & POIN */
  .isi { font-size: 11pt; line-height: 1.7; text-align: justify; margin-bottom: 14px; }
  ol.poin { margin: 12px 0 12px 24px; padding-right: 10px; }
  ol.poin li { font-size: 11pt; line-height: 1.7; text-align: justify; margin-bottom: 8px; }

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
  
  /* Paksa browser pakai kertas A4 & hapus margin default */
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
        padding: 15mm 25mm 15mm 30mm; /* Padding aman buat dicetak */
        box-shadow: none; 
        margin: 0;
        page-break-after: avoid; 
    }
    
    /* Rapatkan spasi biar muat 1 lembar */
    .isi { line-height: 1.5; margin-bottom: 10px; } 
    ol.poin li { line-height: 1.5; margin-bottom: 6px; }
    .penutup { line-height: 1.5; margin-bottom: 15px; }
    
    /* Kurangin jarak area tanda tangan */
    .ttd-block .yang-membuat { margin-bottom: 40px; } 
    .ttd-block .meterai { margin-bottom: 35px; }

    /* Balikin ukuran Kop Surat ke standar dokumen */
    .kop-logo-img { width: 90px; }
    .kop-text .univ { font-size: 18px; }
    .kop-text .fak { font-size: 16px; }
    .kop-text .akred { font-size: 14px; }
    .kop-text .alamat { font-size: 12px; }
    .ttd-block .garis-nama { min-width: 220px; }

    /* Sembunyikan tombol biru pas di-print */
    .btn-print { display: none !important; }
  }
</style>
</head>
<body>
<div class="page">
  <table class="kop-table">
        <tr>
            <td class="kop-logo-td">
                <img src="{{ asset('images/logo_ubp.png') }}" alt="Logo UBP" class="kop-logo-img">
            </td>
            
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
            
            <td style="width: 15%;"></td>
        </tr>
    </table>

  <div class="judul">
    <p>SURAT PERNYATAAN<br>PRAKTEK KERJA PROFESI APOTEKER (PKPA)</p>
  </div>

  <div class="isi">Saya yang bertanda tangan di bawah ini :</div>

  <div class="identitas">
    <div class="row"><span class="label">Nama Lengkap</span><span class="titik-dua">:</span><span class="garis"></span></div>
    <div class="row"><span class="label">Tempat, Tanggal Lahir</span><span class="titik-dua">:</span><span class="garis"></span></div>
    <div class="row"><span class="label">Alamat Rumah</span><span class="titik-dua">:</span><span class="garis"></span></div>
    <div class="row"><span class="label">Nomor Telepon/HP</span><span class="titik-dua">:</span><span class="garis"></span></div>
    <div class="row"><span class="label">Asal Universitas (S1)</span><span class="titik-dua">:</span><span class="garis"></span></div>
  </div>

  <div class="isi">
    Dengan ini menyatakan bahwa jika saya diterima sebagai mahasiswa baru pada Program Studi
    Pendidikan Profesi Apoteker (PSPPA) Fakultas Farmasi Universitas Buana Perjuangan
    Karawang, saya bersedia dan sanggup untuk:
  </div>

  <ol class="poin">
    <li>Mengikuti seluruh rangkaian kegiatan Praktik Kerja Profesi Apoteker (PKPA) sesuai dengan jadwal, tempat, dan ketentuan yang telah ditetapkan oleh pihak program studi.</li>
    <li>Mematuhi dan menjalankan seluruh peraturan akademik, tata tertib, serta kode etik mahasiswa yang berlaku di lingkungan Program Studi Pendidikan Profesi Apoteker Fakultas Farmasi Universitas Buana Perjuangan Karawang.</li>
    <li>Menerima segala sanksi akademik maupun sanksi lainnya secara hukum yang berlaku, apabila di kemudian hari saya terbukti melakukan pelanggaran terhadap peraturan yang telah ditetapkan.</li>
  </ol>

  <div class="penutup">
    Demikian surat pernyataan ini saya buat dengan kesadaran penuh, jujur, serta tanpa paksaan dari
    pihak mana pun untuk digunakan sebagaimana mestinya.
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