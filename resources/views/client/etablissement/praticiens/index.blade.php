<x-layouts.app :noindex="true" :title="'Praticiens - ' . $etablissement->name">
    <div class="py-8">
        <h1 class="text-2xl font-bold mb-1">Praticiens</h1>
        <p class="text-sm text-gray-500 mb-6">{{ $etablissement->name }}</p>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        {{-- Ajout --}}
        <form method="POST" action="{{ route('client.etablissement.praticiens.store', $etablissement) }}" class="bg-white rounded-lg shadow-sm border p-4 mb-6 flex gap-3 items-end">
            @csrf
            <div class="flex-1">
                <label class="block text-sm font-medium mb-1">Ajouter un praticien</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Prénom / Nom" required class="w-full border rounded-lg px-3 py-2">
            </div>
            <button type="submit" class="bg-pink-600 text-white px-5 py-2 rounded-lg hover:bg-pink-700">Ajouter</button>
        </form>

        {{-- Liste --}}
        @forelse($etablissement->practitioners as $p)
            <div class="bg-white rounded-lg shadow-sm border p-4 mb-3">
                <form method="POST" action="{{ route('client.etablissement.praticiens.update', [$etablissement, $p]) }}" class="flex flex-wrap gap-3 items-center">
                    @csrf @method('PUT')
                    <input type="text" name="name" value="{{ $p->name }}" required class="flex-1 min-w-48 border rounded-lg px-3 py-2">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked($p->is_active) class="rounded">
                        Actif
                    </label>
                    <button type="submit" class="text-pink-600 hover:underline text-sm">Enregistrer</button>
                    <a href="{{ route('client.etablissement.praticiens.horaires', [$etablissement, $p]) }}" class="text-pink-600 hover:underline text-sm">Horaires de travail</a>
                </form>
                <form method="POST" action="{{ route('client.etablissement.praticiens.destroy', [$etablissement, $p]) }}" class="mt-2" onsubmit="return confirm('Supprimer ce praticien ? Ses rendez-vous et horaires seront perdus.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-red-500 hover:text-red-700 text-xs">Supprimer</button>
                </form>
            </div>
        @empty
            <p class="text-gray-500">Aucun praticien pour l'instant. Ajoutez-en un pour activer la prise de rendez-vous.</p>
        @endforelse
    </div>
</x-layouts.app>
