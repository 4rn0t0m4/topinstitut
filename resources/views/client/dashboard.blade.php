<x-layouts.app :noindex="true" title="Mon espace - TopInstitut">
    @php
        // Groupes d'actions par établissement. Chaque action : [route, label, svg path].
        $actionGroups = [
            'Fiche' => [
                ['edit',          'Coordonnées',  'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z'],
                ['localisation',  'Localisation', 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z'],
                ['presentation',  'Présentation', 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'],
                ['photos',        'Photos',       'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6z'],
                ['faq',           'FAQ',          'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            ],
            'Réservation' => [
                ['prestations',   'Prestations',  'M7 7h.01M7 3h5a1.99 1.99 0 011.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.99 1.99 0 013 12V7a4 4 0 014-4z'],
                ['categories',    'Catégories',   'M4 6a2 2 0 012-2h2l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H6a2 2 0 01-2-2V6z'],
                ['praticiens',    'Praticiens',   'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4z'],
                ['horaires',      'Horaires',     'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            ],
            'Avis' => [
                ['avis',          'Avis',         'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118L2.05 9.771c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'],
            ],
        ];
    @endphp

    <div class="space-y-8">
        {{-- En-tête + stats --}}
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Bonjour {{ $user->username }}</h1>
            <p class="text-sm text-gray-500 mt-1">Gérez vos établissements et suivez votre activité.</p>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg border shadow-sm p-4">
                <div class="text-2xl font-bold text-gray-900">{{ $stats['establishments'] }}</div>
                <div class="text-sm text-gray-500">Établissement{{ $stats['establishments'] > 1 ? 's' : '' }}</div>
            </div>
            <div class="bg-white rounded-lg border shadow-sm p-4">
                <div class="text-2xl font-bold text-gray-900">{{ $stats['reviews_received'] }}</div>
                <div class="text-sm text-gray-500">Avis reçus</div>
            </div>
            <a href="{{ route('client.mes-avis') }}" class="bg-white rounded-lg border shadow-sm p-4 hover:border-pink-300 hover:bg-pink-50 transition">
                <div class="text-2xl font-bold text-gray-900">{{ $stats['reviews_published'] }}</div>
                <div class="text-sm text-gray-500">Mes avis publiés</div>
            </a>
            <a href="{{ route('client.favoris') }}" class="bg-white rounded-lg border shadow-sm p-4 hover:border-pink-300 hover:bg-pink-50 transition">
                <div class="text-2xl font-bold text-gray-900">{{ $stats['favorites'] }}</div>
                <div class="text-sm text-gray-500">Mes favoris</div>
            </a>
        </div>

        {{-- Établissements --}}
        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Mes établissements</h2>

            @forelse($etablissements as $etab)
                <div class="bg-white rounded-lg shadow-sm border p-5 mb-4">
                    {{-- En-tête de la carte --}}
                    <div class="flex items-start justify-between gap-4 pb-4 border-b">
                        <div class="min-w-0">
                            <h3 class="font-semibold text-gray-900 truncate">{{ $etab->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $etab->type_label }} · {{ $etab->city }} · {{ $etab->reviews_count ?? 0 }} avis</p>
                        </div>
                        <a href="{{ $etab->url }}" target="_blank" rel="noopener" class="flex-shrink-0 inline-flex items-center gap-1 text-sm font-medium text-pink-600 hover:text-pink-700">
                            Voir la fiche
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    </div>

                    {{-- Localisation à placer : alerte --}}
                    @if(! ($etab->latitude && $etab->longitude))
                        <a href="{{ route('client.etablissement.localisation', $etab) }}" class="flex items-center gap-2 mt-4 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 hover:bg-amber-100">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Votre établissement n'est pas placé sur la carte — cliquez pour le localiser.
                        </a>
                    @endif

                    {{-- Actions groupées --}}
                    <div class="mt-4 space-y-4">
                        @foreach($actionGroups as $groupLabel => $actions)
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">{{ $groupLabel }}</div>
                                <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-5 gap-2">
                                    @foreach($actions as [$routeName, $label, $iconPath])
                                        <a href="{{ route('client.etablissement.'.$routeName, $etab) }}"
                                           class="flex flex-col items-center justify-center gap-1.5 rounded-lg border border-gray-200 px-2 py-3 text-center hover:border-pink-300 hover:bg-pink-50 transition group">
                                            <svg class="w-5 h-5 text-gray-400 group-hover:text-pink-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}"/>
                                            </svg>
                                            <span class="text-xs font-medium text-gray-700 group-hover:text-pink-700 leading-tight">{{ $label }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg border border-dashed p-8 text-center">
                    <p class="text-gray-500">Vous ne gérez aucun établissement pour le moment.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-layouts.app>
