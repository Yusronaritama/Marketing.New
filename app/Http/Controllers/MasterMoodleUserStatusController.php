<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MasterMoodleUserStatusController extends Controller
{
    /**
     * UPDATE STATUS KELULUSAN OTOMATIS BERDASARKAN ATTEMPT COUNT DAN NILAI QUIZ
     */
    public function updateStatusKelulusanOtomatis(Request $request)
    {
        try {
            DB::beginTransaction();

            $limit = $request->input('limit', 1000);
            $dryRun = $request->input('dry_run', false); // Mode test tanpa update

            Log::info('Starting automatic status kelulusan update', [
                'limit' => $limit,
                'dry_run' => $dryRun
            ]);

            // Ambil data dari master_moodle_users
            $users = DB::table('master_moodle_users')
                     ->select('*')
                     ->limit($limit)
                     ->get();

            $stats = [
                'total' => count($users),
                'updated' => 0,
                'no_change' => 0,
                'errors' => 0,
                'details' => [
                    'attempt_count_updated' => 0,
                    'nilai_quiz_updated' => 0,
                    'both_conditions_updated' => 0,
                    'status_changes' => []
                ]
            ];

            foreach ($users as $user) {
                try {
                    $newStatus = $this->determineStatus($user);
                    $currentStatus = $user->status_kelulusan;

                    // Jika status berubah
                    if ($newStatus !== $currentStatus) {
                        if (!$dryRun) {
                            // Update database
                            DB::table('master_moodle_users')
                                ->where('id', $user->id)
                                ->update([
                                    'status_kelulusan' => $newStatus,
                                    'updated_at' => now()
                                ]);
                        }

                        $stats['updated']++;
                        $stats['details']['status_changes'][] = [
                            'id' => $user->id,
                            'master_user_id' => $user->master_user_id,
                            'moodle_user_id' => $user->moodle_user_id,
                            'from' => $currentStatus,
                            'to' => $newStatus,
                            'attempt_count' => $user->attempt_count,
                            'nilai_quiz' => $user->nilai_quiz
                        ];

                        // Kategorikan berdasarkan kondisi
                        if ($user->attempt_count >= 1 && $user->nilai_quiz > 0) {
                            $stats['details']['both_conditions_updated']++;
                        } elseif ($user->attempt_count >= 1) {
                            $stats['details']['attempt_count_updated']++;
                        } elseif ($user->nilai_quiz > 0) {
                            $stats['details']['nilai_quiz_updated']++;
                        }

                        Log::info('Status updated', [
                            'user_id' => $user->id,
                            'from' => $currentStatus,
                            'to' => $newStatus,
                            'attempt_count' => $user->attempt_count,
                            'nilai_quiz' => $user->nilai_quiz
                        ]);
                    } else {
                        $stats['no_change']++;
                    }

                } catch (\Exception $e) {
                    Log::error('Error updating user status: ' . $e->getMessage(), [
                        'user_id' => $user->id ?? 'unknown'
                    ]);
                    $stats['errors']++;
                }
            }

            DB::commit();

            Log::info('Automatic status update completed', $stats);

            $response = [
                'success' => true,
                'message' => $dryRun ? 
                    'Dry run completed - Check details for proposed changes' : 
                    'Status kelulusan berhasil diupdate otomatis',
                'stats' => $stats,
                'dry_run' => $dryRun
            ];

            if ($dryRun) {
                $response['message'] = 'DRY RUN: No actual updates were made';
            }

            return response()->json($response);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error in automatic status update: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Gagal mengupdate status kelulusan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * TENTUKAN STATUS BERDASARKAN ATTEMPT COUNT DAN NILAI QUIZ
     */
    private function determineStatus($user)
    {
        $attemptCount = $user->attempt_count ?? 0;
        $nilaiQuiz = $user->nilai_quiz ?? 0;

        // Jika sudah ada attempt (attempt_count >= 1) ATAU sudah ada nilai (> 0)
        if ($attemptCount >= 1 || $nilaiQuiz > 0) {
            return 'Sudah mengerjakan quiz';
        }

        // Default status
        return 'Belum mengerjakan quiz';
    }

    /**
     * UPDATE STATUS DENGAN LOGIKA LEBIH DETAIL
     */
    public function updateStatusKelulusanDetail(Request $request)
    {
        try {
            DB::beginTransaction();

            $limit = $request->input('limit', 1000);
            $dryRun = $request->input('dry_run', false);

            Log::info('Starting detailed status kelulusan update', [
                'limit' => $limit,
                'dry_run' => $dryRun
            ]);

            $users = DB::table('master_moodle_users')
                     ->select('*')
                     ->limit($limit)
                     ->get();

            $stats = [
                'total' => count($users),
                'updated' => 0,
                'no_change' => 0,
                'errors' => 0,
                'categories' => [
                    'sudah_mengerjakan' => 0,
                    'belum_mengerjakan' => 0,
                    'lulus' => 0,
                    'tidak_lulus' => 0,
                    'sudah_attempt_tanpa_nilai' => 0
                ]
            ];

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

                        $stats['updated']++;
                        
                        // Kategorikan
                        if (strpos($newStatus, 'Sudah mengerjakan') !== false) {
                            $stats['categories']['sudah_mengerjakan']++;
                        } elseif (strpos($newStatus, 'Belum mengerjakan') !== false) {
                            $stats['categories']['belum_mengerjakan']++;
                        } elseif (strpos($newStatus, 'LULUS') !== false) {
                            $stats['categories']['lulus']++;
                        } elseif (strpos($newStatus, 'TIDAK LULUS') !== false) {
                            $stats['categories']['tidak_lulus']++;
                        } elseif (strpos($newStatus, 'Sudah attempt') !== false) {
                            $stats['categories']['sudah_attempt_tanpa_nilai']++;
                        }

                    } else {
                        $stats['no_change']++;
                    }

                } catch (\Exception $e) {
                    Log::error('Error in detailed status update: ' . $e->getMessage(), [
                        'user_id' => $user->id ?? 'unknown'
                    ]);
                    $stats['errors']++;
                }
            }

            DB::commit();

            Log::info('Detailed status update completed', $stats);

            return response()->json([
                'success' => true,
                'message' => $dryRun ? 
                    'Dry run completed for detailed status update' : 
                    'Status kelulusan detail berhasil diupdate',
                'stats' => $stats,
                'dry_run' => $dryRun
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error in detailed status update: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Gagal mengupdate status detail: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * LOGIKA STATUS DETAIL
     */
    private function determineDetailedStatus($user)
    {
        $attemptCount = $user->attempt_count ?? 0;
        $nilaiQuiz = $user->nilai_quiz ?? 0;
        $prodiLulus = $user->prodi_lulus ?? '';
        $statusSaatIni = $user->status_kelulusan ?? '';

        // Jika sudah ada status LULUS atau TIDAK LULUS, pertahankan
        if (strpos($statusSaatIni, 'LULUS') !== false || strpos($statusSaatIni, 'TIDAK LULUS') !== false) {
            return $statusSaatIni;
        }

        // Jika sudah ada prodi lulus dan nilai > 0
        if (!empty($prodiLulus) && $nilaiQuiz > 0) {
            return 'LULUS - ' . ($user->nama_prodi_lulus ?? $prodiLulus);
        }

        // Jika ada attempt tapi belum ada nilai
        if ($attemptCount >= 1 && $nilaiQuiz == 0) {
            return 'Sudah attempt tapi belum ada nilai';
        }

        // Jika sudah ada nilai tapi belum tentu lulus
        if ($nilaiQuiz > 0) {
            // Cek apakah memenuhi passing grade (logika sederhana)
            $isLulus = $this->checkKelulusanSederhana($user);
            if ($isLulus) {
                return 'LULUS - Nilai: ' . $nilaiQuiz;
            } else {
                return 'TIDAK LULUS - Nilai: ' . $nilaiQuiz;
            }
        }

        // Jika ada attempt
        if ($attemptCount >= 1) {
            return 'Sudah mengerjakan quiz';
        }

        // Default
        return 'Belum mengerjakan quiz';
    }

    /**
     * CEK KELULUSAN SEDERHANA BERDASARKAN NILAI
     */
    private function checkKelulusanSederhana($user)
    {
        $nilaiQuiz = $user->nilai_quiz ?? 0;
        
        // Logika sederhana: jika nilai >= 17 dianggap lulus
        // Anda bisa menyesuaikan dengan passing grade yang sebenarnya
        return $nilaiQuiz >= 17;
    }

    /**
     * FIX STATUS UNTUK USER DENGAN ATTEMPT COUNT >= 1 ATAU NILAI > 0
     */
    public function fixStatusForAttemptedUsers(Request $request)
    {
        try {
            DB::beginTransaction();

            $dryRun = $request->input('dry_run', false);

            Log::info('Fixing status for users with attempts or nilai', [
                'dry_run' => $dryRun
            ]);

            // Cari user yang attempt_count >= 1 ATAU nilai_quiz > 0 tapi status masih "Belum mengerjakan quiz"
            $usersToFix = DB::table('master_moodle_users')
                          ->where('status_kelulusan', 'Belum mengerjakan quiz')
                          ->where(function($query) {
                              $query->where('attempt_count', '>=', 1)
                                    ->orWhere('nilai_quiz', '>', 0);
                          })
                          ->select('*')
                          ->get();

            $stats = [
                'total_found' => count($usersToFix),
                'updated' => 0,
                'errors' => 0,
                'fixed_users' => []
            ];

            foreach ($usersToFix as $user) {
                try {
                    $newStatus = $this->determineStatusBasedOnConditions($user);

                    if (!$dryRun) {
                        DB::table('master_moodle_users')
                            ->where('id', $user->id)
                            ->update([
                                'status_kelulusan' => $newStatus,
                                'updated_at' => now()
                            ]);
                    }

                    $stats['updated']++;
                    $stats['fixed_users'][] = [
                        'id' => $user->id,
                        'master_user_id' => $user->master_user_id,
                        'moodle_user_id' => $user->moodle_user_id,
                        'attempt_count' => $user->attempt_count,
                        'nilai_quiz' => $user->nilai_quiz,
                        'old_status' => 'Belum mengerjakan quiz',
                        'new_status' => $newStatus
                    ];

                    Log::info('Fixed user status', [
                        'user_id' => $user->id,
                        'attempt_count' => $user->attempt_count,
                        'nilai_quiz' => $user->nilai_quiz,
                        'new_status' => $newStatus
                    ]);

                } catch (\Exception $e) {
                    Log::error('Error fixing user status: ' . $e->getMessage(), [
                        'user_id' => $user->id ?? 'unknown'
                    ]);
                    $stats['errors']++;
                }
            }

            DB::commit();

            Log::info('Status fix completed', $stats);

            return response()->json([
                'success' => true,
                'message' => $dryRun ? 
                    'Dry run completed - Found users to fix' : 
                    'Status berhasil difix untuk user dengan attempt/nilai',
                'stats' => $stats,
                'dry_run' => $dryRun
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error fixing status: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Gagal memperbaiki status: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * TENTUKAN STATUS BERDASARKAN KONDISI
     */
    private function determineStatusBasedOnConditions($user)
    {
        $attemptCount = $user->attempt_count ?? 0;
        $nilaiQuiz = $user->nilai_quiz ?? 0;

        if ($attemptCount >= 1 && $nilaiQuiz > 0) {
            return 'Sudah mengerjakan quiz - Nilai: ' . $nilaiQuiz;
        } elseif ($attemptCount >= 1) {
            return 'Sudah mengerjakan quiz - Menunggu nilai';
        } elseif ($nilaiQuiz > 0) {
            return 'Sudah mengerjakan quiz - Nilai: ' . $nilaiQuiz;
        }

        return 'Belum mengerjakan quiz';
    }

    /**
     * GET STATUS SUMMARY
     */
    public function getStatusSummary(Request $request)
    {
        try {
            $summary = DB::table('master_moodle_users')
                       ->select('status_kelulusan', DB::raw('COUNT(*) as count'))
                       ->groupBy('status_kelulusan')
                       ->get();

            $totalUsers = DB::table('master_moodle_users')->count();
            
            $attemptStats = DB::table('master_moodle_users')
                            ->select(
                                DB::raw('SUM(CASE WHEN attempt_count >= 1 THEN 1 ELSE 0 END) as with_attempts'),
                                DB::raw('SUM(CASE WHEN attempt_count = 0 THEN 1 ELSE 0 END) as without_attempts'),
                                DB::raw('SUM(CASE WHEN nilai_quiz > 0 THEN 1 ELSE 0 END) as with_nilai'),
                                DB::raw('SUM(CASE WHEN attempt_count >= 1 AND nilai_quiz = 0 THEN 1 ELSE 0 END) as attempt_without_nilai'),
                                DB::raw('SUM(CASE WHEN attempt_count >= 1 AND nilai_quiz > 0 THEN 1 ELSE 0 END) as attempt_with_nilai')
                            )
                            ->first();

            $inconsistentStatus = DB::table('master_moodle_users')
                                 ->where('status_kelulusan', 'Belum mengerjakan quiz')
                                 ->where(function($query) {
                                     $query->where('attempt_count', '>=', 1)
                                           ->orWhere('nilai_quiz', '>', 0);
                                 })
                                 ->count();

            return response()->json([
                'success' => true,
                'summary' => [
                    'total_users' => $totalUsers,
                    'status_distribution' => $summary,
                    'attempt_stats' => $attemptStats,
                    'inconsistent_status_count' => $inconsistentStatus
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting status summary: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Gagal mengambil summary status: ' . $e->getMessage()
            ]);
        }
    }
}