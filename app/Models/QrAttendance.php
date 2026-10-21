<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class QrAttendance extends Model
{
    protected $table = 'qr_attendances';

    protected $fillable = [
        'user_id',
        'token',
        'status',
        'expired_at',
        'scanned_at',
        'scanned_by',
        'scanned_location',
        'sesi',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
        'scanned_at' => 'datetime',
    ];

    // ─── Relasi ────────────────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ─── Scopes ────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                     ->where(function ($q) {
                         $q->whereNull('expired_at')
                           ->orWhere('expired_at', '>', now());
                     });
    }

    public function scopeScanned($query)
    {
        return $query->where('status', 'scanned');
    }

    // ─── Helpers ────────────────────────────────────────────────

    /**
     * Cek apakah QR masih bisa digunakan
     */
    public function isActive(): bool
    {
        if ($this->status !== 'active') return false;
        if ($this->expired_at && $this->expired_at->isPast()) return false;
        return true;
    }

    /**
     * Generate token unik 48 karakter
     */
    public static function generateToken(): string
    {
        do {
            $token = Str::random(48);
        } while (static::where('token', $token)->exists());

        return $token;
    }

    /**
     * URL publik untuk scan QR
     */
    public function getScanUrl(): string
    {
        return route('qr.scan', ['token' => $this->token]);
    }

    /**
     * Data yang di-encode ke dalam QR (hanya URL scan)
     */
    public function getQrContent(): string
    {
        return $this->getScanUrl();
    }
}