<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstablishmentSlug extends Model
{
    protected $fillable = ['slug', 'establishment_id'];

    public function establishment(): BelongsTo
    {
        return $this->belongsTo(Establishment::class);
    }
}
