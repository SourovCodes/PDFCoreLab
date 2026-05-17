<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\WebhookDelivery;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class WebhookDeliveryController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:pending,delivered,failed'],
            'event' => ['nullable', 'in:compression.completed,compression.failed'],
        ]);

        $deliveries = $request->user()
            ->webhookDeliveries()
            ->with('apiKey:id,name', 'pdfCompression:id,public_id,original_filename')
            ->when($validated['status'] ?? null, fn ($q, $status) => $q->where('webhook_deliveries.status', $status))
            ->when($validated['event'] ?? null, fn ($q, $event) => $q->where('webhook_deliveries.event', $event))
            ->latest('webhook_deliveries.id')
            ->paginate(20)
            ->withQueryString();

        return view('dashboard.webhook-deliveries.index', [
            'deliveries' => $deliveries,
            'filters' => $validated,
        ]);
    }

    public function show(Request $request, WebhookDelivery $webhookDelivery): View
    {
        abort_unless($webhookDelivery->apiKey?->user_id === $request->user()->id, Response::HTTP_NOT_FOUND);

        $webhookDelivery->load('apiKey:id,name', 'pdfCompression:id,public_id,original_filename,status');

        return view('dashboard.webhook-deliveries.show', [
            'delivery' => $webhookDelivery,
        ]);
    }
}
