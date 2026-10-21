<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupGelombang extends Model
{
    protected $table = 'group_gelombang';
    
    protected $fillable = [
        'gelombang',
        'nama_gelombang',
        'deskripsi',
        'link_group',
        'tipe_group',
        'is_active',
        'urutan'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'gelombang' => 'integer',
        'urutan' => 'integer'
    ];

    // Scope untuk gelombang aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope untuk urutan
    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan')->orderBy('gelombang');
    }

    // Accessor untuk nama gelombang lengkap
    public function getNamaLengkapAttribute()
    {
        return "Gelombang {$this->gelombang}: {$this->nama_gelombang}";
    }

    // Accessor untuk icon tipe group
    public function getIconTipeAttribute()
    {
        $icons = [
            'whatsapp' => 'M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z',
            'telegram' => 'M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z',
            'discord' => 'M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z',
            'zoom' => 'M21 12a9 9 0 11-18 0 9 9 0 0118 0z M21 12c0-3.866-3.135-7-7-7s-7 3.134-7 7 3.135 7 7 7 7-3.134 7-7z M14 12a2 2 0 11-4 0 2 2 0 014 0z',
            'meet' => 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z'
        ];

        return $icons[$this->tipe_group] ?? $icons['whatsapp'];
    }

    // Accessor untuk warna tipe group
    public function getWarnaTipeAttribute()
    {
        $colors = [
            'whatsapp' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            'telegram' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
            'discord' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300',
            'zoom' => 'bg-blue-50 text-blue-700 dark:bg-blue-800 dark:text-blue-200',
            'meet' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
        ];

        return $colors[$this->tipe_group] ?? $colors['whatsapp'];
    }

    // Method untuk validasi link
    public function isValidLink()
    {
        return filter_var($this->link_group, FILTER_VALIDATE_URL);
    }

    // Method untuk short link
    public function getShortLinkAttribute()
    {
        return strlen($this->link_group) > 50 
            ? substr($this->link_group, 0, 50) . '...' 
            : $this->link_group;
    }
    
    public function masterUsers()
    {
        return $this->hasMany(MasterUser::class, 'gelombang', 'gelombang');
    }
}