<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MoodleUserCourse extends Model
{
    use HasFactory;

    protected $table = 'moodle_user_courses';

    protected $fillable = [
        'moodle_user_id',
        'course_id',
        'shortname',
        'fullname',
        'displayname',
        'summary',
        'startdate',
        'enddate',
        'last_accessed',
        'visible',
        'progress',
        'completed',
        'course_data'
    ];

    protected $casts = [
        'startdate' => 'datetime',
        'enddate' => 'datetime',
        'last_accessed' => 'datetime',
        'visible' => 'boolean',
        'progress' => 'decimal:2',
        'completed' => 'boolean',
        'course_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke MasterMoodle berdasarkan moodle_user_id
     */
    public function moodleUser()
    {
        return $this->belongsTo(MasterMoodle::class, 'moodle_user_id', 'moodle_user_id');
    }

    /**
     * Relasi ke MasterUser melalui MasterMoodle
     */
    public function masterUser()
    {
        return $this->hasOneThrough(
            MasterUser::class,
            MasterMoodle::class,
            'moodle_user_id', // Foreign key di MasterMoodle
            'id', // Foreign key di MasterUser
            'moodle_user_id', // Local key di MoodleUserCourse
            'master_user_id' // Local key di MasterMoodle
        );
    }

    /**
     * Relasi ke User melalui MasterUser
     */
    public function user()
    {
        return $this->hasOneThrough(
            User::class,
            MasterMoodle::class,
            'moodle_user_id', // Foreign key di MasterMoodle
            'id', // Foreign key di User
            'moodle_user_id', // Local key di MoodleUserCourse
            'master_user_id' // Local key di MasterMoodle
        );
    }

    /**
     * Accessor untuk progress dengan persentase
     */
    public function getProgressPercentageAttribute()
    {
        return $this->progress . '%';
    }

    /**
     * Accessor untuk status kursus
     */
    public function getStatusAttribute()
    {
        if ($this->completed) {
            return 'Completed';
        }

        if ($this->progress > 0) {
            return 'In Progress';
        }

        return 'Not Started';
    }

    /**
     * Scope untuk kursus yang visible
     */
    public function scopeVisible($query)
    {
        return $query->where('visible', true);
    }

    /**
     * Scope untuk kursus yang completed
     */
    public function scopeCompleted($query)
    {
        return $query->where('completed', true);
    }

    /**
     * Scope untuk kursus berdasarkan user moodle
     */
    public function scopeByMoodleUser($query, $moodleUserId)
    {
        return $query->where('moodle_user_id', $moodleUserId);
    }
}