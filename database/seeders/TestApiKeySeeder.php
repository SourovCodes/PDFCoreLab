<?php

namespace Database\Seeders;

use App\Enums\GhostscriptPreset;
use App\Enums\PdfCompressionStatus;
use App\Models\ApiKey;
use App\Models\PdfCompression;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TestApiKeySeeder extends Seeder
{
    private const TEST_USER_EMAIL = 'test-api-key@example.com';

    private const TEST_USER_NAME = 'API Key Test User';

    private const TEST_API_KEY_NAME = 'web-route-test-key';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::query()->firstOrCreate(
            ['email' => self::TEST_USER_EMAIL],
            [
                'name' => self::TEST_USER_NAME,
                'password' => 'password',
            ],
        );

        $plainTextApiKey = sprintf('test-api-key-user-%s', $user->getKey());

        $apiKey = ApiKey::query()->firstOrCreate(
            ['key_hash' => hash('sha256', $plainTextApiKey)],
            [
                'user_id' => $user->getKey(),
                'name' => self::TEST_API_KEY_NAME,
                'is_active' => true,
            ],
        );

        collect($this->pdfCompressionPayloads($user->getKey(), $apiKey->getKey()))
            ->each(function (array $attributes): void {
                PdfCompression::query()->firstOrCreate(
                    ['original_path' => $attributes['original_path']],
                    $attributes,
                );
            });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function pdfCompressionPayloads(int $userId, int $apiKeyId): array
    {
        return [
            [
                'public_id' => (string) Str::ulid(),
                'api_key_id' => $apiKeyId,
                'user_id' => $userId,
                'original_filename' => 'queued-sample.pdf',
                'original_mime_type' => 'application/pdf',
                'original_size_bytes' => 1250000,
                'original_disk' => 'local',
                'original_path' => 'seeded/testing/queued-sample.pdf',
                'compressed_disk' => null,
                'compressed_path' => null,
                'compressed_size_bytes' => null,
                'ghostscript_preset' => GhostscriptPreset::Screen,
                'status' => PdfCompressionStatus::Queued,
                'failure_message' => null,
                'queued_at' => now()->subMinutes(15),
                'processing_started_at' => null,
                'processed_at' => null,
                'failed_at' => null,
            ],
            [
                'public_id' => (string) Str::ulid(),
                'api_key_id' => $apiKeyId,
                'user_id' => $userId,
                'original_filename' => 'completed-sample.pdf',
                'original_mime_type' => 'application/pdf',
                'original_size_bytes' => 2200000,
                'original_disk' => 'local',
                'original_path' => 'seeded/testing/completed-sample.pdf',
                'compressed_disk' => 'local',
                'compressed_path' => 'seeded/testing/completed-sample-compressed.pdf',
                'compressed_size_bytes' => 980000,
                'ghostscript_preset' => GhostscriptPreset::Ebook,
                'status' => PdfCompressionStatus::Completed,
                'failure_message' => null,
                'queued_at' => now()->subMinutes(30),
                'processing_started_at' => now()->subMinutes(28),
                'processed_at' => now()->subMinutes(25),
                'failed_at' => null,
            ],
            [
                'public_id' => (string) Str::ulid(),
                'api_key_id' => $apiKeyId,
                'user_id' => $userId,
                'original_filename' => 'failed-sample.pdf',
                'original_mime_type' => 'application/pdf',
                'original_size_bytes' => 1850000,
                'original_disk' => 'local',
                'original_path' => 'seeded/testing/failed-sample.pdf',
                'compressed_disk' => null,
                'compressed_path' => null,
                'compressed_size_bytes' => null,
                'ghostscript_preset' => GhostscriptPreset::Printer,
                'status' => PdfCompressionStatus::Failed,
                'failure_message' => 'Dummy seeded failure for testing.',
                'queued_at' => now()->subMinutes(45),
                'processing_started_at' => now()->subMinutes(42),
                'processed_at' => null,
                'failed_at' => now()->subMinutes(40),
            ],
        ];
    }
}
