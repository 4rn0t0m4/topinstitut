<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Horaire extends Model
{
    const JOURS = [
        1 => 'Lundi',
        2 => 'Mardi',
        3 => 'Mercredi',
        4 => 'Jeudi',
        5 => 'Vendredi',
        6 => 'Samedi',
        7 => 'Dimanche',
    ];

    protected $fillable = [
        'etablissement_id', 'jour', 'matin_ouverture', 'matin_fermeture',
        'aprem_ouverture', 'aprem_fermeture', 'ferme',
    ];

    protected function casts(): array
    {
        return ['ferme' => 'boolean'];
    }

    public function getJourLabelAttribute(): string
    {
        return self::JOURS[$this->jour] ?? '';
    }

    public function etablissement(): BelongsTo
    {
        return $this->belongsTo(Etablissement::class);
    }
}
