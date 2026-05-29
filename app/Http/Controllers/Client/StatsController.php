<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Services\EstablishmentStatsService;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function __construct(private EstablishmentStatsService $stats) {}

    public function show(Request $request, Establishment $etablissement)
    {
        $this->authorize('manage', $etablissement);

        $days = (int) $request->input('days', 30);
        if (! in_array($days, [7, 30, 90], true)) {
            $days = 30;
        }

        $summary = $this->stats->summary($etablissement, $days);

        return view('client.etablissement.stats', [
            'etablissement' => $etablissement,
            'summary' => $summary,
            'days' => $days,
            'eventLabels' => EstablishmentStatsService::EVENT_TYPES,
        ]);
    }
}
