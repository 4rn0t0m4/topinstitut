<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePresentationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $establishment = $this->route('etablissement');
        return $this->user()->can('manage', $establishment);
    }

    public function rules(): array
    {
        return [
            'description' => ['nullable', 'string', 'max:10000'],
            'pricing' => ['nullable', 'string', 'max:10000'],
            'tagline' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'description' => 'description',
            'pricing' => 'tarifs',
            'tagline' => 'accroche',
        ];
    }
}
