<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessFile extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'business_id',
        'original_name',
        'stored_name',
        'file_path',
        'mime_type',
        'file_size',
        'file_type',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function sourceContents(): HasMany
    {
        return $this->hasMany(BusinessSourceContent::class);
    }

    public function isImage(): bool
    {
        return $this->file_type === 'image';
    }

    public function isDocument(): bool
    {
        return $this->file_type === 'document';
    }

    public function formattedSize(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2).' MB';
        }

        return number_format(max($bytes, 0) / 1024, 1).' KB';
    }
}
