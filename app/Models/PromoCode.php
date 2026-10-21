<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PromoCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'description',
        'discount',
        'expired_at',
        'is_active',
        'usage_limit',
        'used_count'
    ];

    protected $casts = [
        'expired_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($promoCode) {
            if (empty($promoCode->code)) {
                $promoCode->code = static::generateUniqueCode();
            }
        });
    }
    
    public static function generateUniqueCode($length = 8)
    {
        do {
            $code = strtoupper(Str::random($length));
        } while (static::where('code', $code)->exists());

        return $code;
    }

    /**
     * Cek apakah promo code masih valid
     */
    public function isValid()
{
    $now = \Carbon\Carbon::now();
    
    return $this->is_active == 1 && 
           ($this->expired_at == null || $this->expired_at > $now) &&
           ($this->usage_limit == 0 || $this->used_count < $this->usage_limit);
}
public function users()
    {
        // Menghubungkan kolom 'promo_code_used' pada tabel users dengan kolom 'code' pada tabel promo_codes
        return $this->hasMany(User::class, 'promo_code_used', 'code');
    }
    /**
     * Alias untuk isAvailable - sama dengan isValid
     */
    public function isAvailable()
    {
        return $this->isValid();
    }

    /**
     * Hitung discount amount
     */
    public function calculateDiscount($amount)
    {
        return ($amount * $this->discount) / 100;
    }

    /**
     * Increment penggunaan promo code
     */
    public function incrementUsage()
    {
        $this->increment('used_count');
    }
    
    public function isFullDiscount()
    {
        return $this->discount == 100;
    }
    
    /**
     * Scope untuk promo code yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('expired_at')
                          ->orWhere('expired_at', '>', now());
                    });
    }
}