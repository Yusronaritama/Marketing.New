<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterUser extends Model
{
    use HasFactory;

    protected $table = 'master_users';

    protected $fillable = [
        'user_id',
        'uuid',
        'promo_code',
        'password',
        'trx_id',
        'va_number',
        'va_status',
        'va_amount',
        'plain_password',
        'gelombang',
        'original_amount',
        'discount_amount',
        'reset_password_token',
        'reset_password_expires',
        'promo_code_used',
        'discount_percentage',
        'final_amount',
        'va_expired_at',
        'paid_at',
        'payment_method',
        'payment_proof',
    ];

    protected $casts = [
        'va_expired_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    /**
     * Relasi ke User
     */
     public function detailUser()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
public function moodleStatus()
{
    // Asumsi nama tabel adalah 'master_m   oodle' dan foreign key 'master_user_id'
    return $this->hasOne(MasterMoodle::class, 'master_user_id'); 
    // Jika belum punya model MasterMoodle, bisa pakai:
    // return $this->hasOne('App\Models\MasterMoodle', 'master_user_id');
}
public function masterMoodle()
{
    return $this->hasOne(MasterMoodle::class, 'master_user_id', 'id');
}
/**
     * Relasi ke MasterEreg (Pendaftaran)
     */
    public function ereg()
    {
        return $this->hasOne(MasterEreg::class, 'user_id', 'user_id');
    }

    /**
     * Relasi ke MasterMoodle
     */
    public function moodleUser()
    {
        return $this->hasOne(MasterMoodle::class, 'master_user_id');
    }

    /**
     * Relasi ke Nilai melalui MasterEreg
     */
    public function nilai()
    {
        return $this->hasOneThrough(
            Nilai::class,
            MasterEreg::class,
            'user_id', // Foreign key on MasterEreg table
            'id_pendaftaran', // Foreign key on Nilai table
            'user_id', // Local key on MasterUser table
            'id' // Local key on MasterEreg table
        );
    }
    
    public function masterUser()
    {
        // Sesuaikan foreign key 'user_id' jika berbeda
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }

    /**
     * Relasi ke Kelulusan melalui MasterEreg
     */
    public function kelulusan()
    {
        return $this->hasOneThrough(
            Kelulusan::class,
            MasterEreg::class,
            'user_id', // Foreign key on MasterEreg table
            'id_pendaftaran', // Foreign key on Kelulusan table
            'user_id', // Local key on MasterUser table
            'id' // Local key on MasterEreg table
        );
    }

    /**
     * Scope untuk user dengan status pending
     */
    public function scopePending($query)
    {
        return $query->where('va_status', 'pending');
    }

    /**
     * Scope untuk user yang perlu diregenerasi
     */
    public function scopeNeedsRegeneration($query)
    {
        return $query->where('va_status', 'pending')
            ->where(function($q) {
                $q->whereNull('va_expired_at')
                  ->orWhere('va_expired_at', '<', now())
                  ->orWhere('va_expired_at', '<', now()->addHour());
            })
            ->whereNull('paid_at');
    }

    /**
     * Cek apakah VA perlu diregenerasi
     */
    public function needsRegeneration()
    {
        if ($this->va_status !== 'pending') {
            return false;
        }

        if ($this->paid_at) {
            return false;
        }

        if (!$this->va_expired_at) {
            return true;
        }

        if (now()->greaterThan($this->va_expired_at)) {
            return true;
        }

        if (now()->addHour()->greaterThan($this->va_expired_at)) {
            return true;
        }

        return false;
    }

    /**
     * Scope untuk user yang sudah bayar
     */
    public function scopeSudahBayar($query)
    {
        return $query->where('va_status', 'paid');
    }

    /**
     * Relasi ke MasterMoodleLulus
     */
    public function masterMoodleLulus()
    {
        return $this->hasOne(MasterMoodleLulus::class, 'master_user_id');
    }

    /**
     * Scope untuk filter gelombang
     */
    public function scopeGelombang($query, $gelombang)
    {
        return $query->where('gelombang', $gelombang);
    }

    /**
     * Relasi ke MasterUsersVerified
     */
    public function masterUsersVerified()
    {
        return $this->hasMany(MasterUsersVerified::class);
    }

    /**
     * Relasi ke GroupGelombang
     */
    public function groupGelombang()
    {
        return $this->belongsTo(GroupGelombang::class, 'gelombang', 'gelombang');
    }

    /**
     * Relasi ke SlikBilling
     */
    public function slikBillings()
    {
        return $this->hasMany(SlikBilling::class, 'user_id', 'id');
    }
    
}