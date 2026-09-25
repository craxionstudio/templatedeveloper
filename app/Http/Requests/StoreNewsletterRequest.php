<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\GuardsAgainstSpam;
use Illuminate\Foundation\Http\FormRequest;

class StoreNewsletterRequest extends FormRequest
{
    use GuardsAgainstSpam;

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
            ...$this->spamRules(),
            'email' => ['required', 'email:rfc', 'max:150'],
            'source_page' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ];
    }
}
