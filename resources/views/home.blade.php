<x-layouts.app title="TopInstitut - Annuaire des instituts de beauté, spas et thalassos en France" description="Trouvez les meilleurs instituts de beauté, esthéticiennes à domicile, spas et thalassos près de chez vous. Avis clients, horaires, coordonnées.">
    @push('jsonld')
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => 'TopInstitut',
        'url' => url('/'),
        'description' => 'Annuaire des instituts de beauté, spas et thalassos en France',
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => url('/recherche') . '?nom={search_term_string}',
            'query-input' => 'required name=search_term_string',
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
    @endpush
    {{-- Hero / Search --}}
    <section class="bg-gradient-to-r from-pink-500 to-pink-600 text-white py-16">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h1 class="text-3xl md:text-4xl font-bold mb-4">Trouvez le meilleur institut de beauté</h1>
            <p class="text-pink-100 mb-8">Instituts, spas, esthéticiennes à domicile et thalassos partout en France</p>

            <form action="{{ route('recherche') }}" method="GET" class="flex flex-col sm:flex-row gap-3 max-w-2xl mx-auto">
                <input type="text" name="nom" placeholder="Nom de l'institut..." class="flex-1 px-4 py-3 rounded-lg bg-white text-gray-900 placeholder-gray-400">
                <div class="flex-1 relative" x-data="villeAutocomplete()" @click.outside="open = false">
                    <input type="text" name="ville" placeholder="Ville..." x-model="query" @input="search()" @focus="open = results.length > 0" autocomplete="off" class="w-full px-4 py-3 rounded-lg bg-white text-gray-900 placeholder-gray-400">
                    <ul x-show="open" x-cloak class="absolute z-50 left-0 right-0 mt-1 bg-white border rounded-lg shadow-lg max-h-60 overflow-y-auto">
                        <template x-for="item in results" :key="item.label">
                            <li @click="select(item)" class="px-4 py-2 cursor-pointer hover:bg-pink-50 text-gray-900 text-sm" x-text="item.label"></li>
                        </template>
                    </ul>
                </div>
                <button type="submit" class="bg-white text-pink-600 font-semibold px-6 py-3 rounded-lg hover:bg-pink-50 transition">Rechercher</button>
            </form>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 py-12">
        {{-- Latest reviews --}}
        @if($latestReviews->isNotEmpty())
            <section class="mb-12">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Derniers avis</h2>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($latestReviews as $review)
                        <div class="bg-white rounded-lg shadow-sm border p-4">
                            <div class="flex items-center gap-2 mb-2">
                                <x-star-rating :rating="$review->average_rating" size="w-4 h-4" />
                                <span class="text-sm text-gray-500">{{ number_format($review->average_rating, 1, ',', '') }}/5</span>
                            </div>
                            <h3 class="font-semibold text-gray-900">{{ $review->title }}</h3>
                            <p class="text-sm text-gray-600 mt-1">{{ Str::limit($review->content, 100) }}</p>
                            <div class="mt-3 flex justify-between items-center text-xs text-gray-400">
                                <span>par {{ $review->reviewer_name }}</span>
                                <a href="{{ $review->establishment->url }}" class="text-pink-600 hover:underline">{{ $review->establishment->title }}</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Latest establishments --}}
        @if($latestEstablishments->isNotEmpty())
            <section class="mb-12">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Nouveaux établissements</h2>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($latestEstablishments as $establishment)
                        <x-etablissement-card :etablissement="$establishment" />
                    @endforeach
                </div>
            </section>
        @endif

        {{-- France Map --}}
        <section>
            <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">Trouvez un institut par département</h2>
            <x-france-map :departments="$departments" />
        </section>

        {{-- Departments list --}}
        <section class="mt-12">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Tous les départements</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2">
                @foreach($departments as $dept)
                    <a href="{{ route('departement.show', $dept->slug) }}"
                       class="text-sm text-gray-700 hover:text-pink-600 hover:bg-pink-50 px-3 py-2 rounded-lg transition">
                        {{ $dept->code }} - {{ $dept->name }}
                    </a>
                @endforeach
            </div>
        </section>
    </div>
</x-layouts.app>
