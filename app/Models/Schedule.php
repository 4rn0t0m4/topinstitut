<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    use HasFactory;

    const DAYS = [
        1 => 'Lundi',
        2 => 'Mardi',
        3 => 'Mercredi',
        4 => 'Jeudi',
        5 => 'Vendredi',
        6 => 'Samedi',
        7 => 'Dimanche',
    ];

    protected $fillable = [
        'establishment_id', 'day_of_week', 'open_am', 'close_am',
        'open_pm', 'close_pm', 'is_closed',
    ];

    protected function casts(): array
    {
        return ['is_closed' => 'boolean'];
    }

    public function getDayLabelAttribute(): string
    {
        return self::DAYS[$this->day_of_week] ?? '';
    }

    public function getStatusAttribute(): string
    {
        if ($this->is_closed) {
            return 'closed';
        }

        $now = now();
        $minutes = $now->hour * 60 + $now->minute;

        $slots = [];
        if ($this->open_am && $this->close_am) {
            $slots[] = ['open' => $this->timeToMinutes($this->open_am), 'close' => $this->timeToMinutes($this->close_am)];
        }
        if ($this->open_pm && $this->close_pm) {
            $slots[] = ['open' => $this->timeToMinutes($this->open_pm), 'close' => $this->timeToMinutes($this->close_pm)];
        }

        if (empty($slots)) {
            return 'closed';
        }

        foreach ($slots as $slot) {
            if ($minutes >= $slot['open'] && $minutes < $slot['close']) {
                return ($slot['close'] - $minutes <= 30) ? 'closing_soon' : 'open';
            }
        }

        foreach ($slots as $slot) {
            if ($slot['open'] > $minutes && $slot['open'] - $minutes <= 30) {
                return 'opening_soon';
            }
        }

        return 'closed';
    }

    private function timeToMinutes(?string $time): int
    {
        if (! $time) {
            return 0;
        }
        $parts = explode(':', $time);

        return ((int) $parts[0]) * 60 + ((int) ($parts[1] ?? 0));
    }

    public function establishment(): BelongsTo
    {
        return $this->belongsTo(Establishment::class);
    }
}
