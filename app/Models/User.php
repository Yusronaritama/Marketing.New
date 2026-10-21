<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Notifications\CustomResetPassword;
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'nilai_cbt',
        'status_cbt',
        'va_number',
        'nim',
        'profesi',
        'promo_code_used',
        'va_amount',
        'va_expired_at',
        'va_status',
        'gelombang',
        'status_wawancara',
        'kel_beasiswa'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'va_expired_at' => 'datetime',
        ];
    }

    /**
     * Relasi one-to-one dengan model Biodata
     */
    public function biodata(): HasOne
    {
        return $this->hasOne(Biodata::class);
    }
        public function masterUsersVerified()
    {
        return $this->hasMany(MasterUsersVerified::class, 'user_id');
    }


    /**
     * Cek apakah user memiliki biodata
     */
    public function hasBiodata(): bool
    {
        return $this->biodata()->exists();
    }

    /**
     * Cek apakah biodata user sudah terverifikasi
     */
    public function hasVerifiedBiodata(): bool
    {
        return $this->biodata && $this->biodata->isVerified();
    }

    /**
     * Get biodata completion status
     */
    public function getBiodataStatusAttribute(): string
    {
        return $this->hasBiodata() ? 'Lengkap' : 'Belum';
    }

    /**
     * Scope untuk users yang sudah mengisi biodata
     */
    public function scopeWithBiodata($query)
    {
        return $query->whereHas('biodata');
    }

    /**
     * Scope untuk users yang belum mengisi biodata
     */
    public function scopeWithoutBiodata($query)
    {
        return $query->whereDoesntHave('biodata');
    }

    /**
     * Relasi one-to-one dengan model MasterUser
     * Diasumsikan MasterUser adalah tabel master yang berelasi dengan user
     */
    // public function masterUser(): HasOne
    // {
    //     return $this->hasOne(MasterUser::class, 'user_id', 'id');
    // }
public function masterUser()
{
    // Local key di tabel 'users' adalah 'id'
    // Foreign key di tabel 'master_users' adalah 'user_id'
    return $this->hasOne(MasterUser::class, 'user_id', 'id');
}

    /**
     * Scope untuk filter berdasarkan role
     */
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope untuk user dengan VA aktif
     */
    public function scopeWithActiveVA($query)
    {
        return $query->where('va_status', 'active');
    }

    /**
     * Cek apakah VA sudah expired
     */
    public function isVAExpired(): bool
    {
        return $this->va_expired_at && $this->va_expired_at->isPast();
    }
public function farmasiVerifications()
{
    return $this->hasMany(FarmasiVerification::class);
}
    /**
     * Cek apakah user memiliki role tertentu
     */
    public function hasRole($role): bool
    {
        return $this->role === $role;
    }

    /**
     * Cek apakah user adalah admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Cek apakah user adalah customer
     */
    public function isCustomer(): bool
    {
        return $this->hasRole('customer');
    }
    

    // Relasi ke MasterEreg
    public function masterEreg()
    {
        return $this->hasOne(MasterEreg::class);
    }
    public function targets()
    {
        return $this->hasMany(Targets::class);
    }
    // relasi ke chat_users
    public function chat_users()
    {
        return $this->hasMany(chat_users::class);
    }
        public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPassword($token));
    }
      // Relasi ke MasterMoodleLulus melalui MasterUser
    public function masterMoodleLulus()
    {
        return $this->hasOneThrough(
            MasterMoodleLulus::class,
            MasterUser::class,
            'user_id', // Foreign key pada MasterUser
            'master_user_id', // Foreign key pada MasterMoodleLulus
            'id', // Local key pada User
            'id' // Local key pada MasterUser
        );
    }
    public function getVaStatusAttribute($value)
{
    // Jika user pakai kode promo, cek apakah promo 100%
    if ($this->promo_code_used) {
        $promo = \App\Models\PromoCode::where('code', $this->promo_code_used)
            ->where('is_active', 1)
            ->where('expired_at', '>=', now())
            ->first();

        if ($promo && $promo->discount == 100) {
            return 'active'; // VA otomatis aktif
        }
    }

    return $value ?? 'inactive';
    
}

    // Atau jika relasi langsung tidak memungkinkan, gunakan akses melalui masterUser
    public function getMoodleLulusAttribute()
    {
        return $this->masterUser ? $this->masterUser->masterMoodleLulus : null;
    }
    
    // Tambahkan ini di dalam class User ya dawg!

    public function biodataProfesi()
    {
        return $this->hasOne(BiodataProfesi::class, 'user_id');
    }


    /**
     * Relasi ke tabel slik_billings
     * Pastikan Model SlikBilling sudah ada
     */
    public function slikBilling()
    {
        return $this->hasOne(\App\Models\SlikBilling::class, 'user_id');
    }

    /**
     * Relasi ke tabel data_mahasiswa_finals
     * Pastikan Model DataMahasiswaFinal sudah ada
     */
public function dataMahasiswaFinal()
{
    return $this->hasOne(DataMahasiswaFinal::class, 'user_id', 'id');
}
public function qrAttendances() {
    return $this->hasMany(\App\Models\QrAttendance::class);
}
public function pendaftarGetNim(): HasOne
    {
        return $this->hasOne(\App\Models\PendaftarGetNim::class, 'user_id', 'id');
    }
public function isProfesi(): bool
    {
        // Mengembalikan nilai true jika kolom profesi ada isinya
        return !empty($this->profesi);
    }    
}