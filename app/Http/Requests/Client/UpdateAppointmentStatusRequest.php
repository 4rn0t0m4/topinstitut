<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage', $this->route('etablissement'));
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:confirmed,cancelled,completed,no_show',
        ];
    }
}
