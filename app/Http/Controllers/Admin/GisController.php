<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;

class GisController extends Controller
{
    /**
     * Halaman Utama GIS (Dashboard Peta)
     */
    public function index()
    {
        // Ambil tahun-tahun yang tersedia dari data pendaftar
        $years = DB::table('master_ereg')
                    ->select(DB::raw('YEAR(created_at) as year'))
                    ->whereNotNull('created_at')
                    ->distinct()
                    ->orderBy('year', 'desc')
                    ->pluck('year')
                    ->toArray();
        
        $availableYears = array_merge(['Semua'], $years);

        return view('admin.gis.index', compact('availableYears'));
    }

    /**
     * API: Data Peta Sebaran & Statistik Sidebar
     * Endpoint: /admin/gis/data
     */
    public function getData(Request $request)
    {
        try {
            $year = $request->query('year', 'Semua');

            // --- 1. Query Data Mentah (Lokasi Pendaftar) ---
            $query = DB::table('master_ereg')
                        ->join('biodatas', 'master_ereg.user_id', '=', 'biodatas.user_id')
                        ->select(
                            'biodatas.provinsi_asal', 
                            'biodatas.kota_kabupaten_asal',
                            'master_ereg.created_at',
                            'master_ereg.jurusan1'
                        )
                        ->whereNotNull('biodatas.provinsi_asal'); 

            if ($year !== 'Semua') {
                $query->whereYear('master_ereg.created_at', $year);
            }

            $rawData = $query->get();

            // --- 2. HITUNG STATISTIK SIDEBAR (PERBAIKAN LEBIH AMAN) ---
            
            // Total Pendaftar: Hitung langsung dari master_ereg (Data Real Pendaftar)
            $queryCount = DB::table('master_ereg');
            if ($year !== 'Semua') {
                $queryCount->whereYear('created_at', $year);
            }
            $totalPendaftar = $queryCount->count();

            // Total Kuota: Sum daya_tampung dari tabel Program Studi
            // Pastikan kolom 'daya_tampung' benar-benar ada di tabel program_studi
            $totalKuota = ProgramStudi::sum('daya_tampung');

            // --- 3. Olah Data Provinsi (Untuk Peta & List) ---
            $provinsi = $rawData
                ->map(function ($item) { return strtoupper(trim($item->provinsi_asal)); })
                ->filter(function ($nama) { return !empty($nama) && $nama !== '-' && strlen($nama) > 2; })
                ->countBy()
                ->map(function ($count, $nama) {
                    return ['provinsi_asal' => $nama, 'total' => $count];
                })
                ->values()->sortByDesc('total')->values();

            // --- 4. Olah Data Kota (Untuk List Top Kota) ---
            $kota = $rawData
                ->map(function ($item) { return strtoupper(trim($item->kota_kabupaten_asal)); })
                ->filter(function ($nama) { return !empty($nama) && $nama !== '-' && strlen($nama) > 3; })
                ->countBy()
                ->map(function ($count, $nama) {
                    return ['kota_kabupaten_asal' => $nama, 'total' => $count];
                })
                ->values()->sortByDesc('total')->take(10)->values();

            // --- 5. Detail Jurusan Per Provinsi (Untuk Overlay Modal) ---
            // Menggunakan pluck agar lebih ringan daripada load model full
            $listProdi = ProgramStudi::pluck('nama_prodi', 'kode_prodi')->toArray();

            $detailJurusan = $rawData
                ->map(function($item) use ($listProdi) {
                    $kode = $item->jurusan1;
                    // Cek apakah kode ada di database prodi, jika tidak pakai kode aslinya
                    $namaJurusan = isset($listProdi[$kode]) ? $listProdi[$kode] : $kode;

                    return [
                        'prov' => strtoupper(trim($item->provinsi_asal)),
                        'jur'  => $namaJurusan
                    ];
                })
                ->filter(function($item) {
                    return !empty($item['prov']) && !empty($item['jur']);
                })
                ->groupBy(function($item) {
                    return $item['prov'] . '|' . $item['jur'];
                })
                ->map(function($group, $key) {
                    list($prov, $jur) = explode('|', $key);
                    return [
                        'provinsi_asal' => $prov,
                        'nama_jurusan'  => $jur,
                        'total'         => $group->count()
                    ];
                })
                ->values();

            // Kirim JSON Lengkap
            return response()->json([
                'status'          => 'success',
                'year'            => $year,
                'total_pendaftar' => $totalPendaftar, 
                'total_kuota'     => $totalKuota,     
                'provinsi'        => $provinsi,
                'kota'            => $kota,
                'detail_jurusan'  => $detailJurusan
            ]);

        } catch (\Exception $e) {
            // Ini akan membantu debugging jika masih error (cek Console > Network > Preview)
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Halaman Detail Kapasitas & Kuota
     * Endpoint: /admin/gis/capacity
     */
    public function capacityPage(Request $request)
    {
        // Ambil Data Program Studi + Hitung Jumlah Pendaftar
        $dataProdi = ProgramStudi::select('id', 'kode_prodi', 'nama_prodi', 'daya_tampung')
            ->withCount(['pendaftar']) // Pastikan relasi 'pendaftar' ada di Model ProgramStudi
            ->get();

        // Hitung Agregat Global
        $totalKuota     = $dataProdi->sum('daya_tampung');
        $totalPendaftar = $dataProdi->sum('pendaftar_count');
        $sisaKuota      = $totalKuota - $totalPendaftar;

        if($sisaKuota < 0) $sisaKuota = 0;

        return view('admin.gis.capacity', compact(
            'dataProdi', 
            'totalKuota', 
            'totalPendaftar', 
            'sisaKuota'
        ));
    }

    /**
     * API: Data Grafik Kelulusan
     */
    public function getKelulusan(Request $request)
    {
        try {
            $year = $request->query('year', 'Semua');

            $query = DB::table('master_moodle_lulus')
                        ->select('nama_prodi_lulus', 'created_at')
                        ->whereNotNull('nama_prodi_lulus');

            if ($year !== 'Semua') {
                $query->whereYear('created_at', $year);
            }

            $rawData = $query->get();

            $chartData = $rawData
                ->map(function($item) {
                    return strtoupper(trim($item->nama_prodi_lulus));
                })
                ->filter(function($nama) {
                    return !empty($nama) && $nama !== '-';
                })
                ->countBy()
                ->map(function($total, $jurusan) {
                    return ['name' => $jurusan, 'y' => $total];
                })
                ->values()
                ->sortByDesc('y')
                ->values();

            return response()->json([
                'status'      => 'success',
                'year'        => $year,
                'data'        => $chartData,
                'total_lulus' => $rawData->count()
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    /**
     * API: Data Rekapitulasi Tahunan (Tabel Matrix)
     */
    public function getRekap(Request $request)
    {
        try {
            $year = $request->query('year', 'Semua');

            $rawData = DB::table('master_moodle_lulus')
                        ->select('nama_prodi_lulus', 'tanggal_lulus', 'created_at')
                        ->whereNotNull('nama_prodi_lulus')
                        ->get();

            if ($rawData->isEmpty()) {
                return response()->json([
                    'status' => 'success', 'years' => [], 'data' => [], 'summary' => [],
                    'message' => 'Data kosong.'
                ]);
            }

            $rekap = [];
            $yearsFound = [];
            $summary = ['grand_total' => 0];

            foreach ($rawData as $item) {
                $prodi = strtoupper(trim($item->nama_prodi_lulus));
                if (empty($prodi) || $prodi === '-') continue;

                if (!empty($item->tanggal_lulus)) {
                    $tahunItem = date('Y', strtotime($item->tanggal_lulus));
                } elseif (!empty($item->created_at)) {
                    $tahunItem = date('Y', strtotime($item->created_at));
                } else {
                    $tahunItem = date('Y');
                }

                if ($year !== 'Semua' && $tahunItem != $year) continue;

                if (!in_array($tahunItem, $yearsFound)) $yearsFound[] = $tahunItem;

                // Init Data
                if (!isset($rekap[$prodi])) $rekap[$prodi] = ['total_all' => 0];
                if (!isset($rekap[$prodi][$tahunItem])) $rekap[$prodi][$tahunItem] = 0;

                // Increment Baris
                $rekap[$prodi][$tahunItem]++;
                $rekap[$prodi]['total_all']++;

                // Increment Summary (Footer)
                if (!isset($summary[$tahunItem])) $summary[$tahunItem] = 0;
                $summary[$tahunItem]++;
                $summary['grand_total']++;
            }

            sort($yearsFound);
            uasort($rekap, function($a, $b) { return $b['total_all'] <=> $a['total_all']; });

            return response()->json([
                'status'  => 'success',
                'years'   => $yearsFound,
                'data'    => $rekap,
                'summary' => $summary
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    /**
     * API: Statistik Pembayaran & Tenor
     */
    public function getPaymentStats(Request $request)
    {
        try {
            $year = $request->query('year', 'Semua');

            $query = DB::table('slik_billings')
                ->select('status', 'tenor_pembayaran', 'created_at')
                ->where('is_active', 1);

            if ($year !== 'Semua') {
                $query->whereYear('created_at', $year);
            }

            $rawData = $query->get();

            $lunasCount = 0;
            $pendingCount = 0;
            
            $tenorStats = [
                'Lunas 1 Tahun'    => 0,
                'Cicilan Bertahap' => 0
            ];

            foreach ($rawData as $item) {
                // Hitung Status
                $status = strtolower($item->status);
                if ($status == 'paid' || $status == 'lunas' || $status == 'settlement' || $status == 'success') {
                    $lunasCount++;
                } else {
                    $pendingCount++;
                }

                // Hitung Tenor
                $tenorRaw = strtolower($item->tenor_pembayaran ?? '');
                
                if (str_contains($tenorRaw, 'tahun')) {
                    $tenorStats['Lunas 1 Tahun']++;
                } elseif (str_contains($tenorRaw, 'cicilan')) {
                    $tenorStats['Cicilan Bertahap']++;
                } 
            }

            // Format Pie Chart
            $pieData = [];
            foreach ($tenorStats as $name => $y) {
                if ($y >= 0) { 
                    $pieData[] = ['name' => $name, 'y' => $y];
                }
            }

            return response()->json([
                'status' => 'success',
                'summary' => [
                    'lunas' => $lunasCount,
                    'pending' => $pendingCount
                ],
                'tenor_distribution' => $pieData
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function syncData() { return response()->json(['status'=>'ok']); }
}