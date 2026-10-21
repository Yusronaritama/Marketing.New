<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BNICallbackLog extends Model
{
    use HasFactory;

    protected $table = 'bni_callback_logs';

    protected $fillable = [
        'client_id',
        'raw_data',
        'decrypted_data',
        'virtual_account',
        'trx_id',
        'trx_amount',
        'payment_amount',
        'datetime_payment',
        'payment_ntb',
        'status',
        'error_message',
        'user_id',
        'master_user_id',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'decrypted_data' => 'array',
        'trx_amount' => 'integer',
        'payment_amount' => 'integer',
        'datetime_payment' => 'datetime'
    ];

    /**
     * Relationship dengan user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship dengan master_user
     */
    public function masterUser()
    {
        return $this->belongsTo(MasterUser::class);
    }

    /**
     * Scope untuk callback yang berhasil
     */
    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }

    /**
     * Scope untuk callback yang gagal
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope untuk callback berdasarkan VA
     */
    public function scopeByVirtualAccount($query, $virtualAccount)
    {
        return $query->where('virtual_account', $virtualAccount);
    }

    /**
     * Scope untuk callback berdasarkan trx_id
     */
    public function scopeByTrxId($query, $trxId)
    {
        return $query->where('trx_id', $trxId);
    }
}