<?php

use App\Models\ApiKey;
use App\Models\User;

test('owner can set a webhook url and receives a signing secret once', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $apiKey = ApiKey::factory()->for($user)->create();

    $response = $this->actingAs($user)
        ->patch(route('dashboard.api-keys.webhook', $apiKey), [
            'webhook_url' => 'https://example.test/hook',
        ]);

    $response->assertRedirect(route('dashboard.api-keys.index'))
        ->assertSessionHas('newWebhookSecret');

    $apiKey->refresh();
    expect($apiKey->webhook_url)->toBe('https://example.test/hook')
        ->and($apiKey->webhook_secret)->not->toBeNull();
});

test('regenerate_secret rotates the signing secret', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $apiKey = ApiKey::factory()->for($user)->create([
        'webhook_url' => 'https://example.test/hook',
        'webhook_secret' => str_repeat('a', 48),
    ]);

    $oldSecret = $apiKey->webhook_secret;

    $this->actingAs($user)
        ->patch(route('dashboard.api-keys.webhook', $apiKey), [
            'webhook_url' => 'https://example.test/hook',
            'regenerate_secret' => '1',
        ])
        ->assertRedirect(route('dashboard.api-keys.index'))
        ->assertSessionHas('newWebhookSecret');

    $apiKey->refresh();
    expect($apiKey->webhook_secret)->not->toBe($oldSecret);
});

test('clearing the webhook url removes the secret too', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $apiKey = ApiKey::factory()->for($user)->create([
        'webhook_url' => 'https://example.test/hook',
        'webhook_secret' => str_repeat('b', 48),
    ]);

    $this->actingAs($user)
        ->patch(route('dashboard.api-keys.webhook', $apiKey), [
            'webhook_url' => '',
        ])
        ->assertRedirect(route('dashboard.api-keys.index'));

    $apiKey->refresh();
    expect($apiKey->webhook_url)->toBeNull()
        ->and($apiKey->webhook_secret)->toBeNull();
});

test('other users cannot update someone else api key webhook', function () {
    $owner = User::factory()->create(['email_verified_at' => now()]);
    $intruder = User::factory()->create(['email_verified_at' => now()]);
    $apiKey = ApiKey::factory()->for($owner)->create();

    $this->actingAs($intruder)
        ->patch(route('dashboard.api-keys.webhook', $apiKey), [
            'webhook_url' => 'https://example.test/hook',
        ])
        ->assertForbidden();

    expect($apiKey->fresh()->webhook_url)->toBeNull();
});

test('invalid webhook url is rejected', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $apiKey = ApiKey::factory()->for($user)->create();

    $this->actingAs($user)
        ->from(route('dashboard.api-keys.index'))
        ->patch(route('dashboard.api-keys.webhook', $apiKey), [
            'webhook_url' => 'not-a-url',
        ])
        ->assertSessionHasErrors('webhook_url');

    expect($apiKey->fresh()->webhook_url)->toBeNull();
});
