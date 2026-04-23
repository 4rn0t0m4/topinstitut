<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Review extends Model
{
    protected $fillable = [
        'establishment_id', 'user_id', 'title', 'content', 'ip',
        'author_name', 'author_email', 'verification_token', 'email_verified_at',
        'is_approved', 'is_rejected', 'reply', 'replied_at',
        'rating_welcome', 'rating_quality', 'rating_variety',
        'rating_price', 'rating_ambiance', 'rating_cleanliness',
    ];

    protected function casts(): array
    {
        return [
            'is_approved' => 'boolean',
            'is_rejected' => 'boolean',
            'replied_at' => 'datetime',
            'email_verified_at' => 'datetime',
        ];
    }

    public function getReviewerNameAttribute(): string
    {
        return $this->user?->username ?? $this->author_name ?? 'Anonyme';
    }

    public function getAverageRatingAttribute(): float
    {
        return round(($this->rating_welcome + $this->rating_quality + $this->rating_variety
            + $this->rating_price + $this->rating_ambiance + $this->rating_cleanliness) / 6, 1);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('is_approved', true)->where('is_rejected', false);
    }

    public function establishment(): BelongsTo
    {
        return $this->belongsTo(Establishment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ReviewVote::class);
    }
}
