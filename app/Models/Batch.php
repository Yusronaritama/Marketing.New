<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'batch_name',
        'total_questions',
        'answers_per_question',
        'duration',
        'expired_at',
    ];
    public function questions()
{
    return $this->hasMany(Question::class);
}

    protected $dates = ['expired_at'];
}
