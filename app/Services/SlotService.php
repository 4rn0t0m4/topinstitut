<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Establishment;
use App\Models\Practitioner;
use App\Models\Service;
use Illuminate\Support\Carbon;

class SlotService
{
    /** Pas de génération des créneaux, en minutes. */
    private const STEP = 15;

    /**
     * Liste des créneaux libres ("HH:MM") pour une date donnée.
     * Un créneau est dispo si au moins un praticien éligible est libre.
     *
     * @return array<int, string>
     */
    public function availableSlots(Establishment $establishment, Service $service, Carbon $date, ?int $practitionerId = null): array
    {
        $practitioners = $this->eligiblePractitioners($establishment, $practitionerId);
        if ($practitioners->isEmpty()) {
            return [];
        }

        $duration = max(5, (int) $service->duration_minutes);
        $slots = [];

        foreach ($practitioners as $practitioner) {
            foreach ($this->freeStartMinutes($practitioner, $date, $duration) as $minute) {
                $slots[$minute] = true;
            }
        }

        ksort($slots);

        return array_map(fn ($m) => sprintf('%02d:%02d', intdiv($m, 60), $m % 60), array_keys($slots));
    }

    /**
     * Trouve un praticien libre pour un créneau précis (pour la réservation).
     * Respecte le praticien demandé si fourni.
     */
    public function findFreePractitioner(Establishment $establishment, Service $service, Carbon $start, ?int $practitionerId = null): ?Practitioner
    {
        $practitioners = $this->eligiblePractitioners($establishment, $practitionerId);
        $duration = max(5, (int) $service->duration_minutes);
        $startMinute = $start->hour * 60 + $start->minute;
        $date = $start->copy()->startOfDay();

        foreach ($practitioners as $practitioner) {
            if (in_array($startMinute, $this->freeStartMinutes($practitioner, $date, $duration), true)) {
                return $practitioner;
            }
        }

        return null;
    }

    private function eligiblePractitioners(Establishment $establishment, ?int $practitionerId)
    {
        return $establishment->practitioners()
            ->where('is_active', true)
            ->when($practitionerId, fn ($q) => $q->where('id', $practitionerId))
            ->with('schedules')
            ->get();
    }

    /**
     * Minutes de début (depuis minuit) où le praticien peut commencer la prestation.
     *
     * @return array<int, int>
     */
    private function freeStartMinutes(Practitioner $practitioner, Carbon $date, int $duration): array
    {
        $dayOfWeek = (int) $date->isoWeekday(); // 1 = lundi … 7 = dimanche

        $workRanges = $practitioner->schedules
            ->where('day_of_week', $dayOfWeek)
            ->map(fn ($s) => [
                'start' => $this->toMinutes($s->start_time),
                'end' => $this->toMinutes($s->end_time),
            ])
            ->values();

        if ($workRanges->isEmpty()) {
            return [];
        }

        $busy = $this->busyIntervals($practitioner, $date);

        // Ne propose pas de créneau déjà passé pour aujourd'hui.
        $minStart = $date->isToday() ? now()->hour * 60 + now()->minute : 0;

        $free = [];
        foreach ($workRanges as $range) {
            for ($t = $range['start']; $t + $duration <= $range['end']; $t += self::STEP) {
                if ($t < $minStart) {
                    continue;
                }
                if ($this->overlapsAny($t, $t + $duration, $busy)) {
                    continue;
                }
                $free[] = $t;
            }
        }

        return $free;
    }

    /**
     * Intervalles occupés (RDV actifs) du praticien pour la date.
     *
     * @return array<int, array{start:int, end:int}>
     */
    private function busyIntervals(Practitioner $practitioner, Carbon $date): array
    {
        return Appointment::query()
            ->where('practitioner_id', $practitioner->id)
            ->active()
            ->whereBetween('starts_at', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])
            ->get(['starts_at', 'ends_at'])
            ->map(fn ($a) => [
                'start' => $a->starts_at->hour * 60 + $a->starts_at->minute,
                'end' => $a->ends_at->hour * 60 + $a->ends_at->minute,
            ])
            ->all();
    }

    private function overlapsAny(int $start, int $end, array $intervals): bool
    {
        foreach ($intervals as $i) {
            if ($i['start'] < $end && $start < $i['end']) {
                return true;
            }
        }

        return false;
    }

    private function toMinutes(string $time): int
    {
        [$h, $m] = array_pad(explode(':', $time), 2, 0);

        return ((int) $h) * 60 + (int) $m;
    }
}
