<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentLog extends Model
{
    use HasFactory;

    protected $table = 'payment_logs';

    protected $fillable = [
        'user_id',
        'master_user_id',
        'virtual_account',
        'trx_id',
        'trx_amount',
        'payment_amount',
        'cumulative_payment_amount',
        'payment_ntb',
        'datetime_payment',
        'datetime_payment_iso8601',
        'status',
        'payment_data'
    ];

    protected $casts = [
        'trx_amount' => 'integer',
        'payment_amount' => 'integer',
        'cumulative_payment_amount' => 'integer',
        'datetime_payment' => 'datetime',
        'payment_data' => 'array'
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
     * Scope untuk pembayaran yang berhasil
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope untuk pembayaran partial
     */
    public function scopePartial($query)
    {
        return $query->where('status', 'partial');
    }

    /**
     * Scope untuk duplicate payment
     */
    public function scopeDuplicate($query)
    {
        return $query->where('status', 'duplicate');
    }

    /**
     * Format amount untuk display
     */
    public function getFormattedAmountAttribute()
    {
        return 'Rp ' . number_format($this->payment_amount, 0, ',', '.');
    }

    /**
     * Format tanggal pembayaran
     */
    public function getFormattedDateAttribute()
    {
        return $this->datetime_payment 
            ? $this->datetime_payment->format('d/m/Y H:i:s')
            : '-';
    }
}