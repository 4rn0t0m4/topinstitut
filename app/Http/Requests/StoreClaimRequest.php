<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClaimRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'manager_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'siret' => 'nullable|string|max:14',
            'message' => 'nullable|string|max:2000',
        ];
    }
}
