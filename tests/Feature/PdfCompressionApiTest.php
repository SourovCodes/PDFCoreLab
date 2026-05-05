<?php

use App\Enums\GhostscriptPreset;
use App\Enums\PdfCompressionStatus;
use App\Jobs\CompressPdfCompressionJob;
use App\Models\ApiKey;
use App\Models\PdfCompression;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

// ─── Authentication ───

test('request without api key returns 401', function () {
    $this->getJson('/api/v1/pdf-compressions')
        ->assertUnauthorized();
});

test('request with invalid api key returns 401', function () {
    $this->getJson('/api/v1/pdf-compressions', ['X-API-Key' => 'invalid-key'])
        ->assertUnauthorized();
});

test('request with inactive api key returns 401', function () {
    $plainKey = 'test-inactive-key';
    ApiKey::factory()->inactive()->withKnownKey($plainKey)->create();

    $this->getJson('/api/v1/pdf-compressions', ['X-API-Key' => $plainKey])
        ->assertUnauthorized();
});

// ─── Index ───

test('index returns paginated compressions for authenticated api key', function () {
    $plainKey = 'test-index-key';
    $user = User::factory()->create();
    $apiKey = ApiKey::factory()->withKnownKey($plainKey)->for($user)->create();

    PdfCompression::factory()
        ->count(3)
        ->for($apiKey)
        ->for($user)
        ->completed()
        ->create();

    $this->getJson('/api/v1/pdf-compressions', ['X-API-Key' => $plainKey])
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

test('index only returns compressions belonging to the authenticated api key', function () {
    $plainKey = 'test-own-key';
    $user = User::factory()->create();
    $apiKey = ApiKey::factory()->withKnownKey($plainKey)->for($user)->create();

    PdfCompression::factory()->for($apiKey)->for($user)->completed()->create();

    $otherUser = User::factory()->create();
    $otherApiKey = ApiKey::factory()->for($otherUser)->create();
    PdfCompression::factory()->for($otherApiKey)->for($otherUser)->completed()->create();

    $this->getJson('/api/v1/pdf-compressions', ['X-API-Key' => $plainKey])
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

test('index filters by status', function () {
    $plainKey = 'test-filter-key';
    $user = User::factory()->create();
    $apiKey = ApiKey::factory()->withKnownKey($plainKey)->for($user)->create();

    PdfCompression::factory()->for($apiKey)->for($user)->completed()->count(2)->create();
    PdfCompression::factory()->for($apiKey)->for($user)->failed()->create();

    $this->getJson('/api/v1/pdf-compressions?status=completed', ['X-API-Key' => $plainKey])
        ->assertSuccessful()
        ->assertJsonCount(2, 'data');
});

test('index validates per_page parameter', function () {
    $plainKey = 'test-perpage-key';
    ApiKey::factory()->withKnownKey($plainKey)->create();

    $this->getJson('/api/v1/pdf-compressions?per_page=999', ['X-API-Key' => $plainKey])
        ->assertUnprocessable();
});

// ─── Show ───

test('show returns a compression belonging to the api key', function () {
    $plainKey = 'test-show-key';
    $user = User::factory()->create();
    $apiKey = ApiKey::factory()->withKnownKey($plainKey)->for($user)->create();

    $compression = PdfCompression::factory()->for($apiKey)->for($user)->completed()->create();

    $this->getJson("/api/v1/pdf-compressions/{$compression->public_id}", ['X-API-Key' => $plainKey])
        ->assertSuccessful()
        ->assertJsonPath('data.public_id', $compression->public_id);
});

test('show returns 404 for compression belonging to another api key', function () {
    $plainKey = 'test-show-other-key';
    ApiKey::factory()->withKnownKey($plainKey)->create();

    $otherUser = User::factory()->create();
    $otherApiKey = ApiKey::factory()->for($otherUser)->create();
    $compression = PdfCompression::factory()->for($otherApiKey)->for($otherUser)->completed()->create();

    $this->getJson("/api/v1/pdf-compressions/{$compression->public_id}", ['X-API-Key' => $plainKey])
        ->assertNotFound();
});

// ─── Store ───

test('store queues a pdf for compression', function () {
    Queue::fake();
    Storage::fake('local');

    $plainKey = 'test-store-key';
    $user = User::factory()->create();
    ApiKey::factory()->withKnownKey($plainKey)->for($user)->create();

    $file = UploadedFile::fake()->create('document.pdf', 1024, 'application/pdf');

    $this->postJson('/api/v1/pdf-compressions', [
        'pdf' => $file,
        'preset' => 'screen',
    ], ['X-API-Key' => $plainKey])
        ->assertStatus(202)
        ->assertJsonPath('data.status', 'queued')
        ->assertJsonPath('message', 'PDF queued for compression.');

    Queue::assertPushed(CompressPdfCompressionJob::class);

    expect(PdfCompression::query()->count())->toBe(1);

    $compression = PdfCompression::query()->first();
    expect($compression->status)->toBe(PdfCompressionStatus::Queued)
        ->and($compression->queued_at)->not->toBeNull()
        ->and($compression->ghostscript_preset)->toBe(GhostscriptPreset::Screen);
});

test('store rejects non-pdf files', function () {
    Storage::fake('local');

    $plainKey = 'test-store-reject-key';
    ApiKey::factory()->withKnownKey($plainKey)->create();

    $file = UploadedFile::fake()->create('document.txt', 1024, 'text/plain');

    $this->postJson('/api/v1/pdf-compressions', [
        'pdf' => $file,
        'preset' => 'screen',
    ], ['X-API-Key' => $plainKey])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['pdf']);
});

test('store rejects invalid preset', function () {
    Storage::fake('local');

    $plainKey = 'test-store-preset-key';
    ApiKey::factory()->withKnownKey($plainKey)->create();

    $file = UploadedFile::fake()->create('document.pdf', 1024, 'application/pdf');

    $this->postJson('/api/v1/pdf-compressions', [
        'pdf' => $file,
        'preset' => 'ultra-hd',
    ], ['X-API-Key' => $plainKey])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['preset']);
});

test('store rejects oversized files', function () {
    Storage::fake('local');

    $plainKey = 'test-store-size-key';
    ApiKey::factory()->withKnownKey($plainKey)->create();

    $maxKb = config('pdf-compression.max_upload_size_kb', 51200);
    $file = UploadedFile::fake()->create('big.pdf', $maxKb + 1, 'application/pdf');

    $this->postJson('/api/v1/pdf-compressions', [
        'pdf' => $file,
        'preset' => 'screen',
    ], ['X-API-Key' => $plainKey])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['pdf']);
});

// ─── Cleanup Command ───

test('cleanup command deletes old completed and failed compressions', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $apiKey = ApiKey::factory()->for($user)->create();

    $old = PdfCompression::factory()
        ->for($apiKey)
        ->for($user)
        ->completed()
        ->create(['created_at' => now()->subDays(10)]);

    Storage::disk('local')->put($old->original_path, 'fake-pdf-content');

    $recent = PdfCompression::factory()
        ->for($apiKey)
        ->for($user)
        ->completed()
        ->create(['created_at' => now()->subDay()]);

    $queued = PdfCompression::factory()
        ->for($apiKey)
        ->for($user)
        ->queued()
        ->create(['created_at' => now()->subDays(10)]);

    $this->artisan('pdf:cleanup --days=7')
        ->assertSuccessful();

    expect(PdfCompression::query()->find($old->id))->toBeNull()
        ->and(PdfCompression::query()->find($recent->id))->not->toBeNull()
        ->and(PdfCompression::query()->find($queued->id))->not->toBeNull();
});

test('cleanup command outputs message when nothing to clean', function () {
    $this->artisan('pdf:cleanup')
        ->expectsOutput('No old PDF compressions to clean up.')
        ->assertSuccessful();
});

// ─── API Documentation ───

test('docs ui returns html page', function () {
    $this->get('/api/v1/docs')
        ->assertSuccessful()
        ->assertHeader('content-type', 'text/html; charset=UTF-8')
        ->assertSee('swagger-ui');
});

test('docs spec returns valid openapi json', function () {
    $response = $this->get('/api/v1/docs/openapi.json')
        ->assertSuccessful()
        ->assertHeader('content-type', 'application/json');

    $json = $response->json();

    expect($json)->toHaveKey('openapi')
        ->and($json['openapi'])->toBe('3.1.0')
        ->and($json)->toHaveKey('paths')
        ->and($json['paths'])->toHaveKey('/pdf-compressions');
});
