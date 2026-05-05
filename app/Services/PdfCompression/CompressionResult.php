<?php

namespace App\Services\PdfCompression;

readonly class CompressionResult
{
    public function __construct(
        public string $disk,
        public string $path,
        public int $sizeInBytes,
    ) {}
}
