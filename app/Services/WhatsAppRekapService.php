<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Exception;

class WhatsAppRekapService
{
    public function kirimLaporanHarian()
    {
        $hariIni = Carbon::today();

        // 1. Hitung Maba yang LUNAS HARI INI
        $jumlahHariIni = DB::table('slik_billings')
            ->where('order_id', 'like', 'UKT-%')
            ->whereIn(DB::raw('LOWER(status)'), ['paid', 'lunas'])
            ->whereDate('updated_at', $hariIni)
            ->distinct('user_id')
            ->count('user_id');

        // 2. Hitung TOTAL Keseluruhan Maba LUNAS per Prodi
        // Langsung join dari slik_billings ke program_studi, TANPA lewat tabel users
        $rekapProdi = DB::table('slik_billings')
            ->join('program_studi', 'slik_billings.kode_prodi', '=', 'program_studi.kode_prodi')
            ->where('slik_billings.order_id', 'like', 'UKT-%')
            ->whereIn(DB::raw('LOWER(slik_billings.status)'), ['paid', 'lunas'])
            ->select('program_studi.nama_prodi', DB::raw('count(DISTINCT slik_billings.user_id) as total'))
            ->groupBy('program_studi.nama_prodi')
            ->orderBy('total', 'desc')
            ->get();

        // 2. Rangkai Pesan
        $pesan = "📢 *REKAP HARIAN PMB UBP* 📢\n";
        $pesan .= "📅 Tanggal: " . $hariIni->translatedFormat('d F Y') . "\n\n";
        $pesan .= "🔥 Pendaftar Baru Hari Ini: *$jumlahHariIni Orang*\n\n";
        $pesan .= "📊 *Total per Prodi:*\n";

        foreach ($rekapProdi as $prodi) {
            $pesan .= "▪️ " . $prodi->nama_prodi . ": " . $prodi->total . " mhs\n";
        }
        
        // 3. Konfigurasi API CallMeBot
        $nomorWaGua = "+6285795950577"; // GANTI NOMOR LU
        $apiKey = "API_KEY_LU"; // GANTI API KEY DARI BOT
        $pesanEncoded = urlencode($pesan);
        $urlApi = "https://api.callmebot.com/whatsapp.php?phone={$nomorWaGua}&text={$pesanEncoded}&apikey={$apiKey}";

        // 4. Eksekusi Tembak API
        $response = Http::timeout(5)->get($urlApi);

        if (!$response->successful()) {
            throw new Exception('API CallMeBot gagal merespon.');
        }

        return true; // Berhasil
    }
}