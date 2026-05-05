<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\GhostscriptPreset;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePdfCompressionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pdf' => [
                'required',
                'file',
                'mimes:pdf',
                'mimetypes:application/pdf',
                'max:'.config('pdf-compression.max_upload_size_kb', 51200),
            ],
            'preset' => ['required', 'string', Rule::enum(GhostscriptPreset::class)],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('preset')) {
            $this->merge([
                'preset' => ltrim(strtolower(trim((string) $this->input('preset'))), '/'),
            ]);
        }
    }

    public function attributes(): array
    {
        return [
            'pdf' => 'PDF file',
            'preset' => 'Ghostscript preset',
        ];
    }
}
