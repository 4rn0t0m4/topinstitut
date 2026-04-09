<x-layouts.app title="Contact - TopInstitut">
    <div class="max-w-2xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Contactez-nous</h1>

        <form method="POST" action="{{ route('contact.send') }}" class="bg-white rounded-lg shadow-sm border p-6">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Votre email</label>
                <input type="email" name="email" value="{{ old('email', auth()->user()?->email) }}" required class="w-full border rounded-lg px-3 py-2">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Message</label>
                <textarea name="contenu" rows="6" required class="w-full border rounded-lg px-3 py-2">{{ old('contenu') }}</textarea>
            </div>
            <button type="submit" class="bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700">Envoyer</button>
        </form>
    </div>
</x-layouts.app>
