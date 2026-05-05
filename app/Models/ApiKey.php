<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiKey extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'key_hash',
        'is_active',
        'last_used_at',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pdfCompressions()
    {
        return $this->hasMany(PdfCompression::class);
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_used_at' => 'timestamp',
        ];
    }
}
