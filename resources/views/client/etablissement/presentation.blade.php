<x-layouts.app :title="'Présentation - ' . $etablissement->titre">
    <div class="max-w-2xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Présentation - {{ $etablissement->titre }}</h1>

        <form method="POST" action="{{ route('client.etablissement.presentation.update', $etablissement) }}" class="bg-white rounded-lg shadow-sm border p-6">
            @csrf @method('PUT')
            <div class="grid gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Accroche</label>
                    <input type="text" name="accroche" value="{{ old('accroche', $etablissement->accroche) }}" maxlength="255" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Description</label>
                    <textarea name="description" rows="8" class="w-full border rounded-lg px-3 py-2">{{ old('description', $etablissement->description) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Tarifs</label>
                    <textarea name="tarifs" rows="5" class="w-full border rounded-lg px-3 py-2">{{ old('tarifs', $etablissement->tarifs) }}</textarea>
                </div>
            </div>
            <button type="submit" class="mt-6 bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700">Enregistrer</button>
        </form>
    </div>
</x-layouts.app>
