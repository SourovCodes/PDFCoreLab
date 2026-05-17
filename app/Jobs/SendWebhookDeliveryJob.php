<?php

namespace App\Jobs;

use App\Models\WebhookDelivery;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class SendWebhookDeliveryJob implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public bool $deleteWhenMissingModels = true;

    public int $tries = 5;

    public int $timeout = 15;

    public function __construct(public WebhookDelivery $delivery) {}

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [10, 30, 120, 300, 900];
    }

    public function handle(): void
    {
        $this->delivery->refresh();

        $apiKey = $this->delivery->apiKey;

        if ($apiKey === null || ! $apiKey->hasWebhook()) {
            $this->delivery->forceFill([
                'status' => WebhookDelivery::STATUS_FAILED,
                'error' => 'API key is missing or no longer has a webhook configured.',
            ])->save();

            return;
        }

        $body = json_encode($this->delivery->payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($body === false) {
            throw new RuntimeException('Failed to encode webhook payload.');
        }

        $signature = hash_hmac('sha256', $body, $apiKey->webhook_secret);

        $attempt = $this->delivery->attempt + 1;

        $this->delivery->forceFill(['attempt' => $attempt])->save();

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'User-Agent' => 'PDFCoreLab-Webhook/1.0',
            'X-PDFCoreLab-Event' => $this->delivery->event,
            'X-PDFCoreLab-Delivery' => $this->delivery->public_id,
            'X-PDFCoreLab-Signature' => 'sha256='.$signature,
            'X-PDFCoreLab-Attempt' => (string) $attempt,
        ])
            ->withBody($body, 'application/json')
            ->timeout(10)
            ->connectTimeout(5)
            ->post($this->delivery->url);

        $responseBody = Str::limit((string) $response->body(), 2000, '');

        if ($response->successful()) {
            $this->delivery->forceFill([
                'status' => WebhookDelivery::STATUS_DELIVERED,
                'response_status' => $response->status(),
                'response_body' => $responseBody,
                'error' => null,
                'delivered_at' => now(),
            ])->save();

            return;
        }

        $this->delivery->forceFill([
            'response_status' => $response->status(),
            'response_body' => $responseBody,
            'error' => 'Webhook endpoint returned non-2xx status.',
        ])->save();

        throw new RuntimeException(sprintf(
            'Webhook delivery %s failed with HTTP %d.',
            $this->delivery->public_id,
            $response->status(),
        ));
    }

    public function failed(?Throwable $exception): void
    {
        if (! $this->delivery->exists) {
            return;
        }

        $this->delivery->refresh();

        Log::error('Webhook delivery failed', [
            'delivery_id' => $this->delivery->public_id,
            'url' => $this->delivery->url,
            'event' => $this->delivery->event,
            'error' => $exception?->getMessage(),
        ]);

        $this->delivery->forceFill([
            'status' => WebhookDelivery::STATUS_FAILED,
            'error' => $exception === null
                ? 'Webhook delivery failed.'
                : Str::limit($exception->getMessage(), 2000, ''),
        ])->save();
    }
}
