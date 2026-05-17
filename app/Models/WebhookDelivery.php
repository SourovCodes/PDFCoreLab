<?php

namespace App\Models;

use Database\Factories\WebhookDeliveryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'api_key_id',
    'pdf_compression_id',
    'event',
    'url',
    'payload',
    'attempt',
    'status',
    'response_status',
    'response_body',
    'error',
    'delivered_at',
])]
class WebhookDelivery extends Model
{
    /** @use HasFactory<WebhookDeliveryFactory> */
    use HasFactory;

    use HasUlids;

    public const STATUS_PENDING = 'pending';

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_FAILED = 'failed';

    public function apiKey(): BelongsTo
    {
        return $this->belongsTo(ApiKey::class);
    }

    public function pdfCompression(): BelongsTo
    {
        return $this->belongsTo(PdfCompression::class);
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
            'pdf_compression_id' => 'integer',
            'payload' => 'array',
            'attempt' => 'integer',
            'response_status' => 'integer',
            'delivered_at' => 'datetime',
        ];
    }
}
