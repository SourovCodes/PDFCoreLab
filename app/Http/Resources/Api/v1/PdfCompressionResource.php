<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

class PdfCompressionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $expiresAt = now()->addMinutes(10);

        return [
            'public_id' => $this->public_id,
            'status' => $this->status->value,
            'ghostscript_preset' => $this->ghostscript_preset->value,
            'original_filename' => $this->original_filename,
            'original_mime_type' => $this->original_mime_type,
            'original_size_bytes' => $this->original_size_bytes,
            'compressed_size_bytes' => $this->compressed_size_bytes,
            'has_compressed_file' => $this->compressed_path !== null,
            'failure_message' => $this->failure_message,
            'queued_at' => $this->queued_at,
            'processing_started_at' => $this->processing_started_at,
            'processed_at' => $this->processed_at,
            'failed_at' => $this->failed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'links' => [
                'original' => URL::signedRoute('api.v1.pdf-compressions.download.original', [
                    'pdfCompression' => $this->public_id,
                ], $expiresAt),
                'compressed' => ($this->compressed_disk !== null && $this->compressed_path !== null)
                    ? URL::signedRoute('api.v1.pdf-compressions.download.compressed', [
                        'pdfCompression' => $this->public_id,
                    ], $expiresAt)
                    : null,
            ],
        ];
    }
}
