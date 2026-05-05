<?php

use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('test api key route creates and then reuses the same user and key', function () {
    $firstResponse = $this->get(route('test.api-key'));

    $firstResponse->assertSuccessful()
        ->assertJsonPath('user.email', 'test-api-key@example.com');

    $firstPayload = $firstResponse->json();

    expect($firstPayload['created'])->toBeTrue();
    expect(User::query()->count())->toBe(1);
    expect(ApiKey::query()->count())->toBe(1);

    $secondResponse = $this->get(route('test.api-key'));

    $secondResponse->assertSuccessful();

    $secondPayload = $secondResponse->json();

    expect($secondPayload['created'])->toBeFalse();
    expect($secondPayload['user']['id'])->toBe($firstPayload['user']['id']);
    expect($secondPayload['api_key'])->toBe($firstPayload['api_key']);
    expect($secondPayload['api_key_id'])->toBe($firstPayload['api_key_id']);
    expect(User::query()->count())->toBe(1);
    expect(ApiKey::query()->count())->toBe(1);

    $apiKey = ApiKey::query()->first();

    expect($apiKey)->not->toBeNull();
    expect($apiKey?->user_id)->toBe($firstPayload['user']['id']);
    expect($apiKey?->key_hash)->toBe(hash('sha256', $firstPayload['api_key']));
});
