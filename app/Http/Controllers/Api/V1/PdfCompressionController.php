<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\PdfCompressionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IndexPdfCompressionRequest;
use App\Http\Requests\Api\V1\StorePdfCompressionRequest;
use App\Http\Resources\Api\V1\PdfCompressionResource;
use App\Jobs\CompressPdfCompressionJob;
use App\Models\ApiKey;
use App\Models\PdfCompression;
use App\Services\PdfCompression\GhostscriptPdfCompressor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PdfCompressionController extends Controller
{
    public function index(IndexPdfCompressionRequest $request)
    {
        $validated = $request->validated();
        $apiKey = $this->apiKey($request);

        $pdfCompressions = $apiKey->pdfCompressions()
            ->when(isset($validated['status']), function ($query) use ($validated) {
                $query->where('status', $validated['status']);
            })
            ->latest()
            ->paginate($validated['per_page'] ?? 15);

        return PdfCompressionResource::collection($pdfCompressions);
    }

    public function show(Request $request, PdfCompression $pdfCompression)
    {
        $apiKey = $this->apiKey($request);
        abort_unless($pdfCompression->api_key_id === $apiKey->id, Response::HTTP_NOT_FOUND);

        return new PdfCompressionResource($pdfCompression);
    }

    public function store(StorePdfCompressionRequest $request)
    {
        $apiKey = $this->apiKey($request);

        $sourceDisk = config('pdf-compression.source_disk', 'local');
        $sourceDirectory = config('pdf-compression.source_directory', 'pdf-compressions/originals');

        $pdfCompression = PdfCompression::create([
            'api_key_id' => $apiKey->id,
            'user_id' => $apiKey->user_id,
            'original_filename' => $request->file('pdf')->getClientOriginalName(),
            'original_path' => $request->file('pdf')->store($sourceDirectory, $sourceDisk),
            'original_disk' => $sourceDisk,
            'original_mime_type' => $request->file('pdf')->getClientMimeType(),
            'original_size_bytes' => $request->file('pdf')->getSize(),
            'status' => PdfCompressionStatus::Queued,
            'ghostscript_preset' => $request->validated('preset'),
            'queued_at' => now(),
        ]);

        if ($this->shouldProcessSynchronously()) {
            return $this->processSync($pdfCompression);
        }

        return $this->processAsync($pdfCompression);
    }

    private function shouldProcessSynchronously(): bool
    {
        $threshold = config('pdf-compression.sync_processing_threshold', 10);
        $activeCount = Cache::increment('pdf-compression:sync-active');

        if ($activeCount <= $threshold) {
            return true;
        }

        Cache::decrement('pdf-compression:sync-active');

        return false;
    }

    private function processSync(PdfCompression $pdfCompression): Response
    {
        try {
            $pdfCompression->forceFill([
                'status' => PdfCompressionStatus::Processing,
                'processing_started_at' => now(),
            ])->save();

            $result = app(GhostscriptPdfCompressor::class)->compress($pdfCompression);

            $pdfCompression->forceFill([
                'status' => PdfCompressionStatus::Completed,
                'compressed_disk' => $result->disk,
                'compressed_path' => $result->path,
                'compressed_size_bytes' => $result->sizeInBytes,
                'processed_at' => now(),
            ])->save();

            return (new PdfCompressionResource($pdfCompression))
                ->additional([
                    'message' => 'PDF compressed successfully.',
                ])
                ->response()
                ->setStatusCode(Response::HTTP_OK);
        } catch (\Throwable $e) {
            Log::warning('Synchronous PDF compression failed, falling back to queue.', [
                'public_id' => $pdfCompression->public_id,
                'error' => $e->getMessage(),
            ]);

            $pdfCompression->forceFill([
                'status' => PdfCompressionStatus::Queued,
                'processing_started_at' => null,
                'failed_at' => null,
                'failure_message' => null,
            ])->save();

            return $this->processAsync($pdfCompression);
        } finally {
            Cache::decrement('pdf-compression:sync-active');
        }
    }

    private function processAsync(PdfCompression $pdfCompression): Response
    {
        CompressPdfCompressionJob::dispatch($pdfCompression);

        return (new PdfCompressionResource($pdfCompression))
            ->additional([
                'message' => 'PDF queued for compression.',
            ])
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    private function apiKey(Request $request): ApiKey
    {
        $apiKey = $request->attributes->get('apiKey');

        abort_unless($apiKey instanceof ApiKey, Response::HTTP_UNAUTHORIZED, 'The supplied API key is invalid.');

        return $apiKey;
    }
}
