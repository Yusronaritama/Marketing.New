<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database (opsional, jika nama tabel bukan 'profiles')
     */
    protected $table = 'profiles';

    /**
     * Primary key (opsional, jika bukan 'id')
     */
    protected $primaryKey = 'id';

    /**
     * Kolom yang dapat diisi (mass assignable)
     */
    protected $fillable = [
        'user_id',
        'phone',
        'address',
        'bio',
        'avatar', // jika ada foto profil
    ];

    /**
     * Relasi ke tabel users
     * Setiap profile dimiliki oleh satu user
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Accessor untuk menampilkan URL avatar jika ada
     * Akan mengembalikan path lengkap ke storage
     */
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return asset('storage/avatars/' . $this->avatar);
        }
        return asset('assets/images/default-avatar.png');
    }
}
