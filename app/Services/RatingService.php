<?php

namespace App\Services;

use App\Models\Etablissement;

class RatingService
{
    public function recalculate(Etablissement $etablissement): void
    {
        $avis = $etablissement->approvedAvis;

        if ($avis->isEmpty()) {
            $etablissement->update(['moyenne' => 0, 'nb_avis' => 0]);
            return;
        }

        $total = $avis->sum(fn ($a) => $a->moyenne);
        $moyenne = round($total / $avis->count(), 1);

        $etablissement->update([
            'moyenne' => $moyenne,
            'nb_avis' => $avis->count(),
        ]);
    }
}
