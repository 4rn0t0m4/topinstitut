<x-layouts.app title="Ajouter un institut - TopInstitut">
    <div class="max-w-2xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Ajouter un institut de beauté</h1>
        <p class="text-gray-600 mb-6">Votre établissement sera vérifié avant publication.</p>

        <form method="POST" action="{{ route('etablissement.store') }}" class="bg-white rounded-lg shadow-sm border p-6">
            @csrf
            <div class="grid gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Nom de l'établissement</label>
                    <input type="text" name="titre" value="{{ old('titre') }}" required class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Type</label>
                    <select name="type" required class="w-full border rounded-lg px-3 py-2">
                        @foreach(\App\Models\Establishment::TYPE_LABELS as $key => $label)
                            <option value="{{ $key }}" {{ old('type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Adresse</label>
                    <input type="text" name="adresse" value="{{ old('adresse') }}" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Code postal</label>
                        <input type="text" name="cp" value="{{ old('cp') }}" maxlength="5" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Ville</label>
                        <input type="text" name="ville" value="{{ old('ville') }}" class="w-full border rounded-lg px-3 py-2">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Téléphone</label>
                    <input type="text" name="telephone" value="{{ old('telephone') }}" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Description</label>
                    <textarea name="description" rows="5" class="w-full border rounded-lg px-3 py-2">{{ old('description') }}</textarea>
                </div>
            </div>
            <button type="submit" class="mt-6 bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700">Enregistrer</button>
        </form>
    </div>
</x-layouts.app>
