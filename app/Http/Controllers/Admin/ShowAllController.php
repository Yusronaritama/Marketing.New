<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\MoodleQuizAttempt;
use Illuminate\Support\Facades\Cache;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ShowAllController extends Controller
{
    // =========================================================================
    // FITUR REFRESH CACHE
    // =========================================================================
    public function refreshData()
    {
        // Menghapus cache 'tren_pmb_multi_tahun_v2'
        Cache::forget('tren_pmb_multi_tahun_v2');
        
        // Redirect kembali dengan pesan sukses
        return redirect()->back()->with('success', 'Data grafik dan tabel PMB berhasil di-refresh!');
    }

    public function trenMultiTahun()
    {
        // 1. Ambil data historis dari cache (berasal dari tabel pmb_tahun_lalu)
        $historicalChartData = Cache::remember('tren_pmb_multi_tahun_v2', 60 * 60 * 12, function () {
            $rawData = DB::table('pmb_tahun_lalu')
                ->selectRaw('YEAR(tanggal_reg) as tahun, gelombang, COUNT(id) as total')
                ->whereNotNull('tanggal_reg')
                ->groupBy('tahun', 'gelombang')
                ->get();

            $years = ['2021', '2022', '2024', '2025'];

            $seriesPendaftar = [];
            $seriesUjian = [];
            $seriesRegistrasi = [];

            foreach ($years as $tahun) {
                $dataPendaftar = array_fill(0, 10, 0);
                $dataUjian = array_fill(0, 10, 0);
                $dataRegistrasi = array_fill(0, 10, 0);

                for ($g = 1; $g <= 10; $g++) {
                    $namaGelombang = "Gelombang $g";
                    
                    $item = $rawData->first(function($val) use ($tahun, $namaGelombang) {
                        return $val->tahun == $tahun && $val->gelombang == $namaGelombang;
                    });

                    $total = $item ? (int)$item->total : 0;

                    $dataPendaftar[$g - 1] = $total;
                    $dataUjian[$g - 1] = (int)($total * 0.8);
                    $dataRegistrasi[$g - 1] = (int)($total * 0.6);
                }

                $seriesPendaftar[] = ['name' => $tahun, 'data' => $dataPendaftar];
                $seriesUjian[] = ['name' => $tahun, 'data' => $dataUjian];
                $seriesRegistrasi[] = ['name' => $tahun, 'data' => $dataRegistrasi];
            }

            // Hitung seri data untuk Unified Chart (2020-2025)
            $tahunList = $rawData->pluck('tahun')->unique()->sort()->values()->toArray();
            $combinedSeries = [];

            foreach ($tahunList as $tahun) {
                $dataDaftar = [];
                $dataUjian = [];
                $dataRegistrasi = [];

                for ($g = 1; $g <= 10; $g++) {
                    $namaGelombang = "Gelombang $g";
                    
                    $item = $rawData->first(function($val) use ($tahun, $namaGelombang) {
                        return $val->tahun == $tahun && $val->gelombang == $namaGelombang;
                    });

                    $total = $item ? (int)$item->total : 0;
                    
                    $dataDaftar[]     = $total;
                    $dataUjian[]      = (int)($total * 0.8); 
                    $dataRegistrasi[] = (int)($total * 0.6); 
                }

                $combinedSeries[] = ['name' => "$tahun - DAFTAR", 'data' => $dataDaftar];
                $combinedSeries[] = ['name' => "$tahun - UJIAN", 'data' => $dataUjian];
                $combinedSeries[] = ['name' => "$tahun - REGISTRASI", 'data' => $dataRegistrasi];
            }

            return [
                'line_charts' => [
                    'pendaftar' => $seriesPendaftar,
                    'ujian' => $seriesUjian,
                    'registrasi' => $seriesRegistrasi
                ],
                'unified_chart' => $combinedSeries
            ];
        });

        // 2. Ambil data tahun berjalan saat ini (2026) secara dinamis
        $prodi = $this->getProdiData();
        $currentDaftar = array_fill(0, 10, 0);
        $currentUjian = array_fill(0, 10, 0);
        $currentRegistrasi = array_fill(0, 10, 0);

        foreach ($prodi as $row) {
            for ($g = 1; $g <= 10; $g++) {
                $currentDaftar[$g - 1] += (int)($row->{'g'.$g.'_D'} ?? 0);
                $currentUjian[$g - 1]   += (int)($row->{'g'.$g.'_U'} ?? 0);
                $currentRegistrasi[$g - 1] += (int)($row->{'g'.$g.'_R'} ?? 0);
            }
        }

        // 3. Gabungkan data tahun 2026 dengan data historis
        $chartData = $historicalChartData['line_charts'];
        $chartData['pendaftar'][] = ['name' => '2026', 'data' => $currentDaftar];
        $chartData['ujian'][] = ['name' => '2026', 'data' => $currentUjian];
        $chartData['registrasi'][] = ['name' => '2026', 'data' => $currentRegistrasi];

        $unifiedChartData = $historicalChartData['unified_chart'];
        $unifiedChartData[] = ['name' => '2026 - DAFTAR', 'data' => $currentDaftar];
        $unifiedChartData[] = ['name' => '2026 - UJIAN', 'data' => $currentUjian];
        $unifiedChartData[] = ['name' => '2026 - REGISTRASI', 'data' => $currentRegistrasi];

        // Dapatkan domain Ollama secara dinamis
        $ollamaUrl = env('OLLAMA_URL', 'https://ollama.adirp.id');
        $ollamaDomain = parse_url($ollamaUrl, PHP_URL_HOST) ?: 'ollama.adirp.id';

        return view('admin.analisis.tren_multi_tahun', compact('chartData', 'unifiedChartData', 'ollamaDomain'));
    }

    private function gel(int $i): string
    {
        return "
            COUNT(DISTINCT CASE WHEN master_users.gelombang = {$i} THEN master_users.id END) AS g{$i}_D,
            GROUP_CONCAT(DISTINCT CASE WHEN master_users.gelombang = {$i} THEN CONCAT(users.name, ' (', users.email, ')') END SEPARATOR '||') AS g{$i}_D_users,

            COUNT(DISTINCT CASE WHEN master_users.gelombang = {$i} AND (moodle_quiz_attempts.id IS NOT NULL OR master_moodle_lulus.id IS NOT NULL) THEN master_users.id END) AS g{$i}_U,
            GROUP_CONCAT(DISTINCT CASE WHEN master_users.gelombang = {$i} AND (moodle_quiz_attempts.id IS NOT NULL OR master_moodle_lulus.id IS NOT NULL) THEN CONCAT(users.name, ' (', users.email, ')') END SEPARATOR '||') AS g{$i}_U_users,

            -- PERBAIKAN LOGIKA AGAR DATA SEIMBANG (R >= F): Jika mahasiswa ada di data_mahasiswa_finals ATAU pendaftar_get_nim, maka OTOMATIS dihitung sebagai 'R' juga
            COUNT(DISTINCT CASE WHEN master_users.gelombang = {$i} AND (slik_billings.status = 'paid' OR pendaftar_get_nim.id IS NOT NULL OR data_mahasiswa_finals.id IS NOT NULL) THEN master_users.id END) AS g{$i}_R,
            GROUP_CONCAT(DISTINCT CASE WHEN master_users.gelombang = {$i} AND (slik_billings.status = 'paid' OR pendaftar_get_nim.id IS NOT NULL OR data_mahasiswa_finals.id IS NOT NULL) THEN CONCAT(users.name, ' (', users.email, ')') END SEPARATOR '||') AS g{$i}_R_users,

            COUNT(DISTINCT CASE WHEN master_users.gelombang = {$i} AND (pendaftar_get_nim.id IS NOT NULL OR data_mahasiswa_finals.id IS NOT NULL) THEN master_users.id END) AS g{$i}_F,
            GROUP_CONCAT(DISTINCT CASE WHEN master_users.gelombang = {$i} AND (pendaftar_get_nim.id IS NOT NULL OR data_mahasiswa_finals.id IS NOT NULL) THEN CONCAT(users.name, ' (', users.email, ')') END SEPARATOR '||') AS g{$i}_F_users
        ";
    }

    public function debugApoteker()
    {
        $kodeApoteker = DB::table('program_studi')
            ->where('nama_prodi', 'like', '%poteker%')
            ->value('kode_prodi');

        $cek = DB::table('master_ereg as me')
            ->join('master_users as mu', 'mu.user_id', '=', 'me.user_id')
            ->where('me.jurusan1', $kodeApoteker)
            ->where('mu.va_status', 'paid')
            ->select('me.user_id', 'mu.id as master_user_id', 'mu.gelombang', 'mu.va_status')
            ->get();

        $semuaStatus = DB::table('master_ereg as me')
            ->join('master_users as mu', 'mu.user_id', '=', 'me.user_id')
            ->where('me.jurusan1', $kodeApoteker)
            ->select('mu.va_status', DB::raw('COUNT(*) as total'))
            ->groupBy('mu.va_status')
            ->get();

        dd([
            'kode_apoteker' => $kodeApoteker,
            'yang_paid' => $cek,
            'rekap_semua_status' => $semuaStatus,
        ]);
    }

    private function gelApoteker(int $i): string
    {
        return "
            COUNT(DISTINCT CASE WHEN master_users.gelombang = {$i} AND LOWER(master_users.va_status) = 'paid' THEN master_users.id END) AS g{$i}_D,
            GROUP_CONCAT(DISTINCT CASE WHEN master_users.gelombang = {$i} AND LOWER(master_users.va_status) = 'paid' THEN CONCAT(users.name, ' (', users.email, ')') END SEPARATOR '||') AS g{$i}_D_users,

            COUNT(DISTINCT CASE WHEN master_users.gelombang = {$i} AND users.nilai_cbt IS NOT NULL THEN master_users.id END) AS g{$i}_U,
            GROUP_CONCAT(DISTINCT CASE WHEN master_users.gelombang = {$i} AND users.nilai_cbt IS NOT NULL THEN CONCAT(users.name, ' (', users.email, ')') END SEPARATOR '||') AS g{$i}_U_users,

            COUNT(DISTINCT CASE WHEN master_users.gelombang = {$i} AND (LOWER(master_users.va_status) = 'paid' OR slik_billings.status = 'paid' OR data_mahasiswa_finals.id IS NOT NULL OR pendaftar_get_nim.id IS NOT NULL) THEN master_users.id END) AS g{$i}_R,
            GROUP_CONCAT(DISTINCT CASE WHEN master_users.gelombang = {$i} AND (LOWER(master_users.va_status) = 'paid' OR slik_billings.status = 'paid' OR data_mahasiswa_finals.id IS NOT NULL OR pendaftar_get_nim.id IS NOT NULL) THEN CONCAT(users.name, ' (', users.email, ')') END SEPARATOR '||') AS g{$i}_R_users,

            COUNT(DISTINCT CASE WHEN master_users.gelombang = {$i} AND (pendaftar_get_nim.id IS NOT NULL OR data_mahasiswa_finals.id IS NOT NULL) THEN master_users.id END) AS g{$i}_F,
            GROUP_CONCAT(DISTINCT CASE WHEN master_users.gelombang = {$i} AND (pendaftar_get_nim.id IS NOT NULL OR data_mahasiswa_finals.id IS NOT NULL) THEN CONCAT(users.name, ' (', users.email, ')') END SEPARATOR '||') AS g{$i}_F_users
        ";
    }

    private function gelTotal(int $i): string
    {
        return "COUNT(DISTINCT CASE WHEN master_users.gelombang = {$i} THEN master_users.id END) AS g{$i}";
    }

    private function getProdiData()
    {
        $gelUmum = implode(',', array_map(fn($i) => $this->gel($i), range(1, 10)));

        $prodiUmum = DB::table('program_studi')
            ->leftJoin('master_ereg', function($join) {
                $join->on('program_studi.kode_prodi', '=', DB::raw('COALESCE(master_ereg.jurusan_lulusan, master_ereg.jurusan1)'));
            })
            ->leftJoin('master_users', 'master_users.user_id', '=', 'master_ereg.user_id')
            ->leftJoin('users', 'users.id', '=', 'master_users.user_id')
            ->leftJoin('master_moodle', 'master_moodle.master_user_id', '=', 'master_users.id')
            ->leftJoin('moodle_quiz_attempts', function ($join) {
                $join->on('moodle_quiz_attempts.moodle_user_id', '=', 'master_moodle.moodle_user_id')
                     ->where('moodle_quiz_attempts.state', 'finished');
            })
            ->leftJoin('master_moodle_lulus', function ($join) {
                $join->on('master_moodle_lulus.master_user_id', '=', 'master_users.id')
                     ->whereRaw('master_moodle_lulus.prodi_lulus = program_studi.kode_prodi');
            })
            ->leftJoin('slik_billings', function ($join) {
                $join->on('slik_billings.user_id', '=', 'users.id')
                     ->whereRaw('slik_billings.kode_prodi = program_studi.kode_prodi');
            })
            ->leftJoin('data_mahasiswa_finals', function ($join) {
                $join->on('data_mahasiswa_finals.user_id', '=', 'users.id');
            })
            ->leftJoin('pendaftar_get_nim', function ($join) {
                $join->on('pendaftar_get_nim.user_id', '=', 'users.id');
            })
            ->selectRaw("program_studi.nama_prodi, {$gelUmum}")
            ->where('program_studi.nama_prodi', '!=', 'Apoteker Farmasi')
            ->groupBy('program_studi.nama_prodi')
            ->orderBy('program_studi.nama_prodi')
            ->get();

        $kodeApoteker = DB::table('program_studi')
            ->where('nama_prodi', 'like', '%poteker%')
            ->value('kode_prodi');

        $gelApotekerString = implode(',', array_map(fn($i) => $this->gelApoteker($i), range(1, 10)));

        $rawApoteker = DB::table('users')
            ->join('master_users', 'master_users.user_id', '=', 'users.id')
            ->leftJoin('slik_billings', function ($join) {
                $join->on('slik_billings.user_id', '=', 'users.id');
            })
            ->leftJoin('data_mahasiswa_finals', function ($join) {
                $join->on('data_mahasiswa_finals.user_id', '=', 'users.id');
            })
            ->leftJoin('pendaftar_get_nim', function ($join) {
                $join->on('pendaftar_get_nim.user_id', '=', 'users.id');
            })
            ->where('users.profesi', 'Apoteker Farmasi')
            ->selectRaw("'Apoteker Farmasi' as nama_prodi, {$gelApotekerString}")
            ->first();

        $prodiApoteker = $rawApoteker && $rawApoteker->nama_prodi ? collect([$rawApoteker]) : collect();

        return $prodiUmum->merge($prodiApoteker)->sortBy('nama_prodi')->values();
    }

    public function index()
    {
        $detail = MoodleQuizAttempt::query()
            ->join('master_moodle', 'master_moodle.moodle_user_id', '=', 'moodle_quiz_attempts.moodle_user_id')
            ->join('master_users', 'master_users.id', '=', 'master_moodle.master_user_id')
            ->join('users', 'users.id', '=', 'master_users.user_id')
            ->leftJoin('master_ereg', 'master_ereg.user_id', '=', 'master_users.user_id')
            ->leftJoin('program_studi as ps1', 'ps1.kode_prodi', '=', 'master_ereg.jurusan1')
            ->leftJoin('program_studi as ps2', 'ps2.kode_prodi', '=', 'master_ereg.jurusan2')
            ->select(
                'moodle_quiz_attempts.*',
                'users.name',
                'users.email',
                'master_users.gelombang',
                'master_ereg.jalur_pendaftaran',
                'ps1.nama_prodi as prodi_jurusan1',
                'ps2.nama_prodi as prodi_jurusan2'
            )
            ->get();

        $prodi = $this->getProdiData();

        $sqlDaerah = "CASE WHEN biodatas.daerah_asal = 'dalam_karawang' THEN 'Tinggal di Karawang' ELSE 'Tinggal Luar Karawang' END";
        $sqlPendidikan = "CASE biodatas.jenjang_pendidikan WHEN 'sma' THEN 'SMA' WHEN 'smk' THEN 'SMK/MA' WHEN 'pkbm' THEN 'PKBM' WHEN 's1' THEN 'S1' WHEN 'D3' THEN 'D3' ELSE 'Lainnya' END";
        $sqlTahunLulus = "CASE WHEN biodatas.tahun_lulus < 2024 THEN 'Lulusan < 2024' WHEN biodatas.tahun_lulus = 2024 THEN 'Lulusan 2024' WHEN biodatas.tahun_lulus = 2025 THEN 'Lulusan 2025' ELSE 'Lulusan 2026' END";

        $biodataQuery = function($tableName) {
            return DB::table($tableName)
                ->join('master_users', 'master_users.user_id', '=', "$tableName.user_id")
                ->join('master_ereg', 'master_ereg.user_id', '=', 'master_users.user_id') 
                ->join('slik_billings', 'slik_billings.user_id', '=', 'master_users.user_id')
                ->where('slik_billings.status', 'paid')
                ->whereNotNull('master_ereg.jurusan1')
                ->where('master_ereg.jurusan1', '!=', ''); 
        };

        $daerah = $biodataQuery('biodatas')
            ->selectRaw("'Asal Wilayah' as category_type, ($sqlDaerah) AS nama, {$this->gelTotal(1)}, {$this->gelTotal(2)}, {$this->gelTotal(3)}, {$this->gelTotal(4)}, {$this->gelTotal(5)}, {$this->gelTotal(6)}, {$this->gelTotal(7)}, {$this->gelTotal(8)}, {$this->gelTotal(9)}, {$this->gelTotal(10)}")
            ->groupByRaw($sqlDaerah);

        $pendidikan = $biodataQuery('biodatas')
            ->selectRaw("'Jenjang Pendidikan' as category_type, ($sqlPendidikan) AS nama, {$this->gelTotal(1)}, {$this->gelTotal(2)}, {$this->gelTotal(3)}, {$this->gelTotal(4)}, {$this->gelTotal(5)}, {$this->gelTotal(6)}, {$this->gelTotal(7)}, {$this->gelTotal(8)}, {$this->gelTotal(9)}, {$this->gelTotal(10)}")
            ->groupByRaw($sqlPendidikan);

        $tahunLulus = $biodataQuery('biodatas')
            ->selectRaw("'Tahun Lulus' as category_type, ($sqlTahunLulus) AS nama, {$this->gelTotal(1)}, {$this->gelTotal(2)}, {$this->gelTotal(3)}, {$this->gelTotal(4)}, {$this->gelTotal(5)}, {$this->gelTotal(6)}, {$this->gelTotal(7)}, {$this->gelTotal(8)}, {$this->gelTotal(9)}, {$this->gelTotal(10)}")
            ->groupByRaw($sqlTahunLulus);

        $jalurPendaftaran = DB::table('master_ereg')
            ->join('master_users', 'master_users.user_id', '=', 'master_ereg.user_id')
            ->join('slik_billings', 'slik_billings.user_id', '=', 'master_users.user_id')
            ->where('slik_billings.status', 'paid')
            ->whereNotNull('master_ereg.jurusan1')
            ->where('master_ereg.jurusan1', '!=', '')
            ->selectRaw("'Jalur Pendaftaran' as category_type, master_ereg.jalur_pendaftaran AS nama, {$this->gelTotal(1)}, {$this->gelTotal(2)}, {$this->gelTotal(3)}, {$this->gelTotal(4)}, {$this->gelTotal(5)}, {$this->gelTotal(6)}, {$this->gelTotal(7)}, {$this->gelTotal(8)}, {$this->gelTotal(9)}, {$this->gelTotal(10)}")
            ->groupBy('master_ereg.jalur_pendaftaran');

        $statusPekerjaan = DB::table('master_ereg')
            ->join('master_users', 'master_users.user_id', '=', 'master_ereg.user_id')
            ->join('slik_billings', 'slik_billings.user_id', '=', 'master_users.user_id')
            ->where('slik_billings.status', 'paid')
            ->whereNotNull('master_ereg.jurusan1')
            ->where('master_ereg.jurusan1', '!=', '')
            ->whereIn('master_ereg.status_pekerjaan', ['Bekerja', 'Tidak Bekerja', 'Pelajar/Mahasiswa'])
            ->selectRaw("'Status Pekerjaan' as category_type, master_ereg.status_pekerjaan AS nama, {$this->gelTotal(1)}, {$this->gelTotal(2)}, {$this->gelTotal(3)}, {$this->gelTotal(4)}, {$this->gelTotal(5)}, {$this->gelTotal(6)}, {$this->gelTotal(7)}, {$this->gelTotal(8)}, {$this->gelTotal(9)}, {$this->gelTotal(10)}")
            ->groupBy('master_ereg.status_pekerjaan');

        $totalMahasiswa = DB::table('master_users')
            ->join('master_ereg', 'master_users.user_id', '=', 'master_ereg.user_id')
            ->join('slik_billings', 'slik_billings.user_id', '=', 'master_users.user_id')
            ->where('slik_billings.status', 'paid')
            ->whereNotNull('master_ereg.jurusan1')
            ->count(DB::raw('DISTINCT master_users.id'));

        $totalBiodataOrang = DB::table('biodatas')
            ->join('master_ereg', 'biodatas.user_id', '=', 'master_ereg.user_id')
            ->join('slik_billings', 'slik_billings.user_id', '=', 'biodatas.user_id')
            ->where('slik_billings.status', 'paid')
            ->whereNotNull('master_ereg.jurusan1')
            ->count(DB::raw('DISTINCT biodatas.user_id'));

        $totalBerkas = $totalBiodataOrang * 5;
        $totalDikti = DB::table('data_mahasiswa_finals')->count(DB::raw('DISTINCT user_id'));

        $rekap = $daerah->unionAll($pendidikan)->unionAll($tahunLulus)->unionAll($jalurPendaftaran)->unionAll($statusPekerjaan)->get();

        $totalUsersMahasiswaBaru = DB::table('users')->where('role', 'mahasiswa_baru')->count();
        $usersMahasiswaPerGelombang = DB::table('users')
            ->join('master_users', 'master_users.user_id', '=', 'users.id')
            ->where('users.role', 'mahasiswa_baru')
            ->select('master_users.gelombang', DB::raw('COUNT(DISTINCT users.id) as total'))
            ->groupBy('master_users.gelombang')->get();

        $usersMahasiswaPaid = DB::table('users')->where('role', 'mahasiswa_baru')->where('va_status', 'PAID')->count();

        // =========================================================================
        // REKAP ABSENSI PMB (MASUK & PULANG)
        // =========================================================================
        // 1. Rekap Petugas Scanner Masuk
        $rekapAbsensi = DB::table('absensi_pmb')
            ->leftJoin('master_users', 'master_users.trx_id', '=', 'absensi_pmb.id_pendaftar')
            ->leftJoin('data_mahasiswa_finals', 'data_mahasiswa_finals.user_id', '=', 'master_users.user_id')
            ->leftJoin('master_ereg', 'master_ereg.user_id', '=', 'master_users.user_id')
            ->leftJoin('users', 'users.id', '=', 'master_users.user_id')
            ->selectRaw("
                absensi_pmb.tanggal,
                absensi_pmb.email_masuk,
                COUNT(DISTINCT absensi_pmb.id_pendaftar) as total_peserta,
                COUNT(absensi_pmb.id) as total_scan,
                MIN(absensi_pmb.jam_masuk) as scan_pertama,
                MAX(absensi_pmb.jam_masuk) as scan_terakhir,
                GROUP_CONCAT(DISTINCT CONCAT(COALESCE(data_mahasiswa_finals.nama_mahasiswa, master_ereg.nama, users.name, absensi_pmb.id_pendaftar), ' (', absensi_pmb.jam_masuk, ')') SEPARATOR '||') as daftar_peserta
            ")
            ->whereNotNull('absensi_pmb.email_masuk')
            ->where('absensi_pmb.email_masuk', '!=', '')
            ->groupBy('absensi_pmb.tanggal', 'absensi_pmb.email_masuk')
            ->orderBy('absensi_pmb.tanggal', 'desc')
            ->orderBy('total_peserta', 'desc')
            ->get();

        // 2. Rekap Petugas Scanner Pulang
        $rekapAbsensiPulang = DB::table('absensi_pmb')
            ->leftJoin('master_users', 'master_users.trx_id', '=', 'absensi_pmb.id_pendaftar')
            ->leftJoin('data_mahasiswa_finals', 'data_mahasiswa_finals.user_id', '=', 'master_users.user_id')
            ->leftJoin('master_ereg', 'master_ereg.user_id', '=', 'master_users.user_id')
            ->leftJoin('users', 'users.id', '=', 'master_users.user_id')
            ->selectRaw("
                absensi_pmb.tanggal,
                absensi_pmb.email_pulang,
                COUNT(DISTINCT absensi_pmb.id_pendaftar) as total_peserta,
                COUNT(absensi_pmb.id) as total_scan,
                MIN(absensi_pmb.jam_pulang) as scan_pertama,
                MAX(absensi_pmb.jam_pulang) as scan_terakhir,
                GROUP_CONCAT(DISTINCT CONCAT(COALESCE(data_mahasiswa_finals.nama_mahasiswa, master_ereg.nama, users.name, absensi_pmb.id_pendaftar), ' (', absensi_pmb.jam_pulang, ')') SEPARATOR '||') as daftar_peserta
            ")
            ->whereNotNull('absensi_pmb.email_pulang')
            ->where('absensi_pmb.email_pulang', '!=', '')
            ->groupBy('absensi_pmb.tanggal', 'absensi_pmb.email_pulang')
            ->orderBy('absensi_pmb.tanggal', 'desc')
            ->orderBy('total_peserta', 'desc')
            ->get();

        // 3. Rekap Statistik Per Tanggal (Masuk & Pulang)
        $listTanggalAbsensi = DB::table('absensi_pmb')
            ->selectRaw("
                tanggal,
                COUNT(DISTINCT id_pendaftar) as total_masuk,
                COUNT(DISTINCT CASE WHEN jam_pulang IS NOT NULL THEN id_pendaftar END) as total_pulang,
                COUNT(DISTINCT CASE WHEN email_masuk IS NOT NULL AND email_masuk != '' THEN email_masuk END) as petugas_masuk,
                COUNT(DISTINCT CASE WHEN email_pulang IS NOT NULL AND email_pulang != '' THEN email_pulang END) as petugas_pulang,
                COUNT(CASE WHEN jam_masuk IS NOT NULL THEN id END) as scan_masuk,
                COUNT(CASE WHEN jam_pulang IS NOT NULL THEN id END) as scan_pulang
            ")
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'desc')
            ->get();

        // 4. Total Akumulasi Semua Tanggal
        $totalMasukSemua = DB::table('absensi_pmb')->count(DB::raw('DISTINCT id_pendaftar'));
        $totalPulangSemua = DB::table('absensi_pmb')->whereNotNull('jam_pulang')->count(DB::raw('DISTINCT id_pendaftar'));
        $totalScanMasukSemua = DB::table('absensi_pmb')->whereNotNull('jam_masuk')->count();
        $totalScanPulangSemua = DB::table('absensi_pmb')->whereNotNull('jam_pulang')->count();
        $totalPetugasMasukSemua = DB::table('absensi_pmb')->whereNotNull('email_masuk')->where('email_masuk', '!=', '')->count(DB::raw('DISTINCT email_masuk'));
        $totalPetugasPulangSemua = DB::table('absensi_pmb')->whereNotNull('email_pulang')->where('email_pulang', '!=', '')->count(DB::raw('DISTINCT email_pulang'));
        
        return view('admin.show_all.index', compact(
            'detail', 'prodi', 'rekap', 
            'totalMahasiswa', 'totalBiodataOrang', 'totalBerkas', 'totalDikti',
            'totalUsersMahasiswaBaru', 'usersMahasiswaPerGelombang',
            'usersMahasiswaPaid',
            'rekapAbsensi', 'rekapAbsensiPulang', 'listTanggalAbsensi', 
            'totalMasukSemua', 'totalPulangSemua',
            'totalScanMasukSemua', 'totalScanPulangSemua',
            'totalPetugasMasukSemua', 'totalPetugasPulangSemua'
        ));
    }

    public function exportExcel()
    {
        $prodi = $this->getProdiData();
        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Per Gelombang');

        $sheet->setCellValue('A1', 'Nama Program Studi');
        $col = 2;
        for ($i = 1; $i <= 10; $i++) {
            $sheet->setCellValueByColumnAndRow($col, 1, "Gel $i (D)");
            $sheet->setCellValueByColumnAndRow($col + 1, 1, "Gel $i (U)");
            $sheet->setCellValueByColumnAndRow($col + 2, 1, "Gel $i (R)");
            $sheet->setCellValueByColumnAndRow($col + 3, 1, "Gel $i (F)");
            $col += 4;
        }
        $sheet->setCellValueByColumnAndRow($col, 1, "Total D");
        $sheet->setCellValueByColumnAndRow($col + 1, 1, "Total U");
        $sheet->setCellValueByColumnAndRow($col + 2, 1, "Total R");
        $sheet->setCellValueByColumnAndRow($col + 3, 1, "Total F");

        $grandTotals = [];
        for ($i = 1; $i <= 10; $i++) {
            $grandTotals[$i] = ['D' => 0, 'U' => 0, 'R' => 0, 'F' => 0];
        }
        $allTotalD = 0; $allTotalU = 0; $allTotalR = 0; $allTotalF = 0;

        $rowNum = 2;
        foreach ($prodi as $item) {
            $sheet->setCellValue('A' . $rowNum, $item->nama_prodi);
            $col = 2;
            $rowSumD = 0; $rowSumU = 0; $rowSumR = 0; $rowSumF = 0;

            for ($i = 1; $i <= 10; $i++) {
                $d = $item->{'g'.$i.'_D'} ?? 0;
                $u = $item->{'g'.$i.'_U'} ?? 0;
                $r = $item->{'g'.$i.'_R'} ?? 0;
                $f = $item->{'g'.$i.'_F'} ?? 0;

                $sheet->setCellValueByColumnAndRow($col, $rowNum, $d);
                $sheet->setCellValueByColumnAndRow($col + 1, $rowNum, $u);
                $sheet->setCellValueByColumnAndRow($col + 2, $rowNum, $r);
                $sheet->setCellValueByColumnAndRow($col + 3, $rowNum, $f);

                $grandTotals[$i]['D'] += $d;
                $grandTotals[$i]['U'] += $u;
                $grandTotals[$i]['R'] += $r;
                $grandTotals[$i]['F'] += $f;

                $rowSumD += $d; $rowSumU += $u; $rowSumR += $r; $rowSumF += $f;
                $col += 4;
            }

            $sheet->setCellValueByColumnAndRow($col, $rowNum, $rowSumD);
            $sheet->setCellValueByColumnAndRow($col + 1, $rowNum, $rowSumU);
            $sheet->setCellValueByColumnAndRow($col + 2, $rowNum, $rowSumR);
            $sheet->setCellValueByColumnAndRow($col + 3, $rowNum, $rowSumF);

            $allTotalD += $rowSumD; $allTotalU += $rowSumU; $allTotalR += $rowSumR; $allTotalF += $rowSumF;
            $rowNum++;
        }

        $sheet->setCellValue('A' . $rowNum, 'GRAND TOTAL');
        $col = 2;
        for ($i = 1; $i <= 10; $i++) {
            $sheet->setCellValueByColumnAndRow($col, $rowNum, $grandTotals[$i]['D']);
            $sheet->setCellValueByColumnAndRow($col + 1, $rowNum, $grandTotals[$i]['U']);
            $sheet->setCellValueByColumnAndRow($col + 2, $rowNum, $grandTotals[$i]['R']);
            $sheet->setCellValueByColumnAndRow($col + 3, $rowNum, $grandTotals[$i]['F']);
            $col += 4;
        }
        $sheet->setCellValueByColumnAndRow($col, $rowNum, $allTotalD);
        $sheet->setCellValueByColumnAndRow($col + 1, $rowNum, $allTotalU);
        $sheet->setCellValueByColumnAndRow($col + 2, $rowNum, $allTotalR);
        $sheet->setCellValueByColumnAndRow($col + 3, $rowNum, $allTotalF);

        $highestColumn = $sheet->getHighestColumn();
        $sheet->getStyle('A1:' . $highestColumn . '1')->getFont()->setBold(true);
        $sheet->getStyle('A' . $rowNum . ':' . $highestColumn . $rowNum)->getFont()->setBold(true);
        $sheet->freezePane('B2');

        $detailSheet = $spreadsheet->createSheet();
        $detailSheet->setTitle('Detail Peserta');

        $detailSheet->setCellValue('A1', 'Nama Program Studi');
        $detailSheet->setCellValue('B1', 'Gelombang');
        $detailSheet->setCellValue('C1', 'Kategori');
        $detailSheet->setCellValue('D1', 'Nama');
        $detailSheet->setCellValue('E1', 'Email');
        $detailSheet->getStyle('A1:E1')->getFont()->setBold(true);

        $kategoriLabel = [
            'D' => 'Diterima',
            'U' => 'Ujian',
            'R' => 'Registrasi',
            'F' => 'Final (Dikti)',
        ];

        $detailRow = 2;
        foreach ($prodi as $item) {
            for ($i = 1; $i <= 10; $i++) {
                foreach (['D', 'U', 'R', 'F'] as $kategori) {
                    $rawUsers = $item->{'g'.$i.'_'.$kategori.'_users'} ?? '';
                    if (empty($rawUsers)) {
                        continue;
                    }

                    $userList = array_filter(explode('||', $rawUsers));
                    foreach ($userList as $userEntry) {
                        $nama = $userEntry;
                        $email = '';
                        if (preg_match('/^(.*)\s\(([^)]+)\)$/', $userEntry, $m)) {
                            $nama = trim($m[1]);
                            $email = trim($m[2]);
                        }

                        $detailSheet->setCellValue('A' . $detailRow, $item->nama_prodi);
                        $detailSheet->setCellValue('B' . $detailRow, "Gelombang $i");
                        $detailSheet->setCellValue('C' . $detailRow, $kategoriLabel[$kategori]);
                        $detailSheet->setCellValue('D' . $detailRow, $nama);
                        $detailSheet->setCellValue('E' . $detailRow, $email);
                        $detailRow++;
                    }
                }
            }
        }

        foreach (range('A', 'E') as $col) {
            $detailSheet->getColumnDimension($col)->setAutoSize(true);
        }
        $detailSheet->freezePane('A2');
        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Analisis_PMB_Gelombang_' . now()->format('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$fileName.'"');
        $writer->save('php://output');
        exit;
    }

    public function exportExcelProdi($namaProdi)
    {
        $namaProdi = urldecode($namaProdi);

        $prodi = $this->getProdiData();
        $item = $prodi->firstWhere('nama_prodi', $namaProdi);

        if (!$item) {
            abort(404, 'Program studi tidak ditemukan.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Ringkasan');

        $sheet->setCellValue('A1', 'Nama Program Studi');
        $col = 2;
        for ($i = 1; $i <= 10; $i++) {
            $sheet->setCellValueByColumnAndRow($col, 1, "Gel $i (D)");
            $sheet->setCellValueByColumnAndRow($col + 1, 1, "Gel $i (U)");
            $sheet->setCellValueByColumnAndRow($col + 2, 1, "Gel $i (R)");
            $sheet->setCellValueByColumnAndRow($col + 3, 1, "Gel $i (F)");
            $col += 4;
        }
        $sheet->setCellValueByColumnAndRow($col, 1, "Total D");
        $sheet->setCellValueByColumnAndRow($col + 1, 1, "Total U");
        $sheet->setCellValueByColumnAndRow($col + 2, 1, "Total R");
        $sheet->setCellValueByColumnAndRow($col + 3, 1, "Total F");

        $sheet->setCellValue('A2', $item->nama_prodi);
        $col = 2;
        $totalD = 0; $totalU = 0; $totalR = 0; $totalF = 0;
        for ($i = 1; $i <= 10; $i++) {
            $d = $item->{'g'.$i.'_D'} ?? 0;
            $u = $item->{'g'.$i.'_U'} ?? 0;
            $r = $item->{'g'.$i.'_R'} ?? 0;
            $f = $item->{'g'.$i.'_F'} ?? 0;

            $sheet->setCellValueByColumnAndRow($col, 2, $d);
            $sheet->setCellValueByColumnAndRow($col + 1, 2, $u);
            $sheet->setCellValueByColumnAndRow($col + 2, 2, $r);
            $sheet->setCellValueByColumnAndRow($col + 3, 2, $f);

            $totalD += $d; $totalU += $u; $totalR += $r; $totalF += $f;
            $col += 4;
        }
        $sheet->setCellValueByColumnAndRow($col, 2, $totalD);
        $sheet->setCellValueByColumnAndRow($col + 1, 2, $totalU);
        $sheet->setCellValueByColumnAndRow($col + 2, 2, $totalR);
        $sheet->setCellValueByColumnAndRow($col + 3, 2, $totalF);

        $highestColumn = $sheet->getHighestColumn();
        $sheet->getStyle('A1:' . $highestColumn . '1')->getFont()->setBold(true);
        $sheet->getStyle('A2:' . $highestColumn . '2')->getFont()->setBold(true);
        $sheet->freezePane('B2');

        $detailSheet = $spreadsheet->createSheet();
        $detailSheet->setTitle('Detail Peserta');

        $detailSheet->setCellValue('A1', 'Gelombang');
        $detailSheet->setCellValue('B1', 'Kategori');
        $detailSheet->setCellValue('C1', 'Nama');
        $detailSheet->setCellValue('D1', 'Email');
        $detailSheet->getStyle('A1:D1')->getFont()->setBold(true);

        $kategoriLabel = [
            'D' => 'Diterima',
            'U' => 'Ujian',
            'R' => 'Registrasi',
            'F' => 'Final (Dikti)',
        ];

        $detailRow = 2;
        for ($i = 1; $i <= 10; $i++) {
            foreach (['D', 'U', 'R', 'F'] as $kategori) {
                $rawUsers = $item->{'g'.$i.'_'.$kategori.'_users'} ?? '';
                if (empty($rawUsers)) {
                    continue;
                }

                $userList = array_filter(explode('||', $rawUsers));
                foreach ($userList as $userEntry) {
                    $nama = $userEntry;
                    $email = '';
                    if (preg_match('/^(.*)\s\(([^)]+)\)$/', $userEntry, $m)) {
                        $nama = trim($m[1]);
                        $email = trim($m[2]);
                    }

                    $detailSheet->setCellValue('A' . $detailRow, "Gelombang $i");
                    $detailSheet->setCellValue('B' . $detailRow, $kategoriLabel[$kategori]);
                    $detailSheet->setCellValue('C' . $detailRow, $nama);
                    $detailSheet->setCellValue('D' . $detailRow, $email);
                    $detailRow++;
                }
            }
        }

        foreach (range('A', 'D') as $col) {
            $detailSheet->getColumnDimension($col)->setAutoSize(true);
        }
        $detailSheet->freezePane('A2');

        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);
        $safeNama = preg_replace('/[^A-Za-z0-9\-]+/', '_', $namaProdi);
        $fileName = 'Analisis_PMB_' . $safeNama . '_' . now()->format('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$fileName.'"');
        $writer->save('php://output');
        exit;
    }

    public function getJumlahPerGelombang(Request $request)
    {
        return DB::table('master_users as mu')
            ->join('master_ereg as me', 'mu.user_id', '=', 'me.user_id')
            ->where('me.jurusan1', $request->jurusan1)
            ->select('mu.gelombang', DB::raw('COUNT(DISTINCT mu.id) as total'))
            ->groupBy('mu.gelombang')
            ->orderBy('mu.gelombang')
            ->get();
    }

    public function aiAnalyze(Request $request)
    {
        $rawData = DB::table('pmb_tahun_lalu')
            ->selectRaw('YEAR(tanggal_reg) as tahun, gelombang, COUNT(id) as total')
            ->whereNotNull('tanggal_reg')
            ->groupBy('tahun', 'gelombang')
            ->get();

        $years = ['2021', '2022', '2024', '2025'];
        $reportData = [];

        foreach ($years as $tahun) {
            $dataDaftar = [];
            for ($g = 1; $g <= 10; $g++) {
                $namaGelombang = "Gelombang $g";
                $item = $rawData->first(function($val) use ($tahun, $namaGelombang) {
                    return $val->tahun == $tahun && $val->gelombang == $namaGelombang;
                });
                $dataDaftar["Gelombang $g"] = $item ? (int)$item->total : 0;
            }
            $reportData[$tahun] = $dataDaftar;
        }

        $prodi = $this->getProdiData();
        $currentDaftar = array_fill(1, 10, 0);
        foreach ($prodi as $row) {
            for ($g = 1; $g <= 10; $g++) {
                $currentDaftar[$g] += (int)($row->{'g'.$g.'_D'} ?? 0);
            }
        }
        $reportData['2026'] = [];
        for ($g = 1; $g <= 10; $g++) {
            $reportData['2026']["Gelombang $g"] = $currentDaftar[$g];
        }

        $formattedData = json_encode($reportData, JSON_PRETTY_PRINT);

        $prompt = "Anda adalah Asisten Analisis Data PMB (Penerimaan Mahasiswa Baru) Universitas Buana Perjuangan Karawang. 
Berikut adalah data pendaftar PMB per gelombang dari tahun ke tahun (2021, 2022, 2024, 2025, dan tahun berjalan 2026):\n\n" . $formattedData . "\n\n" .
"Tolong lakukan analisis komprehensif mengenai data tersebut dengan detail sebagai berikut:
1. **Analisis Tren Pendaftaran**: Bagaimana perkembangan pendaftaran dari tahun ke tahun? Apakah ada tren kenaikan atau penurunan pada gelombang tertentu?
2. **Komparasi Tahun 2026**: Bandingkan performa pendaftaran tahun berjalan 2026 dengan tahun-tahun sebelumnya. Apakah performa tahun ini lebih baik atau ada kemunduran? Di gelombang mana performa 2026 paling menonjol?
3. **Rekomendasi Strategis**: Berikan rekomendasi konkret dan actionable untuk tim marketing PMB UBP Karawang untuk mengoptimalkan pendaftaran di gelombang-gelombang berikutnya.

Jawablah dalam Bahasa Indonesia yang profesional, ramah, dan optimis. Format jawaban Anda menggunakan Markdown yang rapi, bersih, terstruktur dengan subjudul, poin-poin, dan cetak tebal (bold) untuk kata penting agar mudah dibaca oleh pimpinan.";

        try {
            $ollamaUrl = rtrim(env('OLLAMA_URL', 'https://ollama.adirp.id'), '/');
            $response = \Illuminate\Support\Facades\Http::timeout(120)->post($ollamaUrl . '/api/generate', [
                'model' => 'gemma4:26b',
                'prompt' => $prompt,
                'stream' => false
            ]);

            if ($response->successful()) {
                $result = $response->json();
                $analysis = $result['response'] ?? 'Gagal memproses respons dari AI.';
                
                return response()->json([
                    'status' => 'success',
                    'analysis' => $analysis
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ollama API mengembalikan error: ' . $response->body()
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghubungi AI Ollama: ' . $e->getMessage()
            ], 500);
        }
    }
}