<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlikBilling extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'slik_billings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'master_ereg_id',
        'order_id',
        'trx_id',           // Pastikan trx_id (dari SLIK) juga masuk fillable
        'master_user_trx_id', // Pastikan kolom ini masuk fillable (untuk relasi)
        'virtual_account',
        'kode_prodi',       
        'nama_jurusan',
        'is_active',
        'tenor_pembayaran',
        'amount',
        'standard_amount',
        'is_custom_amount',
        'status',
        'payment_ntb',
        'datetime_payment',
        'datetime_expired',
        'slik_request_payload',
        'slik_response_data',
        'customer_name',// Tambahkan ini jika Anda pakai
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'standard_amount' => 'decimal:2',
        'is_custom_amount' => 'boolean',
        'datetime_payment' => 'datetime',
        'datetime_expired' => 'datetime',
        'slik_request_payload' => 'array',
        'slik_response_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the billing.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the master ereg that owns the billing.
     */
    public function masterEreg(): BelongsTo
    {
        return $this->belongsTo(MasterEreg::class, 'master_ereg_id');
    }

    // ====================================================================
    // [BARU] RELASI KE MASTER USER
    // ====================================================================
    /**
     * Get the master user associated with the billing.
     * Relasi ini berguna untuk mencari tagihan berdasarkan TRX ID (NIM Sementara)
     */
    public function masterUser(): BelongsTo
    {
        // Hubungkan SlikBilling (user_id) ke MasterUser (user_id)
        return $this->belongsTo(MasterUser::class, 'user_id', 'id');
    }

    /**
     * Get the program studi that owns the billing.
     */
    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class, 'kode_prodi', 'kode_prodi')
            ->withDefault([
                'nama_prodi' => 'Jurusan Tidak Dikenal'
            ]);
    }

    /**
     * Scope a query to only include pending billings.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include paid billings.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope a query to only include expired billings.
     */
    public function scopeExpired($query)
    {
        return $query->where('datetime_expired', '<', now())
                    ->where('status', 'pending');
    }

    /**
     * Check if billing is paid.
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Check if billing is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if billing is expired.
     */
    public function isExpired(): bool
    {
        return $this->datetime_expired && $this->datetime_expired->lt(now()) && $this->isPending();
    }

    /**
     * Check if billing uses custom amount.
     */
    public function isCustomAmount(): bool
    {
        return $this->is_custom_amount;
    }

    /**
     * Get formatted amount.
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    /**
     * Get formatted standard amount.
     */
    public function getFormattedStandardAmountAttribute(): string
    {
        return $this->standard_amount ? 'Rp ' . number_format($this->standard_amount, 0, ',', '.') : '-';
    }

    /**
     * Get amount difference (custom - standard).
     */
    public function getAmountDifferenceAttribute(): ?float
    {
        if (!$this->is_custom_amount || !$this->standard_amount) {
            return null;
        }
        return $this->amount - $this->standard_amount;
    }

    /**
     * Get formatted amount difference.
     */
    public function getFormattedAmountDifferenceAttribute(): string
    {
        $difference = $this->amount_difference;
        if ($difference === null) {
            return '-';
        }
        
        $sign = $difference >= 0 ? '+' : '';
        return $sign . 'Rp ' . number_format(abs($difference), 0, ',', '.');
    }
    
    public function scopeIsSelisih($query)
    {
        return $query->where('order_id', 'like', '%SELISIH%');
    }
    public function scopeIsNormal($query)
    {
        return $query->where('order_id', 'not like', '%SELISIH%');
    }
    
    
    
    
    
    
    
}