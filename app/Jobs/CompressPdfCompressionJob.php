<?php

namespace App\Jobs;

use App\Enums\PdfCompressionStatus;
use App\Enums\WebhookEvent;
use App\Models\PdfCompression;
use App\Services\PdfCompression\GhostscriptPdfCompressor;
use App\Support\Webhooks\WebhookDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class CompressPdfCompressionJob implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public bool $deleteWhenMissingModels = true;

    public int $tries = 3;

    public int $timeout = 300;

    public function __construct(public PdfCompression $pdfCompression) {}

    public function backoff(): array
    {
        return [5, 30, 120];
    }

    public function handle(GhostscriptPdfCompressor $compressor): void
    {
        $this->pdfCompression->refresh();

        if ($this->pdfCompression->status === PdfCompressionStatus::Completed) {
            return;
        }

        Log::info('Starting PDF compression', [
            'public_id' => $this->pdfCompression->public_id,
            'original_size' => $this->pdfCompression->original_size_bytes,
            'preset' => $this->pdfCompression->ghostscript_preset->value,
        ]);

        $this->pdfCompression->forceFill([
            'status' => PdfCompressionStatus::Processing,
            'processing_started_at' => now(),
            'failed_at' => null,
            'failure_message' => null,
        ])->save();

        $result = $compressor->compress($this->pdfCompression);

        $this->pdfCompression->forceFill([
            'status' => PdfCompressionStatus::Completed,
            'compressed_disk' => $result->disk,
            'compressed_path' => $result->path,
            'compressed_size_bytes' => $result->sizeInBytes,
            'processed_at' => now(),
            'failed_at' => null,
            'failure_message' => null,
        ])->save();

        Log::info('PDF compression completed', [
            'public_id' => $this->pdfCompression->public_id,
            'original_size' => $this->pdfCompression->original_size_bytes,
            'compressed_size' => $result->sizeInBytes,
            'reduction_percent' => round((1 - $result->sizeInBytes / $this->pdfCompression->original_size_bytes) * 100, 1),
        ]);

        app(WebhookDispatcher::class)->dispatch(
            $this->pdfCompression->apiKey,
            WebhookEvent::CompressionCompleted,
            $this->pdfCompression,
        );
    }

    public function failed(?Throwable $exception): void
    {
        if (! $this->pdfCompression->exists) {
            return;
        }

        $this->pdfCompression->refresh();

        Log::error('PDF compression failed', [
            'public_id' => $this->pdfCompression->public_id,
            'error' => $exception?->getMessage(),
        ]);

        $this->pdfCompression->forceFill([
            'status' => PdfCompressionStatus::Failed,
            'failed_at' => now(),
            'failure_message' => $exception === null ? 'PDF compression failed.' : Str::limit($exception->getMessage(), 2000),
        ])->save();

        app(WebhookDispatcher::class)->dispatch(
            $this->pdfCompression->apiKey,
            WebhookEvent::CompressionFailed,
            $this->pdfCompression,
        );
    }
}
