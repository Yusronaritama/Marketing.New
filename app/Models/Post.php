<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'image',
        'author_id',
        'status',
        'publish_start',
        'publish_end',
        'schedule_type',
        'meta_description',
        'tags',
        'views',
    ];

    protected $casts = [
        'publish_start' => 'datetime',
        'publish_end' => 'datetime',
        'is_published' => 'boolean',
    ];

    /**
     * Get the author that owns the post.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the tags as array.
     */
    public function getTagsArrayAttribute()
    {
        return $this->tags ? explode(',', $this->tags) : [];
    }

    /**
     * Scope a query to only include published posts.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->where(function($q) {
                        $q->where('schedule_type', 'immediate')
                          ->orWhere(function($q2) {
                              $q2->where('schedule_type', 'scheduled')
                                 ->where('publish_start', '<=', now())
                                 ->where(function($q3) {
                                     $q3->whereNull('publish_end')
                                        ->orWhere('publish_end', '>', now());
                                 });
                          });
                    });
    }

    /**
     * Scope a query to only include draft posts.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft')
                    ->orWhere(function($q) {
                        $q->where('schedule_type', 'scheduled')
                          ->where(function($q2) {
                              $q2->where('publish_start', '>', now())
                                 ->orWhere('publish_end', '<=', now());
                          });
                    });
    }

    /**
     * Check if post is currently published based on schedule
     */
    public function getIsCurrentlyPublishedAttribute()
    {
        if ($this->status !== 'published') {
            return false;
        }

        if ($this->schedule_type === 'immediate') {
            return true;
        }

        $now = now();
        $startValid = $this->publish_start ? $this->publish_start <= $now : true;
        $endValid = $this->publish_end ? $this->publish_end > $now : true;

        return $startValid && $endValid;
    }

    /**
     * Get current status based on schedule
     */
    public function getCurrentStatusAttribute()
    {
        if ($this->status === 'draft') {
            return 'draft';
        }

        if ($this->schedule_type === 'immediate') {
            return 'published';
        }

        $now = now();
        
        if ($this->publish_start && $this->publish_start > $now) {
            return 'scheduled';
        }

        if ($this->publish_end && $this->publish_end <= $now) {
            return 'expired';
        }

        return 'published';
    }
    public function getEffectiveStatusAttribute()
{
    if ($this->schedule_type === 'immediate') {
        return $this->status;
    }

    $now = now();
    
    // Jika belum waktunya publish
    if ($this->publish_start && $this->publish_start > $now) {
        return 'scheduled';
    }
    
    // Jika sudah expired
    if ($this->publish_end && $this->publish_end <= $now) {
        return 'expired';
    }
    
    // Jika dalam rentang waktu publish
    return 'published';
}

/**
 * Scope untuk posts yang aktif (sedang dipublikasi)
 */
public function scopeActive($query)
{
    return $query->where(function($q) {
        $q->where('schedule_type', 'immediate')
          ->where('status', 'published')
          ->orWhere(function($q2) {
              $now = now();
              $q2->where('schedule_type', 'scheduled')
                 ->where('status', 'published')
                 ->where('publish_start', '<=', $now)
                 ->where(function($q3) use ($now) {
                     $q3->whereNull('publish_end')
                        ->orWhere('publish_end', '>', $now);
                 });
          });
    });
}
}
