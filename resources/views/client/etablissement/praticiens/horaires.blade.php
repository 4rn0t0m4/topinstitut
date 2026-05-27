<x-layouts.app :noindex="true" :title="'Horaires - ' . $practitioner->name">
    <div class="max-w-2xl mx-auto px-4 py-8">
        <a href="{{ route('client.etablissement.praticiens', $etablissement) }}" class="text-sm text-pink-600 hover:underline">&larr; Retour aux praticiens</a>
        <h1 class="text-2xl font-bold mt-2 mb-1">Horaires de travail</h1>
        <p class="text-sm text-gray-500 mb-6">{{ $practitioner->name }} — {{ $etablissement->name }}</p>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('client.etablissement.praticiens.horaires.update', [$etablissement, $practitioner]) }}" class="bg-white rounded-lg shadow-sm border p-6">
            @csrf @method('PUT')

            <div class="hidden sm:grid grid-cols-12 gap-2 text-xs text-gray-500 mb-2 px-1">
                <span class="col-span-3"></span>
                <span class="col-span-2 text-center">Matin début</span>
                <span class="col-span-2 text-center">Matin fin</span>
                <span class="col-span-2 text-center">A-M début</span>
                <span class="col-span-2 text-center">A-M fin</span>
            </div>

            @foreach(\App\Models\Schedule::DAYS as $num => $label)
                @php
                    $ranges = $schedules[$num] ?? collect();
                    $am = $ranges->first(fn($r) => substr($r->start_time, 0, 5) < '13:00');
                    $pm = $ranges->first(fn($r) => substr($r->start_time, 0, 5) >= '13:00');
                @endphp
                <div class="grid grid-cols-12 gap-2 items-center border-b last:border-0 py-2">
                    <span class="col-span-12 sm:col-span-3 font-medium text-sm">{{ $label }}</span>
                    <input type="time" name="days[{{ $num }}][am_start]" value="{{ $am ? substr($am->start_time, 0, 5) : '' }}" class="col-span-3 sm:col-span-2 border rounded px-2 py-1 text-sm">
                    <input type="time" name="days[{{ $num }}][am_end]" value="{{ $am ? substr($am->end_time, 0, 5) : '' }}" class="col-span-3 sm:col-span-2 border rounded px-2 py-1 text-sm">
                    <input type="time" name="days[{{ $num }}][pm_start]" value="{{ $pm ? substr($pm->start_time, 0, 5) : '' }}" class="col-span-3 sm:col-span-2 border rounded px-2 py-1 text-sm">
                    <input type="time" name="days[{{ $num }}][pm_end]" value="{{ $pm ? substr($pm->end_time, 0, 5) : '' }}" class="col-span-3 sm:col-span-2 border rounded px-2 py-1 text-sm">
                </div>
            @endforeach

            <p class="text-xs text-gray-400 mt-3">Laissez vide un jour non travaillé. Le créneau du matin et de l'après-midi permet de gérer la pause déjeuner.</p>

            <button type="submit" class="mt-6 bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700">Enregistrer</button>
        </form>
    </div>
</x-layouts.app>
