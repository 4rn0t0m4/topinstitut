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
        'email', 'password', 'username', 'last_name', 'first_name', 'gender',
        'address', 'postal_code', 'city', 'department_code', 'city_id',
        'longitude', 'latitude', 'phone', 'mobile',
        'date_of_birth', 'photo', 'is_admin',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'date_of_birth' => 'date',
            'review_count' => 'integer',
        ];
    }

    public function cityRelation(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function establishments(): BelongsToMany
    {
        return $this->belongsToMany(Establishment::class)->withTimestamps();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(Establishment::class, 'favorites')->withTimestamps();
    }

    public function tier(): ?UserTier
    {
        return UserTier::where('min_reviews', '<=', $this->review_count)
            ->where('max_reviews', '>=', $this->review_count)
            ->first();
    }
}
