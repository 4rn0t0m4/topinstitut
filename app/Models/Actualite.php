<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Actualite extends Model
{
    protected $fillable = ['etablissement_id', 'titre', 'contenu', 'photo', 'date_limite'];

    protected function casts(): array
    {
        return ['date_limite' => 'date'];
    }

    public function etablissement(): BelongsTo
    {
        return $this->belongsTo(Etablissement::class);
    }
}
