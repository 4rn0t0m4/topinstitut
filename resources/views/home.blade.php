<x-layouts.app title="TopInstitut - Annuaire des instituts de beauté en France">
    {{-- Hero / Search --}}
    <section class="bg-gradient-to-r from-pink-500 to-pink-600 text-white py-16">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h1 class="text-3xl md:text-4xl font-bold mb-4">Trouvez le meilleur institut de beauté</h1>
            <p class="text-pink-100 mb-8">Instituts, spas, esthéticiennes à domicile et thalassos partout en France</p>

            <form action="{{ route('recherche') }}" method="GET" class="flex flex-col sm:flex-row gap-3 max-w-2xl mx-auto">
                <input type="text" name="nom" placeholder="Nom de l'institut..." class="flex-1 px-4 py-3 rounded-lg text-gray-900 placeholder-gray-400">
                <input type="text" name="ville" placeholder="Ville..." class="flex-1 px-4 py-3 rounded-lg text-gray-900 placeholder-gray-400">
                <button type="submit" class="bg-white text-pink-600 font-semibold px-6 py-3 rounded-lg hover:bg-pink-50 transition">Rechercher</button>
            </form>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 py-12">
        {{-- Latest reviews --}}
        @if($derniersAvis->isNotEmpty())
            <section class="mb-12">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Derniers avis</h2>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($derniersAvis as $avis)
                        <div class="bg-white rounded-lg shadow-sm border p-4">
                            <div class="flex items-center gap-2 mb-2">
                                <x-star-rating :rating="$avis->moyenne" size="w-4 h-4" />
                                <span class="text-sm text-gray-500">{{ number_format($avis->moyenne, 1, ',', '') }}/5</span>
                            </div>
                            <h3 class="font-semibold text-gray-900">{{ $avis->titre }}</h3>
                            <p class="text-sm text-gray-600 mt-1">{{ Str::limit($avis->contenu, 100) }}</p>
                            <div class="mt-3 flex justify-between items-center text-xs text-gray-400">
                                <span>par {{ $avis->user->pseudo }}</span>
                                <a href="{{ $avis->etablissement->url }}" class="text-pink-600 hover:underline">{{ $avis->etablissement->titre }}</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Latest establishments --}}
        @if($derniersEtablissements->isNotEmpty())
            <section class="mb-12">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Nouveaux établissements</h2>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($derniersEtablissements as $etablissement)
                        <x-etablissement-card :etablissement="$etablissement" />
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Departments --}}
        <section>
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Recherche par département</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2">
                @foreach($departements as $dept)
                    <a href="{{ route('departement.show', $dept->departement_url) }}"
                       class="text-sm text-gray-700 hover:text-pink-600 hover:bg-pink-50 px-3 py-2 rounded-lg transition">
                        {{ $dept->numero }} - {{ $dept->departement }}
                    </a>
                @endforeach
            </div>
        </section>
    </div>
</x-layouts.app>
