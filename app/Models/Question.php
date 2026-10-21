<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'batch_id',
        'text',
        'options',
        'correct_answer',
        'image_path',
        'document_path',
        'expired_time',
    ];
    public function batch()
{
    return $this->belongsTo(Batch::class);
}

}