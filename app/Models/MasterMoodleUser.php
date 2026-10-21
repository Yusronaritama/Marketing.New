<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterMoodleUser extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan oleh model.
     * Sesuaikan jika ada kesalahan ketik di gambar.
     */
    protected $table = 'master_moodle_users';

    /**
     * Atribut yang dapat diisi secara massal.
     * Didasarkan pada screenshot 'image_2c7a36.png'
     */
    protected $fillable = [
        'master_user_id',
        'moodle_user_id',
        'username',
        'email',
        'firstname',
        'lastname',
        'prodi_pilihan1',
        'nama_prodi_pilihan1',
        'prodi_pilihan2',
        'nama_prodi_pilihan2',
        'quiz_id',
        'attempt_count',
        'status_kelulusan',
        'nilai_quiz',
        'data_source',
        // 'updated_at' dan 'created_at' biasanya dihandle otomatis
    ];

    /**
     * Relasi ke MasterUser (penghubung ke tabel users Laravel)
     */
    public function masterUser()
    {
        return $this->belongsTo(MasterUser::class, 'master_user_id');
    }
}