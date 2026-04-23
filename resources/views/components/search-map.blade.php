@props(['establishments'])

@php
    $items = $establishments instanceof \Illuminate\Contracts\Pagination\Paginator
        ? $establishments->getCollection()
        : collect($establishments);
    $points = $items
        ->filter(fn ($e) => $e->latitude && $e->longitude)
        ->map(fn ($e) => [
            'id' => $e->id,
            'lat' => (float) $e->latitude,
            'lng' => (float) $e->longitude,
            'name' => $e->name,
            'city' => $e->city,
            'url' => $e->url,
            'rating' => $e->rating > 0 ? (float) $e->rating : ($e->google_rating ? (float) $e->google_rating : null),
        ])
        ->values();
@endphp

@push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5/dist/MarkerCluster.Default.css" />
@endpush

@if($points->isNotEmpty())
    <div id="search-map" class="w-full h-[600px] rounded-lg border"></div>

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9/dist/leaflet.js"></script>
        <script src="https://unpkg.com/leaflet.markercluster@1.5/dist/leaflet.markercluster.js"></script>
        <script src="https://unpkg.com/leaflet.gridlayer.googlemutant@latest/dist/Leaflet.GoogleMutant.js"></script>
        <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_PLACES_API_KEY') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const points = @json($points);
                const map = L.map('search-map');
                L.gridLayer.googleMutant({ type: 'roadmap', maxZoom: 20 }).addTo(map);

                const cluster = L.markerClusterGroup();
                const bounds = [];
                points.forEach(p => {
                    const marker = L.marker([p.lat, p.lng]);
                    const rating = p.rating ? `<div class="text-xs text-yellow-600 mt-1">★ ${p.rating.toLocaleString('fr-FR')}</div>` : '';
                    marker.bindPopup(`
                        <strong><a href="${p.url}" class="text-pink-600 hover:underline">${p.name}</a></strong>
                        <div class="text-xs text-gray-500">${p.city || ''}</div>
                        ${rating}
                    `);
                    cluster.addLayer(marker);
                    bounds.push([p.lat, p.lng]);
                });
                map.addLayer(cluster);

                if (bounds.length === 1) {
                    map.setView(bounds[0], 14);
                } else {
                    map.fitBounds(bounds, { padding: [30, 30] });
                }
            });
        </script>
    @endpush
@else
    <div class="w-full h-[600px] rounded-lg border flex items-center justify-center bg-gray-50 text-gray-500">
        Aucun établissement géolocalisé pour cette recherche.
    </div>
@endif
