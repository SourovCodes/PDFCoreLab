<?php

namespace App\Support\Webhooks;

use App\Enums\WebhookEvent;
use App\Http\Resources\Api\V1\PdfCompressionResource;
use App\Jobs\SendWebhookDeliveryJob;
use App\Models\ApiKey;
use App\Models\PdfCompression;
use App\Models\WebhookDelivery;
use Illuminate\Http\Request;

class WebhookDispatcher
{
    public function dispatch(ApiKey $apiKey, WebhookEvent $event, PdfCompression $compression): ?WebhookDelivery
    {
        if (! $apiKey->hasWebhook()) {
            return null;
        }

        $data = PdfCompressionResource::make($compression)->toArray(Request::create('/'));

        $payload = [
            'event' => $event->value,
            'delivered_at' => now()->toIso8601String(),
            'data' => $data,
        ];

        $delivery = WebhookDelivery::create([
            'api_key_id' => $apiKey->id,
            'pdf_compression_id' => $compression->id,
            'event' => $event->value,
            'url' => $apiKey->webhook_url,
            'payload' => $payload,
            'attempt' => 0,
            'status' => WebhookDelivery::STATUS_PENDING,
        ]);

        $payload['delivery_id'] = $delivery->public_id;

        $delivery->forceFill(['payload' => $payload])->save();

        SendWebhookDeliveryJob::dispatch($delivery);

        return $delivery;
    }
}
