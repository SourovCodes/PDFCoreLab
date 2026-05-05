<?php

namespace App\Services\PdfCompression;

use App\Models\PdfCompression;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Process;

class GhostscriptPdfCompressor
{
    public function compress(PdfCompression $pdfCompression): CompressionResult
    {
        $sourceDisk = Storage::disk($pdfCompression->original_disk);
        $outputDiskName = config('pdf-compression.output_disk');
        $outputDisk = Storage::disk($outputDiskName);

        if (! $sourceDisk->exists($pdfCompression->original_path)) {
            throw new RuntimeException('The source PDF could not be found on the configured storage disk.');
        }

        $outputPath = trim(config('pdf-compression.output_directory'), '/').'/'.$pdfCompression->public_id.'.pdf';
        $outputDisk->makeDirectory(dirname($outputPath));

        if ($outputDisk->exists($outputPath)) {
            $outputDisk->delete($outputPath);
        }

        $process = new Process([
            config('pdf-compression.ghostscript_binary', 'gs'),
            '-sDEVICE=pdfwrite',
            '-dCompatibilityLevel=1.4',
            '-dNOPAUSE',
            '-dQUIET',
            '-dBATCH',
            '-dDetectDuplicateImages=true',
            '-dCompressFonts=true',
            '-dSubsetFonts=true',
            '-dPDFSETTINGS='.$pdfCompression->ghostscript_preset->cliValue(),
            '-sOutputFile='.$outputDisk->path($outputPath),
            $sourceDisk->path($pdfCompression->original_path),
        ]);

        $process->setTimeout(config('pdf-compression.process_timeout_seconds', 300));
        $process->mustRun();

        if (! $outputDisk->exists($outputPath)) {
            throw new RuntimeException('Ghostscript completed without producing a compressed PDF.');
        }

        $compressedSize = $outputDisk->size($outputPath);

        if (! is_int($compressedSize) || $compressedSize <= 0) {
            throw new RuntimeException('The compressed PDF is empty.');
        }

        return new CompressionResult(
            disk: $outputDiskName,
            path: $outputPath,
            sizeInBytes: $compressedSize,
        );
    }
}
