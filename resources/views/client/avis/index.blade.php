<x-layouts.app :noindex="true" :title="'Avis - ' . $etablissement->titre">
    <div class="max-w-3xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Avis - {{ $etablissement->titre }}</h1>

        @forelse($avis as $a)
            <div class="bg-white rounded-lg shadow-sm border p-4 mb-4">
                <div class="flex justify-between items-start">
                    <div>
                        <span class="font-semibold">{{ $a->user->pseudo }}</span>
                        <span class="text-sm text-gray-400 ml-2">{{ $a->created_at->format('d/m/Y') }}</span>
                        @if($a->valide)
                            <span class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded-full ml-2">Publié</span>
                        @elseif($a->refus)
                            <span class="bg-red-100 text-red-700 text-xs px-2 py-0.5 rounded-full ml-2">Refusé</span>
                        @else
                            <span class="bg-orange-100 text-orange-700 text-xs px-2 py-0.5 rounded-full ml-2">En attente</span>
                        @endif
                    </div>
                    <x-star-rating :rating="$a->moyenne" size="w-4 h-4" />
                </div>
                <h3 class="font-medium mt-2">{{ $a->titre }}</h3>
                <p class="text-sm text-gray-700 mt-1">{{ $a->contenu }}</p>
                @if($a->reponse)
                    <div class="bg-gray-50 rounded-lg p-3 mt-3 text-sm">
                        <span class="font-medium text-pink-600">Votre réponse :</span>
                        <p class="text-gray-700 mt-1">{{ $a->reponse }}</p>
                    </div>
                @else
                    <a href="{{ route('client.etablissement.avis.repondre', [$etablissement, $a]) }}" class="text-pink-600 text-sm hover:underline mt-2 inline-block">Répondre</a>
                @endif
            </div>
        @empty
            <p class="text-gray-500">Aucun avis pour le moment.</p>
        @endforelse

        {{ $avis->links() }}
    </div>
</x-layouts.app>
