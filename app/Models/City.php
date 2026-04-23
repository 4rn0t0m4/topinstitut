<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    protected $fillable = [
        'name', 'slug', 'postal_code', 'insee_code',
        'department_code', 'population', 'latitude', 'longitude',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_code', 'code');
    }

    public function establishments(): HasMany
    {
        return $this->hasMany(Establishment::class);
    }
}
