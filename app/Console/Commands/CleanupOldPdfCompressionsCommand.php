<?php

namespace App\Console\Commands;

use App\Enums\PdfCompressionStatus;
use App\Models\PdfCompression;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

#[Signature('pdf:cleanup {--days= : Number of days to retain records (defaults to config value)}')]
#[Description('Delete old completed and failed PDF compression records and their files')]
class CleanupOldPdfCompressionsCommand extends Command
{
    public function handle(): int
    {
        $days = (int) ($this->option('days') ?: config('pdf-compression.retention_days', 7));

        $cutoff = now()->subDays($days);

        $query = PdfCompression::query()
            ->whereIn('status', [PdfCompressionStatus::Completed, PdfCompressionStatus::Failed])
            ->where('created_at', '<', $cutoff);

        $total = $query->count();

        if ($total === 0) {
            $this->info('No old PDF compressions to clean up.');

            return self::SUCCESS;
        }

        $this->info("Cleaning up {$total} PDF compression(s) older than {$days} days...");

        $deleted = 0;
        $errors = 0;

        $query->chunkById(100, function ($compressions) use (&$deleted, &$errors): void {
            foreach ($compressions as $compression) {
                try {
                    $this->deleteFiles($compression);
                    $compression->delete();
                    $deleted++;
                } catch (\Throwable $e) {
                    $errors++;
                    Log::error('Failed to clean up PDF compression', [
                        'id' => $compression->id,
                        'public_id' => $compression->public_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });

        $this->info("Deleted {$deleted} record(s). Errors: {$errors}.");

        Log::info('PDF compression cleanup completed', [
            'deleted' => $deleted,
            'errors' => $errors,
        ]);

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function deleteFiles(PdfCompression $compression): void
    {
        if ($compression->original_disk && $compression->original_path) {
            Storage::disk($compression->original_disk)->delete($compression->original_path);
        }

        if ($compression->compressed_disk && $compression->compressed_path) {
            Storage::disk($compression->compressed_disk)->delete($compression->compressed_path);
        }
    }
}
