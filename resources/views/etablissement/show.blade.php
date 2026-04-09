<x-layouts.app
    :title="$etablissement->titre . ' - ' . $etablissement->type_label . ' à ' . $etablissement->ville . ' - TopInstitut'"
    :description="$etablissement->titre . ', ' . strtolower($etablissement->type_label) . ' à ' . $etablissement->ville . ($etablissement->nb_avis > 0 ? '. Note : ' . number_format($etablissement->moyenne, 1, ',', '') . '/5 (' . $etablissement->nb_avis . ' avis)' : '') . '. Adresse, horaires, avis et coordonnées.'"
>
    @if($etablissement->latitude && $etablissement->longitude)
        @push('head')
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9/dist/leaflet.css" />
        @endpush
    @endif

    @push('jsonld')
    <x-breadcrumb-jsonld :items="[
        ['name' => 'Accueil', 'url' => '/'],
        ['name' => $etablissement->type_label, 'url' => '/recherche_institut.html'],
        ['name' => $etablissement->titre],
    ]" />
    @endpush

    {{-- Schema.org LocalBusiness --}}
    @push('jsonld')
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BeautySalon',
        'name' => $etablissement->titre,
        'description' => $etablissement->accroche ?: Str::limit(strip_tags($etablissement->description), 200),
        'url' => url($etablissement->url),
        'telephone' => $etablissement->telephone,
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => $etablissement->adresse,
            'postalCode' => $etablissement->cp,
            'addressLocality' => $etablissement->ville,
            'addressCountry' => 'FR',
        ],
    ] + ($etablissement->latitude ? [
        'geo' => [
            '@type' => 'GeoCoordinates',
            'latitude' => $etablissement->latitude,
            'longitude' => $etablissement->longitude,
        ],
    ] : [])
    + ($etablissement->nb_avis > 0 ? [
        'aggregateRating' => [
            '@type' => 'AggregateRating',
            'ratingValue' => number_format($etablissement->moyenne, 1, '.', ''),
            'bestRating' => '5',
            'worstRating' => '1',
            'ratingCount' => $etablissement->nb_avis,
        ],
    ] : [])
    + ($etablissement->photo ? [
        'image' => asset('storage/etablissements/' . $etablissement->id . '/' . $etablissement->photos->first()?->filename),
    ] : []), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
    @endpush

    <div class="max-w-7xl mx-auto px-4 py-8">
        {{-- Breadcrumb --}}
        <nav class="text-sm text-gray-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-pink-600">Accueil</a>
            <span class="mx-1">/</span>
            <span>{{ $etablissement->type_label }}</span>
            <span class="mx-1">/</span>
            <span class="text-gray-900">{{ $etablissement->titre }}</span>
        </nav>

        <div class="grid lg:grid-cols-3 gap-8">
            {{-- Main content --}}
            <div class="lg:col-span-2">
                <h1 class="text-3xl font-bold text-gray-900">{{ $etablissement->titre }}</h1>
                <p class="text-pink-600 mt-1">{{ $etablissement->type_label }}</p>

                @if($etablissement->nb_avis > 0)
                    <div class="flex items-center gap-2 mt-3">
                        <x-star-rating :rating="$etablissement->moyenne" />
                        <span class="font-semibold">{{ number_format($etablissement->moyenne, 1, ',', '') }}/5</span>
                        <span class="text-gray-500">({{ $etablissement->nb_avis }} avis)</span>
                    </div>
                @endif

                {{-- Photos --}}
                @if($etablissement->photos->isNotEmpty())
                    <div class="mt-6 grid grid-cols-2 md:grid-cols-3 gap-2">
                        @foreach($etablissement->photos as $photo)
                            <img src="{{ asset('storage/' . $photo->filename) }}" alt="{{ $etablissement->titre }}" class="rounded-lg object-cover h-48 w-full">
                        @endforeach
                    </div>
                @endif

                {{-- Description --}}
                @if($etablissement->description)
                    <div class="mt-8">
                        <h2 class="text-xl font-semibold mb-3">Présentation</h2>
                        <div class="prose text-gray-700">{!! nl2br(e($etablissement->description)) !!}</div>
                    </div>
                @endif

                {{-- Tarifs --}}
                @if($etablissement->tarifs)
                    <div class="mt-8">
                        <h2 class="text-xl font-semibold mb-3">Tarifs</h2>
                        <div class="prose text-gray-700">{!! nl2br(e($etablissement->tarifs)) !!}</div>
                    </div>
                @endif

                {{-- Horaires --}}
                @if($etablissement->horairesRelation->isNotEmpty())
                    <div class="mt-8">
                        <h2 class="text-xl font-semibold mb-3">Horaires</h2>
                        <table class="w-full text-sm">
                            @foreach($etablissement->horairesRelation as $h)
                                <tr class="border-b">
                                    <td class="py-2 font-medium">{{ $h->jour_label }}</td>
                                    <td class="py-2 text-gray-600">
                                        @if($h->ferme)
                                            <span class="text-red-500">Fermé</span>
                                        @else
                                            {{ $h->matin_ouverture ? substr($h->matin_ouverture, 0, 5) . ' - ' . substr($h->matin_fermeture, 0, 5) : '' }}
                                            @if($h->aprem_ouverture)
                                                / {{ substr($h->aprem_ouverture, 0, 5) }} - {{ substr($h->aprem_fermeture, 0, 5) }}
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                @endif

                {{-- Actualités --}}
                @if($etablissement->actualites->isNotEmpty())
                    <div class="mt-8">
                        <h2 class="text-xl font-semibold mb-3">Actualités</h2>
                        @foreach($etablissement->actualites as $actu)
                            <div class="bg-pink-50 border border-pink-100 rounded-lg p-4 mb-3">
                                <h3 class="font-semibold text-pink-700">{{ $actu->titre }}</h3>
                                @if($actu->contenu)
                                    <p class="text-sm text-gray-700 mt-1">{{ $actu->contenu }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Reviews --}}
                <div class="mt-8">
                    <h2 class="text-xl font-semibold mb-3">Avis ({{ $etablissement->approvedAvis->count() }})</h2>

                    @foreach($etablissement->approvedAvis as $avis)
                        <div class="bg-white border rounded-lg p-4 mb-4" id="avis-{{ $avis->id }}">
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="font-semibold">{{ $avis->auteur_name }}</span>
                                    <span class="text-sm text-gray-400 ml-2">{{ $avis->created_at->format('d/m/Y') }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <x-star-rating :rating="$avis->moyenne" size="w-4 h-4" />
                                    <span class="text-sm font-semibold">{{ number_format($avis->moyenne, 1, ',', '') }}</span>
                                </div>
                            </div>

                            <h3 class="font-medium mt-2">{{ $avis->titre }}</h3>
                            <p class="text-sm text-gray-700 mt-1">{{ $avis->contenu }}</p>

                            <div class="grid grid-cols-3 sm:grid-cols-6 gap-2 mt-3 text-xs text-gray-500">
                                <div>Accueil: {{ $avis->note_accueil }}/5</div>
                                <div>Qualité: {{ $avis->note_qualite }}/5</div>
                                <div>Choix: {{ $avis->note_choix }}/5</div>
                                <div>Prix: {{ $avis->note_prix }}/5</div>
                                <div>Cadre: {{ $avis->note_cadre }}/5</div>
                                <div>Propreté: {{ $avis->note_proprete }}/5</div>
                            </div>

                            @if($avis->reponse)
                                <div class="bg-gray-50 rounded-lg p-3 mt-3 text-sm">
                                    <span class="font-medium text-pink-600">Réponse de l'établissement :</span>
                                    <p class="text-gray-700 mt-1">{{ $avis->reponse }}</p>
                                </div>
                            @endif
                        </div>
                    @endforeach

                    {{-- Review form --}}
                    <div class="bg-white border rounded-lg p-6 mt-6" x-data="{ submitError: '' }">
                        <h3 class="text-lg font-semibold mb-4">Donner votre avis</h3>
                        <form action="{{ route('avis.store') }}" method="POST" @submit="
                            const notes = ['note_accueil','note_qualite','note_choix','note_prix','note_cadre','note_proprete'];
                            const missing = notes.filter(n => !$el.querySelector('[name='+n+']').value || $el.querySelector('[name='+n+']').value === '0');
                            if (missing.length) { submitError = 'Veuillez attribuer toutes les notes (étoiles).'; $event.preventDefault(); return; }
                            submitError = '';
                        ">
                            @csrf
                            <input type="hidden" name="etablissement_id" value="{{ $etablissement->id }}">

                            @guest
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Votre pseudo <span class="text-red-500">*</span></label>
                                        <input type="text" name="pseudo_auteur" required class="w-full border rounded-lg px-3 py-2" value="{{ old('pseudo_auteur') }}" placeholder="Ex: Marie75">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Votre email <span class="text-red-500">*</span></label>
                                        <input type="email" name="email_auteur" required class="w-full border rounded-lg px-3 py-2" value="{{ old('email_auteur') }}" placeholder="Pour confirmer votre avis">
                                        <p class="text-xs text-gray-400 mt-1">Un email de confirmation vous sera envoyé. Votre adresse ne sera pas publiée.</p>
                                    </div>
                                </div>
                            @endguest

                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-1">Titre <span class="text-red-500">*</span></label>
                                <input type="text" name="titre" required class="w-full border rounded-lg px-3 py-2" value="{{ old('titre') }}">
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-1">Votre avis <span class="text-red-500">*</span></label>
                                <textarea name="contenu" rows="4" required class="w-full border rounded-lg px-3 py-2">{{ old('contenu') }}</textarea>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-4">
                                @foreach(['accueil' => 'Accueil', 'qualite' => 'Qualité', 'choix' => 'Choix', 'prix' => 'Prix', 'cadre' => 'Cadre', 'proprete' => 'Propreté'] as $key => $label)
                                    <x-star-rating-input name="note_{{ $key }}" :label="$label" :value="old('note_' . $key, 0)" />
                                @endforeach
                            </div>

                            <p x-show="submitError" x-text="submitError" class="text-red-500 text-sm mb-3" x-cloak></p>
                            <button type="submit" class="bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700">Envoyer mon avis</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div>
                <div class="bg-white rounded-lg shadow-sm border p-6 sticky top-4">
                    <h2 class="font-semibold text-lg mb-4">Informations</h2>

                    @if($etablissement->adresse)
                        <div class="mb-3">
                            <p class="text-sm text-gray-500">Adresse</p>
                            <p class="text-sm">{{ $etablissement->adresse }}<br>{{ $etablissement->cp }} {{ $etablissement->ville }}</p>
                        </div>
                    @endif

                    @if($etablissement->latitude && $etablissement->longitude)
                        <div class="mb-4 rounded-lg overflow-hidden border" id="map" style="height: 200px;"></div>
                    @endif

                    @if($etablissement->telephone)
                        <x-phone-reveal :phone="$etablissement->telephone" :etablissement-id="$etablissement->id" label="Téléphone" />
                    @endif

                    @if($etablissement->portable)
                        <x-phone-reveal :phone="$etablissement->portable" :etablissement-id="$etablissement->id" label="Portable" :portable="true" />
                    @endif

                    <div class="flex gap-2 mt-4">
                        <a href="{{ route('contact.etablissement', $etablissement) }}" class="flex-1 text-center bg-pink-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-pink-700">Contacter</a>
                    </div>

                    {{-- Categories --}}
                    @if($etablissement->categories->isNotEmpty())
                        <div class="mt-6 border-t pt-4">
                            <p class="text-sm text-gray-500 mb-2">Prestations</p>
                            <div class="flex flex-wrap gap-1">
                                @foreach($etablissement->categories as $cat)
                                    <span class="text-xs bg-pink-50 text-pink-700 px-2 py-1 rounded">{{ $cat->nom }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Nearby --}}
                @if($nearby instanceof \Illuminate\Support\Collection && $nearby->isNotEmpty())
                    <div class="mt-6">
                        <h3 class="font-semibold text-gray-900 mb-3">À proximité</h3>
                        <div class="space-y-3">
                            @foreach($nearby as $proche)
                                <x-etablissement-card :etablissement="$proche" />
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @if($etablissement->latitude && $etablissement->longitude)
        <script src="https://unpkg.com/leaflet@1.9/dist/leaflet.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var map = L.map('map', { scrollWheelZoom: false }).setView([{{ $etablissement->latitude }}, {{ $etablissement->longitude }}], 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap'
                }).addTo(map);
                L.marker([{{ $etablissement->latitude }}, {{ $etablissement->longitude }}])
                    .addTo(map)
                    .bindPopup('<strong>{{ e($etablissement->titre) }}</strong>');
            });
        </script>
    @endif
</x-layouts.app>
