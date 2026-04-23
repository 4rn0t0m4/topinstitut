<x-layouts.app :title="'Instituts de beauté à ' . $city->name . ' (' . $city->postal_code . ') - TopInstitut'" :description="'Liste des instituts de beauté, spas et esthéticiennes à ' . $city->name . ' (' . $city->postal_code . '). Consultez les avis, horaires et coordonnées.'">
    @push('jsonld')
    <x-breadcrumb-jsonld :items="[
        ['name' => 'Accueil', 'url' => '/'],
        ['name' => $department->name, 'url' => '/' . $department->slug],
        ['name' => $city->name],
    ]" />
    @endpush

    <div class="max-w-7xl mx-auto px-4 py-8">
        <nav class="text-sm text-gray-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-pink-600">Accueil</a>
            <span class="mx-1">/</span>
            <a href="{{ route('departement.show', $department->slug) }}" class="hover:text-pink-600">{{ $department->name }}</a>
            <span class="mx-1">/</span>
            <span class="text-gray-900">{{ $city->name }}</span>
        </nav>

        <h1 class="text-3xl font-bold text-gray-900 mb-6">Instituts de beauté à {{ $city->name }}</h1>

        @if($establishments->isNotEmpty())
            <div class="space-y-4">
                @foreach($establishments as $establishment)
                    <x-etablissement-card :etablissement="$establishment" :rank="$establishment->city_rank ?: null" />
                @endforeach
            </div>
            <div class="mt-6">{{ $establishments->links() }}</div>
        @else
            <p class="text-gray-500">Aucun institut trouvé à {{ $city->name }}.</p>
        @endif

        {{-- Prestations disponibles : types + catégories --}}
        @php
            $typesAvailable = \App\Models\Establishment::active()
                ->where('city_id', $city->id)
                ->distinct()
                ->pluck('type');

            $categoriesAvailable = \App\Models\Category::whereHas('establishments', function ($q) use ($city) {
                $q->where('is_active', true)->where('city_id', $city->id);
            })->orderBy('name')->get();
        @endphp
        @if($typesAvailable->isNotEmpty() || $categoriesAvailable->isNotEmpty())
            <section class="mt-12 pt-8 border-t">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Prestations à {{ $city->name }}</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach($typesAvailable as $typeId)
                        @php $typeSlug = \App\Models\Establishment::TYPE_SLUGS[$typeId]; @endphp
                        <a href="/{{ $department->slug }}/{{ $city->slug }}/{{ $typeSlug }}" class="px-3 py-2 bg-pink-50 hover:bg-pink-100 text-pink-700 text-sm rounded transition">
                            {{ \App\Models\Establishment::TYPE_LABELS[$typeId] }} à {{ $city->name }}
                        </a>
                    @endforeach
                    @foreach($categoriesAvailable as $cat)
                        <a href="/{{ $department->slug }}/{{ $city->slug }}/{{ $cat->slug }}" class="px-3 py-2 bg-gray-50 hover:bg-gray-100 text-gray-700 text-sm rounded transition">
                            {{ $cat->name }} à {{ $city->name }}
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-layouts.app>
