<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MoodleQuizAttempt;
use App\Models\MasterMoodle;
use App\Models\MoodleUserCourse;
use App\Models\MasterEreg;
use App\Models\MasterMoodleLulus;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MoodleCompleteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = MoodleQuizAttempt::with([
            'moodleUser',
            'moodleUser.masterUser',
            'moodleUser.masterUser.user',
            'moodleUser.masterUser.ereg',
            'course'
        ])->orderBy('created_at', 'desc');

        // Filter berdasarkan moodle_user_id jika ada
        if ($request->has('moodle_user_id') && $request->moodle_user_id) {
            $query->where('moodle_user_id', $request->moodle_user_id);
        }

        // Filter berdasarkan quiz_id jika ada
        if ($request->has('quiz_id') && $request->quiz_id) {
            $query->where('quiz_id', $request->quiz_id);
        }

        // Filter berdasarkan status state
        if ($request->has('state') && $request->state) {
            $query->where('state', $request->state);
        }

        // Filter berdasarkan program studi
        if ($request->has('program_studi') && $request->program_studi) {
            $query->whereHas('moodleUser.masterUser.ereg', function($q) use ($request) {
                $q->where('jurusan1', 'like', "%{$request->program_studi}%")
                  ->orWhere('jurusan2', 'like', "%{$request->program_studi}%");
            });
        }

        // Filter berdasarkan status kelolosan
        if ($request->has('kelolosan') && $request->kelolosan) {
            if ($request->kelolosan === 'lolos') {
                $query->where(function($q) {
                    $q->where(function($subQ) {
                        // Logika untuk cek kelolosan jurusan 1
                        $subQ->whereHas('moodleUser.masterUser.ereg', function($eregQ) {
                            $eregQ->whereNotNull('jurusan1');
                        });
                    })->orWhere(function($subQ) {
                        // Logika untuk cek kelolosan jurusan 2
                        $subQ->whereHas('moodleUser.masterUser.ereg', function($eregQ) {
                            $eregQ->whereNotNull('jurusan2');
                        });
                    });
                });
            } elseif ($request->kelolosan === 'tidak_lolos') {
                // Logika untuk tidak lolos
            }
        }

        // Search - Tambahkan score dalam pencarian
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('attempt_id', 'like', "%{$search}%")
                  ->orWhere('grade', 'like', "%{$search}%")
                  ->orWhere('score', 'like', "%{$search}%")  // Tambah score
                  ->orWhereHas('moodleUser', function($q) use ($search) {
                      $q->where('username', 'like', "%{$search}%");
                  })
                  ->orWhereHas('moodleUser.masterUser.user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('moodleUser.masterUser.ereg', function($q) use ($search) {
                      $q->where('jurusan1', 'like', "%{$search}%")
                        ->orWhere('jurusan2', 'like', "%{$search}%")
                        ->orWhere('nama', 'like', "%{$search}%");
                  })
                  ->orWhereHas('course', function($q) use ($search) {
                      $q->where('shortname', 'like', "%{$search}%")
                        ->orWhere('fullname', 'like', "%{$search}%")
                        ->orWhere('displayname', 'like', "%{$search}%");
                  });
            });
        }

        $attempts = $query->paginate(20);
        $moodleUsers = MasterMoodle::whereNotNull('moodle_user_id')->get();
        $quizzes = MoodleUserCourse::distinct()->get(['course_id', 'shortname', 'fullname']);

        // Ambil daftar program studi langsung dari tabel program_studi
        $programStudiList = DB::table('program_studi')
            ->where('status_prodi', '1') // Hanya yang aktif
            ->orderBy('kode_prodi')
            ->get(['kode_prodi', 'nama_prodi', 'passing_grade', 'kuota']);

        return view('admin.moodle.complete.index', compact('attempts', 'moodleUsers', 'quizzes', 'programStudiList'));
    }

    /**
     * Sync data peserta yang lolos ke tabel master_moodle_lulus
     */
public function syncLolos(Request $request)
{
    try {
        DB::beginTransaction();
        $lolosCount = 0;
        $updatedCount = 0;

        // Ambil semua attempt selesai tanpa filter waktu agar tidak ada yang terlewat
        $finishedAttempts = MoodleQuizAttempt::with([
            'moodleUser.masterUser.user', 
            'moodleUser.masterUser.ereg'
        ])
        ->where('state', 'finished')
        ->get();

        foreach ($finishedAttempts as $attempt) {
            // Skip jika relasi krusial tidak ada
            if (!$attempt->moodleUser || !$attempt->moodleUser->masterUser) continue;

            $statusLolos = $this->hitungStatusKelolosan($attempt);
            
            if ($statusLolos['lolos_jurusan1'] === 'LOLOS' || $statusLolos['lolos_jurusan2'] === 'LOLOS') {
                $jurusanLolos = $this->tentukanJurusanLolos($attempt, $statusLolos);
                
                if ($jurusanLolos) {
                    $nilaiQuiz = $attempt->score ?? $attempt->grade ?? 0;
                    
                    // Pastikan format tanggal aman
                    $tanggalLulus = $attempt->timefinish ? Carbon::createFromTimestamp($attempt->timefinish) : now();

                    // SOLUSI UTAMA: Gunakan kriteria pencarian yang sesuai dengan UNIQUE KEY di database
                    $record = MasterMoodleLulus::updateOrCreate(
                        [
                            'master_user_id' => $attempt->moodleUser->masterUser->id,
                            'moodle_user_id' => $attempt->moodle_user_id,
                        ],
                        [
                            'quiz_id'          => $attempt->quiz_id,
                            'username'         => $attempt->moodleUser->username,
                            'email'            => $attempt->moodleUser->masterUser->user->email ?? '-',
                            'nama_lengkap'     => $attempt->moodleUser->masterUser->user->name ?? '-',
                            'prodi_lulus'      => $jurusanLolos['kode_prodi'],
                            'nama_prodi_lulus' => $jurusanLolos['nama_prodi'],
                            'pilihan_lulus'    => $jurusanLolos['pilihan'],
                            'nilai_quiz'       => $nilaiQuiz,
                            'status_kelulusan' => 'LULUS',
                            'tanggal_lulus'    => $tanggalLulus,
                            'updated_at'       => now()
                        ]
                    );

                    if ($record->wasRecentlyCreated) $lolosCount++; else $updatedCount++;
                }
            }
        }

        DB::commit();
        return response()->json([
            'success' => true,
            'lolos_count' => $lolosCount,
            'updated_count' => $updatedCount,
            'total_processed' => $lolosCount + $updatedCount,
            'message' => "Berhasil sinkronisasi."
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error("Sync Lolos Error: " . $e->getMessage());
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

    /**
     * Hitung status kelolosan berdasarkan SCORE saja
     */
    private function hitungStatusKelolosan($attempt)
    {
        $ereg = $attempt->moodleUser->masterUser->ereg;
        
        if (!$ereg) {
            return [
                'lolos_jurusan1' => 'TIDAK LOLOS',
                'lolos_jurusan2' => 'TIDAK LOLOS',
                'passing_grade_jurusan1' => null,
                'passing_grade_jurusan2' => null
            ];
        }

        // GUNAKAN SCORE sebagai nilai utama
        $nilai = $attempt->score ?? $attempt->grade ?? 0;
        
        \Log::info("Checking kelolosan for user: " . ($attempt->moodleUser->username ?? 'Unknown'));
        \Log::info("SCORE yang digunakan: " . $nilai);
        \Log::info("Jurusan1: " . ($ereg->jurusan1 ?? 'NULL'));
        \Log::info("Jurusan2: " . ($ereg->jurusan2 ?? 'NULL'));

        // Hitung status jurusan 1
        $statusJurusan1 = 'TIDAK LOLOS';
        $pgJurusan1 = null;
        
        if ($ereg->jurusan1) {
            $pgJurusan1 = DB::table('program_studi')
                ->where('status_prodi', '1')
                ->where(function($query) use ($ereg) {
                    $query->where('kode_prodi', $ereg->jurusan1)
                          ->orWhere('nama_prodi', 'like', "%{$ereg->jurusan1}%")
                          ->orWhere('nama_prodi', $ereg->jurusan1);
                })
                ->value('passing_grade');

            \Log::info("Passing grade jurusan1: " . ($pgJurusan1 ?? 'NOT FOUND'));

            if ($pgJurusan1 !== null && $nilai >= $pgJurusan1) {
                $statusJurusan1 = 'LOLOS';
                \Log::info("Jurusan1 LOLOS: {$nilai} >= {$pgJurusan1}");
            } else {
                \Log::info("Jurusan1 TIDAK LOLOS: {$nilai} < " . ($pgJurusan1 ?? 'NULL'));
            }
        }

        // Hitung status jurusan 2
        $statusJurusan2 = 'TIDAK LOLOS';
        $pgJurusan2 = null;
        
        if ($ereg->jurusan2) {
            $pgJurusan2 = DB::table('program_studi')
                ->where('status_prodi', '1')
                ->where(function($query) use ($ereg) {
                    $query->where('kode_prodi', $ereg->jurusan2)
                          ->orWhere('nama_prodi', 'like', "%{$ereg->jurusan2}%")
                          ->orWhere('nama_prodi', $ereg->jurusan2);
                })
                ->value('passing_grade');

            \Log::info("Passing grade jurusan2: " . ($pgJurusan2 ?? 'NOT FOUND'));

            if ($pgJurusan2 !== null && $nilai >= $pgJurusan2) {
                $statusJurusan2 = 'LOLOS';
                \Log::info("Jurusan2 LOLOS: {$nilai} >= {$pgJurusan2}");
            } else {
                \Log::info("Jurusan2 TIDAK LOLOS: {$nilai} < " . ($pgJurusan2 ?? 'NULL'));
            }
        }

        return [
            'lolos_jurusan1' => $statusJurusan1,
            'lolos_jurusan2' => $statusJurusan2,
            'passing_grade_jurusan1' => $pgJurusan1,
            'passing_grade_jurusan2' => $pgJurusan2
        ];
    }

    /**
     * Tentukan jurusan yang lolos
     */
    private function tentukanJurusanLolos($attempt, $statusLolos)
    {
        $ereg = $attempt->moodleUser->masterUser->ereg;
        
        if (!$ereg) {
            return null;
        }

        $nilai = $attempt->score ?? $attempt->grade ?? 0;

        // Prioritaskan jurusan 1 jika lolos
        if ($statusLolos['lolos_jurusan1'] === 'LOLOS' && $ereg->jurusan1) {
            $programStudi = DB::table('program_studi')
                ->where('status_prodi', '1')
                ->where(function($query) use ($ereg) {
                    $query->where('kode_prodi', $ereg->jurusan1)
                          ->orWhere('nama_prodi', 'like', "%{$ereg->jurusan1}%")
                          ->orWhere('nama_prodi', $ereg->jurusan1);
                })
                ->first();

            return [
                'kode_prodi' => $programStudi ? $programStudi->kode_prodi : $ereg->jurusan1,
                'nama_prodi' => $programStudi ? $programStudi->nama_prodi : $ereg->jurusan1,
                'pilihan' => 1,
                'passing_grade' => $programStudi ? $programStudi->passing_grade : $statusLolos['passing_grade_jurusan1']
            ];
        }

        // Jika jurusan 1 tidak lolos, cek jurusan 2
        if ($statusLolos['lolos_jurusan2'] === 'LOLOS' && $ereg->jurusan2) {
            $programStudi = DB::table('program_studi')
                ->where('status_prodi', '1')
                ->where(function($query) use ($ereg) {
                    $query->where('kode_prodi', $ereg->jurusan2)
                          ->orWhere('nama_prodi', 'like', "%{$ereg->jurusan2}%")
                          ->orWhere('nama_prodi', $ereg->jurusan2);
                })
                ->first();

            return [
                'kode_prodi' => $programStudi ? $programStudi->kode_prodi : $ereg->jurusan2,
                'nama_prodi' => $programStudi ? $programStudi->nama_prodi : $ereg->jurusan2,
                'pilihan' => 2,
                'passing_grade' => $programStudi ? $programStudi->passing_grade : $statusLolos['passing_grade_jurusan2']
            ];
        }

        return null;
    }

    /**
     * Generate keterangan untuk record kelulusan
     */
    private function generateKeterangan($attempt, $jurusanLolos, $statusLolos)
    {
        $nilai = $attempt->score ?? $attempt->grade ?? 0;
        $keterangan = "Lulus pada pilihan {$jurusanLolos['pilihan']} - {$jurusanLolos['nama_prodi']}";
        
        if ($jurusanLolos['passing_grade']) {
            $selisih = $nilai - $jurusanLolos['passing_grade'];
            if ($selisih > 0) {
                $keterangan .= " | Selisih: +" . number_format($selisih, 2) . " dari PG";
            }
        }

        $keterangan .= " | Score: {$nilai}";
        
        if ($attempt->percentage) {
            $keterangan .= " ({$attempt->percentage}%)";
        }

        $keterangan .= " | J1: {$statusLolos['lolos_jurusan1']}, J2: {$statusLolos['lolos_jurusan2']}";

        return $keterangan;
    }

    /**
     * Get statistics for dashboard
     */
    public function getStatistics()
    {
        $totalLulus = MasterMoodleLulus::count();
        $lulusHariIni = MasterMoodleLulus::whereDate('tanggal_lulus', today())->count();
        $lulusMingguIni = MasterMoodleLulus::whereBetween('tanggal_lulus', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $lulusBulanIni = MasterMoodleLulus::whereMonth('tanggal_lulus', now()->month)
            ->whereYear('tanggal_lulus', now()->year)
            ->count();

        return response()->json([
            'total_lulus' => $totalLulus,
            'lulus_hari_ini' => $lulusHariIni,
            'lulus_minggu_ini' => $lulusMingguIni,
            'lulus_bulan_ini' => $lulusBulanIni
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $attempt = MoodleQuizAttempt::with([
            'moodleUser',
            'moodleUser.masterUser',
            'moodleUser.masterUser.user',
            'moodleUser.masterUser.ereg',
            'course'
        ])->findOrFail($id);
        
        return view('admin.moodle.complete.show', compact('attempt'));
    }

    public function edit($id)
    {
        $attempt = MoodleQuizAttempt::findOrFail($id);
        
        if (request()->ajax()) {
            return response()->json($attempt);
        }
        
        return view('admin.moodle.complete.edit', compact('attempt'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'score' => 'required|numeric|min:0|max:100', // Validasi nilai 0-100
        ]);

        $attempt = MoodleQuizAttempt::findOrFail($id);

        // Ambil nilai baru dari input
        $newScore = $request->score; // Ini adalah Nilai Skala 100 (contoh: 38.00)
        
        // Ambil max grade (default 100 jika null)
        $maxGrade = $attempt->max_grade > 0 ? $attempt->max_grade : 100;

        // Hitung ulang Raw Grade (Nilai asli Moodle)
        // Rumus balik: (Score / 100) * MaxGrade
        $newRawGrade = ($newScore / 100) * $maxGrade;

        // Hitung status kelulusan baru (Threshold 60)
        $isPassed = $newScore >= 60;

        $attempt->update([
            'score' => $newScore,           // Simpan nilai skala 100
            'percentage' => $newScore,      // Percentage sama dengan score skala 100
            'grade' => $newRawGrade,        // Simpan raw grade hasil konversi balik
            'sumgrades' => $newRawGrade,    // Simpan sumgrades
            'is_passed' => $isPassed,
            'is_manual_edit' => true,       // <--- PENTING: Set Flag ini ke True
            'sync_notes' => $attempt->sync_notes . " | Manual Edit: {$newScore} by Admin (" . now() . ")"
        ]);

        return redirect()
            ->route('admin.moodle.quiz-complete.index')
            ->with('success', 'Nilai berhasil diperbarui dan dikunci dari sinkronisasi otomatis.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $attempt = MoodleQuizAttempt::findOrFail($id);
        $attempt->delete();

        return redirect()->route('admin.moodle.quiz-complete.index')
            ->with('success', 'Data quiz attempt berhasil dihapus.');
    }

    public function byUser($moodleUserId): View
    {
        $moodleUser = MasterMoodle::with(['masterUser', 'masterUser.user', 'masterUser.ereg'])
            ->where('moodle_user_id', $moodleUserId)
            ->firstOrFail();
            
        $attempts = MoodleQuizAttempt::where('moodle_user_id', $moodleUserId)
            ->with(['course', 'moodleUser.masterUser.ereg'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.moodle.complete.by-user', compact('attempts', 'moodleUser'));
    }

    /**
     * Show attempts by specific quiz
     */
    public function byQuiz($quizId): View
    {
        $quiz = MoodleUserCourse::where('course_id', $quizId)->firstOrFail();
            
        $attempts = MoodleQuizAttempt::where('quiz_id', $quizId)
            ->with(['moodleUser', 'moodleUser.masterUser.user', 'moodleUser.masterUser.ereg'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.moodle.complete.by-quiz', compact('attempts', 'quiz'));
    }

    /**
     * Show participants with specific grade range
     */
    public function byGradeRange(Request $request): View
    {
        $min = $request->get('min', 0);
        $max = $request->get('max', 100);
        
        $attempts = MoodleQuizAttempt::with([
            'moodleUser',
            'moodleUser.masterUser',
            'moodleUser.masterUser.user',
            'moodleUser.masterUser.ereg',
            'course'
        ])
        ->whereBetween('score', [$min, $max])  // Gunakan score bukan grade
        ->orderBy('score', 'desc')
        ->paginate(20);

        return view('admin.moodle.complete.grade-range', compact('attempts', 'min', 'max'));
    }

    /**
     * Show participants who passed (lolos)
     */
    public function showLolos(Request $request): View
    {
        $query = MoodleQuizAttempt::with([
            'moodleUser',
            'moodleUser.masterUser',
            'moodleUser.masterUser.user',
            'moodleUser.masterUser.ereg',
            'course'
        ])
        ->where('state', 'finished')
        ->orderBy('score', 'desc');  // Gunakan score untuk sorting

        // Filter hanya yang lolos
        $attempts = $query->get()->filter(function($attempt) {
            $statusLolos = $this->hitungStatusKelolosan($attempt);
            return $statusLolos['lolos_jurusan1'] === 'LOLOS' || 
                   $statusLolos['lolos_jurusan2'] === 'LOLOS';
        });

        $totalLolos = $attempts->count();
        $averageScore = $attempts->avg('score');
        $highestScore = $attempts->max('score');

        return view('admin.moodle.complete.lolos', compact('attempts', 'totalLolos', 'averageScore', 'highestScore'));
    }
}