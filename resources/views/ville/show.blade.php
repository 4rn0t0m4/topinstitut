<x-layouts.app :title="'Instituts de beauté à ' . $ville->nom_ville . ' (' . $ville->code_postal . ') - TopInstitut'" :description="'Liste des instituts de beauté, spas et esthéticiennes à ' . $ville->nom_ville . ' (' . $ville->code_postal . '). Consultez les avis, horaires et coordonnées.'">
    @push('jsonld')
    <x-breadcrumb-jsonld :items="[
        ['name' => 'Accueil', 'url' => '/'],
        ['name' => $ville->departementRelation->departement ?? '', 'url' => '/departement-' . ($ville->departementRelation->departement_url ?? '') . '.html'],
        ['name' => $ville->nom_ville],
    ]" />
    @endpush

    <div class="max-w-7xl mx-auto px-4 py-8">
        <nav class="text-sm text-gray-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-pink-600">Accueil</a>
            <span class="mx-1">/</span>
            <a href="{{ route('departement.show', $ville->departementRelation->departement_url ?? '') }}" class="hover:text-pink-600">{{ $ville->departementRelation->departement ?? '' }}</a>
            <span class="mx-1">/</span>
            <span class="text-gray-900">{{ $ville->nom_ville }}</span>
        </nav>

        <h1 class="text-3xl font-bold text-gray-900 mb-6">Instituts de beauté à {{ $ville->nom_ville }}</h1>

        @if($etablissements->isNotEmpty())
            <div class="space-y-4">
                @foreach($etablissements as $etablissement)
                    <x-etablissement-card :etablissement="$etablissement" :rank="$etablissement->classement_ville ?: null" />
                @endforeach
            </div>
            <div class="mt-6">{{ $etablissements->links() }}</div>
        @else
            <p class="text-gray-500">Aucun institut trouvé à {{ $ville->nom_ville }}.</p>
        @endif
    </div>
</x-layouts.app>
