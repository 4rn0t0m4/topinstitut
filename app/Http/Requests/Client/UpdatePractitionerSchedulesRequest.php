<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePractitionerSchedulesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage', $this->route('etablissement'));
    }

    public function rules(): array
    {
        return [
            'days' => 'array',
            'days.*.am_start' => 'nullable|date_format:H:i',
            'days.*.am_end' => 'nullable|date_format:H:i',
            'days.*.pm_start' => 'nullable|date_format:H:i',
            'days.*.pm_end' => 'nullable|date_format:H:i',
        ];
    }
}
