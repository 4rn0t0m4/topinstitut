<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    protected $fillable = [
        'establishment_id', 'service_category_id', 'name', 'description',
        'duration_minutes', 'price', 'is_bookable', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'is_bookable' => 'boolean',
        ];
    }

    public function establishment(): BelongsTo
    {
        return $this->belongsTo(Establishment::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function getDurationLabelAttribute(): string
    {
        $h = intdiv($this->duration_minutes, 60);
        $m = $this->duration_minutes % 60;

        if ($h && $m) {
            return "{$h}h{$m}";
        }
        if ($h) {
            return "{$h}h";
        }

        return "{$m} min";
    }
}
