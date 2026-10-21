<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UjianCountdown extends Model
{
    protected $table = 'ujian_countdowns';
    
    protected $fillable = [
        'ujian_name', // Diubah dari exam_name
        'description',
        'start_date', // Diubah dari start_time
        'end_date', // Diubah dari end_time
        'is_active',
        'status'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean'
    ];

    public function updateStatus()
    {
        $now = now();
        
        if ($now < $this->start_date) {
            $this->status = 'upcoming';
        } elseif ($now >= $this->start_date && $now <= $this->end_date) {
            $this->status = 'ongoing';
        } else {
            $this->status = 'finished';
        }
        
        $this->save();
    }

    // Helper method untuk menghitung durasi dalam hari
    public function getDurationInDaysAttribute()
    {
        return $this->start_date->diffInDays($this->end_date);
    }

    // Helper method untuk menghitung durasi dalam jam
    public function getDurationInHoursAttribute()
    {
        return $this->start_date->diffInHours($this->end_date);
    }

    // Helper method untuk menghitung sisa waktu dalam detik
    public function getRemainingSecondsAttribute()
    {
        return max(0, $this->end_date->diffInSeconds(now()));
    }

    // Helper method untuk format tanggal
    public function getFormattedStartDateAttribute()
    {
        return $this->start_date->format('d M Y, H:i');
    }

    public function getFormattedEndDateAttribute()
    {
        return $this->end_date->format('d M Y, H:i');
    }
}