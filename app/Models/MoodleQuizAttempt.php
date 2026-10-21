<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MoodleQuizAttempt extends Model
{
    use HasFactory;

    protected $table = 'moodle_quiz_attempts';

    protected $fillable = [
        'attempt_id',
        'moodle_user_id',
        'quiz_id',
        'state',
        'timestart',
        'timefinish',
        'duration',
        'attempted_at',
        'grade', // Dipertahankan untuk referensi
        'max_grade',
        'percentage',
        'is_passed',
        'score', // Menjadi nilai utama untuk seleksi
        'total_questions',
        'correct_answers',
        'wrong_answers',
        'grade_updated_at',
        'grade_calculation_method',
        'grade_calculation_details',
        'attempt_data',
        'review_data',
        'is_manual_edit',
        // Hapus sumgrades karena sudah tidak digunakan
    ];

    protected $casts = [
        'timestart' => 'datetime',
        'timefinish' => 'datetime',
        'attempted_at' => 'datetime',
        'grade_updated_at' => 'datetime',
        'grade_calculation_details' => 'array',
        'score' => 'decimal:2', // Score sebagai tipe utama
        'grade' => 'decimal:2', // Dipertahankan untuk kompatibilitas
        'max_grade' => 'decimal:2',
        'percentage' => 'decimal:2',
        'is_passed' => 'boolean',
        'total_questions' => 'integer',
        'correct_answers' => 'integer',
        'grade_calculation_method' => 'string',
        'wrong_answers' => 'integer',
        'attempt_data' => 'array',
        'review_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $dates = [
        'timestart',
        'timefinish',
        'attempted_at',
        'grade_updated_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $appends = [
        'status',
        'duration_formatted',
        'grade_percentage',
        'grade_letter',
        'status_lolos_jurusan1',
        'status_lolos_jurusan2',
        'passing_grade_jurusan1',
        'passing_grade_jurusan2',
        'nama_prodi1',
        'nama_prodi2',
        'rekomendasi_prodi',
        'prodi_diterima',
        'status_penerimaan',
        'master_user',
        'user',
        'ereg',
        'course',
        'nilai_seleksi', // Nilai yang digunakan untuk seleksi (score)
    ];

    /**
     * Relasi ke MasterMoodle berdasarkan moodle_user_id
     */
    public function moodleUser()
    {
        return $this->belongsTo(MasterMoodle::class, 'moodle_user_id', 'moodle_user_id');
    }

    /**
     * Relasi langsung ke ProgramStudi untuk jurusan 1
     */
    public function programStudi1()
    {
        return $this->belongsTo(ProgramStudi::class, 'jurusan1_kode', 'kode_prodi')
            ->withDefault([
                'nama_prodi' => 'Program Studi Tidak Ditemukan',
                'passing_grade' => 0,
                'kuota' => 0
            ]);
    }

    /**
     * Accessor untuk metode kalkulasi grade
     */
    public function getGradeCalculationMethodAttribute($value)
    {
        return $value ?? 'score_based'; // default diubah ke score_based
    }

    /**
     * Accessor untuk informasi kalkulasi grade
     */
    public function getGradeCalculationInfoAttribute()
    {
        $method = $this->grade_calculation_method ?? 'score_based';
        
        switch ($method) {
            case 'score_based':
                return [
                    'method' => 'Score Based',
                    'formula' => 'Nilai Seleksi = Score',
                    'description' => 'Score digunakan sebagai nilai seleksi utama'
                ];
            case 'percentage_based':
                return [
                    'method' => 'Percentage Based',
                    'formula' => "Grade = ({$this->percentage}% / 100) × {$this->max_grade}",
                    'calculation' => "({$this->percentage} / 100) × {$this->max_grade} = {$this->grade}",
                    'description' => 'Grade dihitung dari persentase'
                ];
            case 'review_based':
                return [
                    'method' => 'Review Data Based',
                    'description' => 'Grade diambil dari review data Moodle'
                ];
            default:
                return [
                    'method' => $method,
                    'description' => 'Unknown calculation method'
                ];
        }
    }

    /**
     * Relasi langsung ke ProgramStudi untuk jurusan 2
     */
    public function programStudi2()
    {
        return $this->belongsTo(ProgramStudi::class, 'jurusan2_kode', 'kode_prodi')
            ->withDefault([
                'nama_prodi' => 'Program Studi Tidak Ditemukan',
                'passing_grade' => 0,
                'kuota' => 0
            ]);
    }

    /**
     * Relasi ke MoodleUserCourse berdasarkan quiz_id
     */
    public function course()
    {
        return $this->belongsTo(MoodleUserCourse::class, 'quiz_id', 'course_id');
    }

    /**
     * Accessor untuk nilai yang digunakan dalam seleksi (Menggunakan SCORE)
     */
    public function getNilaiSeleksiAttribute()
    {
        return $this->score ?? $this->grade ?? 0;
    }

    /**
     * Accessor untuk status attempt
     */
    public function getStatusAttribute()
    {
        return match($this->state) {
            'finished' => 'Selesai',
            'inprogress' => 'Dalam Proses',
            'overdue' => 'Terlambat',
            'abandoned' => 'Ditinggalkan',
            default => $this->state,
        };
    }

    /**
     * Accessor untuk durasi pengerjaan (formatted)
     */
    public function getDurationFormattedAttribute()
    {
        if ($this->duration) {
            $hours = floor($this->duration / 3600);
            $minutes = floor(($this->duration % 3600) / 60);
            $seconds = $this->duration % 60;
            
            if ($hours > 0) {
                return sprintf('%d jam %d menit %d detik', $hours, $minutes, $seconds);
            } elseif ($minutes > 0) {
                return sprintf('%d menit %d detik', $minutes, $seconds);
            } else {
                return sprintf('%d detik', $seconds);
            }
        }
        
        if ($this->timestart && $this->timefinish) {
            $start = \Carbon\Carbon::parse($this->timestart);
            $finish = \Carbon\Carbon::parse($this->timefinish);
            $duration = $finish->diffInSeconds($start);
            
            $hours = floor($duration / 3600);
            $minutes = floor(($duration % 3600) / 60);
            $seconds = $duration % 60;
            
            if ($hours > 0) {
                return sprintf('%d jam %d menit %d detik', $hours, $minutes, $seconds);
            } elseif ($minutes > 0) {
                return sprintf('%d menit %d detik', $minutes, $seconds);
            } else {
                return sprintf('%d detik', $seconds);
            }
        }
        
        return '-';
    }

    /**
     * Accessor untuk nilai dalam persentase (menggunakan score sebagai prioritas)
     */
    public function getGradePercentageAttribute()
    {
        // Prioritaskan score untuk perhitungan persentase
        if (!is_null($this->score) && $this->max_grade > 0) {
            $percentage = ($this->score / $this->max_grade) * 100;
            return round($percentage, 2) . '%';
        }
        
        if (!is_null($this->grade) && $this->max_grade > 0) {
            $percentage = ($this->grade / $this->max_grade) * 100;
            return round($percentage, 2) . '%';
        }
        
        return '0%';
    }

    /**
     * Accessor untuk grade letter (A, B, C, D, E) berdasarkan score
     */
    public function getGradeLetterAttribute()
    {
        // Gunakan percentage yang sudah dihitung dari score
        if (!is_null($this->percentage)) {
            $percentage = $this->percentage;
        } elseif (!is_null($this->score) && $this->max_grade > 0) {
            $percentage = ($this->score / $this->max_grade) * 100;
        } else {
            return 'E';
        }
        
        if ($percentage >= 85) return 'A';
        if ($percentage >= 75) return 'B';
        if ($percentage >= 65) return 'C';
        if ($percentage >= 55) return 'D';
        return 'E';
    }

    /**
     * Accessor untuk MasterUser melalui MasterMoodle
     */
    public function getMasterUserAttribute()
    {
        if ($this->moodleUser) {
            return $this->moodleUser->masterUser;
        }
        return null;
    }

    /**
     * Accessor untuk User melalui MasterMoodle dan MasterUser
     */
    public function getUserAttribute()
    {
        if ($this->masterUser && $this->masterUser->user) {
            return $this->masterUser->user;
        }
        return null;
    }

    /**
     * Accessor untuk MasterEreg melalui MasterMoodle dan MasterUser
     */
    public function getEregAttribute()
    {
        if ($this->masterUser && $this->masterUser->ereg) {
            return $this->masterUser->ereg;
        }
        return null;
    }

    /**
     * Accessor untuk status kelolosan jurusan 1 berdasarkan SCORE
     */
    public function getStatusLolosJurusan1Attribute()
    {
        if (!$this->ereg || !$this->ereg->jurusan1_kode) {
            return '-';
        }

        return $this->checkKelolosan($this->nilai_seleksi, $this->ereg->jurusan1_kode);
    }

    /**
     * Accessor untuk status kelolosan jurusan 2 berdasarkan SCORE
     */
    public function getStatusLolosJurusan2Attribute()
    {
        if (!$this->ereg || !$this->ereg->jurusan2_kode) {
            return '-';
        }

        return $this->checkKelolosan($this->nilai_seleksi, $this->ereg->jurusan2_kode);
    }

    /**
     * Accessor untuk passing grade jurusan 1
     */
    public function getPassingGradeJurusan1Attribute()
    {
        if (!$this->ereg || !$this->ereg->jurusan1_kode) {
            return null;
        }

        return $this->getPassingGradeFromDatabase($this->ereg->jurusan1_kode);
    }

    /**
     * Accessor untuk passing grade jurusan 2
     */
    public function getPassingGradeJurusan2Attribute()
    {
        if (!$this->ereg || !$this->ereg->jurusan2_kode) {
            return null;
        }

        return $this->getPassingGradeFromDatabase($this->ereg->jurusan2_kode);
    }

    /**
     * Accessor untuk nama program studi jurusan 1
     */
    public function getNamaProdi1Attribute()
    {
        if (!$this->ereg || !$this->ereg->jurusan1_kode) {
            return null;
        }

        return $this->getNamaProdiFromDatabase($this->ereg->jurusan1_kode);
    }

    /**
     * Accessor untuk nama program studi jurusan 2
     */
    public function getNamaProdi2Attribute()
    {
        if (!$this->ereg || !$this->ereg->jurusan2_kode) {
            return null;
        }

        return $this->getNamaProdiFromDatabase($this->ereg->jurusan2_kode);
    }

    /**
     * Accessor untuk rekomendasi program studi berdasarkan SCORE
     */
    public function getRekomendasiProdiAttribute()
    {
        return $this->getRekomendasiFromDatabase($this->nilai_seleksi);
    }

    /**
     * Accessor untuk program studi yang diterima (prioritas jurusan 1 jika kedua jurusan lolos)
     * Berdasarkan SCORE
     */
    public function getProdiDiterimaAttribute()
    {
        if (!$this->ereg) {
            return null;
        }

        $lolosJurusan1 = $this->status_lolos_jurusan1 === 'LOLOS';
        $lolosJurusan2 = $this->status_lolos_jurusan2 === 'LOLOS';

        // Jika kedua jurusan lolos, prioritaskan jurusan 1
        if ($lolosJurusan1 && $lolosJurusan2) {
            $passingGrade1 = $this->getPassingGradeFromDatabase($this->ereg->jurusan1_kode);
            $namaProdi1 = $this->getNamaProdiFromDatabase($this->ereg->jurusan1_kode);
            
            return [
                'prodi' => $namaProdi1,
                'kode_prodi' => $this->ereg->jurusan1_kode,
                'passing_grade' => $passingGrade1,
                'nilai' => $this->nilai_seleksi,
                'selisih' => $this->nilai_seleksi - $passingGrade1,
                'prioritas' => 'Jurusan 1 (Prioritas Utama)',
                'grade_letter' => $this->grade_letter,
                'percentage' => $this->percentage
            ];
        }

        // Jika hanya jurusan 1 yang lolos
        if ($lolosJurusan1) {
            $passingGrade1 = $this->getPassingGradeFromDatabase($this->ereg->jurusan1_kode);
            $namaProdi1 = $this->getNamaProdiFromDatabase($this->ereg->jurusan1_kode);
            
            return [
                'prodi' => $namaProdi1,
                'kode_prodi' => $this->ereg->jurusan1_kode,
                'passing_grade' => $passingGrade1,
                'nilai' => $this->nilai_seleksi,
                'selisih' => $this->nilai_seleksi - $passingGrade1,
                'prioritas' => 'Jurusan 1',
                'grade_letter' => $this->grade_letter,
                'percentage' => $this->percentage
            ];
        }

        // Jika hanya jurusan 2 yang lolos
        if ($lolosJurusan2) {
            $passingGrade2 = $this->getPassingGradeFromDatabase($this->ereg->jurusan2_kode);
            $namaProdi2 = $this->getNamaProdiFromDatabase($this->ereg->jurusan2_kode);
            
            return [
                'prodi' => $namaProdi2,
                'kode_prodi' => $this->ereg->jurusan2_kode,
                'passing_grade' => $passingGrade2,
                'nilai' => $this->nilai_seleksi,
                'selisih' => $this->nilai_seleksi - $passingGrade2,
                'prioritas' => 'Jurusan 2',
                'grade_letter' => $this->grade_letter,
                'percentage' => $this->percentage
            ];
        }

        // Jika tidak ada yang lolos, return null
        return null;
    }

    /**
     * Accessor untuk status penerimaan berdasarkan SCORE
     */
    public function getStatusPenerimaanAttribute()
    {
        $prodiDiterima = $this->prodi_diterima;
        
        if ($prodiDiterima) {
            return [
                'status' => 'DITERIMA',
                'prodi' => $prodiDiterima['prodi'],
                'kode_prodi' => $prodiDiterima['kode_prodi'],
                'prioritas' => $prodiDiterima['prioritas'],
                'selisih' => $prodiDiterima['selisih'],
                'nilai' => $prodiDiterima['nilai'],
                'grade_letter' => $prodiDiterima['grade_letter'],
                'percentage' => $prodiDiterima['percentage']
            ];
        }

        return [
            'status' => 'TIDAK DITERIMA',
            'prodi' => null,
            'kode_prodi' => null,
            'prioritas' => null,
            'selisih' => null,
            'nilai' => $this->nilai_seleksi,
            'grade_letter' => $this->grade_letter,
            'percentage' => $this->percentage
        ];
    }

    /**
     * Ambil passing grade dari tabel program_studi berdasarkan kode_prodi
     */
    private function getPassingGradeFromDatabase($kodeProdi)
    {
        return ProgramStudi::where('kode_prodi', $kodeProdi)
            ->where('status_prodi', 1) // Hanya program studi aktif
            ->value('passing_grade');
    }

    /**
     * Ambil nama program studi dari tabel program_studi berdasarkan kode_prodi
     */
    private function getNamaProdiFromDatabase($kodeProdi)
    {
        return ProgramStudi::where('kode_prodi', $kodeProdi)
            ->where('status_prodi', 1) // Hanya program studi aktif
            ->value('nama_prodi');
    }

    /**
     * Cek kelolosan berdasarkan SCORE dan kode program studi
     */
    private function checkKelolosan($nilai, $kodeProdi)
    {
        $passingGrade = $this->getPassingGradeFromDatabase($kodeProdi);
        if ($passingGrade === null) return 'TIDAK LOLOS';
        
        return $nilai >= $passingGrade ? 'LOLOS' : 'TIDAK LOLOS';
    }

    /**
     * Ambil rekomendasi program studi dari database berdasarkan SCORE
     */
    private function getRekomendasiFromDatabase($nilai)
    {
        $rekomendasi = ProgramStudi::where('passing_grade', '<=', $nilai)
            ->where('status_prodi', 1) // Hanya program studi aktif
            ->orderBy('passing_grade', 'asc')
            ->get()
            ->map(function ($prodi) use ($nilai) {
                return [
                    'kode_prodi' => $prodi->kode_prodi,
                    'nama_prodi' => $prodi->nama_prodi,
                    'jenjang' => $prodi->jenjang,
                    'fakultas' => $prodi->fakultas,
                    'passing_grade' => $prodi->passing_grade,
                    'kuota' => $prodi->kuota,
                    'selisih' => $nilai - $prodi->passing_grade,
                    'akreditasi' => $prodi->akreditasi,
                    'harga_semester' => $prodi->harga_semester,
                    'harga_tahun' => $prodi->harga_tahun,
                    'grade_required_letter' => $this->calculateGradeLetter($prodi->passing_grade)
                ];
            })
            ->toArray();

        // Urutkan berdasarkan selisih tertinggi (nilai paling jauh di atas passing grade)
        usort($rekomendasi, function($a, $b) {
            return $b['selisih'] <=> $a['selisih'];
        });

        return $rekomendasi;
    }

    /**
     * Hitung grade letter berdasarkan passing grade
     */
    private function calculateGradeLetter($passingGrade)
    {
        if ($this->max_grade <= 0) {
            return 'E';
        }
        
        $percentage = ($passingGrade / $this->max_grade) * 100;
        
        if ($percentage >= 85) return 'A';
        if ($percentage >= 75) return 'B';
        if ($percentage >= 65) return 'C';
        if ($percentage >= 55) return 'D';
        return 'E';
    }

    /**
     * Scope untuk attempt yang finished
     */
    public function scopeFinished($query)
    {
        return $query->where('state', 'finished');
    }

    /**
     * Scope untuk attempt berdasarkan user moodle
     */
    public function scopeByMoodleUser($query, $moodleUserId)
    {
        return $query->where('moodle_user_id', $moodleUserId);
    }

    /**
     * Scope untuk attempt berdasarkan quiz
     */
    public function scopeByQuiz($query, $quizId)
    {
        return $query->where('quiz_id', $quizId);
    }

    /**
     * Scope untuk filter berdasarkan program studi
     */
    public function scopeByProgramStudi($query, $kodeProdi)
    {
        return $query->whereHas('moodleUser.masterUser.ereg', function($q) use ($kodeProdi) {
            $q->where('jurusan1_kode', $kodeProdi)
              ->orWhere('jurusan2_kode', $kodeProdi);
        });
    }

    /**
     * Scope untuk filter yang lolos di program studi tertentu berdasarkan SCORE
     */
    public function scopeLolosProgramStudi($query, $jurusanNumber = 1)
    {
        return $query->whereHas('moodleUser.masterUser.ereg', function($q) use ($jurusanNumber) {
            $field = $jurusanNumber == 1 ? 'jurusan1_kode' : 'jurusan2_kode';
            $q->whereNotNull($field);
        })->get()->filter(function($attempt) use ($jurusanNumber) {
            $statusField = $jurusanNumber == 1 ? 'status_lolos_jurusan1' : 'status_lolos_jurusan2';
            return $attempt->$statusField === 'LOLOS';
        });
    }

    /**
     * Scope untuk filter yang diterima berdasarkan SCORE
     */
    public function scopeDiterima($query)
    {
        return $query->get()->filter(function($attempt) {
            return $attempt->status_penerimaan['status'] === 'DITERIMA';
        });
    }

    /**
     * Scope untuk filter berdasarkan range nilai (menggunakan SCORE)
     */
    public function scopeRangeNilai($query, $min, $max)
    {
        return $query->whereBetween('score', [$min, $max]);
    }

    /**
     * Scope untuk filter berdasarkan grade letter berdasarkan SCORE
     */
    public function scopeByGradeLetter($query, $gradeLetter)
    {
        return $query->whereRaw("CASE 
            WHEN score IS NOT NULL AND max_grade > 0 THEN
                CASE 
                    WHEN (score / max_grade * 100) >= 85 THEN 'A'
                    WHEN (score / max_grade * 100) >= 75 THEN 'B'
                    WHEN (score / max_grade * 100) >= 65 THEN 'C'
                    WHEN (score / max_grade * 100) >= 55 THEN 'D'
                    ELSE 'E'
                END
            WHEN percentage >= 85 THEN 'A'
            WHEN percentage >= 75 THEN 'B'
            WHEN percentage >= 65 THEN 'C'
            WHEN percentage >= 55 THEN 'D'
            ELSE 'E'
        END = ?", [$gradeLetter]);
    }

    /**
     * Scope untuk filter berdasarkan status kelulusan (is_passed)
     */
    public function scopePassed($query)
    {
        return $query->where('is_passed', true);
    }

    /**
     * Scope untuk filter berdasarkan status tidak lulus
     */
    public function scopeFailed($query)
    {
        return $query->where('is_passed', false);
    }

    /**
     * Scope untuk filter yang sudah selesai dan memiliki SCORE
     */
    public function scopeHasNilai($query)
    {
        return $query->finished()->whereNotNull('score');
    }

    /**
     * Scope untuk filter dengan score minimal
     */
    public function scopeMinScore($query, $minScore)
    {
        return $query->where('score', '>=', $minScore);
    }

    /**
     * Scope untuk filter dengan percentage minimal berdasarkan SCORE
     */
    public function scopeMinPercentage($query, $minPercentage)
    {
        return $query->where('percentage', '>=', $minPercentage);
    }

    /**
     * Method untuk update score manual
     */
    public function updateScore($score, $maxGrade = 100)
    {
        $this->score = $score;
        $this->max_grade = $maxGrade;
        $this->percentage = ($score / $maxGrade) * 100;
        $this->is_passed = $this->percentage >= 60; // Standar kelulusan 60%
        $this->grade_updated_at = now();
        $this->grade_calculation_method = 'score_based';
        $this->save();
        
        return $this;
    }

    /**
     * Method untuk mendapatkan statistik kelolosan berdasarkan SCORE
     */
    public static function getStatistikKelolosan()
    {
        $totalAttempts = self::finished()->hasNilai()->count();
        $diterima = self::diterima()->count();
        
        // Statistik berdasarkan grade letter berdasarkan SCORE
        $gradeStats = self::selectRaw("
            CASE 
                WHEN score IS NOT NULL AND max_grade > 0 THEN
                    CASE 
                        WHEN (score / max_grade * 100) >= 85 THEN 'A'
                        WHEN (score / max_grade * 100) >= 75 THEN 'B'
                        WHEN (score / max_grade * 100) >= 65 THEN 'C'
                        WHEN (score / max_grade * 100) >= 55 THEN 'D'
                        ELSE 'E'
                    END
                WHEN percentage >= 85 THEN 'A'
                WHEN percentage >= 75 THEN 'B'
                WHEN percentage >= 65 THEN 'C'
                WHEN percentage >= 55 THEN 'D'
                ELSE 'E'
            END as grade_letter,
            COUNT(*) as count
        ")
        ->whereNotNull('score')
        ->groupBy('grade_letter')
        ->orderBy('grade_letter')
        ->get()
        ->pluck('count', 'grade_letter');

        // Hitung rata-rata score
        $averageScore = self::whereNotNull('score')->avg('score') ?? 0;

        return [
            'total_peserta' => $totalAttempts,
            'diterima' => $diterima,
            'tidak_diterima' => $totalAttempts - $diterima,
            'persentase_diterima' => $totalAttempts > 0 ? round(($diterima / $totalAttempts) * 100, 2) : 0,
            'grade_distribution' => $gradeStats,
            'rata_rata_percentage' => round(self::whereNotNull('percentage')->avg('percentage') ?? 0, 2),
            'rata_rata_score' => round($averageScore, 2),
            'score_min' => round(self::whereNotNull('score')->min('score') ?? 0, 2),
            'score_max' => round(self::whereNotNull('score')->max('score') ?? 0, 2),
        ];
    }

    /**
     * Method untuk mendapatkan summary statistik berdasarkan SCORE
     */
    public function getSummary()
    {
        return [
            'attempt_id' => $this->attempt_id,
            'user' => [
                'username' => $this->moodleUser->username ?? null,
                'nama_lengkap' => $this->user ? ($this->user->name ?? null) : null,
                'email' => $this->user->email ?? null,
            ],
            'quiz' => [
                'quiz_id' => $this->quiz_id,
                'state' => $this->status,
                'timestart' => $this->timestart?->format('Y-m-d H:i:s'),
                'timefinish' => $this->timefinish?->format('Y-m-d H:i:s'),
                'duration' => $this->duration_formatted,
            ],
            'nilai' => [
                'score' => $this->score,
                'grade' => $this->grade,
                'max_grade' => $this->max_grade,
                'percentage' => $this->percentage,
                'grade_letter' => $this->grade_letter,
                'is_passed' => $this->is_passed ? 'LULUS' : 'TIDAK LULUS',
                'total_questions' => $this->total_questions,
                'correct_answers' => $this->correct_answers,
                'wrong_answers' => $this->wrong_answers,
                'nilai_seleksi' => $this->nilai_seleksi,
                'grade_calculation_method' => $this->grade_calculation_info['method'],
            ],
            'penerimaan' => $this->status_penerimaan,
            'jurusan' => [
                'jurusan1' => [
                    'kode' => $this->ereg->jurusan1_kode ?? null,
                    'nama' => $this->nama_prodi1,
                    'passing_grade' => $this->passing_grade_jurusan1,
                    'status' => $this->status_lolos_jurusan1,
                ],
                'jurusan2' => [
                    'kode' => $this->ereg->jurusan2_kode ?? null,
                    'nama' => $this->nama_prodi2,
                    'passing_grade' => $this->passing_grade_jurusan2,
                    'status' => $this->status_lolos_jurusan2,
                ],
            ],
            'rekomendasi_count' => count($this->rekomendasi_prodi),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'grade_updated_at' => $this->grade_updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Method untuk mendapatkan nilai utama yang ditampilkan (untuk kompatibilitas)
     */
    public function getNilaiTampilAttribute()
    {
        return $this->nilai_seleksi;
    }

    /**
     * Method untuk mendapatkan persentase berdasarkan score
     */
    public function getPersentaseDariScoreAttribute()
    {
        if ($this->score !== null && $this->max_grade > 0) {
            return round(($this->score / $this->max_grade) * 100, 2);
        }
        return $this->percentage;
    }
}