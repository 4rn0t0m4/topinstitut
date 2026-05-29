<?php

namespace App\Http\Controllers;

use App\Models\Establishment;
use App\Services\EstablishmentStatsService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EstablishmentEventController extends Controller
{
    public function __construct(private EstablishmentStatsService $stats) {}

    /**
     * Reçoit les évènements front (clic téléphone, ouverture galerie, etc.)
     * et incrémente l'agrégat journalier. Volontairement permissif : un évènement
     * raté ne doit pas casser l'UX. Skip bots/owners via shouldTrack().
     */
    public function track(Request $request, Establishment $etablissement): Response
    {
        $data = $request->validate([
            'event_type' => 'required|string|max:32',
        ]);

        if ($this->stats->shouldTrack($request, $etablissement)) {
            $this->stats->recordEvent($etablissement, $data['event_type']);
        }

        return response()->noContent();
    }
}
