<x-layouts.app :title="'Horaires - ' . $etablissement->titre">
    <div class="max-w-2xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Horaires - {{ $etablissement->titre }}</h1>

        <form method="POST" action="{{ route('client.etablissement.horaires.update', $etablissement) }}" class="bg-white rounded-lg shadow-sm border p-6">
            @csrf @method('PUT')

            @foreach(\App\Models\Horaire::JOURS as $num => $label)
                @php $h = $horaires[$num] ?? null; @endphp
                <div class="border-b last:border-0 py-3">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-medium text-sm">{{ $label }}</span>
                        <label class="flex items-center gap-1 text-sm">
                            <input type="checkbox" name="horaires[{{ $num }}][ferme]" value="1" {{ $h?->ferme ? 'checked' : '' }} class="rounded">
                            Fermé
                        </label>
                    </div>
                    <div class="grid grid-cols-4 gap-2 text-sm">
                        <input type="time" name="horaires[{{ $num }}][matin_ouverture]" value="{{ $h?->matin_ouverture ? substr($h->matin_ouverture, 0, 5) : '' }}" class="border rounded px-2 py-1">
                        <input type="time" name="horaires[{{ $num }}][matin_fermeture]" value="{{ $h?->matin_fermeture ? substr($h->matin_fermeture, 0, 5) : '' }}" class="border rounded px-2 py-1">
                        <input type="time" name="horaires[{{ $num }}][aprem_ouverture]" value="{{ $h?->aprem_ouverture ? substr($h->aprem_ouverture, 0, 5) : '' }}" class="border rounded px-2 py-1">
                        <input type="time" name="horaires[{{ $num }}][aprem_fermeture]" value="{{ $h?->aprem_fermeture ? substr($h->aprem_fermeture, 0, 5) : '' }}" class="border rounded px-2 py-1">
                    </div>
                </div>
            @endforeach

            <button type="submit" class="mt-6 bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700">Enregistrer</button>
        </form>
    </div>
</x-layouts.app>
