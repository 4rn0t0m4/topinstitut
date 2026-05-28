<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

class SlotsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_id' => 'required|integer',
            'practitioner_id' => 'nullable|integer',
            'date' => 'nullable|date_format:Y-m-d|after_or_equal:today',
        ];
    }
}
