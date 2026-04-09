<x-layouts.app title="Rechercher un institut de beauté - TopInstitut" description="Recherchez un institut de beauté, spa, esthéticienne ou thalasso par nom, ville ou catégorie de prestation.">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Recherche</h1>

        <form action="{{ route('recherche') }}" method="GET" class="bg-white border rounded-lg p-6 mb-8">
            <div class="grid sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Nom</label>
                    <input type="text" name="nom" value="{{ $nom ?? '' }}" class="w-full border rounded-lg px-3 py-2" placeholder="Institut...">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Ville</label>
                    <input type="text" name="ville" value="{{ $villeNom ?? '' }}" class="w-full border rounded-lg px-3 py-2" placeholder="Ville...">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700">Rechercher</button>
                </div>
            </div>
        </form>

        @if($etablissements->isNotEmpty())
            <p class="text-sm text-gray-500 mb-4">{{ $etablissements->total() }} résultat(s)</p>
            <div class="space-y-4">
                @foreach($etablissements as $etablissement)
                    <x-etablissement-card :etablissement="$etablissement" />
                @endforeach
            </div>
            <div class="mt-6">
                {{ $etablissements->withQueryString()->links() }}
            </div>
        @else
            <p class="text-gray-500">Aucun résultat pour votre recherche.</p>
        @endif
    </div>
</x-layouts.app>
