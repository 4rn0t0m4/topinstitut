<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\City;
use App\Models\Establishment;
use Illuminate\Http\Request;

class RechercheController extends Controller
{
    public function index(Request $request)
    {
        $query = Establishment::active();

        $name = $request->input('nom');
        $cityName = $request->input('ville');
        $category = $request->input('categorie');
        $type = $request->filled('type') ? (int) $request->input('type') : null;
        $openNow = $request->boolean('ouvert');
        $withPhotos = $request->boolean('avec_photos');
        $minRating = $request->filled('note_min') ? (float) $request->input('note_min') : null;
        $sort = $request->input('tri', 'rating');

        if ($name) {
            $query->where('name', 'like', '%'.$name.'%');
        }

        if ($cityName) {
            $city = City::where('name', 'like', $cityName)->first();
            if ($city) {
                if ($city->latitude && $city->longitude) {
                    $query->nearby($city->latitude, $city->longitude, 15);
                } else {
                    $query->where('city_id', $city->id);
                }
            }
        }

        if ($category) {
            $query->whereHas('categories', fn ($q) => $q->where('categories.id', $category));
        }

        if ($type !== null && isset(Establishment::TYPE_LABELS[$type])) {
            $query->byType($type);
        }

        if ($openNow) {
            $query->openNow();
        }

        if ($withPhotos) {
            $query->withPhotos();
        }

        if ($minRating) {
            $query->minRating($minRating);
        }

        match ($sort) {
            'avis' => $query->orderByDesc('review_count'),
            'recent' => $query->latest(),
            default => $query->orderByDesc('rating'),
        };

        $establishments = $query->with(['schedules', 'photos'])->paginate(20)->withQueryString();

        $categories = Category::orderBy('name')->get();

        return view('recherche.index', compact(
            'establishments', 'categories',
            'name', 'cityName', 'category', 'type', 'openNow', 'withPhotos', 'minRating', 'sort'
        ));
    }
}
