<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NimImportLog extends Model
{
    protected $fillable = [
        'admin_user_id',
        'filename',
        'original_filename',
        'total_rows',
        'success_count',
        'skipped_count',
        'failed_count',
        'overwrite_mode',
        'status',
        'error_message',
    ];

    protected $casts = [
        'overwrite_mode' => 'boolean',
        'total_rows'     => 'integer',
        'success_count'  => 'integer',
        'skipped_count'  => 'integer',
        'failed_count'   => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Relasi
    // -------------------------------------------------------------------------

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(NimImportDetail::class, 'import_log_id');
    }

    // -------------------------------------------------------------------------
    // Scope
    // -------------------------------------------------------------------------

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
public function syncCounts()
{
    $stats = \App\Models\NimImportDetail::where('import_log_id', $this->id)
        ->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN result_status = 'berhasil' THEN 1 ELSE 0 END) as success,
            SUM(CASE WHEN result_status = 'dilewati' THEN 1 ELSE 0 END) as skipped,
            SUM(CASE WHEN result_status = 'tidak_ditemukan' THEN 1 ELSE 0 END) as failed
        ")
        ->first();

    $this->update([
        'total_rows'    => $stats->total ?? 0,
        'success_count' => $stats->success ?? 0,
        'skipped_count' => $stats->skipped ?? 0,
        'failed_count'  => $stats->failed ?? 0,
        'status'        => 'completed'
    ]);
}
}