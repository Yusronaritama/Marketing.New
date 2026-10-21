<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class MasterMoodleLulusController extends Controller
{
    /**
 * TAMPILKAN LIST USER YANG LULUS DENGAN AUTO-UPDATE DAN DATA PRODI
 */
public function listLulusUsersWithAutoUpdate(Request $request)
{
    try {
        $limit = $request->input('limit', 50);
        $search = $request->input('search', '');
        $prodi = $request->input('prodi', '');
        $autoUpdate = $request->input('auto_update', true);

        Log::info('Accessing lulus users list with auto-update and prodi data', [
            'auto_update' => $autoUpdate,
            'search' => $search,
            'prodi' => $prodi
        ]);

        // Jika auto update diaktifkan, lakukan update status terlebih dahulu
        if ($autoUpdate) {
            $updateResult = $this->autoUpdateStatusAndSaveLulus();
            
            if (!$updateResult['success']) {
                Log::warning('Auto-update failed, continuing with existing data', [
                    'error' => $updateResult['error']
                ]);
            } else {
                Log::info('Auto-update completed', [
                    'updated' => $updateResult['stats']['updated'],
                    'lulus_saved' => $updateResult['stats']['lulus_saved']
                ]);
            }
        }
$query = DB::table('master_moodle_lulus')
         ->select([
             'master_moodle_lulus.*',
             'master_ereg.jurusan1',
             'master_ereg.jurusan2',
             // Hapus join ke program_studi karena kita pakai mapping manual
             DB::raw('NULL as nama_prodi_pilihan1'),
             DB::raw('NULL as passing_grade_pilihan1'),
             DB::raw('NULL as nama_prodi_pilihan2'),
             DB::raw('NULL as passing_grade_pilihan2'),
             DB::raw('NULL as nama_prodi_lulus_detail'),
             DB::raw('NULL as passing_grade_lulus'),
         ])
         ->leftJoin('master_users', 'master_moodle_lulus.master_user_id', '=', 'master_users.id')
         ->leftJoin('master_ereg', 'master_users.user_id', '=', 'master_ereg.user_id')
         // HAPUS JOIN KE program_studi
         ->orderBy('master_moodle_lulus.tanggal_lulus', 'desc')
         ->orderBy('master_moodle_lulus.nilai_quiz', 'desc');

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('master_moodle_lulus.username', 'like', "%{$search}%")
                  ->orWhere('master_moodle_lulus.email', 'like', "%{$search}%")
                  ->orWhere('master_moodle_lulus.nama_lengkap', 'like', "%{$search}%");
            });
        }

        if (!empty($prodi)) {
            $query->where('master_moodle_lulus.prodi_lulus', $prodi);
        }

        $users = $query->paginate($limit);

      // Process users to ensure all properties exist
$processedUsers = $users->getCollection()->map(function($user) {
    // Ambil data prodi dari mapping manual untuk pilihan 1 dan 2
    $prodiData = $this->getProdiData($user->jurusan1 ?? '', $user->jurusan2 ?? '');
    
    // Ambil data prodi lulus dari mapping manual
    $prodiLulusData = $this->getProdiData($user->prodi_lulus ?? '', '');
    
    // Set data dari mapping manual untuk pilihan 1 dan 2
    $user->nama_prodi_pilihan1 = $prodiData['nama_prodi1'] ?? $user->jurusan1;
    $user->nama_prodi_pilihan2 = $prodiData['nama_prodi2'] ?? $user->jurusan2;
    $user->passing_grade_pilihan1 = $prodiData['passing_grade1'] ?? 100.00;
    $user->passing_grade_pilihan2 = $prodiData['passing_grade2'] ?? 100.00;
    $user->ensured_passing_grade1 = $prodiData['passing_grade1'] ?? 100.00;
    $user->ensured_passing_grade2 = $prodiData['passing_grade2'] ?? 100.00;
    
    // **PERBAIKAN: Set data prodi lulus dari mapping**
    $user->nama_prodi_lulus_detail = $prodiLulusData['nama_prodi1'] ?? $user->nama_prodi_lulus ?? $user->prodi_lulus;
    $user->passing_grade_lulus = $prodiLulusData['passing_grade1'] ?? 100.00;
    
    // Data lainnya
    $user->jurusan1 = $user->jurusan1 ?? null;
    $user->jurusan2 = $user->jurusan2 ?? null;
    $user->nama_prodi_lulus = $user->nama_prodi_lulus ?? null;
    
    return $user;
});

        $users->setCollection($processedUsers);

$summary = [
    'total_lulus' => DB::table('master_moodle_lulus')->count(),
    'by_prodi' => DB::table('master_moodle_lulus')
                  ->select(
                      'prodi_lulus', 
                      DB::raw('COALESCE(nama_prodi_lulus, prodi_lulus) as nama_prodi'),
                      DB::raw('COUNT(*) as count'),
                      DB::raw('AVG(nilai_quiz) as rata_nilai')
                  )
                  ->groupBy('prodi_lulus', 'nama_prodi_lulus')
                  ->get(),
    'rata_nilai' => DB::table('master_moodle_lulus')->avg('nilai_quiz'),
    'nilai_tertinggi' => DB::table('master_moodle_lulus')->max('nilai_quiz'),
    'nilai_terendah' => DB::table('master_moodle_lulus')->min('nilai_quiz')
];
        // Process summary by_prodi untuk memastikan semua property ada
        $processedByProdi = $summary['by_prodi']->map(function($item) {
            $item->nama_prodi_lulus = $item->nama_prodi ?? $item->prodi_lulus ?? 'Tidak diketahui';
            return $item;
        });
        $summary['by_prodi'] = $processedByProdi;

        // Statistik kelulusan berdasarkan pilihan prodi
        $kelulusanStats = $this->getKelulusanStats();

        $lastUpdate = DB::table('master_moodle_lulus')
                      ->select('updated_at')
                      ->orderBy('updated_at', 'desc')
                      ->first();

        return view('admin.moodle.master-moodle-lulus-list', [
            'users' => $users,
            'summary' => $summary,
            'kelulusan_stats' => $kelulusanStats,
            'search' => $search,
            'prodi' => $prodi,
            'limit' => $limit,
            'auto_update' => $autoUpdate,
            'last_update' => $lastUpdate ? $lastUpdate->updated_at : null,
            'success' => true
        ]);

    } catch (\Exception $e) {
        Log::error('Error in listLulusUsersWithAutoUpdate: ' . $e->getMessage());

        return view('admin.moodle.master-moodle-lulus-list', [
            'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
            'success' => false,
            'auto_update' => false
        ]);
    }
}

   private function getKelulusanStats()
{
    try {
        // Karena kita pakai mapping manual, hitung statistik sederhana
        $totalLulus = DB::table('master_moodle_lulus')->count();
        $rataNilai = DB::table('master_moodle_lulus')->avg('nilai_quiz');
        $nilaiTertinggi = DB::table('master_moodle_lulus')->max('nilai_quiz');

        return (object) [
            'total_lulus' => $totalLulus,
            'rata_nilai' => $rataNilai,
            'nilai_tertinggi' => $nilaiTertinggi,
            'lulus_pilihan1' => 0, // Tidak bisa dihitung tanpa join
            'lulus_pilihan2' => 0, // Tidak bisa dihitung tanpa join
            'actual_lulus_pilihan1' => 0, // Tidak bisa dihitung tanpa join
            'actual_lulus_pilihan2' => 0, // Tidak bisa dihitung tanpa join
        ];

    } catch (\Exception $e) {
        Log::error('Error in getKelulusanStats: ' . $e->getMessage());
        return null;
    }
}
    private function autoUpdateStatusAndSaveLulus()
    {
        try {
            DB::beginTransaction();

            Log::info('Starting auto-update status with prodi data from master_ereg');

            // PERBAIKAN: Ambil data dengan join yang benar
            $users = DB::table('master_moodle_users')
                     ->select([
                         'master_moodle_users.*',
                         'master_ereg.jurusan1',
                         'master_ereg.jurusan2',
                         'master_ereg.user_id as ereg_user_id'
                     ])
                     // PERBAIKAN: Join hanya menggunakan master_user_id
                     ->leftJoin('master_ereg', 'master_moodle_users.master_user_id', '=', 'master_ereg.user_id')
                     ->where(function($query) {
                         $query->where('master_moodle_users.attempt_count', '>=', 1)
                               ->orWhere('master_moodle_users.nilai_quiz', '>', 0);
                     })
                     ->get();

            $updateStats = [
                'total_processed' => count($users),
                'updated' => 0,
                'lulus_saved' => 0,
                'errors' => 0
            ];

            $lulusUsers = [];

            foreach ($users as $user) {
                try {
                    // Tentukan status dengan data prodi dari master_ereg
                    $newStatus = $this->determineDetailedStatusWithProdi($user);
                    $currentStatus = $user->status_kelulusan;

                    // Update status jika berubah
                    if ($newStatus['status'] !== $currentStatus) {
                        DB::table('master_moodle_users')
                            ->where('id', $user->id)
                            ->update([
                                'status_kelulusan' => $newStatus['status'],
                                'prodi_lulus' => $newStatus['prodi_lulus'],
                                'nama_prodi_lulus' => $newStatus['nama_prodi_lulus'],
                                'pilihan_lulus' => $newStatus['pilihan_lulus'],
                                'updated_at' => now()
                            ]);
                        
                        $updateStats['updated']++;
                    }

                    // Kumpulkan user yang lulus
                    if (strpos($newStatus['status'], 'LULUS') !== false) {
                        $lulusUsers[] = (object) array_merge((array) $user, [
                            'prodi_lulus' => $newStatus['prodi_lulus'],
                            'nama_prodi_lulus' => $newStatus['nama_prodi_lulus'],
                            'pilihan_lulus' => $newStatus['pilihan_lulus'],
                            'status_kelulusan' => $newStatus['status']
                        ]);
                    }

                } catch (\Exception $e) {
                    Log::error('Error in auto-update user: ' . $e->getMessage(), [
                        'user_id' => $user->id ?? 'unknown'
                    ]);
                    $updateStats['errors']++;
                }
            }

            // Simpan user yang lulus ke master_moodle_lulus
            if (!empty($lulusUsers)) {
                $saveStats = $this->saveLulusUsersToTable($lulusUsers, false);
                $updateStats['lulus_saved'] = $saveStats['saved'];
            }

            DB::commit();

            Log::info('Auto-update with prodi data completed successfully', $updateStats);

            return [
                'success' => true,
                'stats' => $updateStats
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error in autoUpdateStatusAndSaveLulus: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    /**
     * UPDATE STATUS DETAIL DAN SIMPAN KE MASTER_MOODLE_LULUS (MANUAL)
     */
    public function updateStatusDetailAndSaveToLulus(Request $request)
    {
        try {
            DB::beginTransaction();

            $limit = $request->input('limit', 1000);
            $dryRun = $request->input('dry_run', false);

            Log::info('Starting detailed status update and save to lulus table', [
                'limit' => $limit,
                'dry_run' => $dryRun
            ]);

            // 1. Update status di master_moodle_users
            $users = DB::table('master_moodle_users')
                     ->select('*')
                     ->limit($limit)
                     ->get();

            $updateStats = [
                'total' => count($users),
                'updated' => 0,
                'no_change' => 0,
                'errors' => 0,
                'lulus_count' => 0
            ];

            $lulusUsers = [];

            foreach ($users as $user) {
                try {
                    $newStatus = $this->determineDetailedStatus($user);
                    $currentStatus = $user->status_kelulusan;

                    if ($newStatus !== $currentStatus) {
                        if (!$dryRun) {
                            DB::table('master_moodle_users')
                                ->where('id', $user->id)
                                ->update([
                                    'status_kelulusan' => $newStatus,
                                    'updated_at' => now()
                                ]);
                        }

                        $updateStats['updated']++;
                    } else {
                        $updateStats['no_change']++;
                    }

                    // Kumpulkan user yang lulus
                    if (strpos($newStatus, 'LULUS') !== false) {
                        $updateStats['lulus_count']++;
                        $lulusUsers[] = $user;
                    }

                } catch (\Exception $e) {
                    Log::error('Error updating user status: ' . $e->getMessage(), [
                        'user_id' => $user->id ?? 'unknown'
                    ]);
                    $updateStats['errors']++;
                }
            }

            // 2. Simpan user yang lulus ke master_moodle_lulus
            $saveStats = $this->saveLulusUsersToTable($lulusUsers, $dryRun);

            DB::commit();

            Log::info('Status update and save to lulus completed', [
                'update_stats' => $updateStats,
                'save_stats' => $saveStats
            ]);

            // 3. Redirect ke halaman list user lulus
            if (!$dryRun) {
                return redirect()->route('admin.moodle.master-moodle-lulus.list')
                             ->with('success', 'Status berhasil diupdate dan data lulus disimpan!')
                             ->with('stats', [
                                 'updated' => $updateStats['updated'],
                                 'lulus_saved' => $saveStats['saved'],
                                 'lulus_total' => $updateStats['lulus_count']
                             ]);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'DRY RUN: No actual updates were made',
                    'update_stats' => $updateStats,
                    'save_stats' => $saveStats,
                    'lulus_users_sample' => array_slice($lulusUsers, 0, 5)
                ]);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error in status update and save to lulus: ' . $e->getMessage());

            return redirect()->route('admin.moodle.master-moodle-lulus.list')
                         ->with('error', 'Gagal mengupdate status: ' . $e->getMessage());
        }
    }

    /**
     * SIMPAN USER LULUS KE TABEL MASTER_MOODLE_LULUS
     */
    private function saveLulusUsersToTable($lulusUsers, $dryRun = false)
    {
        try {
            $stats = [
                'total' => count($lulusUsers),
                'saved' => 0,
                'updated' => 0,
                'errors' => 0
            ];

            // Pastikan tabel master_moodle_lulus ada
            $this->ensureLulusTableExists();

            foreach ($lulusUsers as $user) {
                try {
                    $lulusData = $this->prepareLulusDataWithProdi($user);

                    // Cek apakah user sudah ada di tabel lulus
                    $existingLulus = DB::table('master_moodle_lulus')
                                     ->where('master_user_id', $user->master_user_id)
                                     ->orWhere('moodle_user_id', $user->moodle_user_id)
                                     ->first();

                    if (!$dryRun) {
                        if ($existingLulus) {
                            // Update existing record
                            DB::table('master_moodle_lulus')
                                ->where('id', $existingLulus->id)
                                ->update($lulusData);
                            $stats['updated']++;
                        } else {
                            // Insert new record
                            DB::table('master_moodle_lulus')->insert($lulusData);
                            $stats['saved']++;
                        }
                    } else {
                        $stats['saved']++; // Count for dry run
                    }

                } catch (\Exception $e) {
                    Log::error('Error saving lulus user: ' . $e->getMessage(), [
                        'user_id' => $user->id ?? 'unknown'
                    ]);
                    $stats['errors']++;
                }
            }

            return $stats;

        } catch (\Exception $e) {
            Log::error('Error in saveLulusUsersToTable: ' . $e->getMessage());
            throw $e;
        }
    }
private function getProdiData($jurusan1, $jurusan2)
{
    $result = [
        'nama_prodi1' => '',
        'passing_grade1' => 100, // Default value
        'nama_prodi2' => '',
        'passing_grade2' => 100, // Default value
        'kode_prodi1' => $jurusan1,
        'kode_prodi2' => $jurusan2
    ];

    try {
        // Ambil mapping prodi
        $prodiMapping = $this->getProdiMapping();

        // Proses prodi 1
        if (!empty($jurusan1)) {
            if (isset($prodiMapping[$jurusan1])) {
                $mapping = $prodiMapping[$jurusan1];
                $result['nama_prodi1'] = $mapping[0]; // nama prodi
                $result['passing_grade1'] = (float) $mapping[1]; // passing grade
                $result['kode_prodi1'] = $mapping[2] ?? $jurusan1; // kode prodi resmi
            } else {
                // Jika tidak ada di mapping, gunakan nilai default
                $result['nama_prodi1'] = $jurusan1;
                $result['passing_grade1'] = 100;
                Log::warning('Prodi not found in mapping', ['jurusan' => $jurusan1]);
            }
        }

        // Proses prodi 2
        if (!empty($jurusan2)) {
            if (isset($prodiMapping[$jurusan2])) {
                $mapping = $prodiMapping[$jurusan2];
                $result['nama_prodi2'] = $mapping[0]; // nama prodi
                $result['passing_grade2'] = (float) $mapping[1]; // passing grade
                $result['kode_prodi2'] = $mapping[2] ?? $jurusan2; // kode prodi resmi
            } else {
                // Jika tidak ada di mapping, gunakan nilai default
                $result['nama_prodi2'] = $jurusan2;
                $result['passing_grade2'] = 100;
                Log::warning('Prodi not found in mapping', ['jurusan' => $jurusan2]);
            }
        }

    } catch (\Exception $e) {
        Log::error('Error getting prodi data: ' . $e->getMessage());
        
        // Fallback jika terjadi error
        if (!empty($jurusan1)) {
            $result['nama_prodi1'] = $jurusan1;
            $result['passing_grade1'] = 100;
        }
        if (!empty($jurusan2)) {
            $result['nama_prodi2'] = $jurusan2;
            $result['passing_grade2'] = 100;
        }
    }

    return $result;
}
/**
 * MAPPING PRODI MANUAL TANPA AKSES DATABASE
 */
private function getProdiMapping()
{
    return [
        // Format: 'kode_dari_master_ereg' => ['nama_prodi', 'passing_grade', 'kode_prodi_resmi']
        'Hukum' => ['Ilmu Hukum', 20.00, '73201'],
        'Psikologi' => ['Psikologi', 17.00, null], // Tidak ada di database, passing grade tinggi
        'Teknik Mesin' => ['Teknik Mesin', 17.00, '21201'],
        'Teknik Elektro' => ['Teknik Elektro', 17.00, '26201'],
        'Teknik Informatika' => ['Teknik Informatika', 20.00, '61201'],
        'Sistem Informasi' => ['Sistem Informasi', 17.00, '62201'],
        'Akuntansi' => ['Akuntansi', 17.00, '55201'],
        'Manajemen' => ['Manajemen', 17.00, '57201'],
        'Administrasi Rumah Sakit' => ['Administrasi Rumah Sakit', 1.00, '48201'],
        'Ilmu Hukum' => ['Ilmu Hukum', 20.00, '73201'],
        'Ilmu Administrasi Negara' => ['Ilmu Administrasi Negara', 17.00, '74201'],
        'Pendidikan Bahasa Inggris' => ['Pendidikan Bahasa Inggris', 17.00, '86206'],
        'Bimbingan dan Konseling' => ['Bimbingan dan Konseling', 5.00, '87205'],
        'Pendidikan Guru Sekolah Dasar' => ['Pendidikan Guru Sekolah Dasar', 5.00, '86230'],
        
        // Default mapping untuk kode prodi yang ada di database
        '21201' => ['Teknik Mesin', 17.00, '21201'],
        '26201' => ['Teknik Elektro', 17.00, '26201'],
        '61201' => ['Teknik Informatika', 20.00, '61201'],
        '62201' => ['Sistem Informasi', 17.00, '62201'],
        '55201' => ['Akuntansi', 17.00, '55201'],
        '57201' => ['Manajemen', 17.00, '57201'],
        '48201' => ['Administrasi Rumah Sakit', 1.00, '48201'],
        '73201' => ['Ilmu Hukum', 20.00, '73201'],
        '74201' => ['Ilmu Administrasi Negara', 17.00, '74201'],
        '86206' => ['Pendidikan Bahasa Inggris', 17.00, '86206'],
        '87205' => ['Bimbingan dan Konseling', 5.00, '87205'],
        '86230' => ['Pendidikan Guru Sekolah Dasar', 5.00, '86230'],
    ];
}
    private function ensureLulusTableExists()
    {
        if (!Schema::hasTable('master_moodle_lulus')) {
            Schema::create('master_moodle_lulus', function ($table) {
                $table->id();
                $table->unsignedBigInteger('master_user_id')->nullable();
                $table->unsignedBigInteger('moodle_user_id')->nullable();
                $table->string('username', 255);
                $table->string('email', 255);
                $table->string('nama_lengkap', 255);
                $table->string('prodi_lulus', 20)->nullable();
                $table->string('nama_prodi_lulus', 255)->nullable();
                $table->string('pilihan_lulus', 20)->nullable();
                $table->decimal('nilai_quiz', 8, 2)->default(0);
                $table->integer('attempt_count')->default(0);
                $table->string('status_kelulusan', 100);
                $table->text('keterangan')->nullable();
                $table->timestamp('tanggal_lulus')->nullable();
                $table->timestamps();
                
                $table->index('master_user_id');
                $table->index('moodle_user_id');
                $table->index('email');
                $table->index('prodi_lulus');
                $table->index('tanggal_lulus');
            });

            Log::info('Created master_moodle_lulus table');
        }
    }
 /**
     * PREPARE DATA UNTUK TABEL LULUS DENGAN DATA PRODI
     */
    private function prepareLulusDataWithProdi($user)
{
    // Ambil data prodi lengkap untuk pilihan
    $prodiData = $this->getProdiData($user->jurusan1 ?? '', $user->jurusan2 ?? '');
    
    // Ambil data prodi lulus dari mapping
    $prodiLulusData = $this->getProdiData($user->prodi_lulus ?? '', '');

    // Tentukan nama prodi lulus yang sesuai
    $namaProdiLulus = '';
    if ($user->pilihan_lulus === 'Pilihan 1') {
        $namaProdiLulus = $prodiData['nama_prodi1'] ?? $user->nama_prodi_lulus;
    } elseif ($user->pilihan_lulus === 'Pilihan 2') {
        $namaProdiLulus = $prodiData['nama_prodi2'] ?? $user->nama_prodi_lulus;
    } else {
        // **PERBAIKAN: Gunakan mapping untuk prodi lulus**
        $namaProdiLulus = $prodiLulusData['nama_prodi1'] ?? $user->nama_prodi_lulus ?? $user->prodi_lulus;
    }

    return [
        'master_user_id' => $user->master_user_id,
        'moodle_user_id' => $user->moodle_user_id,
        'username' => $user->username,
        'email' => $user->email,
        'nama_lengkap' => $user->firstname,
        'prodi_lulus' => $user->prodi_lulus,
        'nama_prodi_lulus' => $namaProdiLulus,
        'pilihan_lulus' => $user->pilihan_lulus,
        'nilai_quiz' => $user->nilai_quiz,
        'attempt_count' => $user->attempt_count,
        'status_kelulusan' => $user->status_kelulusan,
        'keterangan' => 'Auto-generated with prodi data from master_ereg',
        'tanggal_lulus' => now(),
        'created_at' => now(),
        'updated_at' => now()
    ];
}
    /**
     * PREPARE DATA UNTUK TABEL LULUS
     */
    private function prepareLulusData($user)
    {
        return [
            'master_user_id' => $user->master_user_id,
            'moodle_user_id' => $user->moodle_user_id,
            'username' => $user->username,
            'email' => $user->email,
            'nama_lengkap' => $user->firstname,
            'prodi_lulus' => $user->prodi_lulus,
            'nama_prodi_lulus' => $user->nama_prodi_lulus,
            'nilai_quiz' => $user->nilai_quiz,
            'attempt_count' => $user->attempt_count,
            'status_kelulusan' => $user->status_kelulusan,
            'keterangan' => 'Auto-generated from status update',
            'tanggal_lulus' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ];
    }
public function exportCSV(Request $request)
{
    try {
        $search = $request->input('search', '');
        $prodi = $request->input('prodi', '');

        $query = DB::table('master_moodle_lulus')
                 ->select([
                     'master_moodle_lulus.*',
                     'master_ereg.jurusan1',
                     'master_ereg.jurusan2',
                 ])
                 ->leftJoin('master_users', 'master_moodle_lulus.master_user_id', '=', 'master_users.id')
                 ->leftJoin('master_ereg', 'master_users.user_id', '=', 'master_ereg.user_id')
                 ->orderBy('master_moodle_lulus.tanggal_lulus', 'desc')
                 ->orderBy('master_moodle_lulus.nilai_quiz', 'desc');

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('master_moodle_lulus.username', 'like', "%{$search}%")
                  ->orWhere('master_moodle_lulus.email', 'like', "%{$search}%")
                  ->orWhere('master_moodle_lulus.nama_lengkap', 'like', "%{$search}%");
            });
        }

        if (!empty($prodi)) {
            $query->where('master_moodle_lulus.prodi_lulus', $prodi);
        }

        $users = $query->get();

        $filename = 'data_lulus_moodle_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, [
                'No', 'Nama Lengkap', 'Email', 'Username', 
                'Prodi Pilihan 1', 'Passing Grade P1', 'Prodi Pilihan 2', 'Passing Grade P2',
                'Prodi Lulus', 'Nama Prodi Lulus', 'Pilihan Lulus', 'Nilai Quiz',
                'Attempt Count', 'Status Kelulusan', 'Tanggal Lulus', 'Keterangan'
            ]);

            // Data
            $counter = 1;
            foreach ($users as $user) {
                $prodiData = $this->getProdiData($user->jurusan1 ?? '', $user->jurusan2 ?? '');
                $prodiLulusData = $this->getProdiData($user->prodi_lulus ?? '', '');

                fputcsv($file, [
                    $counter++,
                    $user->nama_lengkap ?? '-',
                    $user->email ?? '-',
                    $user->username ?? '-',
                    $prodiData['nama_prodi1'] ?? $user->jurusan1 ?? '-',
                    $prodiData['passing_grade1'] ?? 100.00,
                    $prodiData['nama_prodi2'] ?? $user->jurusan2 ?? '-',
                    $prodiData['passing_grade2'] ?? 100.00,
                    $user->prodi_lulus ?? '-',
                    $prodiLulusData['nama_prodi1'] ?? $user->nama_prodi_lulus ?? $user->prodi_lulus ?? '-',
                    $user->pilihan_lulus ?? '-',
                    $user->nilai_quiz ?? 0,
                    $user->attempt_count ?? 0,
                    $user->status_kelulusan ?? '-',
                    $user->tanggal_lulus ? \Carbon\Carbon::parse($user->tanggal_lulus)->format('d/m/Y H:i') : '-',
                    $user->keterangan ?? '-'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);

    } catch (\Exception $e) {
        Log::error('Error exporting to CSV: ' . $e->getMessage());

        return redirect()->route('admin.moodle.master-moodle-lulus.list')
                     ->with('error', 'Gagal mengexport data: ' . $e->getMessage());
    }
}
private function determineDetailedStatusWithProdi($user)
{
    $attemptCount = $user->attempt_count ?? 0;
    $nilaiQuiz = $user->nilai_quiz ?? 0;
    $jurusan1 = $user->jurusan1 ?? '';
    $jurusan2 = $user->jurusan2 ?? '';
    $statusSaatIni = $user->status_kelulusan ?? '';

    // Jika sudah ada status LULUS atau TIDAK LULUS, pertahankan
    if (strpos($statusSaatIni, 'LULUS') !== false || strpos($statusSaatIni, 'TIDAK LULUS') !== false) {
        return [
            'status' => $statusSaatIni,
            'prodi_lulus' => $user->prodi_lulus ?? '',
            'nama_prodi_lulus' => $user->nama_prodi_lulus ?? '',
            'pilihan_lulus' => $user->pilihan_lulus ?? ''
        ];
    }

    // Ambil data passing grade dari mapping manual
    $prodiData = $this->getProdiData($jurusan1, $jurusan2);
    $passingGrade1 = $prodiData['passing_grade1'] ?? 100;
    $passingGrade2 = $prodiData['passing_grade2'] ?? 100;
    
    // Gunakan kode prodi dari mapping
    $kodeProdi1 = $prodiData['kode_prodi1'] ?? $jurusan1;
    $kodeProdi2 = $prodiData['kode_prodi2'] ?? $jurusan2;

    // Cek kelulusan berdasarkan pilihan prodi
    $lulusP1 = !empty($jurusan1) && $nilaiQuiz >= $passingGrade1;
    $lulusP2 = !empty($jurusan2) && $nilaiQuiz >= $passingGrade2;

    // LOGIKA UTAMA: Jika lulus di kedua prodi, prioritaskan Pilihan 1
    if ($lulusP1 && $lulusP2) {
        return [
            'status' => 'LULUS - ' . ($prodiData['nama_prodi1'] ?? $jurusan1),
            'prodi_lulus' => $kodeProdi1,
            'nama_prodi_lulus' => $prodiData['nama_prodi1'] ?? $jurusan1,
            'pilihan_lulus' => 'Pilihan 1'
        ];
    }

    // Jika hanya lulus di pilihan 1
    if ($lulusP1) {
        return [
            'status' => 'LULUS - ' . ($prodiData['nama_prodi1'] ?? $jurusan1),
            'prodi_lulus' => $kodeProdi1,
            'nama_prodi_lulus' => $prodiData['nama_prodi1'] ?? $jurusan1,
            'pilihan_lulus' => 'Pilihan 1'
        ];
    }

    // Jika hanya lulus di pilihan 2
    if ($lulusP2) {
        return [
            'status' => 'LULUS - ' . ($prodiData['nama_prodi2'] ?? $jurusan2),
            'prodi_lulus' => $kodeProdi2,
            'nama_prodi_lulus' => $prodiData['nama_prodi2'] ?? $jurusan2,
            'pilihan_lulus' => 'Pilihan 2'
        ];
    }

    // Jika ada attempt tapi belum ada nilai
    if ($attemptCount >= 1 && $nilaiQuiz == 0) {
        return [
            'status' => 'Sudah attempt tapi belum ada nilai',
            'prodi_lulus' => '',
            'nama_prodi_lulus' => '',
            'pilihan_lulus' => ''
        ];
    }

    // Jika sudah ada nilai tapi tidak lulus
    if ($nilaiQuiz > 0) {
        $selisihP1 = !empty($jurusan1) ? max(0, $passingGrade1 - $nilaiQuiz) : 0;
        $selisihP2 = !empty($jurusan2) ? max(0, $passingGrade2 - $nilaiQuiz) : 0;
        
        $keterangan = 'TIDAK LULUS';
        if (!empty($jurusan1) && !empty($jurusan2)) {
            $keterangan .= ' - Butuh ' . number_format($selisihP1, 2) . ' poin untuk ' . 
                          ($prodiData['nama_prodi1'] ?? $jurusan1) . ' (Passing: ' . $passingGrade1 . '), ' . 
                          number_format($selisihP2, 2) . ' poin untuk ' . 
                          ($prodiData['nama_prodi2'] ?? $jurusan2) . ' (Passing: ' . $passingGrade2 . ')';
        } elseif (!empty($jurusan1)) {
            $keterangan .= ' - Butuh ' . number_format($selisihP1, 2) . ' poin untuk ' . 
                          ($prodiData['nama_prodi1'] ?? $jurusan1) . ' (Passing: ' . $passingGrade1 . ')';
        } elseif (!empty($jurusan2)) {
            $keterangan .= ' - Butuh ' . number_format($selisihP2, 2) . ' poin untuk ' . 
                          ($prodiData['nama_prodi2'] ?? $jurusan2) . ' (Passing: ' . $passingGrade2 . ')';
        }
        
        return [
            'status' => $keterangan,
            'prodi_lulus' => '',
            'nama_prodi_lulus' => '',
            'pilihan_lulus' => ''
        ];
    }

    // Jika ada attempt
    if ($attemptCount >= 1) {
        return [
            'status' => 'Sudah mengerjakan quiz',
            'prodi_lulus' => '',
            'nama_prodi_lulus' => '',
            'pilihan_lulus' => ''
        ];
    }

    // Default
    return [
        'status' => 'Belum mengerjakan quiz',
        'prodi_lulus' => '',
        'nama_prodi_lulus' => '',
        'pilihan_lulus' => ''
    ];
}
    /**
     * CEK KELULUSAN SEDERHANA BERDASARKAN NILAI
     */
    private function checkKelulusanSederhana($user)
    {
        $nilaiQuiz = $user->nilai_quiz ?? 0;
        
        // Logika sederhana: jika nilai >= 17 dianggap lulus
        return $nilaiQuiz >= 17;
    }

    /**
     * GET LULUS SUMMARY STATISTICS
     */
    public function getLulusSummary(Request $request)
    {
        try {
            $summary = DB::table('master_moodle_lulus')
                       ->select(
                           DB::raw('COUNT(*) as total_lulus'),
                           DB::raw('AVG(nilai_quiz) as rata_nilai'),
                           DB::raw('MAX(nilai_quiz) as nilai_tertinggi'),
                           DB::raw('MIN(nilai_quiz) as nilai_terendah')
                       )
                       ->first();

            $prodiSummary = DB::table('master_moodle_lulus')
                            ->select('prodi_lulus', 'nama_prodi_lulus', DB::raw('COUNT(*) as count'))
                            ->groupBy('prodi_lulus', 'nama_prodi_lulus')
                            ->orderBy('count', 'desc')
                            ->get();

            return response()->json([
                'success' => true,
                'summary' => $summary,
                'prodi_summary' => $prodiSummary
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting lulus summary: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Gagal mengambil summary lulus: ' . $e->getMessage()
            ]);
        }
    }
}