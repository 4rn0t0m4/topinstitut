<x-layouts.app :title="'Actualité - ' . $etablissement->titre">
    <div class="max-w-2xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Actualité / Promotion - {{ $etablissement->titre }}</h1>

        <form method="POST" action="{{ route('client.etablissement.actualite.update', $etablissement) }}" class="bg-white rounded-lg shadow-sm border p-6">
            @csrf @method('PUT')
            <div class="grid gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Titre</label>
                    <input type="text" name="titre" value="{{ old('titre', $actualite?->titre) }}" required class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Contenu</label>
                    <textarea name="contenu" rows="5" class="w-full border rounded-lg px-3 py-2">{{ old('contenu', $actualite?->contenu) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Date limite</label>
                    <input type="date" name="date_limite" value="{{ old('date_limite', $actualite?->date_limite?->format('Y-m-d')) }}" class="w-full border rounded-lg px-3 py-2">
                </div>
            </div>
            <button type="submit" class="mt-6 bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700">Enregistrer</button>
        </form>
    </div>
</x-layouts.app>
