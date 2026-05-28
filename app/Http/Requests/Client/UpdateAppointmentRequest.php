<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage', $this->route('etablissement'));
    }

    public function rules(): array
    {
        return [
            'practitioner_id' => 'required|integer',
            'service_id' => 'nullable|integer',
            'service_name' => 'required_without:service_id|nullable|string|max:255',
            'duration_minutes' => 'required|integer|min:5|max:600',
            'date' => 'required|date_format:Y-m-d',
            'time' => 'required|date_format:H:i',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:30',
            'notes' => 'nullable|string|max:1000',
            'notify_customer' => 'nullable|boolean',
        ];
    }
}
