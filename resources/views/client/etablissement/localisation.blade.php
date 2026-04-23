<x-layouts.app :noindex="true" :title="'Localisation - ' . $etablissement->titre">
    <div class="max-w-2xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Localisation - {{ $etablissement->titre }}</h1>

        <form method="POST" action="{{ route('client.etablissement.localisation.update', $etablissement) }}" class="bg-white rounded-lg shadow-sm border p-6">
            @csrf @method('PUT')

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Latitude</label>
                    <input type="number" step="any" name="latitude" value="{{ old('latitude', $etablissement->latitude) }}" required class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Longitude</label>
                    <input type="number" step="any" name="longitude" value="{{ old('longitude', $etablissement->longitude) }}" required class="w-full border rounded-lg px-3 py-2">
                </div>
            </div>

            <p class="text-sm text-gray-500 mb-4">Vous pouvez trouver les coordonnées GPS de votre établissement sur Google Maps.</p>

            <button type="submit" class="bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700">Enregistrer</button>
        </form>
    </div>
</x-layouts.app>
