@php
    $pageTitle = $prestationName . ' à ' . $city->name . ' (' . $city->postal_code . ') - TopInstitut';
    $pageDescription = 'Trouvez les meilleurs ' . strtolower($prestationName) . ' à ' . $city->name . ' (' . $city->postal_code . '). ' . $establishments->total() . ' établissement(s), avis clients, horaires et coordonnées.';
@endphp

<x-layouts.app :title="$pageTitle" :description="$pageDescription">
    @push('jsonld')
    <x-breadcrumb-jsonld :items="[
        ['name' => 'Accueil', 'url' => '/'],
        ['name' => $department->name, 'url' => '/' . $department->slug],
        ['name' => $city->name, 'url' => '/' . $department->slug . '/' . $city->slug],
        ['name' => $prestationName],
    ]" />

    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Service',
        'name' => $prestationName . ' à ' . $city->name,
        'description' => $pageDescription,
        'areaServed' => [
            '@type' => 'City',
            'name' => $city->name,
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => $city->name,
                'postalCode' => $city->postal_code,
                'addressCountry' => 'FR',
            ],
        ],
        'provider' => $establishments->take(5)->map(fn ($e) => [
            '@type' => 'BeautySalon',
            'name' => $e->name,
            'url' => url($e->url),
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $e->address,
                'postalCode' => $e->postal_code,
                'addressLocality' => $e->city,
                'addressCountry' => 'FR',
            ],
        ])->values()->all(),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
    @endpush

    <div class="max-w-7xl mx-auto px-4 py-8">
        <nav class="text-sm text-gray-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-pink-600">Accueil</a>
            <span class="mx-1">/</span>
            <a href="{{ route('departement.show', $department->slug) }}" class="hover:text-pink-600">{{ $department->name }}</a>
            <span class="mx-1">/</span>
            <a href="{{ route('ville.show', [$department->slug, $city->slug]) }}" class="hover:text-pink-600">{{ $city->name }}</a>
            <span class="mx-1">/</span>
            <span class="text-gray-700">{{ $prestationName }}</span>
        </nav>

        <h1 class="text-3xl font-bold text-gray-900 mb-3">{{ $prestationName }} à {{ $city->name }}</h1>
        <p class="text-gray-600 mb-6">
            {{ $establishments->total() }} établissement(s) proposant <strong>{{ strtolower($prestationName) }}</strong> à {{ $city->name }} ({{ $city->postal_code }}).
            Consultez les avis clients, les horaires et les coordonnées pour choisir le professionnel qui vous convient.
        </p>

        @if($establishments->isNotEmpty())
            <div class="space-y-4">
                @foreach($establishments as $establishment)
                    <x-etablissement-card :etablissement="$establishment" />
                @endforeach
            </div>
            <div class="mt-6">
                {{ $establishments->links() }}
            </div>
        @else
            <p class="text-gray-500">Aucun établissement trouvé pour cette prestation dans cette ville.</p>
        @endif

        {{-- Autres prestations dans cette ville --}}
        <section class="mt-12 pt-8 border-t">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Autres prestations à {{ $city->name }}</h2>
            <div class="flex flex-wrap gap-2">
                @foreach(\App\Models\Establishment::TYPE_LABELS as $typeId => $label)
                    @php $typeSlug = \App\Models\Establishment::TYPE_SLUGS[$typeId]; @endphp
                    @if($typeSlug !== $prestationSlug)
                        <a href="/{{ $department->slug }}/{{ $city->slug }}/{{ $typeSlug }}" class="px-3 py-2 bg-pink-50 hover:bg-pink-100 text-pink-700 text-sm rounded transition">
                            {{ $label }} à {{ $city->name }}
                        </a>
                    @endif
                @endforeach
            </div>
        </section>

        {{-- Même prestation dans les villes voisines --}}
        <section class="mt-8">
            <h2 class="text-xl font-bold text-gray-900 mb-4">{{ $prestationName }} dans les villes proches</h2>
            @php
                $isTypeSlug = in_array($prestationSlug, \App\Models\Establishment::TYPE_SLUGS, true);
                $nearbyCities = \App\Models\City::where('department_code', $city->department_code)
                    ->where('id', '!=', $city->id)
                    ->whereHas('establishments', function ($q) use ($isTypeSlug, $prestationSlug) {
                        $q->where('is_active', true);
                        if ($isTypeSlug) {
                            $typeId = array_search($prestationSlug, \App\Models\Establishment::TYPE_SLUGS, true);
                            $q->where('type', $typeId);
                        } else {
                            $q->whereHas('categories', fn ($sub) => $sub->where('slug', $prestationSlug));
                        }
                    })
                    ->orderByDesc('population')
                    ->limit(12)
                    ->get();
            @endphp
            <div class="flex flex-wrap gap-2">
                @foreach($nearbyCities as $nearby)
                    <a href="/{{ $department->slug }}/{{ $nearby->slug }}/{{ $prestationSlug }}" class="px-3 py-2 bg-gray-50 hover:bg-gray-100 text-gray-700 text-sm rounded transition">
                        {{ $prestationName }} à {{ $nearby->name }}
                    </a>
                @endforeach
            </div>
        </section>
    </div>
</x-layouts.app>
