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
    <noscript>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9/dist/leaflet.css" />
        <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5/dist/MarkerCluster.css" />
        <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5/dist/MarkerCluster.Default.css" />
    </noscript>
    <style>
        .ti-marker { background: transparent; border: 0; filter: drop-shadow(0 2px 4px rgba(0,0,0,.25)); transition: transform .15s; }
        .ti-marker:hover { transform: scale(1.15); z-index: 1000 !important; }
        .ti-cluster { background: transparent; border: 0; }
    </style>
@endpush

@if($points->isNotEmpty())
    <div id="search-map" class="w-full h-[600px] rounded-lg border"></div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
            window.lazyLoadMap({
                target: '#search-map',
                styles: [
                    'https://unpkg.com/leaflet@1.9/dist/leaflet.css',
                    'https://unpkg.com/leaflet.markercluster@1.5/dist/MarkerCluster.css',
                    'https://unpkg.com/leaflet.markercluster@1.5/dist/MarkerCluster.Default.css',
                ],
                scripts: [
                    'https://unpkg.com/leaflet@1.9/dist/leaflet.js',
                    'https://unpkg.com/leaflet.markercluster@1.5/dist/leaflet.markercluster.js',
                    'https://unpkg.com/leaflet.gridlayer.googlemutant@latest/dist/Leaflet.GoogleMutant.js',
                    'https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_PLACES_API_KEY') }}',
                ],
                onReady: function () {
                    const points = @json($points);
                    const container = document.getElementById('search-map');
                    if (! container) return;

                    const map = L.map(container);
                    L.gridLayer.googleMutant({ type: 'roadmap', maxZoom: 20 }).addTo(map);

                    const pinIcon = L.divIcon({
                        className: 'ti-marker',
                        html: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 44" width="32" height="44">
                            <path d="M16 0C7.2 0 0 7.2 0 16c0 11 16 28 16 28s16-17 16-28C32 7.2 24.8 0 16 0z" fill="#ec4899" stroke="#fff" stroke-width="2"/>
                            <circle cx="16" cy="16" r="6" fill="#fff"/>
                        </svg>`,
                        iconSize: [32, 44],
                        iconAnchor: [16, 44],
                        popupAnchor: [0, -40],
                    });

                    const cluster = L.markerClusterGroup({
                        iconCreateFunction: function (cluster) {
                            const count = cluster.getChildCount();
                            return L.divIcon({
                                className: 'ti-cluster',
                                html: `<div style="background:#ec4899;color:#fff;border:3px solid #fff;border-radius:9999px;width:40px;height:40px;display:flex;align-items:center;justify-content:center;font-weight:700;box-shadow:0 2px 6px rgba(0,0,0,.3)">${count}</div>`,
                                iconSize: [40, 40],
                            });
                        },
                    });
                    const bounds = [];
                    points.forEach(p => {
                        const marker = L.marker([p.lat, p.lng], { icon: pinIcon });
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

                    const fitView = () => {
                        if (bounds.length === 1) {
                            map.setView(bounds[0], 14);
                        } else {
                            map.fitBounds(bounds, { padding: [30, 30] });
                        }
                    };

                    const obs = new ResizeObserver(() => {
                        if (container.offsetWidth > 0 && container.offsetHeight > 0) {
                            map.invalidateSize();
                            fitView();
                        }
                    });
                    obs.observe(container);

                    fitView();
                },
            });
            });
        </script>
    @endpush
@else
    <div class="w-full h-[600px] rounded-lg border flex items-center justify-center bg-gray-50 text-gray-500">
        Aucun établissement géolocalisé pour cette recherche.
    </div>
@endif
