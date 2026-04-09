<x-layouts.guest title="Mot de passe oublié - TopInstitut">
    <h2 class="text-xl font-bold text-center mb-6">Mot de passe oublié</h2>

    @if(session('status'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full border rounded-lg px-3 py-2">
            @error('email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="w-full bg-pink-600 text-white py-2 rounded-lg hover:bg-pink-700">Envoyer le lien</button>
    </form>

    <p class="text-center text-sm text-gray-500 mt-4">
        <a href="{{ route('login') }}" class="text-pink-600 hover:underline">Retour à la connexion</a>
    </p>
</x-layouts.guest>
