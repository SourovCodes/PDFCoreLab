<?php

namespace Database\Factories;

use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApiKey>
 */
class ApiKeyFactory extends Factory
{
    protected $model = ApiKey::class;

    public function definition(): array
    {
        $plainKey = fake()->uuid();

        return [
            'user_id' => User::factory(),
            'name' => fake()->words(2, true),
            'key_hash' => hash('sha256', $plainKey),
            'is_active' => true,
            'last_used_at' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withKnownKey(string $plainKey): static
    {
        return $this->state(fn (array $attributes) => [
            'key_hash' => hash('sha256', $plainKey),
        ]);
    }
}
