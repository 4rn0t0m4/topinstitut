<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Practitioner extends Model
{
    protected $fillable = [
        'establishment_id', 'name', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function establishment(): BelongsTo
    {
        return $this->belongsTo(Establishment::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(PractitionerSchedule::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function timeOffs(): HasMany
    {
        return $this->hasMany(TimeOff::class);
    }

    /**
     * Remplace les horaires de travail du praticien à partir des plages
     * matin/après-midi par jour. Ignore les plages incomplètes ou inversées.
     *
     * @param  array<int|string, array{am_start?:?string, am_end?:?string, pm_start?:?string, pm_end?:?string}>  $days
     */
    public function syncSchedules(array $days): void
    {
        $this->schedules()->delete();

        foreach ($days as $day => $ranges) {
            $day = (int) $day;
            if ($day < 1 || $day > 7) {
                continue;
            }

            foreach ([['am_start', 'am_end'], ['pm_start', 'pm_end']] as [$startKey, $endKey]) {
                $start = $ranges[$startKey] ?? null;
                $end = $ranges[$endKey] ?? null;
                if ($start && $end && $start < $end) {
                    $this->schedules()->create([
                        'day_of_week' => $day,
                        'start_time' => $start,
                        'end_time' => $end,
                    ]);
                }
            }
        }
    }
}
