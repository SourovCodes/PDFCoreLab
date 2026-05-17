<?php

use App\Enums\GhostscriptPreset;
use App\Enums\PdfCompressionStatus;
use App\Enums\WebhookEvent;
use App\Jobs\CompressPdfCompressionJob;
use App\Jobs\SendWebhookDeliveryJob;
use App\Models\ApiKey;
use App\Models\PdfCompression;
use App\Models\User;
use App\Models\WebhookDelivery;
use App\Services\PdfCompression\CompressionResult;
use App\Services\PdfCompression\GhostscriptPdfCompressor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

function makeApiKeyWithWebhook(?string $plainKey = null): ApiKey
{
    $user = User::factory()->create();
    $factory = ApiKey::factory()->for($user);

    if ($plainKey !== null) {
        $factory = $factory->withKnownKey($plainKey);
    }

    return $factory->create([
        'webhook_url' => 'https://example.test/webhooks/pdfcorelab',
        'webhook_secret' => str_repeat('a', 48),
    ]);
}

test('async compression success dispatches a completed webhook delivery', function () {
    Bus::fake([SendWebhookDeliveryJob::class]);
    Storage::fake('local');

    $apiKey = makeApiKeyWithWebhook();

    $compression = PdfCompression::factory()
        ->for($apiKey)
        ->for($apiKey->user)
        ->processing()
        ->create();

    $this->mock(GhostscriptPdfCompressor::class)
        ->shouldReceive('compress')
        ->once()
        ->andReturn(new CompressionResult(
            disk: 'local',
            path: 'pdf-compressions/compressed/test.pdf',
            sizeInBytes: 512_000,
        ));

    (new CompressPdfCompressionJob($compression))
        ->handle(app(GhostscriptPdfCompressor::class));

    expect($compression->fresh()->status)->toBe(PdfCompressionStatus::Completed);

    Bus::assertDispatched(SendWebhookDeliveryJob::class, function (SendWebhookDeliveryJob $job) use ($compression) {
        return $job->delivery->event === WebhookEvent::CompressionCompleted->value
            && $job->delivery->pdf_compression_id === $compression->id;
    });

    expect(WebhookDelivery::query()->count())->toBe(1);
    $delivery = WebhookDelivery::query()->sole();
    expect($delivery->event)->toBe(WebhookEvent::CompressionCompleted->value)
        ->and($delivery->status)->toBe(WebhookDelivery::STATUS_PENDING)
        ->and($delivery->url)->toBe($apiKey->webhook_url)
        ->and($delivery->payload)->toHaveKey('event', WebhookEvent::CompressionCompleted->value)
        ->and($delivery->payload['data'])->toHaveKey('public_id', $compression->public_id);
});

test('async compression failure dispatches a failed webhook delivery', function () {
    Bus::fake([SendWebhookDeliveryJob::class]);

    $apiKey = makeApiKeyWithWebhook();
    $compression = PdfCompression::factory()
        ->for($apiKey)
        ->for($apiKey->user)
        ->processing()
        ->create();

    $job = new CompressPdfCompressionJob($compression);
    $job->failed(new RuntimeException('Ghostscript exploded'));

    expect($compression->fresh()->status)->toBe(PdfCompressionStatus::Failed);

    Bus::assertDispatched(SendWebhookDeliveryJob::class, function (SendWebhookDeliveryJob $job) {
        return $job->delivery->event === WebhookEvent::CompressionFailed->value;
    });
});

test('sync compression does not dispatch any webhook', function () {
    Bus::fake([SendWebhookDeliveryJob::class]);
    Storage::fake('local');

    $plainKey = 'test-sync-no-webhook-key';
    makeApiKeyWithWebhook($plainKey);

    $this->mock(GhostscriptPdfCompressor::class)
        ->shouldReceive('compress')
        ->once()
        ->andReturn(new CompressionResult(
            disk: 'local',
            path: 'pdf-compressions/compressed/test.pdf',
            sizeInBytes: 512_000,
        ));

    $file = UploadedFile::fake()->create('document.pdf', 1024, 'application/pdf');

    $this->postJson('/api/v1/pdf-compressions', [
        'pdf' => $file,
        'preset' => GhostscriptPreset::Screen->value,
    ], ['X-API-Key' => $plainKey])
        ->assertOk()
        ->assertJsonPath('data.status', 'completed');

    Bus::assertNotDispatched(SendWebhookDeliveryJob::class);
    expect(WebhookDelivery::query()->count())->toBe(0);
});

test('async compression without configured webhook does not create a delivery', function () {
    Bus::fake([SendWebhookDeliveryJob::class]);
    Storage::fake('local');

    $user = User::factory()->create();
    $apiKey = ApiKey::factory()->for($user)->create([
        'webhook_url' => null,
        'webhook_secret' => null,
    ]);

    $compression = PdfCompression::factory()
        ->for($apiKey)
        ->for($user)
        ->processing()
        ->create();

    $this->mock(GhostscriptPdfCompressor::class)
        ->shouldReceive('compress')
        ->once()
        ->andReturn(new CompressionResult(
            disk: 'local',
            path: 'pdf-compressions/compressed/test.pdf',
            sizeInBytes: 512_000,
        ));

    (new CompressPdfCompressionJob($compression))
        ->handle(app(GhostscriptPdfCompressor::class));

    Bus::assertNotDispatched(SendWebhookDeliveryJob::class);
    expect(WebhookDelivery::query()->count())->toBe(0);
});

test('queued store endpoint returns 202 and only the compression job is dispatched at request time', function () {
    Storage::fake('local');
    Cache::put('pdf-compression:sync-active', config('pdf-compression.sync_processing_threshold', 10));
    Bus::fake();

    $plainKey = 'test-async-webhook-key';
    makeApiKeyWithWebhook($plainKey);

    $file = UploadedFile::fake()->create('document.pdf', 1024, 'application/pdf');

    $this->postJson('/api/v1/pdf-compressions', [
        'pdf' => $file,
        'preset' => 'screen',
    ], ['X-API-Key' => $plainKey])
        ->assertStatus(202)
        ->assertJsonPath('data.status', 'queued');

    Bus::assertDispatched(CompressPdfCompressionJob::class);
    Bus::assertNotDispatched(SendWebhookDeliveryJob::class);
});
