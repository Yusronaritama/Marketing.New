<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Targets extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'target',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
