<?php

namespace App\Http\Controllers;

use App\Services\GooglePlacesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PagesJaunesController extends Controller
{
    public function __construct(private GooglePlacesService $googlePlaces) {}

    /**
     * GET /admin/pj/google?q=institut+de+beaute+Caen
     */
    public function googleSearch(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:200',
        ]);

        $data = $this->googlePlaces->searchText($request->input('q'));

        return response()->json($data);
    }

    /**
     * GET /admin/pj/google/detail?place_id=ChIJ...
     */
    public function googleDetail(Request $request): JsonResponse
    {
        $request->validate([
            'place_id' => 'required|string',
        ]);

        $data = $this->googlePlaces->getDetails($request->input('place_id'));

        return response()->json($data ?: ['error' => 'Non trouvé'], $data ? 200 : 404);
    }
}
