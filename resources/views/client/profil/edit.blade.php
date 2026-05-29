<x-layouts.app :noindex="true" title="Modifier mon profil - TopInstitut">
    <div class="py-8">
        <h1 class="text-2xl font-bold mb-6">Modifier mon profil</h1>

        <form method="POST" action="{{ route('client.profil.update') }}" class="bg-white rounded-lg shadow-sm border p-6">
            @csrf @method('PUT')

            <div class="grid gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Pseudo</label>
                    <input type="text" name="pseudo" value="{{ old('pseudo', $user->pseudo) }}" required class="w-full border rounded-lg px-3 py-2">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Nom</label>
                        <input type="text" name="nom" value="{{ old('nom', $user->nom) }}" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Prénom</label>
                        <input type="text" name="prenom" value="{{ old('prenom', $user->prenom) }}" class="w-full border rounded-lg px-3 py-2">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Sexe</label>
                    <select name="sexe" class="w-full border rounded-lg px-3 py-2">
                        <option value="">-</option>
                        <option value="female" {{ old('sexe', $user->sexe) === 'female' ? 'selected' : '' }}>Femme</option>
                        <option value="male" {{ old('sexe', $user->sexe) === 'male' ? 'selected' : '' }}>Homme</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Adresse</label>
                    <input type="text" name="adresse" value="{{ old('adresse', $user->adresse) }}" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Code postal</label>
                        <input type="text" name="cp" value="{{ old('cp', $user->cp) }}" maxlength="5" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Ville</label>
                        <input type="text" name="ville" value="{{ old('ville', $user->ville) }}" class="w-full border rounded-lg px-3 py-2">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Tél. fixe</label>
                        <input type="text" name="tel_fixe" value="{{ old('tel_fixe', $user->tel_fixe) }}" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Portable</label>
                        <input type="text" name="tel_port" value="{{ old('tel_port', $user->tel_port) }}" class="w-full border rounded-lg px-3 py-2">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Date de naissance</label>
                    <input type="date" name="anniversaire" value="{{ old('anniversaire', $user->anniversaire?->format('Y-m-d')) }}" class="w-full border rounded-lg px-3 py-2">
                </div>
            </div>

            <button type="submit" class="mt-6 bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700">Enregistrer</button>
        </form>
    </div>
</x-layouts.app>
