<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppStatus extends Model
{
    protected $table = 'whatsapp_statuses'; // Tambahkan ini
    
    protected $fillable = [
        'status',
        'qr_code',
        'phone_number',
        'session_name',
        'is_connected',
        'server_info',
        'details',
        'last_heartbeat'
    ];

    protected $casts = [
        'server_info' => 'array',
        'details' => 'array',
        'is_connected' => 'boolean'
    ];

    public function scopeConnected($query)
    {
        return $query->where('is_connected', true);
    }

    public function scopeLatestStatus($query)
    {
        return $query->latest()->first();
    }
    
    // Method untuk mendapatkan status terbaru
    public static function getLatestStatus()
    {
        return self::latest()->first();
    }
}