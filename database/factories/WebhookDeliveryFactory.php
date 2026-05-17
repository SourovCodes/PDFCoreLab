<?php

namespace Database\Factories;

use App\Enums\WebhookEvent;
use App\Models\ApiKey;
use App\Models\PdfCompression;
use App\Models\WebhookDelivery;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WebhookDelivery>
 */
class WebhookDeliveryFactory extends Factory
{
    protected $model = WebhookDelivery::class;

    public function definition(): array
    {
        return [
            'api_key_id' => ApiKey::factory(),
            'pdf_compression_id' => PdfCompression::factory(),
            'event' => WebhookEvent::CompressionCompleted->value,
            'url' => fake()->url(),
            'payload' => ['event' => WebhookEvent::CompressionCompleted->value, 'data' => []],
            'attempt' => 0,
            'status' => WebhookDelivery::STATUS_PENDING,
        ];
    }
}
