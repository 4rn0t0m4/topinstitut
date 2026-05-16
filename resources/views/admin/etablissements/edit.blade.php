@extends('admin.layouts.app')

@push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9/dist/leaflet.css">
@endpush

@section('content')
    <h1 class="text-2xl font-bold mb-6">{{ $etablissement->exists ? 'Modifier' : 'Créer' }} un établissement</h1>

    <form method="POST" action="{{ $etablissement->exists ? route('admin.etablissements.update', $etablissement) : route('admin.etablissements.store') }}" class="bg-white rounded-lg shadow-sm border p-6 max-w-2xl">
        @csrf
        @if($etablissement->exists) @method('PUT') @endif

        <div class="grid gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Nom</label>
                <input type="text" name="name" value="{{ old('name', $etablissement->name) }}" required class="w-full border rounded-lg px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Type</label>
                <select name="type" required class="w-full border rounded-lg px-3 py-2">
                    @foreach(\App\Models\Establishment::TYPE_LABELS as $key => $label)
                        <option value="{{ $key }}" {{ old('type', $etablissement->type) == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Adresse</label>
                    <input type="text" name="address" value="{{ old('address', $etablissement->address) }}" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Téléphone</label>
                    <input type="text" name="phone" value="{{ old('phone', $etablissement->phone) }}" class="w-full border rounded-lg px-3 py-2">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4" x-data="villeAutocomplete()" x-init="query = @js(old('city', $etablissement->city ?? '')); selectedId = @js((string) old('city_id', $etablissement->city_id ?? '')); selectedPostalCode = @js((string) old('postal_code', $etablissement->postal_code ?? ''))">
                <div>
                    <label class="block text-sm font-medium mb-1">Code postal</label>
                    <input type="text" name="postal_code" x-model="selectedPostalCode" maxlength="5" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div class="relative" @click.outside="open = false">
                    <label class="block text-sm font-medium mb-1">Ville</label>
                    <input type="text" name="city" x-model="query" @input="search()" @focus="open = results.length > 0" autocomplete="off" class="w-full border rounded-lg px-3 py-2">
                    <input type="hidden" name="city_id" :value="selectedId">
                    <ul x-show="open" x-cloak class="absolute z-50 left-0 right-0 mt-1 bg-white border rounded-lg shadow-lg max-h-60 overflow-y-auto">
                        <template x-for="item in results" :key="item.id">
                            <li @click="select(item)" class="px-4 py-2 cursor-pointer hover:bg-pink-50 text-gray-900 text-sm" x-text="item.label"></li>
                        </template>
                    </ul>
                    @if($etablissement->exists && $etablissement->city_id)
                        <p class="text-xs text-green-600 mt-1">✓ Ville liée à la base ({{ $etablissement->cityRelation?->department?->name }})</p>
                    @else
                        <p class="text-xs text-amber-600 mt-1">Sélectionnez la ville dans la liste pour activer l'URL hiérarchique SEO.</p>
                    @endif
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $etablissement->email) }}" class="w-full border rounded-lg px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Description</label>
                <textarea name="description" rows="5" class="w-full border rounded-lg px-3 py-2">{{ old('description', $etablissement->description) }}</textarea>
            </div>

            @php
                $hasPosition = $etablissement->latitude && $etablissement->longitude;
                $initLat = $etablissement->latitude ?? $etablissement->cityRelation?->latitude ?? 46.6;
                $initLng = $etablissement->longitude ?? $etablissement->cityRelation?->longitude ?? 2.4;
                $initZoom = $hasPosition ? 17 : ($etablissement->cityRelation ? 13 : 6);
                $fullAddress = trim(($etablissement->address ?? '').' '.($etablissement->postal_code ?? '').' '.($etablissement->city ?? ''));
            @endphp

            <div class="border-t pt-4 mt-2"
                 x-data="localisationMap({
                     initLat: {{ $initLat }},
                     initLng: {{ $initLng }},
                     initZoom: {{ $initZoom }},
                     hasPosition: {{ $hasPosition ? 'true' : 'false' }},
                     address: @js($fullAddress),
                 })" x-init="init()">
                <h2 class="text-sm font-semibold uppercase text-gray-500 mb-3">Localisation</h2>

                <div class="mb-3">
                    <button type="button" @click="geocode()" :disabled="loading || !address" class="bg-pink-600 text-white px-3 py-1.5 rounded-lg hover:bg-pink-700 disabled:opacity-50 text-sm">
                        <span x-show="!loading">📍 Géolocaliser depuis l'adresse</span>
                        <span x-show="loading">Recherche…</span>
                    </button>
                    <p x-show="geocodeError" x-text="geocodeError" class="text-sm text-red-600 mt-2"></p>
                </div>

                <div id="locmap" class="w-full rounded-lg border" style="height: 360px;"></div>
                <p class="text-xs text-gray-500 mt-2">Cliquez sur la carte ou faites glisser le marqueur pour ajuster.</p>

                <input type="hidden" name="latitude" x-model="lat">
                <input type="hidden" name="longitude" x-model="lng">

                <div class="grid grid-cols-2 gap-4 mt-3">
                    <div>
                        <label class="block text-xs font-medium mb-1 text-gray-500">Latitude</label>
                        <input type="text" x-model="lat" readonly class="w-full border bg-gray-50 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1 text-gray-500">Longitude</label>
                        <input type="text" x-model="lng" readonly class="w-full border bg-gray-50 rounded-lg px-3 py-2 text-sm">
                    </div>
                </div>
            </div>

            @if($etablissement->exists)
                <div>
                    <label class="flex items-center gap-2">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $etablissement->is_active) ? 'checked' : '' }} class="rounded">
                        <span class="text-sm font-medium">Validé</span>
                    </label>
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium mb-2">Caractéristiques</label>
                @php $currentFeatures = old('features', $etablissement->features ?? []); @endphp
                <div class="grid grid-cols-2 gap-2">
                    @foreach(\App\Models\Establishment::FEATURES as $key => $label)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="features[]" value="{{ $key }}" @checked(in_array($key, $currentFeatures)) class="rounded text-pink-600 focus:ring-pink-500">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>

            @if($etablissement->exists)
                <div class="border-t pt-4 mt-2">
                    <h2 class="text-sm font-semibold uppercase text-gray-500 mb-3">Monétisation</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Abonnement</label>
                            <select name="subscription_tier" class="w-full border rounded-lg px-3 py-2">
                                <option value="free" @selected(old('subscription_tier', $etablissement->subscription_tier) === 'free')>Gratuit</option>
                                <option value="premium" @selected(old('subscription_tier', $etablissement->subscription_tier) === 'premium')>Premium</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Fin d'abonnement (laisser vide = illimité)</label>
                            <input type="datetime-local" name="subscription_ends_at" value="{{ old('subscription_ends_at', $etablissement->subscription_ends_at?->format('Y-m-d\TH:i')) }}" class="w-full border rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Sponsorisé jusqu'au</label>
                            <input type="datetime-local" name="featured_until" value="{{ old('featured_until', $etablissement->featured_until?->format('Y-m-d\TH:i')) }}" class="w-full border rounded-lg px-3 py-2">
                            <p class="text-xs text-gray-500 mt-1">Affichage prioritaire dans la recherche et la ville.</p>
                        </div>
                        <div class="flex items-end">
                            <label class="flex items-center gap-2 text-sm font-medium">
                                <input type="hidden" name="is_verified_owner" value="0">
                                <input type="checkbox" name="is_verified_owner" value="1" @checked(old('is_verified_owner', $etablissement->is_verified_owner)) class="rounded text-pink-600">
                                Propriétaire vérifié (badge bleu)
                            </label>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="flex gap-3 mt-6">
            <button type="submit" class="bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700">Enregistrer</button>
            <a href="{{ route('admin.etablissements.index') }}" class="text-gray-500 px-6 py-2">Annuler</a>
        </div>
    </form>
@endsection

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
