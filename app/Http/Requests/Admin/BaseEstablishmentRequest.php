<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

abstract class BaseEstablishmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    /**
     * Règles partagées entre la création et la mise à jour.
     */
    protected function commonRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|integer|in:0,1,2,3',
            'address' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:5',
            'city' => 'nullable|string|max:255',
            'city_id' => 'nullable|integer|exists:cities,id',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'description' => 'nullable|string',
        ];
    }
}
