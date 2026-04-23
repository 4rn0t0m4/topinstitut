<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GooglePlacesService
{
    private string $apiKey;

    private const FIELD_MASK = 'places.id,places.displayName,places.formattedAddress,places.shortFormattedAddress,places.nationalPhoneNumber,places.internationalPhoneNumber,places.websiteUri,places.googleMapsUri,places.regularOpeningHours,places.rating,places.userRatingCount,places.types,places.location,places.photos,places.editorialSummary,places.businessStatus';

    public function __construct()
    {
        $this->apiKey = config('services.google_places.api_key');
    }

    /**
     * Recherche textuelle d'établissements.
     */
    public function searchText(string $query, int $maxResults = 20): array
    {
        $response = Http::withHeaders([
            'X-Goog-Api-Key' => $this->apiKey,
            'X-Goog-FieldMask' => self::FIELD_MASK,
        ])->post('https://places.googleapis.com/v1/places:searchText', [
            'textQuery' => $query,
            'languageCode' => 'fr',
            'maxResultCount' => min($maxResults, 20),
        ]);

        if (! $response->ok()) {
            return ['error' => $response->json('error.message', 'Erreur API'), 'results' => []];
        }

        return [
            'results' => collect($response->json('places', []))->map(fn ($p) => $this->formatPlace($p))->all(),
        ];
    }

    /**
     * Recherche à proximité d'un point GPS.
     */
    public function searchNearby(float $lat, float $lng, float $radiusMeters = 5000, int $maxResults = 20): array
    {
        $response = Http::withHeaders([
            'X-Goog-Api-Key' => $this->apiKey,
            'X-Goog-FieldMask' => self::FIELD_MASK,
        ])->post('https://places.googleapis.com/v1/places:searchNearby', [
            'includedTypes' => ['beauty_salon', 'spa', 'hair_care'],
            'maxResultCount' => min($maxResults, 20),
            'locationRestriction' => [
                'circle' => [
                    'center' => ['latitude' => $lat, 'longitude' => $lng],
                    'radius' => $radiusMeters,
                ],
            ],
            'languageCode' => 'fr',
        ]);

        if (! $response->ok()) {
            return ['error' => $response->json('error.message', 'Erreur API'), 'results' => []];
        }

        return [
            'results' => collect($response->json('places', []))->map(fn ($p) => $this->formatPlace($p))->all(),
        ];
    }

    /**
     * Détail d'un lieu par son Place ID.
     */
    public function getDetails(string $placeId): ?array
    {
        $response = Http::withHeaders([
            'X-Goog-Api-Key' => $this->apiKey,
            'X-Goog-FieldMask' => str_replace('places.', '', self::FIELD_MASK),
        ])->get("https://places.googleapis.com/v1/places/{$placeId}", [
            'languageCode' => 'fr',
        ]);

        if (! $response->ok()) {
            return null;
        }

        return $this->formatPlace($response->json());
    }

    /**
     * Formate un résultat Place en array propre.
     */
    private function formatPlace(array $place): array
    {
        $hours = $place['regularOpeningHours'] ?? [];
        $location = $place['location'] ?? [];

        return [
            'place_id' => $place['id'] ?? '',
            'nom' => $place['displayName']['text'] ?? '',
            'adresse' => $place['formattedAddress'] ?? '',
            'adresse_courte' => $place['shortFormattedAddress'] ?? '',
            'telephone' => $place['nationalPhoneNumber'] ?? '',
            'telephone_international' => $place['internationalPhoneNumber'] ?? '',
            'site_web' => $place['websiteUri'] ?? '',
            'google_maps_url' => $place['googleMapsUri'] ?? '',
            'note' => $place['rating'] ?? null,
            'nb_avis' => $place['userRatingCount'] ?? 0,
            'description' => $place['editorialSummary']['text'] ?? '',
            'statut' => $place['businessStatus'] ?? '',
            'types' => $place['types'] ?? [],
            'latitude' => $location['latitude'] ?? null,
            'longitude' => $location['longitude'] ?? null,
            'horaires' => $hours['weekdayDescriptions'] ?? [],
            'ouvert_maintenant' => $hours['openNow'] ?? null,
            'nb_photos' => count($place['photos'] ?? []),
        ];
    }
}
