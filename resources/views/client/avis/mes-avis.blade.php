<x-layouts.app title="Mes avis - TopInstitut">
    <div class="max-w-3xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Mes avis</h1>

        @forelse($avis as $a)
            <div class="bg-white rounded-lg shadow-sm border p-4 mb-4">
                <div class="flex justify-between items-start">
                    <div>
                        <a href="{{ $a->etablissement->url }}" class="font-medium text-pink-600 hover:underline">{{ $a->etablissement->titre }}</a>
                        <span class="text-sm text-gray-400 ml-2">{{ $a->created_at->format('d/m/Y') }}</span>
                    </div>
                    <x-star-rating :rating="$a->moyenne" size="w-4 h-4" />
                </div>
                <h3 class="font-medium mt-2">{{ $a->titre }}</h3>
                <p class="text-sm text-gray-700 mt-1">{{ $a->contenu }}</p>
            </div>
        @empty
            <p class="text-gray-500">Vous n'avez pas encore déposé d'avis.</p>
        @endforelse

        {{ $avis->links() }}
    </div>
</x-layouts.app>
