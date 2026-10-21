<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MoodleQuizAttempt;
use App\Models\MasterMoodle;
use App\Models\MoodleUserCourse;
use App\Models\MasterEreg;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MoodleQuizAttemptController extends Controller
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
            'moodleUser.masterUser.ereg', // Load ereg melalui masterUser
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

        // Filter berdasarkan jurusan
        if ($request->has('jurusan') && $request->jurusan) {
            $query->whereHas('moodleUser.masterUser.ereg', function($q) use ($request) {
                $q->where('jurusan1', 'like', "%{$request->jurusan}%")
                  ->orWhere('jurusan2', 'like', "%{$request->jurusan}%");
            });
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('attempt_id', 'like', "%{$search}%")
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

        // Ambil daftar jurusan unik untuk filter
        $jurusanList = MasterEreg::distinct()
            ->whereNotNull('jurusan1')
            ->pluck('jurusan1')
            ->merge(MasterEreg::distinct()->whereNotNull('jurusan2')->pluck('jurusan2'))
            ->unique()
            ->sort()
            ->values();

        return view('admin.moodle.quiz-attempts.index', compact('attempts', 'moodleUsers', 'quizzes', 'jurusanList'));
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
            'moodleUser.masterUser.ereg', // Load ereg melalui masterUser
            'course'
        ])->findOrFail($id);
        
        return view('admin.moodle.quiz-attempts.show', compact('attempt'));
    }

    /**
     * Show attempts by specific moodle user
     */
    public function byUser($moodleUserId): View
    {
        $moodleUser = MasterMoodle::with(['masterUser', 'masterUser.user', 'masterUser.ereg'])
            ->where('moodle_user_id', $moodleUserId)
            ->firstOrFail();
            
        $attempts = MoodleQuizAttempt::where('moodle_user_id', $moodleUserId)
            ->with(['course', 'moodleUser.masterUser.ereg'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.moodle.quiz-attempts.by-user', compact('attempts', 'moodleUser'));
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

        return view('admin.moodle.quiz-attempts.by-quiz', compact('attempts', 'quiz'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $attempt = MoodleQuizAttempt::findOrFail($id);
        $attempt->delete();

        return redirect()->route('admin.moodle.quiz-attempts.index')
            ->with('success', 'Data attempt quiz berhasil dihapus.');
    }
}