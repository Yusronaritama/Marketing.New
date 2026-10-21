<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SplFileInfo;

class OCRController extends Controller
{
    /**
     * Fungsi utama untuk menerima & memproses DUA file sekaligus.
     */
 public function scan(Request $request)
    {
        $request->validate([
            'file_kk' => 'required|mimes:pdf,jpg,jpeg,png|max:5120',
            'file_ijazah' => 'required|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $results = [];

        // 1. Proses Kartu Keluarga
        $kk_file = $request->file('file_kk');
        $kk_result = $this->processDocument($kk_file, 'kk');
        if (isset($kk_result['error'])) {
            // 👇 UBAH ERROR HANDLING: Kembalikan JSON error, bukan redirect.
            return response()->json([
                'success' => false,
                'message' => 'File Kartu Keluarga: ' . $kk_result['error']
            ], 422);
        }
        $results['kk'] = $kk_result;

        // 2. Proses Ijazah
        $ijazah_file = $request->file('file_ijazah');
        $ijazah_result = $this->processDocument($ijazah_file, 'ijazah');
        if (isset($ijazah_result['error'])) {
            // 👇 UBAH ERROR HANDLING: Kembalikan JSON error, bukan redirect.
            return response()->json([
                'success' => false,
                'message' => 'File Ijazah: ' . $ijazah_result['error']
            ], 422);
        }
        $results['ijazah'] = $ijazah_result;

        // 👇 UBAH RETURN UTAMA: Kembalikan JSON dengan data hasil scan.
        return response()->json([
            'success' => true,
            'results' => $results
        ]);
    }
    /**
     * [BARU] Menyimpan data yang telah dikoreksi dari form untuk mengatasi error "Page Expired".
     */
    public function save(Request $request)
    {
        $corrected_data_kk = $request->input('kk');
        $corrected_data_ijazah = $request->input('ijazah');

        // Untuk saat ini, kita akan log data yang diterima.
        // Di aplikasi nyata, Anda akan menyimpan data ini ke database.
        Log::info('Data Koreksi KK Diterima:', $corrected_data_kk ?? []);
        Log::info('Data Koreksi Ijazah Diterima:', $corrected_data_ijazah ?? []);

        // Arahkan kembali ke halaman utama dengan pesan sukses.
        // Ganti '/' dengan route halaman upload Anda jika berbeda.
        return redirect('/')->with('success', 'Data koreksi berhasil disimpan!');
    }
    
    /**
     * Fungsi terpusat untuk memproses satu file (KK atau Ijazah).
     */
    private function processDocument(SplFileInfo $file, string $docType): array
    {
        // Coba OCR dengan bahasa Inggris, fallback ke Indonesia jika gagal
        $result = $this->performOcrRequest($file, 'eng') ?? $this->performOcrRequest($file, 'ind');

        if (!isset($result['ParsedResults'][0]['ParsedText'])) {
            return ['error' => 'OCR gagal membaca dokumen. Pastikan gambar jelas dan tidak buram.'];
        }

        $original_raw_text = strtoupper($result['ParsedResults'][0]['ParsedText']);
        
        $extractedData = [];
        $docTypeName = '';

        if ($docType === 'kk') {
            $docTypeName = 'Kartu Keluarga';
            if (!str_contains($original_raw_text, 'KARTU KELUARGA')) {
                return ['error' => 'Dokumen tidak valid. Format tidak terlihat seperti Kartu Keluarga.'];
            }
            $extractedData = $this->parseKartuKeluargaData($original_raw_text);

        } elseif ($docType === 'ijazah') {
            $docTypeName = 'Ijazah';
            if (!str_contains($original_raw_text, 'IJAZAH') && !str_contains($original_raw_text, 'SERTIFIKAT')) {
                return ['error' => 'Dokumen tidak valid. Format tidak terlihat seperti Ijazah.'];
            }
            $extractedData = $this->parseIjazahData($original_raw_text); 
        }

        $formatted_text = $this->formatTextWithAi($original_raw_text, $docTypeName);

        return [
            'type' => $docTypeName,
            'data' => $extractedData,
            'raw_text' => $formatted_text,
        ];
    }
    
    /**
     * Menjalankan permintaan ke OCR.space API.
     */
    private function performOcrRequest(SplFileInfo $file, string $language): ?array
    {
        $params = [
            'language'  => $language,
            'isTable'   => 'true',
            'scale'     => 'true',
            'OCREngine' => 2,
        ];

        $response = Http::withHeaders(['apikey' => env('OCR_SPACE_KEY')])
            ->asMultipart()
            ->attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
            ->post('https://api.ocr.space/parse/image', $params);

        $result = $response->json();
        Log::info("Hasil OCR API ($language):", $result);

        if (!isset($result['ParsedResults'][0]['ParsedText']) || (isset($result['IsErroredOnProcessing']) && $result['IsErroredOnProcessing'] === true)) {
            return null;
        }
        
        return $result;
    }
    
    /**
     * Memformat teks mentah OCR menjadi lebih mudah dibaca menggunakan AI.
     */
    private function formatTextWithAi(string $text, string $docType): string
    {
        try {
            $aiPrompt = "Tolong susun ulang dan rapikan teks acak dari hasil OCR sebuah dokumen '$docType' ini menjadi format yang terstruktur dan mudah dibaca. Jangan menghilangkan informasi apapun, hanya perbaiki format dan tata bahasanya agar logis.\n\nTeks OCR Asli:\n" . $text;

            $response = Http::timeout(30)->post('https://api.botcahx.eu.org/api/search/openai-custom', [
                'message' => $aiPrompt,
                'apikey'  => 'Kakoi16'
            ]);

            if ($response->successful()) {
                $result = $response->json();
                Log::info('Hasil AI Formatter:', $result);

                if (isset($result['result']) && !empty(trim($result['result']))) {
                    return $result['result'];
                }
            }
            
            Log::warning('Pemformatan AI gagal atau respons kosong, kembali ke teks asli.');
            return $text;

        } catch (\Exception $e) {
            Log::error('Gagal menghubungi API AI untuk format teks: ' . $e->getMessage());
            return $text;
        }
    }

    /**
     * [DIMAKSIMALKAN & DIPERBAIKI] Ekstrak data dari teks OCR Kartu Keluarga.
     */
    private function parseKartuKeluargaData($text)
    {
        $text_cleaned = preg_replace('/\s+/', ' ', $text);
        
        // Pola 1: Mencari nama dengan daftar penghenti yang lebih lengkap untuk mencegah 'greedy matching'.
        $nama = $this->extractWithRegex($text_cleaned, '/NAMA KEPALA KELUARGA\s*:*\s*([A-Z\s\.\',]+?)\s*(?:ALAMAT|RT|RW|DESA|KELURAHAN|KECAMATAN|KABUPATEN|KOTA|PROVINSI|$)/i');
        
        // Pola 2 (Fallback): Jika pola pertama gagal, gunakan metode pemisahan (split) dengan daftar penghenti yang lengkap.
        if ($nama === '-') {
            $nama_greedy = $this->extractWithRegex($text_cleaned, '/NAMA KEPALA KELUARGA\s*:*\s*([A-Z\s\.\',]+)/i');
            if ($nama_greedy !== '-') {
                // Memisahkan berdasarkan semua kemungkinan label yang mungkin terbawa.
                $nama = trim(preg_split('/(ALAMAT|NOMOR|NIK|RT|RW|DESA|KELURAHAN|KECAMATAN|KABUPATEN|KOTA|PROVINSI)/', $nama_greedy)[0]);
            }
        }
        
        $nomor_kk = $this->extractWithRegex($text_cleaned, '/(?:NO\.|NOMOR KARTU KELUARGA)\s*:*\s*(\d{16})/i');

        $alamat_parts = [];
        
        // Alamat Jalan: Diekstrak secara terpisah.
        $alamat_jalan = $this->extractWithRegex($text_cleaned, '/ALAMAT\s*:*\s*([\w\s\.\/XNO,]+?)\s*(?:RT|RW|DESA|KELURAHAN|$)/i');
        if ($alamat_jalan !== '-') $alamat_parts['ALAMAT'] = trim($alamat_jalan);

        // RT/RW: Mencari 'RT/RW' diikuti oleh angka, dihentikan oleh label berikutnya.
        $rt_rw = $this->extractWithRegex($text_cleaned, '/RT\/RW\s*:*\s*([\d\s\.\/:]+?)(?:\s*DESA|\s*KELURAHAN|$)/i');
        if ($rt_rw !== '-') {
            $rt_rw_clean = trim(preg_replace('/[^\d\/]/', '', $rt_rw));
            if (!empty($rt_rw_clean)) $alamat_parts['RT/RW'] = 'RT/RW ' . $rt_rw_clean;
        }

        // Desa/Kelurahan
        $desa = $this->extractWithRegex($text_cleaned, '/(?:DESA\/KELURAHAN)\s*:*\s*([\w\s]+?)\s*(?:KECAMATAN|$)/i');
        if ($desa !== '-') $alamat_parts['DESA'] = 'DESA/KEL ' . trim($desa);

        // Kecamatan
        $kecamatan = $this->extractWithRegex($text_cleaned, '/KECAMATAN\s*:*\s*([\w\s]+?)\s*(?:KABUPATEN|KOTA|$)/i');
        if ($kecamatan !== '-') $alamat_parts['KECAMATAN'] = 'KEC. ' . trim($kecamatan);

        // Kabupaten/Kota
        $kab_kota = $this->extractWithRegex($text_cleaned, '/KABUPATEN\/KOTA\s*:*\s*([\w\s]+?)\s*(?:PROVINSI|$)/i');
        if ($kab_kota !== '-') $alamat_parts['KAB_KOTA'] = 'KAB/KOTA ' . trim($kab_kota);

        // Provinsi
        $provinsi = $this->extractWithRegex($text_cleaned, '/PROVINSI\s*:*\s*([\w\s]+?)\s*(?:KODE POS|$)/i');
        if ($provinsi !== '-') $alamat_parts['PROVINSI'] = 'PROV. ' . trim(str_replace('*','', $provinsi));

        // Kode Pos
        $kode_pos = $this->extractWithRegex($text_cleaned, '/KODE POS\s*:*\s*(\d{5})/i');
        if ($kode_pos !== '-') $alamat_parts['KODE_POS'] = 'KODE POS ' . trim($kode_pos);

        $alamat = !empty($alamat_parts) ? implode(', ', $alamat_parts) : '-';

        return [
            'Nama Ayah :'   => $nama,
            'Alamat :' => $alamat,
        ];
    }
    
    /**
     * [DIMAKSIMALKAN] Fungsi untuk mengekstrak data dari teks OCR Ijazah.
     */
    private function parseIjazahData($text)
    {
        // === EKSTRAKSI NAMA LENGKAP (dengan beberapa pola fallback) ===
        // Pola 1: Untuk format dimana NAMA berada di atas label "NAMA"
        $nama = $this->extractWithRegex($text, '/([A-Z\s\.\',]{5,})\R\s*NAMA\s*(?!\s*ORANG TUA)/');

        // Pola 2: Untuk format dimana NAMA berada setelah label "NAMA" (lebih fleksibel)
        if ($nama === '-') {
            $nama = $this->extractWithRegex($text, '/NAMA\s*:*\s+([A-Z\s\.\',]+?)\s*(?:\R|TEMPAT|NAMA ORANG TUA|NOMOR INDUK)/');
        }

        // Pola 3 (BARU & Kuat): Setelah kata "menerangkan bahwa", cari nama hingga "tempat dan tanggal lahir"
        if ($nama === '-') {
            if (preg_match('/MENERANGKAN BAHWA:\s*(.*?)\s*TEMPAT DAN TANGGAL LAHIR/s', $text, $matches)) {
                $lines = array_filter(array_map('trim', explode("\n", trim($matches[1]))));
                $potential_name = end($lines);
                // Membersihkan label 'NAMA' jika masih terbawa
                $nama = trim(preg_replace('/^NAMA\s*:*\s*/', '', $potential_name));
            }
            if(empty($nama) || strlen($nama) < 3) $nama = '-';
        }

        // Pola 4: Fallback lama setelah kata "MENYATAKAN BAHWA"
        if ($nama === '-') {
            $nama = $this->extractWithRegex($text, '/MENYATAKAN BAHWA\s+([A-Z\s\.\',]+)\s+LAHIR/');
        }

        $text_cleaned = preg_replace('/\s+/', ' ', $text);
        
        // === EKSTRAKSI NOMOR IJAZAH (dengan beberapa pola fallback) ===
        $nomor_ijazah = $this->extractWithRegex($text_cleaned, '/(?:NOMOR IJAZAH|NO\.|NOMOR SERI)\s*:*\s*([\w\s\/\.-]+)/');
        
        if ($nomor_ijazah === '-') {
            $nomor_ijazah = $this->extractWithRegex($text_cleaned, '/(DN-\d{2}\s[A-Z]{2}\s\d+)/');
        }

        if ($nomor_ijazah === '-') {
            $nomor_ijazah = $this->extractWithRegex($text_cleaned, '/(M-SMK\/\d{2}-\d\/\d+)/');
        }
        
        // === EKSTRAKSI DATA LAIN ===
        $institusi = $this->extractWithRegex($text_cleaned, '/(UNIVERSITAS|SEKOLAH TINGGI|INSTITUT|POLITEKNIK|AKADEMI|SEKOLAH MENENGAH (?:KEJURUAN|ATAS)|SMK|SMA)[\s\w\.\-]+/i');
        
        $tahun_lulus = $this->extractWithRegex($text_cleaned, '/\d{1,2}(?:-|\s)\w+(?:-|\s)(\d{4})/');
        if ($tahun_lulus === '-') {
             $tahun_lulus = $this->extractWithRegex($text_cleaned, '/(20\d{2})/');
        }

        return [
            'Nama Lengkap' => $nama !== '-' ? ucwords(strtolower($nama)) : '-',
            'Nomor Ijazah' => $nomor_ijazah,
            'Institusi'    => $institusi !== '-' ? ucwords(strtolower(trim($institusi))) : '-',
            'Tahun Lulus'  => $tahun_lulus,
        ];
    }

    /**
     * Fungsi pembantu untuk mengekstrak data menggunakan Regex.
     */
    private function extractWithRegex($text, $pattern)
    {
        if (preg_match($pattern, $text, $matches)) {
            return trim($matches[1] ?? $matches[0]);
        }
        return '-';
    }
}