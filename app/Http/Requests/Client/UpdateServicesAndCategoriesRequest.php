<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServicesAndCategoriesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage', $this->route('etablissement'));
    }

    public function rules(): array
    {
        return [
            'categories' => 'array|max:50',
            'categories.*.cid' => 'required|string|max:20',
            'categories.*.id' => 'nullable|integer',
            'categories.*.name' => 'required|string|max:100',
            'categories.*.description' => 'nullable|string|max:255',
            'services' => 'array|max:200',
            'services.*.id' => 'nullable|integer',
            'services.*.name' => 'required|string|max:255',
            'services.*.category_cid' => 'nullable|string|max:20',
            'services.*.duration_minutes' => 'required|integer|min:5|max:600',
            'services.*.price' => 'nullable|string|max:50',
            'services.*.description' => 'nullable|string|max:500',
            'services.*.is_bookable' => 'nullable|boolean',
        ];
    }
}
