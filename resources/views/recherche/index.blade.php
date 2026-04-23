<x-layouts.app title="Rechercher un institut de beauté - TopInstitut" description="Recherchez un institut de beauté, spa, esthéticienne ou thalasso par nom, ville ou catégorie de prestation.">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Recherche</h1>

        @if($geoloc)
            <div class="mb-4 bg-pink-50 border border-pink-200 rounded-lg px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-2 text-sm text-pink-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                    Résultats autour de votre position (rayon {{ $radius }} km)
                </div>
                <a href="{{ route('recherche') }}" class="text-sm text-pink-600 hover:underline">Désactiver</a>
            </div>
        @endif

        <form action="{{ route('recherche') }}" method="GET" class="bg-white border rounded-lg p-6 mb-8"
              x-data="{ open: {{ $type !== null || $openNow || $withPhotos || $minRating || $category ? 'true' : 'false' }}, loading: false }"
              @submit="loading = true; $dispatch('search-loading')">
            <div class="grid sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Nom</label>
                    <input type="text" name="nom" value="{{ $name ?? '' }}" class="w-full border rounded-lg px-3 py-2" placeholder="Institut...">
                </div>
                <div x-data="villeAutocomplete()" @click.outside="open = false" x-init="query = '{{ $cityName ?? '' }}'">
                    <label class="block text-sm font-medium mb-1">Ville</label>
                    <div class="relative">
                        <input type="text" name="ville" x-model="query" @input="search()" @focus="open = results.length > 0" autocomplete="off" class="w-full border rounded-lg px-3 py-2" placeholder="Ville...">
                        <ul x-show="open" x-cloak class="absolute z-50 left-0 right-0 mt-1 bg-white border rounded-lg shadow-lg max-h-60 overflow-y-auto">
                            <template x-for="item in results" :key="item.label">
                                <li @click="select(item)" class="px-4 py-2 cursor-pointer hover:bg-pink-50 text-gray-900 text-sm" x-text="item.label"></li>
                            </template>
                        </ul>
                    </div>
                </div>
                <div x-data="prestationAutocomplete()" @click.outside="open = false" x-init="query = '{{ optional($categories->firstWhere('id', $category))->name ?? '' }}'">
                    <label class="block text-sm font-medium mb-1">Prestation</label>
                    <div class="relative">
                        <input type="text" x-model="query" @input="search()" @focus="open = results.length > 0" autocomplete="off" class="w-full border rounded-lg px-3 py-2" placeholder="Manucure, massage...">
                        <input type="hidden" name="categorie" x-model="selectedId" value="{{ $category ?? '' }}">
                        <ul x-show="open" x-cloak class="absolute z-50 left-0 right-0 mt-1 bg-white border rounded-lg shadow-lg max-h-60 overflow-y-auto">
                            <template x-for="item in results" :key="item.id">
                                <li @click="select(item)" class="px-4 py-2 cursor-pointer hover:bg-pink-50 text-gray-900 text-sm" x-text="item.name"></li>
                            </template>
                        </ul>
                    </div>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700">Rechercher</button>
                </div>
            </div>

            <div class="mt-4">
                <button type="button" @click="open = !open" class="text-sm text-pink-600 hover:text-pink-700 flex items-center gap-1">
                    <span x-text="open ? 'Masquer les filtres' : 'Plus de filtres'"></span>
                    <svg class="w-4 h-4 transition" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <div x-show="open" x-cloak class="mt-4 grid sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-4 border-t">
                    <div>
                        <label class="block text-sm font-medium mb-1">Type</label>
                        <select name="type" class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">Tous</option>
                            @foreach(\App\Models\Establishment::TYPE_LABELS as $id => $label)
                                <option value="{{ $id }}" @selected($type === $id)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Note minimum</label>
                        <select name="note_min" class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">Toutes</option>
                            <option value="3" @selected($minRating == 3)>3 étoiles et +</option>
                            <option value="4" @selected($minRating == 4)>4 étoiles et +</option>
                            <option value="4.5" @selected($minRating == 4.5)>4,5 étoiles et +</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Tri</label>
                        <select name="tri" class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="rating" @selected($sort === 'rating')>Meilleure note</option>
                            <option value="avis" @selected($sort === 'avis')>Plus d'avis</option>
                            <option value="recent" @selected($sort === 'recent')>Plus récents</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-4 pt-6">
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="ouvert" value="1" @checked($openNow) class="rounded text-pink-600 focus:ring-pink-500">
                            Ouvert maintenant
                        </label>
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="avec_photos" value="1" @checked($withPhotos) class="rounded text-pink-600 focus:ring-pink-500">
                            Avec photos
                        </label>
                    </div>
                </div>
            </div>
        </form>

        <div x-data="{ view: 'list', loading: false }" @search-loading.window="loading = true">
            <div x-show="loading" x-cloak class="space-y-4">
                @for($i = 0; $i < 5; $i++)
                    <x-etablissement-card-skeleton />
                @endfor
            </div>

            <div x-show="!loading">
            @if($establishments->isNotEmpty())
                <div class="flex items-center justify-between mb-4">
                    <p class="text-sm text-gray-500">{{ $establishments->total() }} résultat(s)</p>
                    <div class="inline-flex rounded-lg border bg-white overflow-hidden text-sm">
                        <button type="button" @click="view = 'list'" :class="view === 'list' ? 'bg-pink-600 text-white' : 'text-gray-700 hover:bg-gray-50'" class="px-3 py-1.5 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                            Liste
                        </button>
                        <button type="button" @click="view = 'map'" :class="view === 'map' ? 'bg-pink-600 text-white' : 'text-gray-700 hover:bg-gray-50'" class="px-3 py-1.5 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                            Carte
                        </button>
                    </div>
                </div>

                <div x-show="view === 'list'" class="space-y-4">
                    @foreach($establishments as $establishment)
                        <x-etablissement-card :etablissement="$establishment" />
                    @endforeach
                </div>

                <div x-show="view === 'map'" x-cloak>
                    <x-search-map :establishments="$establishments" />
                </div>

                <div class="mt-6" x-show="view === 'list'">
                    {{ $establishments->links() }}
                </div>
            @else
                <p class="text-gray-500">Aucun résultat pour votre recherche.</p>
            @endif
            </div>
        </div>
    </div>
</x-layouts.app>
