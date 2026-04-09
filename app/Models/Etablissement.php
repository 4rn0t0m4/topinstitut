<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Etablissement extends Model
{
    const TYPE_SLUGS = [
        0 => 'institut-de-beaute',
        1 => 'estheticienne-a-domicile',
        2 => 'spa',
        3 => 'thalasso',
    ];

    const TYPE_LABELS = [
        0 => 'Institut de beauté',
        1 => 'Esthéticienne à domicile',
        2 => 'Spa',
        3 => 'Thalasso',
    ];

    protected $fillable = [
        'type', 'titre', 'slug', 'email', 'adresse', 'cp', 'ville',
        'dept', 'ville_id', 'latitude', 'longitude', 'rayon',
        'description', 'horaires', 'tarifs', 'telephone', 'portable',
        'siret', 'photo', 'accroche', 'valide', 'moyenne', 'nb_avis',
    ];

    protected function casts(): array
    {
        return [
            'type' => 'integer',
            'valide' => 'boolean',
            'moyenne' => 'decimal:1',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    // --- Accessors ---

    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->type] ?? 'Institut de beauté';
    }

    public function getTypeSlugAttribute(): string
    {
        return self::TYPE_SLUGS[$this->type] ?? 'institut-de-beaute';
    }

    public function getUrlAttribute(): string
    {
        return '/' . $this->type_slug . '/' . $this->slug . '.html';
    }

    // --- Scopes ---

    public function scopeValide(Builder $query): Builder
    {
        return $query->where('valide', true);
    }

    public function scopeByType(Builder $query, int $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeNearby(Builder $query, float $lat, float $lng, float $radiusKm = 10): Builder
    {
        return $query->selectRaw(
            '*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance',
            [$lat, $lng, $lat]
        )
            ->having('distance', '<', $radiusKm)
            ->orderBy('distance');
    }

    // --- Relations ---

    public function villeRelation(): BelongsTo
    {
        return $this->belongsTo(Ville::class, 'ville_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Categorie::class, 'categorie_etablissement');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class)->orderBy('ordre');
    }

    public function avis(): HasMany
    {
        return $this->hasMany(Avis::class);
    }

    public function approvedAvis(): HasMany
    {
        return $this->hasMany(Avis::class)->where('valide', true)->where('refus', false);
    }

    public function actualites(): HasMany
    {
        return $this->hasMany(Actualite::class);
    }

    public function horairesRelation(): HasMany
    {
        return $this->hasMany(Horaire::class)->orderBy('jour');
    }

    public function administrateurs(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function slugHistory(): HasMany
    {
        return $this->hasMany(EtablissementSlug::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
