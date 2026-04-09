<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Avis extends Model
{
    protected $fillable = [
        'etablissement_id', 'user_id', 'titre', 'contenu', 'ip',
        'pseudo_auteur', 'email_auteur', 'token_validation', 'email_verified_at',
        'valide', 'refus', 'reponse', 'reponse_date',
        'note_accueil', 'note_qualite', 'note_choix',
        'note_prix', 'note_cadre', 'note_proprete',
    ];

    protected function casts(): array
    {
        return [
            'valide' => 'boolean',
            'refus' => 'boolean',
            'reponse_date' => 'datetime',
            'email_verified_at' => 'datetime',
        ];
    }

    public function getAuteurNameAttribute(): string
    {
        return $this->user?->pseudo ?? $this->pseudo_auteur ?? 'Anonyme';
    }

    public function getMoyenneAttribute(): float
    {
        return round(($this->note_accueil + $this->note_qualite + $this->note_choix
            + $this->note_prix + $this->note_cadre + $this->note_proprete) / 6, 1);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('valide', true)->where('refus', false);
    }

    public function etablissement(): BelongsTo
    {
        return $this->belongsTo(Etablissement::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function utiles(): HasMany
    {
        return $this->hasMany(AvisUtile::class);
    }
}
