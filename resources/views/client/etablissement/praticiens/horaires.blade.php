<x-layouts.app :noindex="true" :title="'Horaires - ' . $practitioner->name">
    @php
        $fmt = function ($v) {
            $v = substr((string) $v, 0, 5);
            if (! preg_match('/^\d{2}:\d{2}$/', $v)) {
                return '';
            }
            return $v === '00:00' ? '' : $v;
        };
        $daysData = [];
        foreach (\App\Models\Schedule::DAYS as $num => $label) {
            $ranges = $schedules[$num] ?? collect();
            $am = $ranges->first(fn ($r) => substr($r->start_time, 0, 5) < '13:00');
            $pm = $ranges->first(fn ($r) => substr($r->start_time, 0, 5) >= '13:00');
            $daysData[$num] = [
                'am_start' => $fmt($am?->start_time),
                'am_end' => $fmt($am?->end_time),
                'pm_start' => $fmt($pm?->start_time),
                'pm_end' => $fmt($pm?->end_time),
            ];
        }
        $times = [];
        for ($m = 0; $m < 24 * 60; $m += 15) {
            $times[] = sprintf('%02d:%02d', intdiv($m, 60), $m % 60);
        }
    @endphp

    <div class="max-w-3xl mx-auto px-4 py-8">
        <a href="{{ route('client.etablissement.praticiens', $etablissement) }}" class="text-sm text-pink-600 hover:underline">&larr; Retour aux praticiens</a>
        <h1 class="text-2xl font-bold mt-2 mb-1">Horaires de travail</h1>
        <p class="text-sm text-gray-500 mb-2">{{ $practitioner->name }} — {{ $etablissement->name }}</p>
        <p class="text-sm text-gray-500 mb-6">Laissez <strong>« — »</strong> sur une demi-journée non travaillée. Les deux créneaux (matin / après-midi) gèrent la pause déjeuner.</p>

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

        <form method="POST" action="{{ route('client.etablissement.praticiens.horaires.update', [$etablissement, $practitioner]) }}"
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

            <div class="flex flex-wrap gap-2 mb-4">
                <button type="button" @click="copyToAll()" class="text-xs border rounded-full px-3 py-1.5 text-gray-600 hover:border-pink-300 hover:text-pink-600">
                    Copier lundi → tous les jours
                </button>
                <button type="button" @click="copyToWeekdays()" class="text-xs border rounded-full px-3 py-1.5 text-gray-600 hover:border-pink-300 hover:text-pink-600">
                    Copier lundi → lun-ven
                </button>
            </div>

            <div class="hidden sm:grid grid-cols-12 gap-2 text-xs text-gray-500 mb-1 px-1">
                <span class="col-span-3"></span>
                <span class="col-span-4 text-center">Matin (début → fin)</span>
                <span class="col-span-4 text-center">Après-midi (début → fin)</span>
                <span class="col-span-1"></span>
            </div>

            @foreach(\App\Models\Schedule::DAYS as $num => $label)
                <div class="border-b last:border-0 py-3 grid grid-cols-2 sm:grid-cols-12 gap-2 items-center">
                    <span class="col-span-2 sm:col-span-3 font-medium text-sm">{{ $label }}</span>

                    <div class="col-span-2 sm:col-span-4 flex items-center gap-1">
                        <select name="days[{{ $num }}][am_start]" x-model="days[{{ $num }}].am_start" aria-label="{{ $label }} matin début" class="flex-1 border rounded px-2 py-1.5 text-sm">
                            <option value="">—</option>
                            @foreach($times as $t)<option value="{{ $t }}">{{ $t }}</option>@endforeach
                        </select>
                        <span class="text-gray-400">→</span>
                        <select name="days[{{ $num }}][am_end]" x-model="days[{{ $num }}].am_end" aria-label="{{ $label }} matin fin" class="flex-1 border rounded px-2 py-1.5 text-sm">
                            <option value="">—</option>
                            @foreach($times as $t)<option value="{{ $t }}">{{ $t }}</option>@endforeach
                        </select>
                    </div>

                    <div class="col-span-2 sm:col-span-4 flex items-center gap-1">
                        <select name="days[{{ $num }}][pm_start]" x-model="days[{{ $num }}].pm_start" aria-label="{{ $label }} après-midi début" class="flex-1 border rounded px-2 py-1.5 text-sm">
                            <option value="">—</option>
                            @foreach($times as $t)<option value="{{ $t }}">{{ $t }}</option>@endforeach
                        </select>
                        <span class="text-gray-400">→</span>
                        <select name="days[{{ $num }}][pm_end]" x-model="days[{{ $num }}].pm_end" aria-label="{{ $label }} après-midi fin" class="flex-1 border rounded px-2 py-1.5 text-sm">
                            <option value="">—</option>
                            @foreach($times as $t)<option value="{{ $t }}">{{ $t }}</option>@endforeach
                        </select>
                    </div>

                    <button type="button" @click="days[{{ $num }}] = { am_start:'', am_end:'', pm_start:'', pm_end:'' }"
                            title="Vider ce jour" class="hidden sm:flex col-span-1 items-center justify-center text-gray-300 hover:text-red-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            @endforeach

            <button type="submit" class="mt-6 bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700">Enregistrer</button>
        </form>
    </div>
</x-layouts.app>
