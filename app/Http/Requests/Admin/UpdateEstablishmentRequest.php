<?php

namespace App\Http\Requests\Admin;

use App\Models\Establishment;
use Illuminate\Validation\Rule;

class UpdateEstablishmentRequest extends BaseEstablishmentRequest
{
    public function rules(): array
    {
        return array_merge($this->commonRules(), [
            'is_active' => 'boolean',
            'features' => 'nullable|array',
            'features.*' => ['string', Rule::in(array_keys(Establishment::FEATURES))],
            'subscription_tier' => 'nullable|in:free,premium',
            'subscription_ends_at' => 'nullable|date',
            'featured_until' => 'nullable|date',
            'is_verified_owner' => 'boolean',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);
    }
}
