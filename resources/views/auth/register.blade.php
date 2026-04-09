<x-layouts.guest title="Inscription - TopInstitut">
    <h2 class="text-xl font-bold text-center mb-6">Créer un compte</h2>

    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Pseudo</label>
            <input type="text" name="pseudo" value="{{ old('pseudo') }}" required class="w-full border rounded-lg px-3 py-2">
            @error('pseudo') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required class="w-full border rounded-lg px-3 py-2">
            @error('email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-3 mb-4">
            <div>
                <label class="block text-sm font-medium mb-1">Nom</label>
                <input type="text" name="nom" value="{{ old('nom') }}" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Prénom</label>
                <input type="text" name="prenom" value="{{ old('prenom') }}" class="w-full border rounded-lg px-3 py-2">
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Sexe</label>
            <select name="sexe" class="w-full border rounded-lg px-3 py-2">
                <option value="">-</option>
                <option value="female" {{ old('sexe') === 'female' ? 'selected' : '' }}>Femme</option>
                <option value="male" {{ old('sexe') === 'male' ? 'selected' : '' }}>Homme</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Mot de passe</label>
            <input type="password" name="password" required class="w-full border rounded-lg px-3 py-2">
            @error('password') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium mb-1">Confirmer le mot de passe</label>
            <input type="password" name="password_confirmation" required class="w-full border rounded-lg px-3 py-2">
        </div>

        <button type="submit" class="w-full bg-pink-600 text-white py-2 rounded-lg hover:bg-pink-700">S'inscrire</button>
    </form>

    <p class="text-center text-sm text-gray-500 mt-4">
        Déjà inscrit ? <a href="{{ route('login') }}" class="text-pink-600 hover:underline">Se connecter</a>
    </p>
</x-layouts.guest>
