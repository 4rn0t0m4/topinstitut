<x-layouts.app :noindex="true" :title="'Horaires - ' . $etablissement->name">
    @php
        // Valeur HH:MM stricte ; 00:00 et valeurs malformées => '' (demi-journée non renseignée).
        $fmt = function ($v) {
            $v = substr((string) $v, 0, 5);
            if (! preg_match('/^\d{2}:\d{2}$/', $v)) {
                return '';
            }
            return $v === '00:00' ? '' : $v;
        };
        $daysData = [];
        foreach (\App\Models\Schedule::DAYS as $num => $label) {
            $h = $horaires[$num] ?? null;
            $daysData[$num] = [
                'open_am' => $fmt($h?->open_am),
                'close_am' => $fmt($h?->close_am),
                'open_pm' => $fmt($h?->open_pm),
                'close_pm' => $fmt($h?->close_pm),
                'is_closed' => (bool) ($h?->is_closed),
            ];
        }
        // Options de créneaux toutes les 15 min.
        $times = [];
        for ($m = 0; $m < 24 * 60; $m += 15) {
            $times[] = sprintf('%02d:%02d', intdiv($m, 60), $m % 60);
        }
    @endphp

    <div class="max-w-3xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-2">Horaires - {{ $etablissement->name }}</h1>
        <p class="text-sm text-gray-500 mb-6">Laissez <strong>« — »</strong> sur une demi-journée pour indiquer qu'elle est fermée (ex. ouvert le matin uniquement).</p>

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

        <form method="POST" action="{{ route('client.etablissement.horaires.update', $etablissement) }}"
              class="bg-white rounded-lg shadow-sm border p-4 sm:p-6"
              x-data="{
                  days: {{ \Illuminate\Support\Js::from($daysData) }},
                  copyToAll() {
                      const src = JSON.parse(JSON.stringify(this.days[1]));
                      for (const n of [2,3,4,5,6,7]) { this.days[n] = { ...src }; }
                  },
                  copyToWeekdays() {
                      const src = JSON.parse(JSON.stringify(this.days[1]));
                      for (const n of [2,3,4,5]) { this.days[n] = { ...src }; }
                  },
              }">
            @csrf @method('PUT')

            {{-- Actions rapides --}}
            <div class="flex flex-wrap gap-2 mb-4">
                <button type="button" @click="copyToAll()" class="text-xs border rounded-full px-3 py-1.5 text-gray-600 hover:border-pink-300 hover:text-pink-600">
                    Copier lundi → tous les jours
                </button>
                <button type="button" @click="copyToWeekdays()" class="text-xs border rounded-full px-3 py-1.5 text-gray-600 hover:border-pink-300 hover:text-pink-600">
                    Copier lundi → lun-ven
                </button>
            </div>

            {{-- En-têtes (desktop) --}}
            <div class="hidden sm:grid grid-cols-12 gap-2 text-xs text-gray-500 mb-1 px-1">
                <span class="col-span-3"></span>
                <span class="col-span-4 text-center">Matin (ouv. → ferm.)</span>
                <span class="col-span-4 text-center">Après-midi (ouv. → ferm.)</span>
                <span class="col-span-1 text-center">Fermé</span>
            </div>

            @foreach(\App\Models\Schedule::DAYS as $num => $label)
                <div class="border-b last:border-0 py-3 grid grid-cols-2 sm:grid-cols-12 gap-2 items-center"
                     :class="days[{{ $num }}].is_closed ? 'opacity-60' : ''">
                    {{-- Source unique pour is_closed --}}
                    <input type="hidden" name="horaires[{{ $num }}][is_closed]" :value="days[{{ $num }}].is_closed ? '1' : '0'">

                    <span class="col-span-2 sm:col-span-3 font-medium text-sm flex items-center justify-between">
                        {{ $label }}
                        <label class="sm:hidden flex items-center gap-1 text-xs text-gray-500">
                            <input type="checkbox" x-model="days[{{ $num }}].is_closed" class="rounded">
                            Fermé
                        </label>
                    </span>

                    <template x-if="days[{{ $num }}].is_closed">
                        <span class="col-span-2 sm:col-span-8 text-sm text-gray-400 italic sm:text-center">Fermé toute la journée</span>
                    </template>

                    <template x-if="!days[{{ $num }}].is_closed">
                        <div class="contents">
                            <div class="col-span-2 sm:col-span-4 flex items-center gap-1">
                                <select name="horaires[{{ $num }}][open_am]" x-model="days[{{ $num }}].open_am" aria-label="{{ $label }} ouverture matin" class="flex-1 border rounded px-2 py-1.5 text-sm">
                                    <option value="">—</option>
                                    @foreach($times as $t)<option value="{{ $t }}">{{ $t }}</option>@endforeach
                                </select>
                                <span class="text-gray-400">→</span>
                                <select name="horaires[{{ $num }}][close_am]" x-model="days[{{ $num }}].close_am" aria-label="{{ $label }} fermeture matin" class="flex-1 border rounded px-2 py-1.5 text-sm">
                                    <option value="">—</option>
                                    @foreach($times as $t)<option value="{{ $t }}">{{ $t }}</option>@endforeach
                                </select>
                            </div>
                            <div class="col-span-2 sm:col-span-4 flex items-center gap-1">
                                <select name="horaires[{{ $num }}][open_pm]" x-model="days[{{ $num }}].open_pm" aria-label="{{ $label }} ouverture après-midi" class="flex-1 border rounded px-2 py-1.5 text-sm">
                                    <option value="">—</option>
                                    @foreach($times as $t)<option value="{{ $t }}">{{ $t }}</option>@endforeach
                                </select>
                                <span class="text-gray-400">→</span>
                                <select name="horaires[{{ $num }}][close_pm]" x-model="days[{{ $num }}].close_pm" aria-label="{{ $label }} fermeture après-midi" class="flex-1 border rounded px-2 py-1.5 text-sm">
                                    <option value="">—</option>
                                    @foreach($times as $t)<option value="{{ $t }}">{{ $t }}</option>@endforeach
                                </select>
                            </div>
                        </div>
                    </template>

                    <label class="hidden sm:flex col-span-1 items-center justify-center">
                        <input type="checkbox" x-model="days[{{ $num }}].is_closed" class="rounded">
                    </label>
                </div>
            @endforeach

            <button type="submit" class="mt-6 bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700">Enregistrer</button>
        </form>
    </div>
</x-layouts.app>
