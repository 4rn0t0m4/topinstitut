<x-layouts.app :title="'Instituts de beauté ' . $department->article . $department->name . ' - TopInstitut'" :description="'Tous les instituts de beauté, spas et esthéticiennes ' . $department->article . $department->name . '. Trouvez le meilleur institut près de chez vous.'">
    @push('jsonld')
    <x-breadcrumb-jsonld :items="[
        ['name' => 'Accueil', 'url' => '/'],
        ['name' => $department->name],
    ]" />
    @endpush

    <div class="max-w-7xl mx-auto px-4 py-8">
        <nav class="text-sm text-gray-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-pink-600">Accueil</a>
            <span class="mx-1">/</span>
            <span class="text-gray-900">{{ $department->name }}</span>
        </nav>

        <h1 class="text-3xl font-bold text-gray-900 mb-6">Instituts de beauté {{ $department->article }}{{ $department->name }}</h1>

        @if($cities->isNotEmpty())
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                @foreach($cities as $city)
                    <a href="{{ route('ville.show', [$department->slug, $city->slug]) }}"
                       class="bg-white border rounded-lg px-4 py-3 hover:border-pink-300 hover:bg-pink-50 transition">
                        <span class="font-medium text-gray-900">{{ $city->name }}</span>
                        <span class="text-sm text-gray-400 ml-1">({{ $city->establishments_count }})</span>
                    </a>
                @endforeach
            </div>
        @else
            <p class="text-gray-500">Aucun institut trouvé dans ce département.</p>
        @endif
    </div>
</x-layouts.app>
