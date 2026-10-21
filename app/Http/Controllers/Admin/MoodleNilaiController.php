<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MoodleNilaiController extends Controller
{
    public function index()
    {
        return view('admin.moodle.nilai');
    }

    public function syncGrades(Request $request)
    {
        $apiUrl = config('services.moodle.url'); 
        $token = config('services.moodle.token');
        $courseId = env('2', 2); 

        if (!$apiUrl) {
            return back()->with('error', 'Konfigurasi URL Moodle tidak ditemukan.');
        }

        try {
            // 1. Ambil data nilai dari Moodle API
            $response = Http::get($apiUrl, [
                'wstoken' => $token,
                'wsfunction' => 'gradereport_user_get_grade_items',
                'moodlewsrestformat' => 'json',
                'courseid' => $courseId
            ]);

            if ($response->failed()) {
                return back()->with('error', 'Gagal terhubung ke server Moodle.');
            }

            $data = $response->json();
            if (isset($data['exception'])) {
                return back()->with('error', 'Moodle Error: ' . $data['message']);
            }

            $userGrades = $data['usergrades'] ?? [];
            $syncCount = 0;
            $updatedFromZeroCount = 0;

            // --- PROSES SYNC ---
            foreach ($userGrades as $user) {
                $moodleUserId = $user['userid'];

                $masterMoodle = DB::table('master_moodle')
                    ->where('moodle_user_id', $moodleUserId)
                    ->first();

                if (!$masterMoodle) {
                    continue;
                }

                foreach ($user['gradeitems'] as $item) {
                    if (isset($item['itemtype']) && $item['itemtype'] == 'mod' && $item['itemmodule'] == 'quiz') {
                        
                        $quizId = $item['iteminstance'];
                        $gradeRaw = $item['graderaw'] ?? 0;
                        $maxGrade = $item['grademax'] ?? 100;
                        
                        // Cek data yang ada di database lokal saat ini
                        $existingAttempt = DB::table('moodle_quiz_attempts')
                            ->where('moodle_user_id', $moodleUserId)
                            ->where('quiz_id', $quizId)
                            ->first();

                        // LOGIKA: Jika data sudah ada dan nilainya > 0, 
                        // dan nilai dari API masih 0, maka kita skip agar tidak menimpa nilai yang sudah benar.
                        // Tetapi jika data di lokal masih 0, kita WAJIB update dengan data terbaru API.
                        if ($existingAttempt && $existingAttempt->grade > 0 && $gradeRaw <= 0) {
                            continue; 
                        }

                        if ($existingAttempt && $existingAttempt->grade <= 0 && $gradeRaw > 0) {
                            $updatedFromZeroCount++;
                        }

                        $percentage = isset($item['percentageformatted']) 
                            ? (float)str_replace('%', '', $item['percentageformatted']) 
                            : ($maxGrade > 0 ? ($gradeRaw / $maxGrade) * 100 : 0);

                        DB::table('moodle_quiz_attempts')->updateOrInsert(
                            ['moodle_user_id' => $moodleUserId, 'quiz_id' => $quizId],
                            [
                                'attempt_id' => $item['id'] ?? ($moodleUserId . $quizId),
                                'grade' => $gradeRaw,
                                'max_grade' => $maxGrade,
                                'percentage' => $percentage,
                                'score' => $gradeRaw,
                                'is_passed' => $gradeRaw >= ($maxGrade * 0.6) ? 1 : 0,
                                'state' => 'finished',
                                'attempt_data' => json_encode($item),
                                'updated_at' => Carbon::now(),
                                'created_at' => Carbon::now(),
                            ]
                        );

                        DB::table('master_moodle')
                            ->where('moodle_user_id', $moodleUserId)
                            ->update([
                                'last_sync_at' => Carbon::now(),
                                'sync_notes' => 'Last Sync: ' . Carbon::now()->toDateTimeString() . ($gradeRaw > 0 ? ' (Grade Updated)' : ' (Grade Still 0)')
                            ]);

                        $syncCount++;
                    }
                }
            }

            // --- PROSES CLEANUP ---
            $deletedRows = DB::table('moodle_quiz_attempts')
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('master_moodle')
                        ->whereRaw('master_moodle.moodle_user_id = moodle_quiz_attempts.moodle_user_id');
                })
                ->delete();

            $message = "Berhasil sinkronisasi $syncCount data.";
            if ($updatedFromZeroCount > 0) {
                $message .= " ($updatedFromZeroCount nilai diperbarui dari nol).";
            }
            if ($deletedRows > 0) {
                $message .= " Menghapus $deletedRows data tidak valid.";
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
}