<?php

namespace Database\Factories;

use App\Enums\GhostscriptPreset;
use App\Enums\PdfCompressionStatus;
use App\Models\ApiKey;
use App\Models\PdfCompression;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PdfCompression>
 */
class PdfCompressionFactory extends Factory
{
    protected $model = PdfCompression::class;

    public function definition(): array
    {
        return [
            'api_key_id' => ApiKey::factory(),
            'user_id' => User::factory(),
            'original_filename' => fake()->word().'.pdf',
            'original_mime_type' => 'application/pdf',
            'original_size_bytes' => fake()->numberBetween(100_000, 10_000_000),
            'original_disk' => 'local',
            'original_path' => 'pdfs/original/'.fake()->uuid().'.pdf',
            'compressed_disk' => null,
            'compressed_path' => null,
            'compressed_size_bytes' => null,
            'ghostscript_preset' => fake()->randomElement(GhostscriptPreset::cases()),
            'status' => PdfCompressionStatus::Queued,
            'failure_message' => null,
            'queued_at' => now(),
            'processing_started_at' => null,
            'processed_at' => null,
            'failed_at' => null,
        ];
    }

    public function queued(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PdfCompressionStatus::Queued,
            'queued_at' => now(),
        ]);
    }

    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PdfCompressionStatus::Processing,
            'queued_at' => now()->subMinutes(5),
            'processing_started_at' => now(),
        ]);
    }

    public function completed(): static
    {
        $originalSize = fake()->numberBetween(500_000, 5_000_000);

        return $this->state(fn (array $attributes) => [
            'status' => PdfCompressionStatus::Completed,
            'queued_at' => now()->subMinutes(10),
            'processing_started_at' => now()->subMinutes(8),
            'processed_at' => now()->subMinutes(5),
            'compressed_disk' => 'local',
            'compressed_path' => 'pdfs/compressed/'.fake()->uuid().'.pdf',
            'compressed_size_bytes' => (int) ($originalSize * 0.6),
            'original_size_bytes' => $originalSize,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PdfCompressionStatus::Failed,
            'queued_at' => now()->subMinutes(15),
            'processing_started_at' => now()->subMinutes(12),
            'failed_at' => now()->subMinutes(10),
            'failure_message' => 'Ghostscript process timed out.',
        ]);
    }
}
