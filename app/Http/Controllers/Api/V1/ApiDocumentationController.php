<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

class ApiDocumentationController extends Controller
{
    public function ui(): View
    {
        return view('api.docs', [
            'specUrl' => route('api.v1.docs.spec'),
        ]);
    }

    public function spec(): JsonResponse
    {
        $path = resource_path('openapi/v1.json');

        abort_unless(file_exists($path), 404, 'OpenAPI spec not found.');

        return new JsonResponse(
            json_decode(file_get_contents($path), true),
            200,
            ['Content-Type' => 'application/json'],
        );
    }
}
