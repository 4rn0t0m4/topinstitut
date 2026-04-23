<x-layouts.app :noindex="true" :title="'Photos - ' . $etablissement->titre">
    <div class="max-w-3xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Photos - {{ $etablissement->titre }}</h1>

        <form method="POST" action="{{ route('client.etablissement.photos.store', $etablissement) }}" enctype="multipart/form-data" class="bg-white rounded-lg shadow-sm border p-6 mb-6">
            @csrf
            <div class="flex gap-3 items-end">
                <div class="flex-1">
                    <label class="block text-sm font-medium mb-1">Ajouter une photo</label>
                    <input type="file" name="photo" accept="image/*" required class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>
                <button type="submit" class="bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700">Ajouter</button>
            </div>
        </form>

        @if($photos->isNotEmpty())
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                @foreach($photos as $photo)
                    <div class="relative group">
                        <img src="{{ asset('storage/' . $photo->filename) }}" alt="" class="rounded-lg object-cover h-48 w-full">
                        <form action="{{ route('client.etablissement.photos.destroy', [$etablissement, $photo]) }}" method="POST" class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition" onsubmit="return confirm('Supprimer cette photo ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="bg-red-600 text-white text-xs px-2 py-1 rounded">Supprimer</button>
                        </form>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500">Aucune photo.</p>
        @endif
    </div>
</x-layouts.app>
