<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MoodleGradeController extends Controller
{
    protected $moodleUrl;
    protected $moodleToken;

    public function __construct()
    {
        $this->moodleUrl = config('services.moodle.url', 'https://elearning2.ubpkarawang.ac.id/webservice/rest/server.php');
        $this->moodleToken = config('services.moodle.token', '47d4b340520e06fbb1d21127d683ae60');
    }

    private function callMoodleApi($functionName, $params = [])
    {
        $payload = array_merge([
            'wstoken' => $this->moodleToken,
            'wsfunction' => $functionName,
            'moodlewsrestformat' => 'json',
        ], $params);

        $response = Http::asForm()->post($this->moodleUrl, $payload);
        return $response->json();
    }

    // 1. Halaman Utama (Hanya me-load View kosong)
    public function index()
    {
        return view('admin.moodle.grades');
    }

    // 2. AJAX Step 1: Ambil daftar user saja (Tanpa cek nilai)
    public function getEnrolledUsers($courseId)
    {
        try {
            // Ambil user enrolled
            $enrolledUsers = $this->callMoodleApi('core_enrol_get_enrolled_users', [
                'courseid' => $courseId
            ]);

            if (isset($enrolledUsers['exception'])) {
                return response()->json(['error' => $enrolledUsers['message']], 500);
            }

            // Kita hanya butuh ID dan Nama untuk dikirim ke JS
            $users = collect($enrolledUsers)->map(function($user) {
                return [
                    'id' => $user['id'],
                    'fullname' => $user['fullname'],
                    'email' => $user['email'] ?? '-'
                ];
            });

            return response()->json(['users' => $users]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 3. AJAX Step 2: Cek nilai untuk SATU user
    public function checkUserGrade(Request $request)
    {
        $courseId = $request->course_id;
        $userId = $request->user_id;

        try {
            $gradeResponse = $this->callMoodleApi('gradereport_user_get_grade_items', [
                'courseid' => $courseId,
                'userid'   => $userId
            ]);

            $finalGrade = null;

            if (isset($gradeResponse['usergrades'][0]['gradeitems'])) {
                foreach ($gradeResponse['usergrades'][0]['gradeitems'] as $item) {
                    // Cari item tipe 'course' (Nilai Akhir)
                    if ($item['itemtype'] === 'course' && !is_null($item['graderaw'])) {
                        $finalGrade = $item['graderaw'];
                        break;
                    }
                }
            }

            // Return JSON ke AJAX
            return response()->json([
                'has_grade' => !is_null($finalGrade),
                'grade' => $finalGrade
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}