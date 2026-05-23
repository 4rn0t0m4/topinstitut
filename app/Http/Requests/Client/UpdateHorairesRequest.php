<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHorairesRequest extends FormRequest
{
    public function authorize(): bool
    {
        $establishment = $this->route('etablissement');
        return $this->user()->can('manage', $establishment);
    }

    public function rules(): array
    {
        return [
            'horaires' => ['required', 'array'],
            'horaires.*.is_closed' => ['boolean'],
            'horaires.*.open_am' => ['nullable', 'date_format:H:i'],
            'horaires.*.close_am' => ['nullable', 'date_format:H:i'],
            'horaires.*.open_pm' => ['nullable', 'date_format:H:i'],
            'horaires.*.close_pm' => ['nullable', 'date_format:H:i'],
        ];
    }

    public function attributes(): array
    {
        return [
            'horaires' => 'horaires',
            'horaires.*.is_closed' => 'fermé',
            'horaires.*.open_am' => 'ouverture matin',
            'horaires.*.close_am' => 'fermeture matin',
            'horaires.*.open_pm' => 'ouverture après-midi',
            'horaires.*.close_pm' => 'fermeture après-midi',
        ];
    }
}
