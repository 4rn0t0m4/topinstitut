<x-layouts.guest title="Connexion - TopInstitut">
    <h2 class="text-xl font-bold text-center mb-6">Connexion</h2>

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full border rounded-lg px-3 py-2">
            @error('email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Mot de passe</label>
            <input type="password" name="password" required class="w-full border rounded-lg px-3 py-2">
        </div>

        <div class="flex items-center justify-between mb-6">
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="remember" class="rounded">
                Se souvenir de moi
            </label>
            <a href="{{ route('password.request') }}" class="text-sm text-pink-600 hover:underline">Mot de passe oublié ?</a>
        </div>

        <button type="submit" class="w-full bg-pink-600 text-white py-2 rounded-lg hover:bg-pink-700">Se connecter</button>
    </form>

    <p class="text-center text-sm text-gray-500 mt-4">
        Pas encore de compte ? <a href="{{ route('register') }}" class="text-pink-600 hover:underline">S'inscrire</a>
    </p>
</x-layouts.guest>
