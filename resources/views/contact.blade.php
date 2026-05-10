<x-layouts.app title="Contact - TopInstitut">
    <div class="max-w-2xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Contactez-nous</h1>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('contact.send') }}" class="bg-white rounded-lg shadow-sm border p-6">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Votre nom</label>
                <input type="text" name="name" value="{{ old('name', auth()->user()?->username) }}" class="w-full border rounded-lg px-3 py-2">
                @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Votre email <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email', auth()->user()?->email) }}" required class="w-full border rounded-lg px-3 py-2">
                @error('email') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Message <span class="text-red-500">*</span></label>
                <textarea name="content" rows="6" required class="w-full border rounded-lg px-3 py-2">{{ old('content') }}</textarea>
                @error('content') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700">Envoyer</button>
        </form>
    </div>
</x-layouts.app>
