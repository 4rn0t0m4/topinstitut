<x-layouts.app :title="'Instituts de beauté ' . $department->article . $department->name . ' - TopInstitut'" :description="'Tous les instituts de beauté, spas et esthéticiennes ' . $department->article . $department->name . '. Trouvez le meilleur institut près de chez vous.'">
    @push('head')
        <noscript><link rel="stylesheet" href="https://unpkg.com/leaflet@1.9/dist/leaflet.css"></noscript>
    @endpush
    @push('jsonld')
    <x-breadcrumb-jsonld :items="[
        ['name' => 'Accueil', 'url' => '/'],
        ['name' => $department->name],
    ]" />
    @endpush

    <div class="max-w-7xl mx-auto px-4 py-8">
        <nav class="text-sm text-gray-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-pink-600">Accueil</a>
            <span class="mx-1">/</span>
            <span class="text-gray-900">{{ $department->name }}</span>
        </nav>

        <h1 class="text-3xl font-bold text-gray-900 mb-6">Instituts de beauté {{ $department->article }}{{ $department->name }}</h1>

        @if($markers->isNotEmpty())
            <div class="mb-8 rounded-lg border shadow-sm" id="dept-map" style="height: 400px; width: 100%; z-index: 0;"></div>
        @endif

        @if($premiums->isNotEmpty())
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Instituts à la une {{ $department->article }}{{ $department->name }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-10">
                @foreach($premiums as $etab)
                    <x-etablissement-card :etablissement="$etab" />
                @endforeach
            </div>
        @endif

        @if($cities->isNotEmpty())
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Villes avec instituts</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                @foreach($cities as $city)
                    <a href="{{ route('ville.show', [$department->slug, $city->slug]) }}"
                       class="bg-white border rounded-lg px-4 py-3 hover:border-pink-300 hover:bg-pink-50 transition">
                        <span class="font-medium text-gray-900">{{ $city->name }}</span>
                        <span class="text-sm text-gray-400 ml-1">({{ $city->establishments_count }})</span>
                    </a>
                @endforeach
            </div>
        @else
            <p class="text-gray-500">Aucun institut trouvé dans ce département.</p>
        @endif
    </div>

    @if($markers->isNotEmpty())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
            window.lazyLoadMap({
                target: '#dept-map',
                styles: ['https://unpkg.com/leaflet@1.9/dist/leaflet.css'],
                scripts: [
                    'https://unpkg.com/leaflet@1.9/dist/leaflet.js',
                    'https://unpkg.com/leaflet.gridlayer.googlemutant@latest/dist/Leaflet.GoogleMutant.js',
                    'https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_PLACES_API_KEY') }}',
                ],
                onReady: function () {
                    var establishments = @json($markers);

                    var map = L.map('dept-map', { scrollWheelZoom: false }).setView([{{ $department->latitude ?? 46.6 }}, {{ $department->longitude ?? 2.3 }}], {{ $department->zoom ?? 9 }});
                    L.gridLayer.googleMutant({ type: 'roadmap', maxZoom: 20 }).addTo(map);
                    setTimeout(function () { map.invalidateSize(); }, 100);

                    // Contour du département via gregoiredavid/france-geojson
                    var deptSlug = '{{ $department->code }}-{{ strtolower($department->slug) }}';
                    var geoUrl = 'https://raw.githubusercontent.com/gregoiredavid/france-geojson/master/departements/' + deptSlug + '/departement-' + deptSlug + '.geojson';
                    fetch(geoUrl)
                        .then(function (r) { return r.json(); })
                        .then(function (geojson) {
                            var layer = L.geoJSON(geojson, {
                                style: { color: '#be185d', weight: 2, fillColor: '#ec4899', fillOpacity: 0.05 }
                            }).addTo(map);
                            map.fitBounds(layer.getBounds(), { padding: [20, 20] });
                        })
                        .catch(function () {});

                    var premiumIcon = L.divIcon({
                        className: 'ti-premium-marker',
                        html: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 36 50" width="36" height="50">'
                            + '<path d="M18 0C8.1 0 0 8.1 0 18c0 12.4 18 32 18 32s18-19.6 18-32C36 8.1 27.9 0 18 0z" fill="#ec4899" stroke="#fff" stroke-width="2.5"/>'
                            + '<circle cx="18" cy="18" r="9" fill="#fff"/>'
                            + '<path d="M18 12l1.8 4.4 4.7.4-3.6 3.1 1.1 4.6L18 22l-4 2.5 1.1-4.6-3.6-3.1 4.7-.4z" fill="#ec4899"/>'
                            + '</svg>',
                        iconSize: [36, 50],
                        iconAnchor: [18, 50],
                        popupAnchor: [0, -45],
                    });
                    var featuredIcon = L.divIcon({
                        className: 'ti-featured-marker',
                        html: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 36 50" width="36" height="50">'
                            + '<path d="M18 0C8.1 0 0 8.1 0 18c0 12.4 18 32 18 32s18-19.6 18-32C36 8.1 27.9 0 18 0z" fill="#f59e0b" stroke="#fff" stroke-width="2.5"/>'
                            + '<circle cx="18" cy="18" r="9" fill="#fff"/>'
                            + '<path d="M18 12l1.8 4.4 4.7.4-3.6 3.1 1.1 4.6L18 22l-4 2.5 1.1-4.6-3.6-3.1 4.7-.4z" fill="#f59e0b"/>'
                            + '</svg>',
                        iconSize: [36, 50],
                        iconAnchor: [18, 50],
                        popupAnchor: [0, -45],
                    });

                    // Premium/featured affichés en dernier pour passer au-dessus
                    establishments.sort(function (a, b) {
                        var sa = a.featured ? 2 : (a.premium ? 1 : 0);
                        var sb = b.featured ? 2 : (b.premium ? 1 : 0);
                        return sa - sb;
                    });

                    establishments.forEach(function (e) {
                        var opts = {};
                        if (e.featured) opts.icon = featuredIcon;
                        else if (e.premium) opts.icon = premiumIcon;
                        if (e.featured || e.premium) opts.zIndexOffset = 1000;
                        var marker = L.marker([e.lat, e.lng], opts).addTo(map);
                        var badge = e.featured ? '<span style="background:#f59e0b;color:#fff;font-size:10px;padding:1px 6px;border-radius:9999px;font-weight:600">Sponsorisé</span> ' :
                                    (e.premium ? '<span style="background:#ec4899;color:#fff;font-size:10px;padding:1px 6px;border-radius:9999px;font-weight:600">Premium</span> ' : '');
                        marker.bindPopup(
                            badge + '<strong><a href="' + e.url + '">' + e.title + '</a></strong><br>' +
                            '<span style="color:#666">' + e.type + '</span><br>' +
                            (e.city || '') + (e.postal_code ? ' (' + e.postal_code + ')' : '') +
                            (e.rating ? '<br>⭐ ' + e.rating + '/5' : '')
                        );
                    });
                },
            });
            });
        </script>
    @endif
</x-layouts.app>
