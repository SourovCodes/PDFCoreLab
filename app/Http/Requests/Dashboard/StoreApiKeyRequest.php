<?php

namespace App\Http\Requests\Dashboard;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreApiKeyRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->user()->apiKeys()->count() >= User::MAX_API_KEYS) {
                $validator->errors()->add('name', sprintf(
                    'You have reached the maximum of %d API keys. Delete or deactivate an existing key before creating a new one.',
                    User::MAX_API_KEYS,
                ));
            }
        });
    }
}
