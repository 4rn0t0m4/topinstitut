<x-layouts.guest title="Réinitialiser le mot de passe - TopInstitut">
    <h2 class="text-xl font-bold text-center mb-6">Nouveau mot de passe</h2>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email', request('email')) }}" required class="w-full border rounded-lg px-3 py-2">
            @error('email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Nouveau mot de passe</label>
            <input type="password" name="password" required class="w-full border rounded-lg px-3 py-2">
            @error('password') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium mb-1">Confirmer</label>
            <input type="password" name="password_confirmation" required class="w-full border rounded-lg px-3 py-2">
        </div>

        <button type="submit" class="w-full bg-pink-600 text-white py-2 rounded-lg hover:bg-pink-700">Réinitialiser</button>
    </form>
</x-layouts.guest>
