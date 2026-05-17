<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreApiKeyRequest;
use App\Http\Requests\Dashboard\UpdateApiKeyWebhookRequest;
use App\Models\ApiKey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ApiKeyController extends Controller
{
    public function index(Request $request): View
    {
        $apiKeys = $request->user()
            ->apiKeys()
            ->latest()
            ->paginate(10);

        return view('dashboard.api-keys.index', [
            'apiKeys' => $apiKeys,
        ]);
    }

    public function store(StoreApiKeyRequest $request): RedirectResponse
    {
        $plainKey = Str::random(48);

        $request->user()->apiKeys()->create([
            'name' => $request->validated('name'),
            'key_hash' => hash('sha256', $plainKey),
        ]);

        return redirect()->route('dashboard.api-keys.index')
            ->with('newKey', $plainKey)
            ->with('success', 'API key created successfully. Copy it now — it won\'t be shown again.');
    }

    public function toggle(Request $request, ApiKey $apiKey): RedirectResponse
    {
        if ($apiKey->user_id !== $request->user()->id) {
            abort(403);
        }

        $apiKey->update(['is_active' => ! $apiKey->is_active]);

        $status = $apiKey->is_active ? 'activated' : 'deactivated';

        return redirect()->route('dashboard.api-keys.index')
            ->with('success', "API key {$status} successfully.");
    }

    public function updateWebhook(UpdateApiKeyWebhookRequest $request, ApiKey $apiKey): RedirectResponse
    {
        if ($apiKey->user_id !== $request->user()->id) {
            abort(403);
        }

        $url = $request->validated('webhook_url');
        $regenerate = (bool) $request->validated('regenerate_secret');

        if ($url === null || $url === '') {
            $apiKey->update([
                'webhook_url' => null,
                'webhook_secret' => null,
            ]);

            return redirect()->route('dashboard.api-keys.index')
                ->with('success', 'Webhook removed.');
        }

        $needsNewSecret = $regenerate || empty($apiKey->webhook_secret);
        $plainSecret = $needsNewSecret ? Str::random(48) : null;

        $apiKey->update(array_filter([
            'webhook_url' => $url,
            'webhook_secret' => $plainSecret,
        ], fn ($value) => $value !== null));

        $message = $needsNewSecret
            ? 'Webhook saved. Copy the new signing secret below — it won\'t be shown again.'
            : 'Webhook URL updated.';

        $redirect = redirect()->route('dashboard.api-keys.index')->with('success', $message);

        if ($plainSecret !== null) {
            $redirect = $redirect
                ->with('newWebhookSecret', $plainSecret)
                ->with('newWebhookSecretKeyId', $apiKey->id);
        }

        return $redirect;
    }
}
