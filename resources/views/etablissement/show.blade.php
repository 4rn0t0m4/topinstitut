<x-layouts.app
    :title="$etablissement->titre . ' - ' . $etablissement->type_label . ' à ' . $etablissement->ville . ' - TopInstitut'"
    :description="$etablissement->titre . ', ' . strtolower($etablissement->type_label) . ' à ' . $etablissement->ville . ($etablissement->nb_avis > 0 ? '. Note : ' . number_format($etablissement->moyenne, 1, ',', '') . '/5 (' . $etablissement->nb_avis . ' avis)' : '') . '. Adresse, horaires, avis et coordonnées.'"
>
    @php
        $villeRel = $etablissement->villeRelation;
        $deptRel = $villeRel?->departementRelation;
    @endphp

    @if($etablissement->latitude && $etablissement->longitude)
        @push('head')
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9/dist/leaflet.css" />
        @endpush
    @endif

    @push('jsonld')
    <x-breadcrumb-jsonld :items="array_filter([
        ['name' => 'Accueil', 'url' => '/'],
        $deptRel ? ['name' => $deptRel->departement, 'url' => '/departement-' . $deptRel->departement_url . '.html'] : null,
        $villeRel ? ['name' => $villeRel->nom_ville, 'url' => '/les-instituts-de-beaute-a-' . $villeRel->url . '.html'] : null,
        ['name' => $etablissement->titre],
    ])" />
    @endpush

    {{-- Schema.org LocalBusiness --}}
    @php
        $schemaLocal = [
            '@context' => 'https://schema.org',
            '@type' => 'BeautySalon',
            'name' => $etablissement->titre,
            'description' => $etablissement->accroche ?: Str::limit(strip_tags($etablissement->description ?? ''), 200),
            'url' => url($etablissement->url),
            'telephone' => $etablissement->telephone,
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $etablissement->adresse,
                'postalCode' => $etablissement->cp,
                'addressLocality' => $etablissement->ville,
                'addressCountry' => 'FR',
            ],
        ];
        if ($etablissement->latitude) {
            $schemaLocal['geo'] = ['@type' => 'GeoCoordinates', 'latitude' => $etablissement->latitude, 'longitude' => $etablissement->longitude];
        }
        if ($etablissement->nb_avis > 0) {
            $schemaLocal['aggregateRating'] = ['@type' => 'AggregateRating', 'ratingValue' => number_format($etablissement->moyenne, 1, '.', ''), 'bestRating' => '5', 'worstRating' => '1', 'ratingCount' => $etablissement->nb_avis];
        }
        if ($etablissement->photos->isNotEmpty()) {
            $schemaLocal['image'] = asset('storage/etablissements/' . $etablissement->id . '/' . $etablissement->photos->first()->filename);
        }
    @endphp
    @push('jsonld')
    <script type="application/ld+json">
    {!! json_encode($schemaLocal, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
    @endpush

    <div class="max-w-7xl mx-auto px-4 py-8">
        {{-- Breadcrumb --}}
        <nav class="text-sm text-gray-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-pink-600">Accueil</a>
            @if($deptRel)
                <span class="mx-1">/</span>
                <span>{{ $deptRel->region }}</span>
                <span class="mx-1">/</span>
                <a href="{{ route('departement.show', $deptRel->departement_url) }}" class="hover:text-pink-600">{{ $deptRel->departement }}</a>
            @endif
            @if($villeRel)
                <span class="mx-1">/</span>
                <a href="{{ route('ville.show', $villeRel->url) }}" class="hover:text-pink-600">{{ $villeRel->nom_ville }}</a>
            @endif
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

                @if($etablissement->classement_ville > 0 && $totalInVille > 1)
                    <p class="text-sm text-gray-600 mt-2">
                        Classé <span class="font-semibold text-pink-600">n°{{ $etablissement->classement_ville }}</span>
                        sur {{ $totalInVille }} instituts de beauté à {{ $etablissement->ville }}
                    </p>
                @endif

                {{-- Photos --}}
                @if($etablissement->photos->isNotEmpty())
                    <div class="mt-6 grid grid-cols-2 md:grid-cols-3 gap-2">
                        @foreach($etablissement->photos as $photo)
                            <img src="{{ asset('storage/etablissements/' . $etablissement->id . '/' . $photo->filename) }}" alt="{{ $etablissement->titre }}" class="rounded-lg object-cover h-48 w-full">
                        @endforeach
                    </div>
                @endif

                {{-- Coordonnées & carte --}}
                <div class="mt-8 bg-white border rounded-lg p-6">
                    <h2 class="text-xl font-semibold mb-4">Coordonnées</h2>

                    <div class="grid sm:grid-cols-2 gap-6">
                        <div class="space-y-3">
                            @if($etablissement->adresse)
                                <div>
                                    <p class="text-sm text-gray-500">Adresse</p>
                                    <p class="text-sm font-medium">{{ $etablissement->adresse }}<br>{{ $etablissement->cp }} {{ $etablissement->ville }}</p>
                                </div>
                            @endif

                            @if($etablissement->telephone)
                                <x-phone-reveal :phone="$etablissement->telephone" :etablissement-id="$etablissement->id" label="Téléphone" />
                            @endif

                            @if($etablissement->portable)
                                <x-phone-reveal :phone="$etablissement->portable" :etablissement-id="$etablissement->id" label="Portable" :portable="true" />
                            @endif

                            <div class="pt-2">
                                <button @click="$store.contactModal.open = true" type="button" class="w-full flex items-center justify-center gap-2 bg-pink-600 text-white font-semibold py-3 px-5 rounded-lg hover:bg-pink-700 transition cursor-pointer">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                                    Contacter cet établissement
                                </button>
                            </div>
                            @if(!$etablissement->administrateurs->contains(auth()->id()))
                                <div class="pt-1">
                                    @auth
                                        <button @click="$store.claimModal.open = true" type="button" class="w-full text-center text-sm text-gray-500 hover:text-pink-600 transition cursor-pointer underline">
                                            Vous êtes le propriétaire ? Revendiquez cet établissement
                                        </button>
                                    @else
                                        <a href="{{ route('login') }}" class="block w-full text-center text-sm text-gray-500 hover:text-pink-600 transition underline">
                                            Vous êtes le propriétaire ? Connectez-vous pour revendiquer
                                        </a>
                                    @endauth
                                </div>
                            @endif
                        </div>

                        @if($etablissement->latitude && $etablissement->longitude)
                            <div class="rounded-lg overflow-hidden border" id="map" style="min-height: 220px;"></div>
                        @endif
                    </div>
                </div>

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
                {{-- Categories --}}
                @if($etablissement->categories->isNotEmpty())
                    <div class="bg-white rounded-lg shadow-sm border p-6 sticky top-4">
                        <h2 class="font-semibold text-lg mb-3">Prestations</h2>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($etablissement->categories as $cat)
                                <span class="text-xs bg-pink-50 text-pink-700 px-2 py-1 rounded">{{ $cat->nom }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

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
    {{-- Contact Modal --}}
    <div x-data="{ sent: false, sending: false, error: '' }"
         x-show="$store.contactModal.open"
         @keydown.escape.window="$store.contactModal.open = false"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4">

        <div class="fixed inset-0 bg-black/50" @click="$store.contactModal.open = false"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg p-6 z-10" @click.stop>
            <button @click="$store.contactModal.open = false" type="button" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 cursor-pointer">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <h2 class="text-xl font-bold text-gray-900 mb-4">Contacter {{ $etablissement->titre }}</h2>

            <div x-show="sent" class="text-center py-8">
                <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-lg font-semibold text-gray-900">Message envoyé !</p>
                <p class="text-sm text-gray-500 mt-1">L'établissement recevra votre message par email.</p>
                <button @click="$store.contactModal.open = false" type="button" class="mt-4 bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700 cursor-pointer">Fermer</button>
            </div>

            <form x-show="!sent" @submit.prevent="
                sending = true; error = '';
                const fd = new FormData($el);
                fetch($el.action, { method: 'POST', body: fd, headers: { 'Accept': 'application/json' } })
                    .then(r => { if (r.ok) { sent = true; } else { return r.json().then(d => { error = d.message || 'Erreur'; }); } })
                    .catch(() => { error = 'Erreur réseau.'; })
                    .finally(() => { sending = false; });
            " action="{{ route('contact.etablissement.send', $etablissement) }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Votre nom</label>
                    <input type="text" name="nom" value="{{ auth()->user()?->pseudo }}" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Votre email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ auth()->user()?->email }}" required class="w-full border rounded-lg px-3 py-2">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Message <span class="text-red-500">*</span></label>
                    <textarea name="contenu" rows="5" required class="w-full border rounded-lg px-3 py-2" placeholder="Votre message..."></textarea>
                </div>
                <p x-show="error" x-text="error" class="text-red-500 text-sm mb-3" x-cloak></p>
                <button type="submit" :disabled="sending" class="w-full bg-pink-600 text-white font-semibold py-3 rounded-lg hover:bg-pink-700 transition disabled:opacity-50 cursor-pointer">
                    <span x-show="!sending">Envoyer le message</span>
                    <span x-show="sending">Envoi en cours...</span>
                </button>
            </form>
        </div>
    </div>

    {{-- Claim Modal --}}
    @auth
    <div x-data="{ sent: false, sending: false, error: '' }"
         x-show="$store.claimModal.open"
         @keydown.escape.window="$store.claimModal.open = false"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4">

        <div class="fixed inset-0 bg-black/50" @click="$store.claimModal.open = false"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg p-6 z-10" @click.stop>
            <button @click="$store.claimModal.open = false" type="button" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 cursor-pointer">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <h2 class="text-xl font-bold text-gray-900 mb-1">Revendiquer cet établissement</h2>
            <p class="text-sm text-gray-500 mb-4">{{ $etablissement->titre }}</p>

            <div x-show="sent" class="text-center py-8">
                <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-lg font-semibold text-gray-900">Demande envoyée !</p>
                <p class="text-sm text-gray-500 mt-1">Notre équipe vérifiera votre demande dans les plus brefs délais.</p>
                <button @click="$store.claimModal.open = false" type="button" class="mt-4 bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700 cursor-pointer">Fermer</button>
            </div>

            <form x-show="!sent" @submit.prevent="
                sending = true; error = '';
                const fd = new FormData($el);
                fetch($el.action, { method: 'POST', body: fd, headers: { 'Accept': 'application/json' } })
                    .then(r => { if (r.ok) { sent = true; } else { return r.json().then(d => { error = d.error || d.message || 'Erreur'; }); } })
                    .catch(() => { error = 'Erreur réseau.'; })
                    .finally(() => { sending = false; });
            " action="{{ route('revendication.store', $etablissement) }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Nom du gérant <span class="text-red-500">*</span></label>
                    <input type="text" name="nom_gerant" required class="w-full border rounded-lg px-3 py-2" placeholder="Prénom et nom">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">N° SIRET</label>
                    <input type="text" name="siret" maxlength="14" class="w-full border rounded-lg px-3 py-2" placeholder="14 chiffres (facultatif)">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Message complémentaire</label>
                    <textarea name="message" rows="3" class="w-full border rounded-lg px-3 py-2" placeholder="Informations supplémentaires..."></textarea>
                </div>
                <p class="text-xs text-gray-400 mb-4">Les demandes de propriété sont systématiquement vérifiées par notre équipe de modérateurs.</p>
                <p x-show="error" x-text="error" class="text-red-500 text-sm mb-3" x-cloak></p>
                <button type="submit" :disabled="sending" class="w-full bg-pink-600 text-white font-semibold py-3 rounded-lg hover:bg-pink-700 transition disabled:opacity-50 cursor-pointer">
                    <span x-show="!sending">Envoyer ma demande</span>
                    <span x-show="sending">Envoi en cours...</span>
                </button>
            </form>
        </div>
    </div>
    @endauth

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
