<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MoodleUserCourse;
use App\Models\MasterMoodle;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MoodleUserCourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = MoodleUserCourse::with([
            'moodleUser', 
            'moodleUser.masterUser',
            'moodleUser.masterUser.user'
        ])->orderBy('created_at', 'desc');

        // Filter berdasarkan moodle_user_id jika ada
        if ($request->has('moodle_user_id') && $request->moodle_user_id) {
            $query->where('moodle_user_id', $request->moodle_user_id);
        }

        // Filter berdasarkan status completed
        if ($request->has('completed') && $request->completed !== '') {
            $query->where('completed', $request->completed);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('shortname', 'like', "%{$search}%")
                  ->orWhere('fullname', 'like', "%{$search}%")
                  ->orWhere('displayname', 'like', "%{$search}%")
                  ->orWhereHas('moodleUser', function($q) use ($search) {
                      $q->where('username', 'like', "%{$search}%");
                  })
                  ->orWhereHas('moodleUser.masterUser.user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $courses = $query->paginate(20);
        $moodleUsers = MasterMoodle::whereNotNull('moodle_user_id')->get();

        return view('admin.moodle.courses.index', compact('courses', 'moodleUsers'));
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $course = MoodleUserCourse::with([
            'moodleUser',
            'moodleUser.masterUser',
            'moodleUser.masterUser.user'
        ])->findOrFail($id);
        
        return view('admin.moodle.courses.show', compact('course'));
    }

    /**
     * Show courses by specific moodle user
     */
    public function byUser($moodleUserId): View
    {
        $moodleUser = MasterMoodle::with(['masterUser', 'masterUser.user'])
            ->where('moodle_user_id', $moodleUserId)
            ->firstOrFail();
            
        $courses = MoodleUserCourse::where('moodle_user_id', $moodleUserId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.moodle.courses.by-user', compact('courses', 'moodleUser'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $course = MoodleUserCourse::findOrFail($id);
        $course->delete();

        return redirect()->route('admin.moodle.courses.index')
            ->with('success', 'Data kursus berhasil dihapus.');
    }
}