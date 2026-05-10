<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Establishment;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function index()
    {
        return view('quiz.index');
    }

    public function submit(Request $request)
    {
        $answers = $request->validate([
            'type' => 'nullable|in:0,1,2,3',
            'city' => 'nullable|string|max:255',
            'features' => 'nullable|array',
            'features.*' => ['string', \Illuminate\Validation\Rule::in(array_keys(Establishment::FEATURES))],
            'min_rating' => 'nullable|numeric|min:0|max:5',
            'radius' => 'nullable|integer|min:1|max:50',
        ]);

        $query = Establishment::active();

        // Type
        if (isset($answers['type']) && $answers['type'] !== null && $answers['type'] !== '') {
            $query->where('type', (int) $answers['type']);
        }

        // Localisation
        $lat = null;
        $lng = null;
        $radius = $answers['radius'] ?? 15;
        if (! empty($answers['city'])) {
            $city = City::where('name', 'like', $answers['city'])
                ->orderByDesc('population')
                ->first();
            if ($city && $city->latitude && $city->longitude) {
                $lat = (float) $city->latitude;
                $lng = (float) $city->longitude;
                $query->nearby($lat, $lng, $radius);
            }
        }

        // Note minimale (souple : on prend rating OU google_rating)
        if (! empty($answers['min_rating'])) {
            $min = (float) $answers['min_rating'];
            $query->where(function ($q) use ($min) {
                $q->where('rating', '>=', $min)
                    ->orWhere('google_rating', '>=', $min);
            });
        }

        // Score : on additionne 1 par feature qui matche
        $features = $answers['features'] ?? [];
        $candidates = $query->with(['photos', 'cityRelation.department'])
            ->orderByDesc('rating')
            ->orderByDesc('review_count')
            ->limit(50)
            ->get();

        $ranked = $candidates->map(function ($etab) use ($features) {
            $matches = 0;
            $etabFeatures = $etab->features ?? [];
            foreach ($features as $f) {
                if (in_array($f, $etabFeatures)) {
                    $matches++;
                }
            }
            $etab->_quiz_score = $matches;
            $etab->_quiz_match_pct = empty($features) ? 100 : (int) round(100 * $matches / count($features));

            return $etab;
        })
            ->sortByDesc(fn ($e) => $e->_quiz_score * 10 + ((float) ($e->rating ?? $e->google_rating ?? 0)))
            ->take(3)
            ->values();

        return view('quiz.results', [
            'matches' => $ranked,
            'answers' => $answers,
            'features' => $features,
        ]);
    }
}
