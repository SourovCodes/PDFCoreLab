<?php

use App\Models\ApiKey;
use App\Models\User;

test('user can create an api key under the limit', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $this->actingAs($user)
        ->post(route('dashboard.api-keys.store'), ['name' => 'first key'])
        ->assertRedirect(route('dashboard.api-keys.index'))
        ->assertSessionHas('newKey');

    expect($user->apiKeys()->count())->toBe(1);
});

test('user cannot create more than the max api keys', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    ApiKey::factory()->for($user)->count(User::MAX_API_KEYS)->create();

    $this->actingAs($user)
        ->from(route('dashboard.api-keys.index'))
        ->post(route('dashboard.api-keys.store'), ['name' => 'one too many'])
        ->assertRedirect(route('dashboard.api-keys.index'))
        ->assertSessionHasErrors('name');

    expect($user->apiKeys()->count())->toBe(User::MAX_API_KEYS);
});

test('api keys index renders the create form when below the limit', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    ApiKey::factory()->for($user)->count(User::MAX_API_KEYS - 1)->create();

    $this->actingAs($user)
        ->get(route('dashboard.api-keys.index'))
        ->assertSuccessful()
        ->assertSee('Create Key');
});

test('api keys index hides the create form when at the limit', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    ApiKey::factory()->for($user)->count(User::MAX_API_KEYS)->create();

    $this->actingAs($user)
        ->get(route('dashboard.api-keys.index'))
        ->assertSuccessful()
        ->assertDontSee('Create Key')
        ->assertSee('reached the maximum');
});
