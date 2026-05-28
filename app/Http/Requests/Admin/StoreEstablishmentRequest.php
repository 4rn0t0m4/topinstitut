<?php

namespace App\Http\Requests\Admin;

class StoreEstablishmentRequest extends BaseEstablishmentRequest
{
    public function rules(): array
    {
        return $this->commonRules();
    }
}
