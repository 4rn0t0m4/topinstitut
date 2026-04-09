<x-layouts.app title="Répondre à un avis - TopInstitut">
    <div class="max-w-2xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Répondre à l'avis de {{ $avis->user->pseudo }}</h1>

        <div class="bg-gray-50 border rounded-lg p-4 mb-6">
            <div class="flex items-center gap-2 mb-2">
                <x-star-rating :rating="$avis->moyenne" size="w-4 h-4" />
                <span class="text-sm">{{ number_format($avis->moyenne, 1, ',', '') }}/5</span>
            </div>
            <h3 class="font-medium">{{ $avis->titre }}</h3>
            <p class="text-sm text-gray-700 mt-1">{{ $avis->contenu }}</p>
        </div>

        <form method="POST" action="{{ route('client.etablissement.avis.reponse', [$etablissement, $avis]) }}" class="bg-white rounded-lg shadow-sm border p-6">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">Votre réponse</label>
                <textarea name="reponse" rows="5" required class="w-full border rounded-lg px-3 py-2">{{ old('reponse') }}</textarea>
            </div>
            <button type="submit" class="mt-4 bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700">Envoyer</button>
        </form>
    </div>
</x-layouts.app>
