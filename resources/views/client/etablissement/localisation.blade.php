<x-layouts.app :noindex="true" :title="'Localisation - ' . $etablissement->name">
    @push('head')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9/dist/leaflet.css">
    @endpush

    <div class="max-w-3xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-2">Localisation - {{ $etablissement->name }}</h1>
        <p class="text-gray-600 mb-6">Placez le marqueur sur la position exacte de votre établissement.</p>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">{{ session('success') }}</div>
        @endif

        @php
            $hasPosition = $etablissement->latitude && $etablissement->longitude;
            $initLat = $etablissement->latitude ?? $etablissement->cityRelation?->latitude ?? 46.6;
            $initLng = $etablissement->longitude ?? $etablissement->cityRelation?->longitude ?? 2.4;
            $initZoom = $hasPosition ? 17 : ($etablissement->cityRelation ? 13 : 6);
            $fullAddress = trim(($etablissement->address ?? '').' '.($etablissement->postal_code ?? '').' '.($etablissement->city ?? ''));
        @endphp

        <form method="POST" action="{{ route('client.etablissement.localisation.update', $etablissement) }}" class="bg-white rounded-lg shadow-sm border p-6"
              x-data="localisationMap({
                  initLat: {{ $initLat }},
                  initLng: {{ $initLng }},
                  initZoom: {{ $initZoom }},
                  hasPosition: {{ $hasPosition ? 'true' : 'false' }},
                  address: @js($fullAddress),
              })" x-init="init()">
            @csrf @method('PUT')

            <div class="mb-4">
                <button type="button" @click="geocode()" :disabled="loading || !address" class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700 disabled:opacity-50 text-sm">
                    <span x-show="!loading">📍 Géolocaliser depuis l'adresse</span>
                    <span x-show="loading">Recherche…</span>
                </button>
                <span x-show="!address" class="text-xs text-gray-500 ml-2">Renseignez d'abord l'adresse dans l'onglet « Coordonnées ».</span>
                <p x-show="geocodeError" x-text="geocodeError" class="text-sm text-red-600 mt-2"></p>
            </div>

            <div id="locmap" class="w-full rounded-lg border" style="height: 420px;"></div>
            <p class="text-xs text-gray-500 mt-2">Faites glisser le marqueur pour ajuster la position exacte.</p>

            <input type="hidden" name="latitude" x-model="lat">
            <input type="hidden" name="longitude" x-model="lng">

            <div class="grid grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-xs font-medium mb-1 text-gray-500">Latitude</label>
                    <input type="text" x-model="lat" readonly class="w-full border bg-gray-50 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1 text-gray-500">Longitude</label>
                    <input type="text" x-model="lng" readonly class="w-full border bg-gray-50 rounded-lg px-3 py-2 text-sm">
                </div>
            </div>

            <button type="submit" class="mt-6 bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700">Enregistrer la position</button>
        </form>
    </div>

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9/dist/leaflet.js" defer></script>
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('localisationMap', (cfg) => ({
                    lat: cfg.hasPosition ? cfg.initLat.toFixed(7) : '',
                    lng: cfg.hasPosition ? cfg.initLng.toFixed(7) : '',
                    loading: false,
                    geocodeError: '',
                    address: cfg.address,
                    map: null,
                    marker: null,

                    init() {
                        const setup = () => {
                            if (!window.L) { setTimeout(setup, 100); return; }
                            this.map = L.map('locmap').setView([cfg.initLat, cfg.initLng], cfg.initZoom);
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '© OpenStreetMap',
                                maxZoom: 19,
                            }).addTo(this.map);

                            if (cfg.hasPosition) this.addMarker(cfg.initLat, cfg.initLng);

                            this.map.on('click', (e) => this.setPosition(e.latlng.lat, e.latlng.lng));
                        };
                        setup();
                    },

                    addMarker(lat, lng) {
                        if (this.marker) { this.marker.setLatLng([lat, lng]); return; }
                        this.marker = L.marker([lat, lng], { draggable: true }).addTo(this.map);
                        this.marker.on('dragend', (e) => {
                            const p = e.target.getLatLng();
                            this.lat = p.lat.toFixed(7);
                            this.lng = p.lng.toFixed(7);
                        });
                    },

                    setPosition(lat, lng, zoom = null) {
                        this.lat = lat.toFixed(7);
                        this.lng = lng.toFixed(7);
                        this.addMarker(lat, lng);
                        if (zoom) this.map.setView([lat, lng], zoom);
                        else this.map.panTo([lat, lng]);
                    },

                    async geocode() {
                        if (!this.address) return;
                        this.loading = true;
                        this.geocodeError = '';
                        try {
                            const url = 'https://api-adresse.data.gouv.fr/search/?limit=1&q=' + encodeURIComponent(this.address);
                            const res = await fetch(url);
                            const data = await res.json();
                            if (!data.features || !data.features.length) {
                                this.geocodeError = 'Adresse introuvable. Ajustez le marqueur manuellement.';
                                return;
                            }
                            const [lng, lat] = data.features[0].geometry.coordinates;
                            this.setPosition(lat, lng, 17);
                        } catch (e) {
                            this.geocodeError = 'Erreur lors de la géolocalisation.';
                        } finally {
                            this.loading = false;
                        }
                    },
                }));
            });
        </script>
    @endpush
</x-layouts.app>
