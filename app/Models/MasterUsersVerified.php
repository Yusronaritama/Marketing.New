<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterUsersVerified extends Model
{
    use HasFactory;

    protected $table = 'master_users_verified';

    protected $fillable = [
        'user_id',
        'master_user_id',
        'verified_at',
        'verified_by',
        'notes',
        'created_at', // Tambahkan ini
        'updated_at'  // Tambahkan ini
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'created_at' => 'datetime', // Tambahkan ini
        'updated_at' => 'datetime'  // Tambahkan ini
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);   
    }

    // Relasi ke MasterUser
    public function masterUser()
    {
        return $this->belongsTo(MasterUser::class);
    }
}