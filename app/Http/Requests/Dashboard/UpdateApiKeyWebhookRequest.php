<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class UpdateApiKeyWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'webhook_url' => ['nullable', 'url:http,https', 'max:2048'],
            'regenerate_secret' => ['nullable', 'boolean'],
        ];
    }
}
