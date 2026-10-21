<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MoodleEnrollmentController extends Controller
{
    protected $moodleUrl;
    protected $moodleToken;

    public function __construct()
    {
        // Konfigurasi Moodle
        $this->moodleUrl   = 'https://elearning2.ubpkarawang.ac.id/webservice/rest/server.php';
        $this->moodleToken = '47d4b340520e06fbb1d21127d683ae60';
    }

    public function index()
    {
        $users = DB::table('master_moodle')
            ->whereNotNull('moodle_user_id')
            ->where('moodle_user_id', '!=', 0)
            ->select('id', 'moodle_user_id', 'username', 'enrol_status', 'sync_notes') 
            ->orderBy('id', 'desc')
            ->paginate(50); // Menambah limit per page agar lebih enak dicentang

        $courses = [
            ["id" => 1, "fullname" => "Buana Online Course (BOC)"],
            ["id" => 2, "fullname" => "Ujian Saringan Masuk (USM)"]
        ];

        return view('moodle.enrollment', compact('users', 'courses'));
    }

    // Method baru khusus menangani AJAX Request
    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|integer',
            'user_ids'  => 'required|array',
        ]);

        $courseId = $request->course_id;
        $localIds = $request->user_ids;

        // Ambil data user dari DB
        $candidates = DB::table('master_moodle')
            ->whereIn('id', $localIds)
            ->whereNotNull('moodle_user_id')
            ->get();

        if ($candidates->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak ada user valid yang ditemukan dalam batch ini.'
            ], 404);
        }

        // Siapkan Parameter Moodle
        $params = [
            'wstoken' => $this->moodleToken,
            'wsfunction' => 'enrol_manual_enrol_users',
            'moodlewsrestformat' => 'json',
        ];

        // Susun array enrolments
        foreach ($candidates as $index => $user) {
            $params["enrolments[$index][roleid]"]   = 5; // Role Student
            $params["enrolments[$index][userid]"]   = $user->moodle_user_id;
            $params["enrolments[$index][courseid]"] = $courseId;
        }

        try {
            // Request ke Moodle (Timeout 30 detik cukup karena kita kirim sedikit-sedikit)
            $response = Http::withOptions(['verify' => false])
                ->timeout(30)
                ->asForm()
                ->post($this->moodleUrl, $params);

            $result = $response->json();

            // Cek jika Moodle merespon dengan Exception
            if (isset($result['exception'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Moodle Error: ' . $result['message']
                ], 500);
            }

            // Update Database Lokal
            DB::table('master_moodle')
                ->whereIn('id', $candidates->pluck('id'))
                ->update([
                    'enrol_status' => 'enrolled',
                    'updated_at' => now(),
                    'sync_notes' => 'Enrolled to Course ID ' . $courseId . ' at ' . now()
                ]);

            return response()->json([
                'status' => 'success',
                'processed_count' => count($candidates),
                'message' => 'Batch berhasil diproses.'
            ]);

        } catch (\Exception $e) {
            Log::error("Moodle Enroll Ajax Error: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }
}