<?php

use App\Http\Controllers\Api\V1\PdfCompressionController;
use App\Http\Controllers\Api\V1\PdfCompressionDownloadController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->name('api.v1.')
    ->middleware(['api.key', 'throttle:api'])
    ->group(function (): void {
        Route::apiResource('pdf-compressions', PdfCompressionController::class)
            ->only(['index', 'show', 'store']);
    });

Route::prefix('v1')
    ->name('api.v1.')
    ->middleware('signed')
    ->group(function (): void {
        Route::get('pdf-compressions/{pdfCompression}/download/original', [PdfCompressionDownloadController::class, 'original'])
            ->name('pdf-compressions.download.original');

        Route::get('pdf-compressions/{pdfCompression}/download/compressed', [PdfCompressionDownloadController::class, 'compressed'])
            ->name('pdf-compressions.download.compressed');
    });
