<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterMoodle extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan oleh model.
     *
     * @var string
     */
    protected $table = 'master_moodle';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'master_user_id',
        'moodle_user_id',
        'username',
        'status',
        'enrol_status',
        'error_message',
        'sync_notes',
        'last_sync_at',
    ];

    /**
     * Casting untuk kolom tanggal
     */
    protected $casts = [
        'last_sync_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke MasterUser
     */
    public function masterUser()
    {
        return $this->belongsTo(MasterUser::class, 'master_user_id');
    }

    /**
     * Relasi ke User melalui MasterUser
     */
    public function user()
    {
        return $this->hasOneThrough(
            User::class,
            MasterUser::class,
            'id', // Foreign key di MasterUser
            'id', // Foreign key di User
            'master_user_id', // Local key di MasterMoodle
            'user_id' // Local key di MasterUser
        );
    }

    /**
     * Relasi ke MoodleUserCourses
     */
    public function courses()
    {
        return $this->hasMany(MoodleUserCourse::class, 'moodle_user_id', 'moodle_user_id');
    }
public function masterMoodleLulus()
{
    return $this->hasOne(MasterMoodleLulus::class, 'moodle_user_id', 'moodle_user_id');
}
public function quizAttempts()
{
    return $this->hasMany(MasterQuizAttempt::class, 'moodle_user_id', 'moodle_user_id');
}
    /**
     * Accessor untuk jumlah kursus
     */
    public function getCoursesCountAttribute()
    {
        return $this->courses()->count();
    }

    /**
     * Accessor untuk kursus yang completed
     */
    public function getCompletedCoursesCountAttribute()
    {
        return $this->courses()->completed()->count();
    }

    // app/Models/MasterMoodle.php
public function getLulusStatus()
{
    $status = [
        'is_lulus' => false,
        'sources' => [],
        'criteria' => []
    ];

    // Cek dari sync_notes
    if (str_contains($this->sync_notes ?? '', 'LULUS')) {
        $status['is_lulus'] = true;
        $status['sources'][] = 'sync_notes';
        $status['criteria'][] = 'sync_notes';
    }

    // Cek dari master_moodle_lulus
    if ($this->masterMoodleLulus && $this->masterMoodleLulus->status_kelulusan === 'LULUS') {
        $status['is_lulus'] = true;
        $status['sources'][] = 'master_moodle_lulus';
        $status['criteria'][] = 'master_moodle_lulus';
    }

    // Cek dari quiz attempts
    $bestAttempt = $this->quizAttempts
        ->where('state', 'finished')
        ->sortByDesc('sumgrades')
        ->first();

    if ($bestAttempt && $bestAttempt->sumgrades >= 60) {
        $status['is_lulus'] = true;
        $status['sources'][] = 'quiz_attempt';
        $status['criteria'][] = 'quiz_attempt';
    }

    return $status;
}
}