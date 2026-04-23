<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $primaryKey = 'code';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'code', 'name', 'slug', 'region',
        'article', 'latitude', 'longitude', 'zoom',
    ];

    public function cities(): HasMany
    {
        return $this->hasMany(City::class, 'department_code', 'code');
    }
}
