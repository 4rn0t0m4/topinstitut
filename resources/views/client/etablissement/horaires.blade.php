<x-layouts.app :noindex="true" :title="'Horaires - ' . $etablissement->name">
    <div class="max-w-2xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Horaires - {{ $etablissement->name }}</h1>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('client.etablissement.horaires.update', $etablissement) }}" class="bg-white rounded-lg shadow-sm border p-6">
            @csrf @method('PUT')

            <div class="grid grid-cols-12 gap-2 text-xs text-gray-500 mb-2 px-1">
                <span class="col-span-3"></span>
                <span class="col-span-2 text-center">Matin ouv.</span>
                <span class="col-span-2 text-center">Matin ferm.</span>
                <span class="col-span-2 text-center">A-M ouv.</span>
                <span class="col-span-2 text-center">A-M ferm.</span>
                <span class="col-span-1 text-center">Fermé</span>
            </div>

            @foreach(\App\Models\Schedule::DAYS as $num => $label)
                @php $h = $horaires[$num] ?? null; @endphp
                <div class="border-b last:border-0 py-3 grid grid-cols-12 gap-2 items-center">
                    <span class="col-span-3 font-medium text-sm">{{ $label }}</span>
                    <input type="time" name="horaires[{{ $num }}][open_am]" value="{{ $h?->open_am ? substr($h->open_am, 0, 5) : '' }}" class="col-span-2 border rounded px-2 py-1 text-sm">
                    <input type="time" name="horaires[{{ $num }}][close_am]" value="{{ $h?->close_am ? substr($h->close_am, 0, 5) : '' }}" class="col-span-2 border rounded px-2 py-1 text-sm">
                    <input type="time" name="horaires[{{ $num }}][open_pm]" value="{{ $h?->open_pm ? substr($h->open_pm, 0, 5) : '' }}" class="col-span-2 border rounded px-2 py-1 text-sm">
                    <input type="time" name="horaires[{{ $num }}][close_pm]" value="{{ $h?->close_pm ? substr($h->close_pm, 0, 5) : '' }}" class="col-span-2 border rounded px-2 py-1 text-sm">
                    <label class="col-span-1 flex items-center justify-center">
                        <input type="hidden" name="horaires[{{ $num }}][is_closed]" value="0">
                        <input type="checkbox" name="horaires[{{ $num }}][is_closed]" value="1" {{ $h?->is_closed ? 'checked' : '' }} class="rounded">
                    </label>
                </div>
            @endforeach

            <button type="submit" class="mt-6 bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700">Enregistrer</button>
        </form>
    </div>
</x-layouts.app>
