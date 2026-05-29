<x-layouts.app :noindex="true" :title="'FAQ - ' . $etablissement->name">
    <div class="py-8">
        <h1 class="text-2xl font-bold mb-1">Questions fréquentes</h1>
        <p class="text-sm text-gray-500 mb-6">{{ $etablissement->name }}</p>

        {{-- New FAQ --}}
        <form method="POST" action="{{ route('client.etablissement.faq.store', $etablissement) }}" class="bg-white rounded-lg shadow-sm border p-6 mb-8">
            @csrf
            <h2 class="font-semibold mb-3">Ajouter une question</h2>
            <div class="grid gap-3">
                <input type="text" name="question" placeholder="Question" required maxlength="255" class="w-full border rounded-lg px-3 py-2">
                <textarea name="answer" rows="3" placeholder="Réponse" required maxlength="2000" class="w-full border rounded-lg px-3 py-2"></textarea>
            </div>
            <button type="submit" class="mt-4 bg-pink-600 text-white px-5 py-2 rounded-lg hover:bg-pink-700">Ajouter</button>
        </form>

        {{-- Existing FAQs --}}
        <div class="space-y-3">
            @forelse($faqs as $faq)
                <div class="bg-white border rounded-lg p-4">
                    <form method="POST" action="{{ route('client.etablissement.faq.update', [$etablissement, $faq]) }}">
                        @csrf @method('PUT')
                        <div class="grid gap-2">
                            <input type="text" name="question" value="{{ old('question', $faq->question) }}" required class="w-full border rounded-lg px-3 py-2 font-medium">
                            <textarea name="answer" rows="3" required class="w-full border rounded-lg px-3 py-2 text-sm">{{ old('answer', $faq->answer) }}</textarea>
                        </div>
                        <div class="flex items-center gap-2 mt-3">
                            <button type="submit" class="text-sm bg-pink-600 text-white px-4 py-1.5 rounded hover:bg-pink-700">Enregistrer</button>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('client.etablissement.faq.destroy', [$etablissement, $faq]) }}" class="mt-2" onsubmit="return confirm('Supprimer cette question ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-sm text-red-500 hover:text-red-700">Supprimer</button>
                    </form>
                </div>
            @empty
                <p class="text-sm text-gray-500 italic">Aucune question pour l'instant.</p>
            @endforelse
        </div>
    </div>
</x-layouts.app>
