<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeneratedWebsite extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'business_id',
        'site_title',
        'slug',
        'tagline',
        'generated_content',
        'generation_status',
        'is_published',
        'published_at',
        'ai_model',
        'error_message',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'generated_content' => 'array',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function isCompleted(): bool
    {
        return $this->generation_status === 'completed';
    }

    public function statusBadgeClass(): string
    {
        return match ($this->generation_status) {
            'completed' => 'status-published',
            'failed' => 'status-failed',
            'pending', 'processing' => 'status-draft',
            default => 'status-draft',
        };
    }

    public function isPublished(): bool
    {
        return (bool) $this->is_published && filled($this->slug);
    }

    public function publicUrl(): ?string
    {
        if (! $this->isPublished()) {
            return null;
        }

        return route('public-site.show', $this->slug);
    }
}
