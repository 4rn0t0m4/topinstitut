<?php

namespace App\Http\Requests\Client;

use App\Models\Establishment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEstablishmentBasicInfoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $establishment = $this->route('etablissement');
        return $this->user()->can('manage', $establishment);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:5'],
            'city' => ['nullable', 'string', 'max:255'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'phone' => ['nullable', 'string', 'max:20'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nom',
            'address' => 'adresse',
            'postal_code' => 'code postal',
            'city' => 'ville',
            'city_id' => 'ville',
            'phone' => 'téléphone',
            'mobile' => 'portable',
            'email' => 'email',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom de l\'établissement est obligatoire.',
        ];
    }
}
