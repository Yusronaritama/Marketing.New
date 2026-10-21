<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class MasterMoodleLulus extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'master_moodle_lulus';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'master_user_id',
        'moodle_user_id',
        'username',
        'email',
        'nama_lengkap',
        'prodi_lulus',
        'nama_prodi_lulus',
        'pilihan_lulus',
        'quiz_id',
        'nilai_quiz',
        'attempt_count',
        'status_kelulusan',
        'keterangan',
        'tanggal_lulus',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'tanggal_lulus' => 'datetime',
        'nilai_quiz' => 'decimal:2',
        'attempt_count' => 'integer',
        'pilihan_lulus' => 'integer',
        'master_user_id' => 'integer',
        'moodle_user_id' => 'integer',
        'quiz_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Get the master user associated with the moodle lulus record.
     */
    public function masterUser()
    {
        return $this->belongsTo(MasterUser::class, 'master_user_id');
    }
    
    /**
     * Get the moodle user associated with the moodle lulus record.
     */
    public function moodleUser()
    {
        return $this->belongsTo(MasterMoodle::class, 'moodle_user_id', 'moodle_user_id');
    }

    /**
     * Get the quiz/course associated with the moodle lulus record.
     */
    public function quiz()
    {
        return $this->belongsTo(MoodleUserCourse::class, 'quiz_id', 'course_id');
    }

    /**
     * Scope a query to only include lulus records with specific status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status_kelulusan', $status);
    }

    /**
     * Scope a query to only include lulus records for specific prodi.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $prodi
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeProdi($query, $prodi)
    {
        return $query->where('prodi_lulus', $prodi)
                    ->orWhere('nama_prodi_lulus', 'like', "%{$prodi}%");
    }

    /**
     * Scope a query to only include lulus records for specific quiz.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $quizId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeQuiz($query, $quizId)
    {
        return $query->where('quiz_id', $quizId);
    }

    /**
     * Scope a query to only include lulus records within date range.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $startDate
     * @param  string  $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTanggalLulusBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal_lulus', [$startDate, $endDate]);
    }

    /**
     * Get the formatted tanggal_lulus attribute.
     *
     * @return string|null
     */
    public function getTanggalLulusFormattedAttribute()
    {
        return $this->tanggal_lulus ? $this->tanggal_lulus->format('d/m/Y H:i') : null;
    }

    /**
     * Get the formatted nilai_quiz attribute.
     *
     * @return string
     */
    public function getNilaiQuizFormattedAttribute()
    {
        return number_format($this->nilai_quiz, 2);
    }

    /**
     * Get the pilihan lulus as text.
     *
     * @return string
     */
    public function getPilihanLulusTextAttribute()
    {
        return $this->pilihan_lulus == 1 ? 'Pilihan 1' : 'Pilihan 2';
    }

    /**
     * Check if the record is for pilihan 1.
     *
     * @return bool
     */
    public function getIsPilihan1Attribute()
    {
        return $this->pilihan_lulus == 1;
    }

    /**
     * Check if the record is for pilihan 2.
     *
     * @return bool
     */
    public function getIsPilihan2Attribute()
    {
        return $this->pilihan_lulus == 2;
    }

    /**
     * Get the status color for UI.
     *
     * @return string
     */
    public function getStatusColorAttribute()
    {
        return match($this->status_kelulusan) {
            'LULUS' => 'success',
            'TIDAK LULUS' => 'danger',
            'MENUNGGU' => 'warning',
            default => 'secondary'
        };
    }

    /**
     * Get the short prodi name (without full description).
     *
     * @return string
     */
    public function getProdiShortAttribute()
    {
        if (preg_match('/\(([^)]+)\)/', $this->nama_prodi_lulus, $matches)) {
            return $matches[1];
        }
        
        return $this->prodi_lulus;
    }

    /**
     * Find record by moodle_user_id and quiz_id.
     *
     * @param int $moodleUserId
     * @param int $quizId
     * @return \App\Models\MasterMoodleLulus|null
     */
    public static function findByMoodleUserAndQuiz($moodleUserId, $quizId)
    {
        return static::where('moodle_user_id', $moodleUserId)
                    ->where('quiz_id', $quizId)
                    ->first();
    }

    /**
     * Get all lulus records for a specific master_user_id.
     *
     * @param int $masterUserId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByMasterUser($masterUserId)
    {
        return static::where('master_user_id', $masterUserId)->get();
    }

    /**
     * Get statistics for dashboard.
     *
     * @return array
     */
    public static function getStatistics()
    {
        return [
            'total' => static::count(),
            'lulus_hari_ini' => static::whereDate('tanggal_lulus', today())->count(),
            'lulus_minggu_ini' => static::whereBetween('tanggal_lulus', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'lulus_bulan_ini' => static::whereMonth('tanggal_lulus', now()->month)
                            ->whereYear('tanggal_lulus', now()->year)
                            ->count(),
            'by_prodi' => static::groupBy('prodi_lulus')
                            ->select('prodi_lulus', DB::raw('COUNT(*) as total'))
                            ->pluck('total', 'prodi_lulus')
                            ->toArray(),
            'by_quiz' => static::groupBy('quiz_id')
                            ->select('quiz_id', DB::raw('COUNT(*) as total'))
                            ->pluck('total', 'quiz_id')
                            ->toArray()
        ];
    }
    public function user()
    {
        // Relasi ke User berdasarkan email
        return $this->belongsTo(User::class, 'email', 'email');
    }
}