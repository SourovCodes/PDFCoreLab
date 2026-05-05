<?php

namespace App\Models;

use App\Enums\GhostscriptPreset;
use App\Enums\PdfCompressionStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'api_key_id',
    'user_id',
    'original_filename',
    'original_mime_type',
    'original_size_bytes',
    'original_disk',
    'original_path',
    'compressed_disk',
    'compressed_path',
    'compressed_size_bytes',
    'ghostscript_preset',
    'status',
    'failure_message',
    'queued_at',
    'processing_started_at',
    'processed_at',
    'failed_at',
])]
class PdfCompression extends Model
{
    use HasUlids;

    public function apiKey(): BelongsTo
    {
        return $this->belongsTo(ApiKey::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function uniqueIds(): array
    {
        return ['public_id'];
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    protected function casts(): array
    {
        return [
            'api_key_id' => 'integer',
            'user_id' => 'integer',
            'ghostscript_preset' => GhostscriptPreset::class,
            'status' => PdfCompressionStatus::class,
            'queued_at' => 'datetime',
            'processing_started_at' => 'datetime',
            'processed_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }
}
