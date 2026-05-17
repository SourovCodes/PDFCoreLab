<?php

use App\Enums\WebhookEvent;
use App\Jobs\SendWebhookDeliveryJob;
use App\Models\ApiKey;
use App\Models\PdfCompression;
use App\Models\User;
use App\Models\WebhookDelivery;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

function makeDelivery(string $secret = 'super-secret-value', array $payload = ['event' => 'compression.completed', 'data' => ['public_id' => '01J0000000000000000000']]): WebhookDelivery
{
    $user = User::factory()->create();
    $apiKey = ApiKey::factory()->for($user)->create([
        'webhook_url' => 'https://example.test/webhook',
        'webhook_secret' => $secret,
    ]);

    $compression = PdfCompression::factory()->for($apiKey)->for($user)->completed()->create();

    return WebhookDelivery::create([
        'api_key_id' => $apiKey->id,
        'pdf_compression_id' => $compression->id,
        'event' => $payload['event'],
        'url' => $apiKey->webhook_url,
        'payload' => $payload,
        'attempt' => 0,
        'status' => WebhookDelivery::STATUS_PENDING,
    ]);
}

test('successful delivery marks row as delivered and sends signed body', function () {
    Http::fake([
        'example.test/*' => Http::response('OK', 200),
    ]);

    $secret = 'super-secret-value';
    $payload = ['event' => WebhookEvent::CompressionCompleted->value, 'data' => ['public_id' => '01J0000000000000000000']];
    $delivery = makeDelivery($secret, $payload);

    (new SendWebhookDeliveryJob($delivery))->handle();

    $delivery->refresh();
    expect($delivery->status)->toBe(WebhookDelivery::STATUS_DELIVERED)
        ->and($delivery->response_status)->toBe(200)
        ->and($delivery->attempt)->toBe(1)
        ->and($delivery->delivered_at)->not->toBeNull();

    Http::assertSent(function (Request $request) use ($secret, $payload) {
        $body = $request->body();
        $expected = 'sha256='.hash_hmac('sha256', $body, $secret);

        return $request->url() === 'https://example.test/webhook'
            && $request->method() === 'POST'
            && $request->header('X-PDFCoreLab-Event')[0] === $payload['event']
            && $request->header('X-PDFCoreLab-Signature')[0] === $expected
            && $request->header('X-PDFCoreLab-Attempt')[0] === '1';
    });
});

test('non-2xx response increments attempt, stores error, and throws to trigger retry', function () {
    Http::fake([
        'example.test/*' => Http::response('Internal Server Error', 500),
    ]);

    $delivery = makeDelivery();

    expect(fn () => (new SendWebhookDeliveryJob($delivery))->handle())
        ->toThrow(RuntimeException::class);

    $delivery->refresh();
    expect($delivery->status)->toBe(WebhookDelivery::STATUS_PENDING)
        ->and($delivery->response_status)->toBe(500)
        ->and($delivery->attempt)->toBe(1)
        ->and($delivery->error)->not->toBeNull();
});

test('failed callback marks delivery as failed', function () {
    $delivery = makeDelivery();

    (new SendWebhookDeliveryJob($delivery))->failed(new RuntimeException('Boom'));

    $delivery->refresh();
    expect($delivery->status)->toBe(WebhookDelivery::STATUS_FAILED)
        ->and($delivery->error)->toContain('Boom');
});
