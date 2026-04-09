<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Departement extends Model
{
    protected $primaryKey = 'numero';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'numero', 'departement', 'departement_url', 'region',
        'article', 'gmap_latitude', 'gmap_longitude', 'zoom',
    ];

    public function villes(): HasMany
    {
        return $this->hasMany(Ville::class, 'departement', 'numero');
    }
}
