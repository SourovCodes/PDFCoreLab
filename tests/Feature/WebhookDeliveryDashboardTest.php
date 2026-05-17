<?php

use App\Enums\WebhookEvent;
use App\Models\ApiKey;
use App\Models\PdfCompression;
use App\Models\User;
use App\Models\WebhookDelivery;

function makeDashboardDelivery(User $user, array $overrides = []): WebhookDelivery
{
    $apiKey = ApiKey::factory()->for($user)->create();
    $compression = PdfCompression::factory()->for($apiKey)->for($user)->completed()->create();

    return WebhookDelivery::create(array_merge([
        'api_key_id' => $apiKey->id,
        'pdf_compression_id' => $compression->id,
        'event' => WebhookEvent::CompressionCompleted->value,
        'url' => 'https://example.test/hook',
        'payload' => ['event' => WebhookEvent::CompressionCompleted->value, 'data' => ['public_id' => $compression->public_id]],
        'attempt' => 1,
        'status' => WebhookDelivery::STATUS_DELIVERED,
        'response_status' => 200,
        'delivered_at' => now(),
    ], $overrides));
}

test('index lists deliveries for the user', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $delivery = makeDashboardDelivery($user);

    $this->actingAs($user)
        ->get(route('dashboard.webhook-deliveries.index'))
        ->assertSuccessful()
        ->assertSee($delivery->event);
});

test('index does not list deliveries from other users', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $other = User::factory()->create(['email_verified_at' => now()]);
    $theirDelivery = makeDashboardDelivery($other);

    $response = $this->actingAs($user)
        ->get(route('dashboard.webhook-deliveries.index'))
        ->assertSuccessful();

    $deliveries = $response->viewData('deliveries');
    expect($deliveries->total())->toBe(0)
        ->and($deliveries->contains('id', $theirDelivery->id))->toBeFalse();
});

test('index filters by status', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $delivered = makeDashboardDelivery($user, ['status' => WebhookDelivery::STATUS_DELIVERED]);
    $failed = makeDashboardDelivery($user, ['status' => WebhookDelivery::STATUS_FAILED, 'response_status' => 500]);

    $response = $this->actingAs($user)
        ->get(route('dashboard.webhook-deliveries.index', ['status' => 'failed']))
        ->assertSuccessful();

    $deliveries = $response->viewData('deliveries');
    expect($deliveries->pluck('id')->all())->toEqual([$failed->id])
        ->and($deliveries->contains('id', $delivered->id))->toBeFalse();
});

test('show renders the delivery payload', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $delivery = makeDashboardDelivery($user);

    $this->actingAs($user)
        ->get(route('dashboard.webhook-deliveries.show', $delivery))
        ->assertSuccessful()
        ->assertSee($delivery->event)
        ->assertSee($delivery->url)
        ->assertSee($delivery->payload['data']['public_id']);
});

test('show returns 404 for a delivery owned by another user', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $other = User::factory()->create(['email_verified_at' => now()]);
    $theirDelivery = makeDashboardDelivery($other);

    $this->actingAs($user)
        ->get(route('dashboard.webhook-deliveries.show', $theirDelivery))
        ->assertNotFound();
});
