<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VirtualAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'va_number',   // ✅ bukan "number"
        'expires_at',
    ];

    protected $dates = ['expires_at'];
}
