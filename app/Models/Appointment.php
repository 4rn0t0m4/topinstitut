<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    const STATUSES = [
        'confirmed' => 'Confirmé',
        'cancelled' => 'Annulé',
        'completed' => 'Honoré',
        'no_show' => 'Absent',
    ];

    protected $fillable = [
        'establishment_id', 'practitioner_id', 'service_id', 'user_id',
        'service_name', 'duration_minutes',
        'customer_name', 'customer_email', 'customer_phone',
        'starts_at', 'ends_at', 'status', 'notes',
        'reminded_day_before_at', 'reminded_same_day_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'duration_minutes' => 'integer',
            'reminded_day_before_at' => 'datetime',
            'reminded_same_day_at' => 'datetime',
        ];
    }

    public function establishment(): BelongsTo
    {
        return $this->belongsTo(Establishment::class);
    }

    public function practitioner(): BelongsTo
    {
        return $this->belongsTo(Practitioner::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['confirmed', 'completed']);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
