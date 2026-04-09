<x-layouts.app :title="'Instituts de beauté ' . $departement->article . $departement->departement . ' - TopInstitut'" :description="'Tous les instituts de beauté, spas et esthéticiennes ' . $departement->article . $departement->departement . '. Trouvez le meilleur institut près de chez vous.'">
    @push('jsonld')
    <x-breadcrumb-jsonld :items="[
        ['name' => 'Accueil', 'url' => '/'],
        ['name' => $departement->departement],
    ]" />
    @endpush

    <div class="max-w-7xl mx-auto px-4 py-8">
        <nav class="text-sm text-gray-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-pink-600">Accueil</a>
            <span class="mx-1">/</span>
            <span class="text-gray-900">{{ $departement->departement }}</span>
        </nav>

        <h1 class="text-3xl font-bold text-gray-900 mb-6">Instituts de beauté {{ $departement->article }}{{ $departement->departement }}</h1>

        @if($villes->isNotEmpty())
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                @foreach($villes as $ville)
                    <a href="{{ route('ville.show', $ville->url) }}"
                       class="bg-white border rounded-lg px-4 py-3 hover:border-pink-300 hover:bg-pink-50 transition">
                        <span class="font-medium text-gray-900">{{ $ville->nom_ville }}</span>
                        <span class="text-sm text-gray-400 ml-1">({{ $ville->etablissements_count }})</span>
                    </a>
                @endforeach
            </div>
        @else
            <p class="text-gray-500">Aucun institut trouvé dans ce département.</p>
        @endif
    </div>
</x-layouts.app>
