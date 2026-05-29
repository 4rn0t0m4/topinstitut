<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Establishment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EstablishmentStatsService
{
    public const EVENT_TYPES = [
        'phone_click' => 'Clics téléphone',
        'directions_click' => 'Clics itinéraire',
        'website_click' => 'Clics site web',
        'gallery_open' => 'Ouvertures galerie',
        'booking_modal_open' => 'Ouvertures réservation',
        'booking_completed' => 'Réservations confirmées',
    ];

    /**
     * Incrémente le compteur de vues du jour pour une fiche. Atomique via
     * ON DUPLICATE KEY UPDATE — pas besoin de transaction ni de verrou.
     */
    public function recordView(Establishment $establishment): void
    {
        try {
            DB::statement(
                'INSERT INTO establishment_visits (establishment_id, date, views, created_at, updated_at)
                 VALUES (?, ?, 1, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE views = views + 1, updated_at = NOW()',
                [$establishment->id, today()->toDateString()]
            );
        } catch (\Throwable $e) {
            // Tracking ne doit jamais bloquer la requête.
        }
    }

    /**
     * Incrémente un évènement (phone_click, …). Idem upsert atomique.
     */
    public function recordEvent(Establishment $establishment, string $eventType): void
    {
        if (! array_key_exists($eventType, self::EVENT_TYPES)) {
            return;
        }
        try {
            DB::statement(
                'INSERT INTO establishment_events (establishment_id, date, event_type, count, created_at, updated_at)
                 VALUES (?, ?, ?, 1, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE count = count + 1, updated_at = NOW()',
                [$establishment->id, today()->toDateString(), $eventType]
            );
        } catch (\Throwable $e) {
            // idem.
        }
    }

    /**
     * Filtres communs : ne compte ni les bots ni les propriétaires connectés.
     */
    public function shouldTrack(Request $request, Establishment $establishment): bool
    {
        if ($this->isBot($request->userAgent())) {
            return false;
        }
        if (Auth::check() && $establishment->owners()->where('user_id', Auth::id())->exists()) {
            return false;
        }
        return true;
    }

    private function isBot(?string $ua): bool
    {
        if (! $ua) {
            return true;
        }
        return preg_match('/bot|crawler|spider|crawling|slurp|preview|fetch|monitor|google|bing|baidu|yandex|duckduckbot|facebook|whatsapp|linkedin|telegram|skype|twitter|headless|lighthouse|pagespeed/i', $ua) === 1;
    }

    /**
     * Récap sur N jours glissants se terminant aujourd'hui.
     *
     * @return array{
     *     days: int,
     *     start: \Illuminate\Support\Carbon,
     *     end: \Illuminate\Support\Carbon,
     *     views: int,
     *     events: array<string, int>,
     *     bookings: int,
     *     conversion_rate: float,
     *     views_series: array<string, int>,
     *     best_day: ?array{date:string, views:int},
     * }
     */
    public function summary(Establishment $establishment, int $days): array
    {
        $start = today()->subDays($days - 1);
        $end = today();

        $views = (int) DB::table('establishment_visits')
            ->where('establishment_id', $establishment->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->sum('views');

        $events = DB::table('establishment_events')
            ->where('establishment_id', $establishment->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->select('event_type', DB::raw('SUM(count) as total'))
            ->groupBy('event_type')
            ->pluck('total', 'event_type')
            ->map(fn ($v) => (int) $v)
            ->all();

        // Bookings : on compte directement depuis appointments (source de vérité,
        // pas affecté par d'éventuelles pertes de l'évènement booking_completed).
        $bookings = Appointment::where('establishment_id', $establishment->id)
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->count();

        $viewsSeries = $this->viewsTimeSeries($establishment, $days, $start, $end);

        $best = null;
        foreach ($viewsSeries as $date => $count) {
            if ($best === null || $count > $best['views']) {
                $best = ['date' => $date, 'views' => $count];
            }
        }

        return [
            'days' => $days,
            'start' => $start,
            'end' => $end,
            'views' => $views,
            'events' => $events,
            'bookings' => $bookings,
            'conversion_rate' => $views > 0 ? round($bookings * 100 / $views, 1) : 0.0,
            'views_series' => $viewsSeries,
            'best_day' => $best && $best['views'] > 0 ? $best : null,
        ];
    }

    /**
     * Série de vues par jour, jours manquants remplis à 0.
     *
     * @return array<string, int>  date (Y-m-d) => count
     */
    public function viewsTimeSeries(Establishment $establishment, int $days, ?Carbon $start = null, ?Carbon $end = null): array
    {
        $start ??= today()->subDays($days - 1);
        $end ??= today();

        $byDay = DB::table('establishment_visits')
            ->where('establishment_id', $establishment->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->pluck('views', 'date')
            ->map(fn ($v) => (int) $v)
            ->all();

        $series = [];
        for ($i = 0; $i < $days; $i++) {
            $key = $start->copy()->addDays($i)->toDateString();
            $series[$key] = $byDay[$key] ?? 0;
        }
        return $series;
    }
}
