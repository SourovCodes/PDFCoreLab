<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\PdfCompressionStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexPdfCompressionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'status' => ['sometimes', 'string', Rule::enum(PdfCompressionStatus::class)],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('status')) {
            $this->merge([
                'status' => strtolower(trim((string) $this->input('status'))),
            ]);
        }
    }
}
