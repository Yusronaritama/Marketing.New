<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use App\Models\PmbTahunLalu; // Model yang baru saja kita buat

class DatabaseImportController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Akses Ditolak.');
        }

        // JIKA SEDANG MEMBUKA HALAMAN PAGINATION ATAU HASIL PREVIEW
        if ($request->has('page') || $request->has('preview')) {
            $cacheKey = 'sql_preview_login_' . Auth::id();
            $cachedData = Cache::get($cacheKey);

            if ($cachedData) {
                $parsedData = $cachedData['parsedData'];
                $fileName = $cachedData['fileName'];
                $targetTable = $cachedData['targetTable'];

                // --- LOGIKA PAGINATION MANUAL DARI ARRAY CACHE ---
                $page = Paginator::resolveCurrentPage() ?: 1;
                $perPage = 50; 
                
                $items = $parsedData[$targetTable]['rows'];
                $totalItems = count($items);
                
                $slice = array_slice($items, ($page - 1) * $perPage, $perPage);
                
                $paginator = new LengthAwarePaginator(
                    $slice, $totalItems, $perPage, $page, [
                        'path' => Paginator::resolveCurrentPath(),
                        'query' => $request->query()
                    ]
                );

                $parsedData[$targetTable]['rows'] = $slice;
                $total = $totalItems;

                return view('admin.database.import', compact('parsedData', 'fileName', 'targetTable', 'paginator', 'total'));
            } else {
                return redirect()->route('admin.database.import')->with('error', 'Sesi data telah kedaluwarsa. Silakan unggah ulang file SQL.');
            }
        }

        return view('admin.database.import');
    }
public function syncAllLegacyDatabases()
    {
        if (\Illuminate\Support\Facades\Auth::user()->role !== 'admin') {
            abort(403, 'Akses Ditolak.');
        }

        set_time_limit(0); 
        ini_set('memory_limit', '2048M'); 
        \Illuminate\Support\Facades\DB::disableQueryLog(); 

        try {
            // 1. Bersihkan data Legacy lama agar tidak ada duplikat saat kita lakukan Bulk Insert
            \App\Models\PmbTahunLalu::where('status_pembayaran', 'LEGACY')->delete();

            $databases = [
                '2021' => 'pmb2021',
                '2022' => 'pmb2022',
                '2024' => 'pmb2024',
                '2025' => 'pmb2025'
            ];

            $totalTersimpan = 0;
            $totalDilewati = 0;

            foreach ($databases as $tahun => $dbConnection) {
                try {
                    $exists = \Schema::connection($dbConnection)->hasTable('login');
                    if (!$exists) continue;

                    // Ambil per 500 data
                    \Illuminate\Support\Facades\DB::connection($dbConnection)
                        ->table('login')
                        ->chunkById(500, function ($rows) use (&$totalTersimpan, &$totalDilewati, $tahun) {
                            
                            // Siapkan array keranjang belanja untuk Bulk Insert
                            $bulkData = [];

                            foreach ($rows as $row) {
                                $rowArray = (array) $row;
                                
                                // NORMALISASI KEY
                                $normalizedRow = [];
                                foreach ($rowArray as $key => $val) {
                                    $cleanKey = strtolower(str_replace(['_', ' '], '', $key));
                                    $normalizedRow[$cleanKey] = $val;
                                }

                                // Deteksi ID 
                                $idPendaftaran = $normalizedRow['idpendaftaran'] 
                                              ?? $normalizedRow['dependaftaran'] 
                                              ?? $normalizedRow['dependaptaran'] 
                                              ?? $normalizedRow['id'] 
                                              ?? null;

                                if (empty($idPendaftaran)) {
                                    $totalDilewati++;
                                    continue;
                                }

                                // Masukkan ke keranjang belanja (Tidak langsung ke database)
                                $bulkData[] = [
                                    'id_pendaftaran'    => "LEGACY-{$tahun}-{$idPendaftaran}",
                                    'hitung'            => $normalizedRow['hitung'] ?? null,
                                    'no_ujian'          => $normalizedRow['noujian'] ?? null,
                                    'input'             => $normalizedRow['input'] ?? null,
                                    'nama_user'         => $normalizedRow['namauser'] ?? null,
                                    'tanggal_reg'       => $normalizedRow['tanggalreg'] ?? null,
                                    'gelombang'         => $this->hitungGelombang($normalizedRow['tanggalreg'] ?? null),
                                    'tahun_pmb'         => $tahun,
                                    'status_pembayaran' => 'LEGACY',
                                    'created_at'        => now(),
                                    'updated_at'        => now(),
                                ];
                            }

                            // 2. BULK INSERT (Menembakkan 500 data sekaligus dalam 1x Query!)
                            if (!empty($bulkData)) {
                                \App\Models\PmbTahunLalu::insert($bulkData);
                                $totalTersimpan += count($bulkData);
                            }

                        }, 'dependaftaran'); // Pastikan ini adalah nama Primary Key yang benar di tabel login lama

                } catch (\Exception $e) {
                    \Log::error("GAGAL KONEKSI/QUERY {$dbConnection}: " . $e->getMessage());
                }
            }

            return redirect()->back()->with('success', "Mass Backup KILAT Selesai! {$totalTersimpan} data berhasil ditarik.");

        } catch (\Exception $e) {
            \Log::error("CRITICAL ERROR: " . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    // Helper sederhana untuk hitung gelombang
    private function hitungGelombang($tanggal) {
        if (!$tanggal) return "Gelombang 1";
        $ts = strtotime($tanggal);
        return $ts ? "Gelombang " . date('n', $ts) : "Gelombang 1";
    }
/**
     * Membaca file baris-demi-baris (Sangat hemat RAM untuk file raksasa)
     */
    private function parseHeavySqlTarget($filePath, $targetTable)
    {
        $parsedTables = [];
        
        // Buka file sebagai stream (tidak dimuat ke RAM sekaligus)
        $handle = fopen($filePath, "r");
        
        if ($handle) {
            $buffer = '';
            $isCapturing = false;
            
            // Baca baris demi baris
            while (($line = fgets($handle)) !== false) {
                
                // Jika baris mengandung awalan INSERT INTO `login`
                if (preg_match('/INSERT\s+INTO\s+`?' . preg_quote($targetTable, '/') . '`?/i', $line)) {
                    $isCapturing = true;
                }
                
                if ($isCapturing) {
                    $buffer .= $line;
                    
                    // Jika query berakhir dengan titik koma (;), proses datanya!
                    if (str_ends_with(trim($line), ';')) {
                        $isCapturing = false;
                        $this->extractDataFromBuffer($buffer, $targetTable, $parsedTables);
                        $buffer = ''; // Kosongkan buffer setelah selesai diproses
                    }
                }
            }
            fclose($handle);
        }
        
        return $parsedTables;
    }
    // private function hitungGelombang($tanggal) {
    //     if (!$tanggal) return "Gelombang 1";
    //     $ts = strtotime($tanggal);
    //     return $ts ? "Gelombang " . date('n', $ts) : "Gelombang 1";
    // }
    public function previewSql(Request $request)
    {
        $request->validate([
            'sql_file' => 'required|file|max:102400', 
        ]);

        try {
            $file = $request->file('sql_file');
            $fileName = $file->getClientOriginalName();
            
            $targetTable = 'login'; 

            $parsedData = $this->parseHeavySqlTarget($file->getRealPath(), $targetTable);

            if (empty($parsedData) || empty($parsedData[$targetTable]['rows'])) {
                return redirect()->back()->with('error', "Tidak ditemukan data untuk tabel '{$targetTable}'.");
            }

            // SIMPAN DATA KE CACHE SELAMA 2 JAM LALU REDIRECT
            $cacheKey = 'sql_preview_login_' . Auth::id();
            Cache::put($cacheKey, [
                'parsedData' => $parsedData,
                'fileName' => $fileName,
                'targetTable' => $targetTable
            ], now()->addHours(2));

            return redirect()->route('admin.database.import', ['preview' => 'true'])
                ->with('success', "Berhasil! Ditemukan " . count($parsedData[$targetTable]['rows']) . " data.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membaca file: ' . $e->getMessage());
        }
    }

    /**
     * FUNGSI BARU: Menyimpan data dari Cache ke Database
     */
    public function storeToDatabase(Request $request)
    {
        $cacheKey = 'sql_preview_login_' . Auth::id();
        $cachedData = Cache::get($cacheKey);

        if (!$cachedData) {
            return redirect()->route('admin.database.import')->with('error', 'Sesi data kedaluwarsa. Silakan upload ulang file SQL.');
        }

        $targetTable = $cachedData['targetTable'];
        $rows = $cachedData['parsedData'][$targetTable]['rows'] ?? [];

        // 1. --- LOGGING START ---
        \Log::info("=== PROSES IMPORT SQL DIMULAI ===");
        \Log::info("Total data di dalam Cache: " . count($rows) . " baris.");
        
        // Log baris pertama untuk melihat BENTUK ASLI NAMA KOLOMNYA
        if (count($rows) > 0) {
            \Log::info("Bentuk Asli Data Baris ke-1 (Raw):", $rows[0]);
        }

        if (empty($rows)) {
            return redirect()->route('admin.database.import')->with('error', 'Tidak ada data untuk disimpan.');
        }

        try {
            DB::beginTransaction();

            $chunks = array_chunk($rows, 500);
            $dataTersimpan = 0;
            $dataDilewati = 0; // Penghitung data yang error/dilewati

            foreach ($chunks as $chunk) {
                foreach ($chunk as $row) {
                    
                    // NORMALISASI KEY
                    $normalizedRow = [];
                    foreach ($row as $key => $val) {
                        $cleanKey = strtolower(str_replace(['_', ' '], '', $key));
                        $normalizedRow[$cleanKey] = $val;
                    }

                    // 2. --- LOGGING NORMALISASI ---
                    // Cek ID Pendaftaran dari berbagai kemungkinan nama kolom di SQL lama
                    $idPendaftaran = $normalizedRow['idpendaftaran'] ?? $normalizedRow['dependaftaran'] ?? $normalizedRow['id'] ?? null;

                    if (empty($idPendaftaran)) {
                        $dataDilewati++;
                        // Hanya cetak log untuk 5 data pertama yang dilewati agar file log tidak penuh
                        if ($dataDilewati <= 5) {
                            \Log::warning("Data di-skip karena ID Kosong! Hasil Normalisasi:", $normalizedRow);
                        }
                        continue;
                    }

                    // 3. Simpan ke database
                    PmbTahunLalu::updateOrCreate(
                        ['id_pendaftaran' => $idPendaftaran],
                        [
                            'hitung'      => $normalizedRow['hitung'] ?? null,
                            'no_ujian'    => $normalizedRow['noujian'] ?? null,
                            'input'       => $normalizedRow['input'] ?? null,
                            'nama_user'   => $normalizedRow['namauser'] ?? null,
                            'tanggal_reg' => $normalizedRow['tanggalreg'] ?? null,
                            'gelombang'   => $row['_gelombang'] ?? null,
                        ]
                    );

                    $dataTersimpan++;
                }
            }

            DB::commit();
            Cache::forget($cacheKey);

            // 4. --- LOGGING END ---
            \Log::info("=== PROSES IMPORT SELESAI ===");
            \Log::info("Total Sukses Tersimpan: {$dataTersimpan}");
            \Log::info("Total Dilewati (Skip): {$dataDilewati}");

            $pesan = "Sukses! {$dataTersimpan} Data disimpan. (Ada {$dataDilewati} data dilewati karena ID kosong).";
            return redirect()->route('admin.database.import')->with('success', $pesan);

        } catch (\Exception $e) {
            DB::rollBack();
            // --- LOGGING JIKA QUERY ERROR / DATABASE CRASH ---
            \Log::error("ERROR IMPORT SQL: " . $e->getMessage());
            \Log::error("Trace:", $e->getTrace());
            
            return redirect()->route('admin.database.import')->with('error', 'Gagal menyimpan data ke database: ' . $e->getMessage());
        }
    }

    // ... FUNGSI PARSING TETAP SAMA SEPERTI SEBELUMNYA ...

    // private function parseHeavySqlTarget($filePath, $targetTable)
    // {
    //     $parsedTables = [];
    //     $handle = fopen($filePath, "r");
        
    //     if ($handle) {
    //         $buffer = '';
    //         $isCapturing = false;
            
    //         while (($line = fgets($handle)) !== false) {
    //             if (!$isCapturing && preg_match('/INSERT\s+INTO\s+`?' . preg_quote($targetTable, '/') . '`?/i', $line)) {
    //                 $isCapturing = true;
    //                 $buffer = ''; 
    //             }
                
    //             if ($isCapturing) {
    //                 $buffer .= $line; 
    //                 if (str_ends_with(trim($line), ';')) {
    //                     $isCapturing = false;
    //                     $this->extractDataFromBuffer($buffer, $targetTable, $parsedTables);
    //                     $buffer = ''; 
    //                 }
    //             }
    //         }
    //         fclose($handle);
    //     }
        
    //     return $parsedTables;
    // }

    private function extractDataFromBuffer($sqlStatement, $targetTable, &$parsedTables)
    {
        if (!preg_match('/INSERT\s+INTO\s+`?[a-zA-Z0-9_]+`?\s*\((.*?)\)\s*VALUES\s*(.*?);/is', $sqlStatement, $match)) {
            return;
        }

        $columns = array_map(function($col) {
            return trim($col, "`'\" \t\n\r");
        }, explode(',', $match[1]));

        $hasTanggalReg = in_array('tanggal_reg', $columns);
        if ($hasTanggalReg && !in_array('_gelombang', $columns)) {
            $columns[] = '_gelombang';
        }

        $valuesStr = trim($match[2]);
        preg_match_all('/\((.*?)\)(?:,|\s*$)/s', $valuesStr, $rowMatches);

        if (empty($rowMatches[1])) {
            return;
        }

        $tableData = [];
        
        foreach ($rowMatches[1] as $rowStr) {
            $values = str_getcsv($rowStr, ',', "'");
            $rowData = [];
            
            foreach ($columns as $index => $col) {
                if ($col === '_gelombang') continue; 
                
                $val = $values[$index] ?? null;
                $val = trim($val);
                if (str_starts_with($val, "'") && str_ends_with($val, "'")) {
                    $val = substr($val, 1, -1);
                }
                $val = rtrim($val, ")"); 

                if ($val === 'NULL' || $val === 'null' || $val === '') {
                    $val = null;
                }
                $rowData[$col] = $val;
            }

            if ($hasTanggalReg) {
                $rawDate = $rowData['tanggal_reg'] ?? null;
                if (!empty($rawDate)) {
                    if (preg_match('/^(\d{4}-\d{2}-\d{2})/', $rawDate, $dateMatch)) {
                        $timestamp = strtotime($dateMatch[1]);
                        if ($timestamp) {
                            $bulan = date('n', $timestamp);
                            $rowData['_gelombang'] = 'Gelombang ' . $bulan;
                        } else {
                            $rowData['_gelombang'] = 'Format Tidak Valid';
                        }
                    } else {
                        $rowData['_gelombang'] = 'Bukan Tanggal';
                    }
                } else {
                    $rowData['_gelombang'] = 'Kosong';
                }
            }

            $tableData[] = $rowData;
        }

        if (!isset($parsedTables[$targetTable])) {
            $parsedTables[$targetTable] = [
                'columns' => $columns,
                'rows' => []
            ];
        }
        
        $parsedTables[$targetTable]['rows'] = array_merge($parsedTables[$targetTable]['rows'], $tableData);
    }
    public function syncDirectFromLegacy()
    {
        if (\Illuminate\Support\Facades\Auth::user()->role !== 'admin') {
            abort(403, 'Akses Ditolak.');
        }

        try {
            // DAFTAR DATABASE LAMA YANG INGIN DITARIK
            $databasesLama = ['pmb2021', 'pmb2022', 'pmb2023']; // Tambahkan jika ada pmb2024
            
            $totalTersimpan = 0;
            $totalDilewati = 0;
            $logProses = [];

            foreach ($databasesLama as $dbName) {
                // 1. Ubah target database secara dinamis
                \Illuminate\Support\Facades\Config::set('database.connections.legacy.database', $dbName);
                \Illuminate\Support\Facades\DB::purge('legacy'); // Bersihkan cache koneksi lama

                try {
                    // Cek apakah database dan tabel 'login' ada
                    $tableExists = \Illuminate\Support\Facades\DB::connection('legacy')
                        ->select("SHOW TABLES LIKE 'login'");

                    if (empty($tableExists)) {
                        $logProses[] = "Database {$dbName}: Dilewati (Tabel 'login' tidak ditemukan).";
                        continue;
                    }

                    \Illuminate\Support\Facades\DB::beginTransaction();

                    // 2. Tarik data dari tabel 'login' di database lama
                    \Illuminate\Support\Facades\DB::connection('legacy')
                        ->table('login')
                        ->chunk(500, function ($rows) use (&$totalTersimpan, &$totalDilewati) {
                            foreach ($rows as $row) {
                                // Ubah object menjadi array agar mudah dinormalisasi
                                $rowArray = (array) $row;
                                
                                // NORMALISASI KEY (Pembersih Typo Nama Kolom)
                                $normalizedRow = [];
                                foreach ($rowArray as $key => $val) {
                                    $cleanKey = strtolower(str_replace(['_', ' '], '', $key));
                                    $normalizedRow[$cleanKey] = $val;
                                }

                                // Cari ID Pendaftaran
                                $idPendaftaran = $normalizedRow['idpendaftaran'] ?? $normalizedRow['dependaftaran'] ?? $normalizedRow['id'] ?? null;

                                if (empty($idPendaftaran)) {
                                    $totalDilewati++;
                                    continue;
                                }

                                // Logika Penentuan Gelombang dari Tanggal
                                $gelombang = null;
                                $tanggalReg = $normalizedRow['tanggalreg'] ?? $normalizedRow['tanggalinput'] ?? null;
                                
                                if (!empty($tanggalReg)) {
                                    if (preg_match('/^(\d{4}-\d{2}-\d{2})/', $tanggalReg, $match)) {
                                        $timestamp = strtotime($match[1]);
                                        if ($timestamp) {
                                            $gelombang = 'Gelombang ' . date('n', $timestamp);
                                        }
                                    }
                                }

                                // 3. Simpan ke database PMB saat ini
                                \App\Models\PmbTahunLalu::updateOrCreate(
                                    ['id_pendaftaran' => $idPendaftaran],
                                    [
                                        'hitung'      => $normalizedRow['hitung'] ?? null,
                                        'no_ujian'    => $normalizedRow['noujian'] ?? null,
                                        'input'       => $normalizedRow['input'] ?? null,
                                        'nama_user'   => $normalizedRow['namauser'] ?? null,
                                        'tanggal_reg' => $tanggalReg,
                                        'gelombang'   => $gelombang,
                                    ]
                                );

                                $totalTersimpan++;
                            }
                        });

                    \Illuminate\Support\Facades\DB::commit();
                    $logProses[] = "Database {$dbName}: Sukses ditarik.";

                } catch (\Exception $dbEx) {
                    \Illuminate\Support\Facades\DB::rollBack();
                    $logProses[] = "Database {$dbName}: Gagal ditarik (" . $dbEx->getMessage() . ").";
                }
            }

            $pesanSukses = "Sinkronisasi Massal Selesai! {$totalTersimpan} data berhasil disedot. {$totalDilewati} data diabaikan. Status: " . implode(" | ", $logProses);
            
            return redirect()->back()->with('success', $pesanSukses);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal melakukan sinkronisasi massal: ' . $e->getMessage());
        }
    }
}