<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterMoodle;
use App\Models\User;
use App\Models\MasterMoodleLulus;
use App\Models\MoodleQuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MasterLulusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
     public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status', 'all');

        $query = MasterMoodle::with([
                'user', 
                'user.masterEreg',
                'masterMoodleLulus', // Load data dari master_moodle_lulus
                'quizAttempts' => function($query) {
                    $query->where('state', 'finished')
                          ->orderBy('attempt_id', 'desc');
                }
            ])
            ->where('status', 'success')
            ->where('enrol_status', 'enrolled')
            ->whereHas('user', function($userQuery) {
                // Hanya tampilkan user yang sudah mengisi master_ereg
                $userQuery->whereHas('masterEreg');
            });

        // Filter berdasarkan search
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('moodle_user_id', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%")
                               ->orWhereHas('masterEreg', function($eregQuery) use ($search) {
                                   $eregQuery->where('nama', 'like', "%{$search}%")
                                            ->orWhere('gmail', 'phone', 'like', "%{$search}%");
                               });
                  });
            });
        }

        // Filter berdasarkan status kelulusan
        if ($status === 'lulus') {
            $query->where(function($q) {
                $q->where('sync_notes', 'like', '%LULUS%')
                  ->orWhereHas('masterMoodleLulus')
                  ->orWhereHas('quizAttempts', function($attemptQuery) {
                      $attemptQuery->where('state', 'finished')
                                  ->where('sumgrades', '>=', 60); // Asumsi nilai minimal 60 untuk lulus
                  });
            });
        } elseif ($status === 'belum_lulus') {
            $query->where(function($q) {
                $q->whereNull('sync_notes')
                  ->where('sync_notes', 'not like', '%LULUS%')
                  ->whereDoesntHave('masterMoodleLulus')
                  ->where(function($subQ) {
                      $subQ->whereDoesntHave('quizAttempts')
                           ->orWhereHas('quizAttempts', function($attemptQuery) {
                               $attemptQuery->where('state', 'finished')
                                           ->where('sumgrades', '<', 60);
                           });
                  });
            });
        }

        $masterMoodle = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.master-moodle-lulus.index', compact('masterMoodle', 'search', 'status'));
    }

    /**
     * Luluskan user secara manual
     */
    public function luluskanUser(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $masterMoodle = MasterMoodle::with([
                'user.masterEreg', 
                'masterMoodleLulus',
                'quizAttempts'
            ])->findOrFail($id);

            // Cek apakah user sudah mengisi master_ereg
            if (!$masterMoodle->user || !$masterMoodle->user->masterEreg) {
                return response()->json([
                    'success' => false,
                    'message' => 'User belum mengisi data master ereg!'
                ], 400);
            }

            // Update sync_notes untuk menandai sebagai lulus
            $masterMoodle->update([
                'sync_notes' => 'LULUS - Diluluskan secara manual oleh admin pada ' . now()->format('Y-m-d H:i:s'),
                'updated_at' => now()
            ]);

            // Nilai otomatis 100 untuk kelulusan manual dan attempt_count = 1
            $nilaiManual = 100.00;
            $attemptCountManual = 1;
            $keteranganManual = 'Diluluskan secara manual oleh admin dengan nilai 100';

            // Cek apakah sudah ada di master_moodle_lulus, jika belum buat record baru
            if (!$masterMoodle->masterMoodleLulus) {
                MasterMoodleLulus::create([
                    'master_user_id' => $masterMoodle->master_user_id,
                    'moodle_user_id' => $masterMoodle->moodle_user_id,
                    'username' => $masterMoodle->username,
                    'email' => $masterMoodle->user->email,
                    'nama_lengkap' => $masterMoodle->user->masterEreg->nama ?? $masterMoodle->user->name,
                    'prodi_lulus' => $masterMoodle->user->masterEreg->prodi_pilihan ?? null,
                    'nama_prodi_lulus' => $masterMoodle->user->masterEreg->nama_prodi ?? null,
                    'pilihan_lulus' => $masterMoodle->user->masterEreg->pilihan ?? null,
                    'quiz_id' => 1197, // Default quiz ID
                    'nilai_quiz' => $nilaiManual, // Nilai otomatis 100 untuk manual
                    'attempt_count' => $attemptCountManual, // Attempt count otomatis 1 untuk manual
                    'status_kelulusan' => 'LULUS',
                    'keterangan' => $keteranganManual,
                    'tanggal_lulus' => now(),
                ]);
            } else {
                // Update record yang sudah ada dengan nilai 100 dan attempt_count 1
                $masterMoodle->masterMoodleLulus->update([
                    'nilai_quiz' => $nilaiManual, // Update nilai menjadi 100
                    'attempt_count' => $attemptCountManual, // Update attempt_count menjadi 1
                    'status_kelulusan' => 'LULUS',
                    'keterangan' => $keteranganManual,
                    'tanggal_lulus' => now(),
                    'updated_at' => now()
                ]);
            }

            // Update status user jika diperlukan
            $user = $masterMoodle->user;
            if ($user) {
                Log::info("User {$user->id} diluluskan secara manual melalui MasterMoodle", [
                    'master_moodle_id' => $id,
                    'admin_id' => auth()->id(),
                    'user_has_ereg' => !is_null($user->masterEreg),
                    'has_quiz_attempts' => $masterMoodle->quizAttempts->count(),
                    'has_master_lulus_record' => !is_null($masterMoodle->masterMoodleLulus),
                    'nilai_diberikan' => $nilaiManual,
                    'attempt_count_diberikan' => $attemptCountManual
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User berhasil diluluskan dengan nilai 100!',
                'data' => [
                    'id' => $masterMoodle->id,
                    'username' => $masterMoodle->username,
                    'sync_notes' => $masterMoodle->sync_notes,
                    'user_has_ereg' => !is_null($masterMoodle->user->masterEreg),
                    'quiz_attempts_count' => $masterMoodle->quizAttempts->count(),
                    'master_lulus_created' => true,
                    'nilai_diberikan' => $nilaiManual,
                    'attempt_count_diberikan' => $attemptCountManual
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error meluluskan user: ' . $e->getMessage(), [
                'master_moodle_id' => $id,
                'admin_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal meluluskan user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Luluskan multiple users sekaligus
     */
    public function luluskanMultiple(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:master_moodle,id'
        ]);

        try {
            DB::beginTransaction();

            $ids = $request->ids;
            $successCount = 0;
            $failedCount = 0;
            $results = [];

            // Nilai otomatis 100 untuk kelulusan manual dan attempt_count = 1
            $nilaiManual = 100.00;
            $attemptCountManual = 1;
            $keteranganManual = 'Diluluskan secara manual oleh admin (bulk) dengan nilai 100';

            foreach ($ids as $id) {
                try {
                    $masterMoodle = MasterMoodle::with([
                        'user.masterEreg', 
                        'masterMoodleLulus',
                        'quizAttempts'
                    ])->find($id);
                    
                    if ($masterMoodle && 
                        $masterMoodle->status === 'success' && 
                        $masterMoodle->enrol_status === 'enrolled' &&
                        $masterMoodle->user && 
                        $masterMoodle->user->masterEreg) {
                        
                        // Update sync_notes
                        $masterMoodle->update([
                            'sync_notes' => 'LULUS - Diluluskan secara manual oleh admin pada ' . now()->format('Y-m-d H:i:s'),
                            'updated_at' => now()
                        ]);

                        // Update atau buat record di master_moodle_lulus
                        if (!$masterMoodle->masterMoodleLulus) {
                            MasterMoodleLulus::create([
                                'master_user_id' => $masterMoodle->master_user_id,
                                'moodle_user_id' => $masterMoodle->moodle_user_id,
                                'username' => $masterMoodle->username,
                                'email' => $masterMoodle->user->email,
                                'nama_lengkap' => $masterMoodle->user->masterEreg->nama ?? $masterMoodle->user->name,
                                'prodi_lulus' => $masterMoodle->user->masterEreg->prodi_pilihan ?? null,
                                'nama_prodi_lulus' => $masterMoodle->user->masterEreg->nama_prodi ?? null,
                                'pilihan_lulus' => $masterMoodle->user->masterEreg->pilihan ?? null,
                                'quiz_id' => 1197,
                                'nilai_quiz' => $nilaiManual, // Nilai otomatis 100 untuk manual
                                'attempt_count' => $attemptCountManual, // Attempt count otomatis 1 untuk manual
                                'status_kelulusan' => 'LULUS',
                                'keterangan' => $keteranganManual,
                                'tanggal_lulus' => now(),
                            ]);
                        } else {
                            $masterMoodle->masterMoodleLulus->update([
                                'nilai_quiz' => $nilaiManual, // Update nilai menjadi 100
                                'attempt_count' => $attemptCountManual, // Update attempt_count menjadi 1
                                'status_kelulusan' => 'LULUS',
                                'keterangan' => $keteranganManual,
                                'tanggal_lulus' => now(),
                                'updated_at' => now()
                            ]);
                        }
                        
                        $successCount++;
                        $results[] = [
                            'id' => $id,
                            'username' => $masterMoodle->username,
                            'status' => 'success',
                            'message' => 'Berhasil diluluskan dengan nilai 100'
                        ];
                    } else {
                        $failedCount++;
                        $errorMessage = 'User tidak memenuhi syarat';
                        
                        if (!$masterMoodle->user) {
                            $errorMessage = 'User tidak ditemukan';
                        } elseif (!$masterMoodle->user->masterEreg) {
                            $errorMessage = 'User belum mengisi master ereg';
                        } elseif ($masterMoodle->status !== 'success') {
                            $errorMessage = 'Status moodle tidak success';
                        } elseif ($masterMoodle->enrol_status !== 'enrolled') {
                            $errorMessage = 'User belum enrolled di course';
                        }
                        
                        $results[] = [
                            'id' => $id,
                            'username' => $masterMoodle ? $masterMoodle->username : 'Unknown',
                            'status' => 'failed',
                            'message' => $errorMessage
                        ];
                    }
                } catch (\Exception $e) {
                    $failedCount++;
                    $results[] = [
                        'id' => $id,
                        'status' => 'failed',
                        'message' => $e->getMessage()
                    ];
                    Log::error("Error meluluskan user {$id}: " . $e->getMessage());
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil meluluskan {$successCount} user dengan nilai 100, gagal: {$failedCount}",
                'data' => [
                    'success_count' => $successCount,
                    'failed_count' => $failedCount,
                    'results' => $results,
                    'nilai_diberikan' => $nilaiManual,
                    'attempt_count_diberikan' => $attemptCountManual
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error meluluskan multiple users: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal meluluskan users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Batalkan kelulusan user - HANYA untuk kelulusan manual
     */
    public function batalkanLulus(Request $request, $id)
    {
        try {
            $masterMoodle = MasterMoodle::with([
                'user.masterEreg',
                'masterMoodleLulus',
                'quizAttempts' => function($query) {
                    $query->where('state', 'finished');
                }
            ])->findOrFail($id);

            // CEK: Hanya boleh membatalkan kelulusan manual
            if (!$this->isLulusManual($masterMoodle)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat membatalkan kelulusan! User ini lulus melalui sistem (bukan manual).'
                ], 400);
            }

            // Update sync_notes di master_moodle
            $masterMoodle->update([
                'sync_notes' => 'Kelulusan dibatalkan oleh admin pada ' . now()->format('Y-m-d H:i:s'),
                'updated_at' => now()
            ]);

            // Update status di master_moodle_lulus jika ada
            if ($masterMoodle->masterMoodleLulus) {
                $masterMoodle->masterMoodleLulus->update([
                    'status_kelulusan' => 'TIDAK LULUS',
                    'keterangan' => 'Kelulusan dibatalkan oleh admin',
                    'updated_at' => now()
                ]);
            }

            Log::info("Kelulusan manual user {$masterMoodle->username} dibatalkan oleh admin", [
                'master_moodle_id' => $id,
                'admin_id' => auth()->id(),
                'username' => $masterMoodle->username
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status kelulusan berhasil dibatalkan!',
                'data' => [
                    'id' => $masterMoodle->id,
                    'username' => $masterMoodle->username,
                    'sync_notes' => $masterMoodle->sync_notes,
                    'user_has_ereg' => $masterMoodle->user && !is_null($masterMoodle->user->masterEreg),
                    'master_lulus_updated' => !is_null($masterMoodle->masterMoodleLulus)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error membatalkan kelulusan user: ' . $e->getMessage(), [
                'master_moodle_id' => $id,
                'admin_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan kelulusan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cek apakah kelulusan bersumber dari manual (bukan dari quiz)
     */
    private function isLulusManual($masterMoodle)
    {
        $status = $this->calculateUserStatus($masterMoodle);
        
        // Jika tidak lulus, return false
        if (!$status['is_lulus']) {
            return false;
        }

        // Cek sumber kelulusan
        $sources = $status['criteria'];
        
        // Jika hanya dari sync_notes (manual), boleh dibatalkan
        if (count($sources) === 1 && in_array('sync_notes', $sources)) {
            return true;
        }

        // Jika dari master_moodle_lulus dengan nilai 100 dan attempt_count 1 (tanda manual)
        if ($masterMoodle->masterMoodleLulus) {
            $isNilaiManual = $masterMoodle->masterMoodleLulus->nilai_quiz == 100.00;
            $isAttemptManual = $masterMoodle->masterMoodleLulus->attempt_count == 1;
            $isKeteranganManual = str_contains($masterMoodle->masterMoodleLulus->keterangan ?? '', 'manual');
            
            if ($isNilaiManual && $isAttemptManual && $isKeteranganManual) {
                return true;
            }
        }

        // Jika ada sumber dari quiz attempt, tidak boleh dibatalkan
        if (in_array('quiz_attempt', $sources)) {
            return false;
        }

        // Default: tidak boleh dibatalkan
        return false;
    }

    /**
     * Get statistics
     */
    public function statistics()
    {
        // Users yang memenuhi syarat (sudah ereg)
        $totalUsers = MasterMoodle::where('status', 'success')
            ->where('enrol_status', 'enrolled')
            ->whereHas('user', function($userQuery) {
                $userQuery->whereHas('masterEreg');
            })
            ->count();

        // Users yang lulus berdasarkan berbagai kriteria
        $lulusFromSyncNotes = MasterMoodle::where('status', 'success')
            ->where('enrol_status', 'enrolled')
            ->where('sync_notes', 'like', '%LULUS%')
            ->whereHas('user', function($userQuery) {
                $userQuery->whereHas('masterEreg');
            })
            ->count();

        $lulusFromMasterLulus = MasterMoodle::where('status', 'success')
            ->where('enrol_status', 'enrolled')
            ->whereHas('masterMoodleLulus', function($query) {
                $query->where('status_kelulusan', 'LULUS');
            })
            ->whereHas('user', function($userQuery) {
                $userQuery->whereHas('masterEreg');
            })
            ->count();

        $lulusFromQuiz = MasterMoodle::where('status', 'success')
            ->where('enrol_status', 'enrolled')
            ->whereHas('quizAttempts', function($query) {
                $query->where('state', 'finished')
                      ->where('sumgrades', '>=', 60); // Nilai minimal 60 untuk lulus
            })
            ->whereHas('user', function($userQuery) {
                $userQuery->whereHas('masterEreg');
            })
            ->count();

        // Gabungkan semua kriteria lulus (unik user)
        $lulusUsers = MasterMoodle::where('status', 'success')
            ->where('enrol_status', 'enrolled')
            ->whereHas('user', function($userQuery) {
                $userQuery->whereHas('masterEreg');
            })
            ->where(function($query) {
                $query->where('sync_notes', 'like', '%LULUS%')
                      ->orWhereHas('masterMoodleLulus', function($q) {
                          $q->where('status_kelulusan', 'LULUS');
                      })
                      ->orWhereHas('quizAttempts', function($q) {
                          $q->where('state', 'finished')
                            ->where('sumgrades', '>=', 60);
                      });
            })
            ->count();

        $belumLulusUsers = $totalUsers - $lulusUsers;

        // Statistics tambahan
        $usersWithQuizAttempts = MasterMoodle::where('status', 'success')
            ->where('enrol_status', 'enrolled')
            ->whereHas('user', function($userQuery) {
                $userQuery->whereHas('masterEreg');
            })
            ->whereHas('quizAttempts')
            ->count();

        $usersWithFinishedQuiz = MasterMoodle::where('status', 'success')
            ->where('enrol_status', 'enrolled')
            ->whereHas('user', function($userQuery) {
                $userQuery->whereHas('masterEreg');
            })
            ->whereHas('quizAttempts', function($query) {
                $query->where('state', 'finished');
            })
            ->count();

        $usersWithMasterLulus = MasterMoodle::where('status', 'success')
            ->where('enrol_status', 'enrolled')
            ->whereHas('user', function($userQuery) {
                $userQuery->whereHas('masterEreg');
            })
            ->whereHas('masterMoodleLulus')
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_users' => $totalUsers,
                'lulus_users' => $lulusUsers,
                'belum_lulus_users' => $belumLulusUsers,
                'lulus_percentage' => $totalUsers > 0 ? round(($lulusUsers / $totalUsers) * 100, 2) : 0,
                
                // Breakdown lulus
                'lulus_from_sync_notes' => $lulusFromSyncNotes,
                'lulus_from_master_lulus' => $lulusFromMasterLulus,
                'lulus_from_quiz' => $lulusFromQuiz,
                
                // Additional stats
                'users_with_quiz_attempts' => $usersWithQuizAttempts,
                'users_with_finished_quiz' => $usersWithFinishedQuiz,
                'users_with_master_lulus' => $usersWithMasterLulus,
                
                // Users tanpa ereg (info saja)
                'users_without_ereg' => MasterMoodle::where('status', 'success')
                    ->where('enrol_status', 'enrolled')
                    ->whereDoesntHave('user.masterEreg')
                    ->count()
            ]
        ]);
    }

    /**
     * Get detailed user status
     */
    public function userDetail($id)
    {
        try {
            $masterMoodle = MasterMoodle::with([
                'user.masterEreg',
                'masterMoodleLulus',
                'quizAttempts' => function($query) {
                    $query->orderBy('attempt_id', 'desc');
                }
            ])->findOrFail($id);

            $status = $this->calculateUserStatus($masterMoodle);

            return response()->json([
                'success' => true,
                'data' => [
                    'master_moodle' => $masterMoodle,
                    'status' => $status
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate user status based on multiple criteria
     */
    private function calculateUserStatus($masterMoodle)
    {
        $status = [
            'is_lulus' => false,
            'criteria' => [],
            'details' => [],
            'sources' => []
        ];

        // Cek dari sync_notes (manual)
        if (str_contains($masterMoodle->sync_notes ?? '', 'LULUS')) {
            $status['is_lulus'] = true;
            $status['criteria'][] = 'sync_notes';
            $status['sources'][] = 'manual';
            $status['details']['sync_notes'] = $masterMoodle->sync_notes;
        }

        // Cek dari master_moodle_lulus
        if ($masterMoodle->masterMoodleLulus && $masterMoodle->masterMoodleLulus->status_kelulusan === 'LULUS') {
            $status['is_lulus'] = true;
            $status['criteria'][] = 'master_moodle_lulus';
            
            // Tentukan apakah ini manual atau sistem
            if ($masterMoodle->masterMoodleLulus->nilai_quiz == 100.00 && 
                $masterMoodle->masterMoodleLulus->attempt_count == 1 &&
                str_contains($masterMoodle->masterMoodleLulus->keterangan ?? '', 'manual')) {
                $status['sources'][] = 'manual';
            } else {
                $status['sources'][] = 'sistem';
            }
            
            $status['details']['master_moodle_lulus'] = $masterMoodle->masterMoodleLulus->toArray();
        }

        // Cek dari quiz attempts (sistem)
        $bestAttempt = $masterMoodle->quizAttempts
            ->where('state', 'finished')
            ->sortByDesc('sumgrades')
            ->first();

        if ($bestAttempt && $bestAttempt->sumgrades >= 60) {
            $status['is_lulus'] = true;
            $status['criteria'][] = 'quiz_attempt';
            $status['sources'][] = 'sistem';
            $status['details']['quiz_attempt'] = [
                'attempt_id' => $bestAttempt->attempt_id,
                'score' => $bestAttempt->sumgrades,
                'time_finish' => $bestAttempt->timefinish
            ];
        }

        return $status;
    }
    public function searchAjax(Request $request)
{
    $search = $request->get('search');
    $status = $request->get('status', 'all');

    $query = MasterMoodle::with([
            'user', 
            'masterMoodleLulus'
        ])
        ->where('status', 'success')
        ->where('enrol_status', 'enrolled');

    if ($search) {
        $query->where(function($q) use ($search) {
            $q->where('username', 'like', "%{$search}%")
              ->orWhere('moodle_user_id', 'like', "%{$search}%") // Tambah search moodle ID
              ->orWhereHas('user', function($u) use ($search) {
                  $u->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
              });
        });
    }

    if ($status === 'lulus') {
        $query->where(function($q) {
            $q->where('sync_notes', 'like', '%LULUS%')
              ->orWhereHas('masterMoodleLulus');
        });
    } elseif ($status === 'belum_lulus') {
        $query->whereDoesntHave('masterMoodleLulus')
              ->where('sync_notes', 'not like', '%LULUS%');
    }

    // Ambil semua hasil yang relevan (Limit tinggi untuk simulasi "semua")
    $results = $query->limit(2000)->get();

    return response()->json([
        'success' => true,
        'data' => $results->map(function($item) {
            $statusLulus = $item->getLulusStatus();
            return [
                'id' => $item->id,
                'username' => $item->username,
                'moodle_user_id' => $item->moodle_user_id,
                'name' => $item->user->name ?? 'N/A',
                'is_lulus' => $statusLulus['is_lulus'],
                'quiz_score' => $item->masterMoodleLulus->nilai_quiz ?? 0,
                'sources' => array_map(fn($s) => ucfirst(str_replace('_', ' ', $s)), $statusLulus['sources'])
            ];
        })
    ]);
}
}