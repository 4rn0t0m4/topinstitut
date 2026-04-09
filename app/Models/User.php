<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'email', 'password', 'pseudo', 'nom', 'prenom', 'sexe',
        'adresse', 'cp', 'ville', 'dept', 'ville_id',
        'longitude', 'latitude', 'tel_fixe', 'tel_port',
        'anniversaire', 'photo', 'is_admin',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'anniversaire' => 'date',
            'avis_nb' => 'integer',
        ];
    }

    public function villeRelation(): BelongsTo
    {
        return $this->belongsTo(Ville::class, 'ville_id');
    }

    public function etablissements(): BelongsToMany
    {
        return $this->belongsToMany(Etablissement::class)->withTimestamps();
    }

    public function avis(): HasMany
    {
        return $this->hasMany(Avis::class);
    }

    public function tier(): ?UserTier
    {
        return UserTier::where('min_avis', '<=', $this->avis_nb)
            ->where('max_avis', '>=', $this->avis_nb)
            ->first();
    }
}
