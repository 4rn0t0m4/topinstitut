<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLocalisationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $establishment = $this->route('etablissement');
        return $this->user()->can('manage', $establishment);
    }

    public function rules(): array
    {
        return [
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ];
    }

    public function attributes(): array
    {
        return [
            'latitude' => 'latitude',
            'longitude' => 'longitude',
        ];
    }
}
