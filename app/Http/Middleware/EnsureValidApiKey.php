<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureValidApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $plainTextApiKey = $request->header('X-API-Key') ?: $request->bearerToken();

        if (! is_string($plainTextApiKey) || $plainTextApiKey === '') {
            return new JsonResponse([
                'message' => 'A valid API key must be provided via the X-API-Key header or Bearer token.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $apiKey = ApiKey::query()
            ->active()
            ->where('key_hash', hash('sha256', $plainTextApiKey))
            ->first();

        if ($apiKey === null) {
            return new JsonResponse([
                'message' => 'The supplied API key is invalid.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $apiKey->forceFill([
            'last_used_at' => now(),
        ])->saveQuietly();

        $request->attributes->set('apiKey', $apiKey);

        return $next($request);
    }
}
