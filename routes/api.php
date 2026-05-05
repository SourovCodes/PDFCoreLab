<?php

use App\Http\Controllers\Api\V1\PdfCompressionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->name('api.v1.')
    ->group(function (): void {
        Route::apiResource('pdf-compressions', PdfCompressionController::class)
            ->middleware('api.key')
            ->only(['index', 'show', 'store']);
    });
