<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PdfCompression;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PdfCompressionDownloadController extends Controller
{
    public function original(PdfCompression $pdfCompression): StreamedResponse
    {
        abort_unless(
            Storage::disk($pdfCompression->original_disk)->exists($pdfCompression->original_path),
            Response::HTTP_NOT_FOUND,
            'The original file could not be found.',
        );

        return Storage::disk($pdfCompression->original_disk)
            ->download($pdfCompression->original_path, $pdfCompression->original_filename);
    }

    public function compressed(PdfCompression $pdfCompression): StreamedResponse
    {
        abort_unless(
            $pdfCompression->compressed_disk !== null && $pdfCompression->compressed_path !== null,
            Response::HTTP_NOT_FOUND,
            'No compressed file is available.',
        );

        abort_unless(
            Storage::disk($pdfCompression->compressed_disk)->exists($pdfCompression->compressed_path),
            Response::HTTP_NOT_FOUND,
            'The compressed file could not be found.',
        );

        $filename = pathinfo($pdfCompression->original_filename, PATHINFO_FILENAME).'-compressed.pdf';

        return Storage::disk($pdfCompression->compressed_disk)
            ->download($pdfCompression->compressed_path, $filename);
    }
}
