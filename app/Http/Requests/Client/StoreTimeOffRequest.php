<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class StoreTimeOffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage', $this->route('etablissement'));
    }

    public function rules(): array
    {
        return [
            'practitioner_id' => 'required|integer',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'reason' => 'nullable|string|max:255',
        ];
    }
}
