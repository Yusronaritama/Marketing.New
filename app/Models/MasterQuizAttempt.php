<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterQuizAttempt extends Model
{
    use HasFactory;

    // Sesuai dengan SQL dump yang Anda berikan
    protected $table = 'moodle_quiz_attempts'; 
    
    // Primary key
    protected $primaryKey = 'id';

    // Kolom yang boleh diisi
    protected $fillable = [
        'moodle_user_id',
        'grade',
        'grade_percentage',
        'max_grade',
        'percentage',
        'is_passed',
        'quiz_id',
        'attempt_id',
        'state',
        'timestart',
        'timefinish',
        'duration',
        'attempted_at',
        'sumgrades',
        'attempt_data',
        'review_data'
    ];
}