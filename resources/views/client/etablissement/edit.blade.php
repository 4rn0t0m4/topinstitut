<x-layouts.app :noindex="true" :title="'Modifier ' . $etablissement->titre">
    <div class="max-w-2xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Coordonnées - {{ $etablissement->titre }}</h1>

        <form method="POST" action="{{ route('client.etablissement.update', $etablissement) }}" class="bg-white rounded-lg shadow-sm border p-6">
            @csrf @method('PUT')
            <div class="grid gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Titre</label>
                    <input type="text" name="titre" value="{{ old('titre', $etablissement->titre) }}" required class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Adresse</label>
                    <input type="text" name="adresse" value="{{ old('adresse', $etablissement->adresse) }}" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Code postal</label>
                        <input type="text" name="cp" value="{{ old('cp', $etablissement->cp) }}" maxlength="5" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Ville</label>
                        <input type="text" name="ville" value="{{ old('ville', $etablissement->ville) }}" class="w-full border rounded-lg px-3 py-2">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Téléphone</label>
                        <input type="text" name="telephone" value="{{ old('telephone', $etablissement->telephone) }}" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Portable</label>
                        <input type="text" name="portable" value="{{ old('portable', $etablissement->portable) }}" class="w-full border rounded-lg px-3 py-2">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $etablissement->email) }}" class="w-full border rounded-lg px-3 py-2">
                </div>
            </div>
            <button type="submit" class="mt-6 bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700">Enregistrer</button>
        </form>
    </div>
</x-layouts.app>
