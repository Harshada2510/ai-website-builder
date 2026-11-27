<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessSourceContent extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'business_id',
        'business_file_id',
        'source_type',
        'source_url',
        'source_title',
        'extracted_text',
        'extraction_status',
        'error_message',
        'extracted_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'extracted_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function businessFile(): BelongsTo
    {
        return $this->belongsTo(BusinessFile::class);
    }

    public function isCompleted(): bool
    {
        return $this->extraction_status === 'completed';
    }

    public function statusBadgeClass(): string
    {
        return match ($this->extraction_status) {
            'completed' => 'status-published',
            'failed' => 'status-failed',
            'needs_review' => 'status-review',
            default => 'status-draft',
        };
    }
}
