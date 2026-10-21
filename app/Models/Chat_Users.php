<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat_Users extends Model
{
    use HasFactory;

    protected $table = 'chat_users';
    protected $fillable = [
        'id',
        'name',
        'email',
        'chat_id',
        'chat_text',
        'chat_image',
        'created_at',
        'updated_at',
        'role',
    ];

    // relasi untuk users
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // relasi untuk masterEreg
    public function masterEreg()
    {
        return $this->belongsTo(MasterEreg::class);
    }
}
