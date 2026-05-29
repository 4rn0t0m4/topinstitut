<x-layouts.app :noindex="true" :title="'Agenda - ' . $etablissement->name">
    @php
        $startHour = 8;
        $endHour = 21;
        $hours = range($startHour, $endHour - 1);
        $totalMin = ($endHour - $startHour) * 60;
        $isWeek = $view === 'week';

        // Snapshot JS de chaque RDV : sert à pré-remplir la modale d'édition.
        $appointmentsJs = [];
        foreach ($practitioners as $pp) {
            foreach ($pp->appointments as $a) {
                $appointmentsJs[(string) $a->id] = [
                    'id' => $a->id,
                    'practitioner_id' => $a->practitioner_id,
                    'service_id' => $a->service_id,
                    'service_name' => $a->service_name,
                    'duration_minutes' => (int) $a->duration_minutes,
                    'customer_name' => $a->customer_name,
                    'customer_email' => $a->customer_email,
                    'customer_phone' => $a->customer_phone,
                    'notes' => $a->notes,
                    'date' => $a->starts_at->format('Y-m-d'),
                    'time' => $a->starts_at->format('H:i'),
                    'status' => $a->status,
                ];
            }
        }
        $servicesJs = $services->map(fn ($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'duration' => $s->duration_minutes,
        ])->values()->all();

        $statusColor = [
            'confirmed' => 'bg-blue-100 border-blue-500 text-blue-900 hover:bg-blue-200',
            'completed' => 'bg-emerald-100 border-emerald-500 text-emerald-900 hover:bg-emerald-200',
            'no_show'   => 'bg-gray-100 border-gray-400 text-gray-500 hover:bg-gray-200',
        ];

        // Helper d'URL : conserve view + praticien à travers la navigation.
        $urlFor = function (\Illuminate\Support\Carbon $d, ?string $forceView = null) use ($etablissement, $view, $selectedPractitioner) {
            $params = [$etablissement, 'date' => $d->format('Y-m-d'), 'view' => $forceView ?? $view];
            if (($forceView ?? $view) === 'week' && $selectedPractitioner) {
                $params['practitioner_id'] = $selectedPractitioner->id;
            }
            return route('client.etablissement.agenda', $params);
        };
    @endphp

    <div class="py-6"
         x-data="agenda({
             services: {{ \Illuminate\Support\Js::from($servicesJs) }},
             appointments: {{ \Illuminate\Support\Js::from($appointmentsJs) }},
             date: '{{ $date->format('Y-m-d') }}',
             startHour: {{ $startHour }},
             endHour: {{ $endHour }},
             basePath: '{{ route('client.etablissement.agenda', $etablissement) }}',
         })">

        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <div>
                <h1 class="text-2xl font-bold">Agenda</h1>
                <p class="text-sm text-gray-500">{{ $etablissement->name }}</p>
            </div>
            <div class="flex gap-2">
                <button type="button" @click="openCreate()" class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700 text-sm">+ Rendez-vous</button>
                <button type="button" @click="showBlock = true" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 text-sm">Bloquer une plage</button>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
                <ul class="list-disc list-inside text-sm">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        {{-- Barre d'outils : navigation + toggle + sélecteur praticien --}}
        <div class="flex flex-wrap items-center justify-center gap-2 mb-3">
            <a href="{{ $urlFor($isWeek ? $date->copy()->subWeek() : $date->copy()->subDay()) }}" class="px-3 py-1.5 border rounded-lg hover:bg-gray-50">&larr;</a>
            <form method="GET" class="flex items-center gap-2">
                <input type="hidden" name="view" value="{{ $view }}">
                @if($isWeek && $selectedPractitioner)
                    <input type="hidden" name="practitioner_id" value="{{ $selectedPractitioner->id }}">
                @endif
                <input type="date" name="date" value="{{ $date->format('Y-m-d') }}" onchange="this.form.submit()" class="border rounded-lg px-3 py-1.5">
            </form>
            <a href="{{ $urlFor($isWeek ? $date->copy()->addWeek() : $date->copy()->addDay()) }}" class="px-3 py-1.5 border rounded-lg hover:bg-gray-50">&rarr;</a>
            <a href="{{ $urlFor(now()) }}" class="text-sm text-pink-600 hover:underline ml-2">Aujourd'hui</a>

            <span class="mx-2 h-6 w-px bg-gray-200"></span>

            <div class="inline-flex border rounded-lg overflow-hidden text-sm">
                <a href="{{ $urlFor($date, 'day') }}" class="px-3 py-1.5 {{ ! $isWeek ? 'bg-pink-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">Jour</a>
                <a href="{{ $urlFor($date, 'week') }}" class="px-3 py-1.5 border-l {{ $isWeek ? 'bg-pink-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">Semaine</a>
            </div>

            @if($isWeek && $allPractitioners->isNotEmpty())
                <form method="GET" class="ml-2">
                    <input type="hidden" name="view" value="week">
                    <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">
                    <select name="practitioner_id" onchange="this.form.submit()" class="border rounded-lg px-3 py-1.5 text-sm">
                        @foreach($allPractitioners as $p)
                            <option value="{{ $p->id }}" {{ $selectedPractitioner?->id === $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </form>
            @endif
        </div>

        {{-- Titre période --}}
        @if($isWeek)
            <p class="text-center font-medium text-gray-700 mb-4 capitalize">
                Semaine du {{ $date->copy()->startOfWeek()->locale('fr')->isoFormat('D MMMM') }} au {{ $date->copy()->endOfWeek()->locale('fr')->isoFormat('D MMMM YYYY') }}
            </p>
        @else
            <p class="text-center font-medium text-gray-700 mb-4 capitalize">{{ $date->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</p>
        @endif

        @if($allPractitioners->isEmpty())
            <div class="bg-white border border-dashed rounded-lg p-8 text-center text-gray-500">
                Aucun praticien actif. <a href="{{ route('client.etablissement.praticiens', $etablissement) }}" class="text-pink-600 hover:underline">Ajoutez-en un</a> pour gérer l'agenda.
            </div>
        @elseif($isWeek && ! $selectedPractitioner)
            <div class="bg-white border border-dashed rounded-lg p-8 text-center text-gray-500">
                Sélectionnez un praticien pour afficher la semaine.
            </div>
        @elseif($isWeek && $weekDays->isEmpty())
            <div class="bg-white border border-dashed rounded-lg p-8 text-center text-gray-500">
                Aucun jour d'ouverture cette semaine. Vérifiez les <a href="{{ route('client.etablissement.horaires', $etablissement) }}" class="text-pink-600 hover:underline">horaires de l'établissement</a>.
            </div>
        @else
            {{-- Grille temporelle --}}
            <div class="bg-white border rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    @if($isWeek)
                        {{-- VUE SEMAINE : ruler + jours ouverts pour le praticien sélectionné --}}
                        <div class="min-w-full" style="display: grid; grid-template-columns: 60px repeat({{ max(1, $weekDays->count()) }}, minmax(120px, 1fr));">

                            <div class="border-b border-r bg-gray-50"></div>
                            @foreach($weekDays as $wd)
                                <div class="border-b border-r last:border-r-0 px-2 py-2 text-center text-xs {{ $wd->isToday() ? 'bg-pink-50 text-pink-700 font-semibold' : 'bg-gray-50 text-gray-700' }}">
                                    <div class="capitalize">{{ $wd->locale('fr')->isoFormat('ddd') }}</div>
                                    <div class="font-semibold">{{ $wd->format('j') }}</div>
                                </div>
                            @endforeach

                            <div class="border-r relative" style="height: {{ $totalMin }}px;">
                                @foreach($hours as $h)
                                    <div class="absolute right-2 text-xs text-gray-400" style="top: {{ ($h - $startHour) * 60 - 6 }}px;">{{ $h }}h</div>
                                @endforeach
                            </div>

                            @foreach($weekDays as $wd)
                                @php
                                    $dayKey = $wd->format('Y-m-d');
                                    $dayStart = $wd->copy()->startOfDay();
                                    $dayEnd = $wd->copy()->endOfDay();
                                    $dayAppointments = $selectedPractitioner->appointments->filter(fn ($a) => $a->starts_at->format('Y-m-d') === $dayKey);
                                    $dayTimeOffs = $selectedPractitioner->timeOffs->filter(fn ($t) => $t->starts_at->lt($dayEnd) && $t->ends_at->gt($dayStart));
                                @endphp
                                <div class="relative border-r last:border-r-0 cursor-pointer select-none {{ $wd->isToday() ? 'bg-pink-50/30' : '' }}"
                                     style="height: {{ $totalMin }}px;"
                                     @click="onColumnClick($event, {{ $selectedPractitioner->id }}, '{{ $dayKey }}')">

                                    @foreach($hours as $h)
                                        <div class="absolute left-0 right-0 border-t border-gray-100" style="top: {{ ($h - $startHour) * 60 }}px;"></div>
                                        <div class="absolute left-0 right-0 border-t border-dashed border-gray-50" style="top: {{ ($h - $startHour) * 60 + 30 }}px;"></div>
                                    @endforeach

                                    @foreach($dayTimeOffs as $off)
                                        @php
                                            $offStartMin = $off->starts_at->lt($dayStart)
                                                ? 0
                                                : max(0, (($off->starts_at->hour - $startHour) * 60) + $off->starts_at->minute);
                                            $offEndMin = $off->ends_at->gt($dayEnd)
                                                ? $totalMin
                                                : min($totalMin, (($off->ends_at->hour - $startHour) * 60) + $off->ends_at->minute);
                                            $offHeight = max(20, $offEndMin - $offStartMin);
                                        @endphp
                                        <div data-timeoff-card
                                             class="absolute left-1 right-1 bg-amber-100/80 border border-amber-300 rounded px-1.5 py-0.5 text-[10px] text-amber-800 overflow-hidden"
                                             style="top: {{ $offStartMin }}px; height: {{ $offHeight }}px; z-index: 5;"
                                             @click.stop>
                                            <div class="font-medium leading-tight">Bloqué</div>
                                            @if($off->reason)<div class="truncate text-amber-700 leading-tight">{{ $off->reason }}</div>@endif
                                        </div>
                                    @endforeach

                                    @foreach($dayAppointments as $rdv)
                                        @php
                                            $top = (($rdv->starts_at->hour - $startHour) * 60) + $rdv->starts_at->minute;
                                            $height = max(20, (int) $rdv->duration_minutes);
                                            $color = $statusColor[$rdv->status] ?? 'bg-blue-100 border-blue-500 text-blue-900';
                                        @endphp
                                        <div data-appointment-card
                                             class="absolute left-0.5 right-0.5 border-l-4 rounded px-1.5 py-0.5 text-[10px] overflow-hidden cursor-pointer transition {{ $color }}"
                                             style="top: {{ $top }}px; height: {{ $height }}px; z-index: 10;"
                                             @click.stop="openEdit({{ $rdv->id }})"
                                             @mouseenter="showTooltip({{ $rdv->id }}, $event)"
                                             @mouseleave="hideTooltip()">
                                            <div class="font-semibold whitespace-nowrap leading-tight">{{ $rdv->starts_at->format('H:i') }}</div>
                                            <div class="truncate font-medium leading-tight">{{ $rdv->customer_name }}</div>
                                            @if($height >= 45)
                                                <div class="truncate opacity-75 leading-tight">{{ $rdv->service_name }}</div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    @else
                        {{-- VUE JOUR : ruler + N colonnes praticiens --}}
                        <div class="min-w-full" style="display: grid; grid-template-columns: 60px repeat({{ $practitioners->count() }}, minmax(180px, 1fr));">

                            <div class="border-b border-r bg-gray-50"></div>
                            @foreach($practitioners as $p)
                                <div class="border-b border-r last:border-r-0 bg-gray-50 px-3 py-2 text-center text-sm font-semibold text-gray-700 truncate">
                                    {{ $p->name }}
                                </div>
                            @endforeach

                            <div class="border-r relative" style="height: {{ $totalMin }}px;">
                                @foreach($hours as $h)
                                    <div class="absolute right-2 text-xs text-gray-400" style="top: {{ ($h - $startHour) * 60 - 6 }}px;">{{ $h }}h</div>
                                @endforeach
                            </div>

                            @foreach($practitioners as $p)
                                @php
                                    $dayStart = $date->copy()->startOfDay();
                                    $dayEnd = $date->copy()->endOfDay();
                                @endphp
                                <div class="relative border-r last:border-r-0 cursor-pointer select-none"
                                     style="height: {{ $totalMin }}px;"
                                     @click="onColumnClick($event, {{ $p->id }})">

                                    @foreach($hours as $h)
                                        <div class="absolute left-0 right-0 border-t border-gray-100" style="top: {{ ($h - $startHour) * 60 }}px;"></div>
                                        <div class="absolute left-0 right-0 border-t border-dashed border-gray-50" style="top: {{ ($h - $startHour) * 60 + 30 }}px;"></div>
                                    @endforeach

                                    @foreach($p->timeOffs as $off)
                                        @php
                                            $offStartMin = $off->starts_at->lt($dayStart)
                                                ? 0
                                                : max(0, (($off->starts_at->hour - $startHour) * 60) + $off->starts_at->minute);
                                            $offEndMin = $off->ends_at->gt($dayEnd)
                                                ? $totalMin
                                                : min($totalMin, (($off->ends_at->hour - $startHour) * 60) + $off->ends_at->minute);
                                            $offHeight = max(20, $offEndMin - $offStartMin);
                                        @endphp
                                        <div data-timeoff-card
                                             class="absolute left-1 right-1 bg-amber-100/80 border border-amber-300 rounded px-2 py-1 text-xs text-amber-800 overflow-hidden"
                                             style="top: {{ $offStartMin }}px; height: {{ $offHeight }}px; z-index: 5;"
                                             @click.stop>
                                            <div class="flex items-start justify-between gap-1">
                                                <div class="min-w-0">
                                                    <div class="font-medium">Bloqué {{ $off->starts_at->format('H:i') }}–{{ $off->ends_at->format('H:i') }}</div>
                                                    @if($off->reason)<div class="truncate text-amber-700">{{ $off->reason }}</div>@endif
                                                </div>
                                                <form method="POST" action="{{ route('client.etablissement.agenda.blocage.destroy', [$etablissement, $p, $off]) }}" onsubmit="return confirm('Débloquer cette plage ?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-amber-700 hover:text-amber-900 text-xs">✕</button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach

                                    @foreach($p->appointments as $rdv)
                                        @php
                                            $top = (($rdv->starts_at->hour - $startHour) * 60) + $rdv->starts_at->minute;
                                            $height = max(20, (int) $rdv->duration_minutes);
                                            $color = $statusColor[$rdv->status] ?? 'bg-blue-100 border-blue-500 text-blue-900';
                                        @endphp
                                        <div data-appointment-card
                                             class="absolute left-1 right-1 border-l-4 rounded px-2 py-1 text-xs overflow-hidden cursor-pointer transition {{ $color }}"
                                             style="top: {{ $top }}px; height: {{ $height }}px; z-index: 10;"
                                             @click.stop="openEdit({{ $rdv->id }})"
                                             @mouseenter="showTooltip({{ $rdv->id }}, $event)"
                                             @mouseleave="hideTooltip()">
                                            <div class="font-semibold whitespace-nowrap">{{ $rdv->starts_at->format('H:i') }}–{{ $rdv->ends_at->format('H:i') }}</div>
                                            <div class="truncate font-medium">{{ $rdv->customer_name }}</div>
                                            @if($height >= 45)
                                                <div class="truncate opacity-75">{{ $rdv->service_name }}</div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="border-t bg-gray-50 px-4 py-2 text-xs text-gray-500 flex flex-wrap items-center gap-x-4 gap-y-1">
                    <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-3 bg-blue-100 border-l-2 border-blue-500 rounded-sm"></span>Confirmé</span>
                    <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-3 bg-emerald-100 border-l-2 border-emerald-500 rounded-sm"></span>Honoré</span>
                    <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-3 bg-gray-100 border-l-2 border-gray-400 rounded-sm"></span>Absent</span>
                    <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-3 bg-amber-100 border border-amber-300 rounded-sm"></span>Plage bloquée</span>
                    <span class="ml-auto italic hidden sm:inline">Clic sur la grille = nouveau RDV · clic sur un RDV = modifier</span>
                </div>
            </div>
        @endif

        {{-- Tooltip enrichi au survol des RDV --}}
        <div x-show="tooltipFor !== null && appointments[tooltipFor]" x-cloak
             :style="`top: ${tooltipPos.y}px; left: ${tooltipPos.x}px;`"
             class="fixed z-50 w-72 bg-white border border-gray-200 rounded-md shadow-lg p-3 text-sm pointer-events-none transition-opacity">
            <template x-if="tooltipFor !== null && appointments[tooltipFor]">
                <div>
                    <div class="flex items-start justify-between gap-2 mb-1">
                        <div class="font-semibold text-gray-900 truncate" x-text="appointments[tooltipFor].customer_name || 'Client'"></div>
                        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full whitespace-nowrap"
                              :class="statusBadgeClass(appointments[tooltipFor].status)"
                              x-text="statusLabel(appointments[tooltipFor].status)"></span>
                    </div>
                    <div class="text-xs text-gray-500 mb-2">
                        <span x-text="appointments[tooltipFor].time"></span>
                        – <span x-text="endTime(appointments[tooltipFor])"></span>
                        · <span x-text="appointments[tooltipFor].duration_minutes + ' min'"></span>
                    </div>
                    <div class="text-sm text-gray-800 mb-2" x-text="appointments[tooltipFor].service_name"></div>
                    <div class="space-y-0.5 text-xs">
                        <template x-if="appointments[tooltipFor].customer_phone">
                            <div class="flex items-center gap-1.5 text-gray-600">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 010 1.414L9.414 8.414a11.042 11.042 0 005.516 5.516l1.293-1.293a1 1 0 011.414 0l2.414 2.414a1 1 0 01.293.707V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                <span x-text="appointments[tooltipFor].customer_phone"></span>
                            </div>
                        </template>
                        <template x-if="appointments[tooltipFor].customer_email">
                            <div class="flex items-center gap-1.5 text-gray-600 truncate">
                                <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <span class="truncate" x-text="appointments[tooltipFor].customer_email"></span>
                            </div>
                        </template>
                    </div>
                    <template x-if="appointments[tooltipFor].notes">
                        <div class="mt-2 pt-2 border-t border-gray-100 text-xs text-gray-600 italic line-clamp-3" x-text="appointments[tooltipFor].notes"></div>
                    </template>
                    <div class="mt-2 pt-2 border-t border-gray-100 text-[10px] text-gray-400">
                        Cliquez pour modifier
                    </div>
                </div>
            </template>
        </div>

        {{-- Modal créer/modifier RDV --}}
        <div x-show="showForm" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showForm = false">
            <div class="bg-white rounded-lg shadow-xl max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto">
                <h2 class="text-lg font-semibold mb-4" x-text="form.id ? 'Modifier le rendez-vous' : 'Ajouter un rendez-vous'"></h2>

                <form method="POST" :action="formAction()" class="space-y-3">
                    @csrf
                    <template x-if="form.id"><input type="hidden" name="_method" value="PATCH"></template>

                    <div>
                        <label class="block text-sm font-medium mb-1">Praticien</label>
                        <select name="practitioner_id" x-model="form.practitioner_id" required class="w-full border rounded-lg px-3 py-2">
                            @foreach($allPractitioners as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Prestation</label>
                        <select x-model="form.service_id" @change="onServiceChange()" name="service_id" class="w-full border rounded-lg px-3 py-2">
                            <option value="">Autre / libre</option>
                            <template x-for="s in services" :key="s.id">
                                <option :value="s.id" x-text="s.name"></option>
                            </template>
                        </select>
                    </div>
                    <div x-show="!form.service_id">
                        <label class="block text-sm font-medium mb-1">Nom de la prestation</label>
                        <input type="text" name="service_name" x-model="form.service_name" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-sm font-medium mb-1">Durée (min)</label>
                            <input type="number" name="duration_minutes" x-model.number="form.duration_minutes" min="5" max="600" step="5" required class="w-full border rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Date</label>
                            <input type="date" name="date" x-model="form.date" required class="w-full border rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Heure</label>
                            <input type="time" name="time" x-model="form.time" step="900" required class="w-full border rounded-lg px-3 py-2">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Client</label>
                        <input type="text" name="customer_name" x-model="form.customer_name" required class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium mb-1">Email</label>
                            <input type="email" name="customer_email" x-model="form.customer_email" class="w-full border rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Téléphone</label>
                            <input type="tel" name="customer_phone" x-model="form.customer_phone" class="w-full border rounded-lg px-3 py-2">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Note</label>
                        <input type="text" name="notes" x-model="form.notes" class="w-full border rounded-lg px-3 py-2">
                    </div>

                    <template x-if="form.id">
                        <div class="bg-gray-50 border rounded-lg p-3 space-y-2">
                            <div class="text-xs font-medium text-gray-600">Statut actuel : <span class="font-semibold" x-text="statusLabel(form.status)"></span></div>
                            <label class="flex items-center gap-2 text-sm" x-show="form.customer_email">
                                <input type="checkbox" name="notify_customer" value="1" x-model="form.notify_customer">
                                Notifier le client par email du nouveau créneau
                            </label>
                        </div>
                    </template>

                    <div class="flex flex-wrap justify-between gap-2 pt-3 border-t">
                        <template x-if="form.id">
                            <div class="flex flex-wrap gap-2">
                                <template x-for="(lbl, st) in statusActions" :key="st">
                                    <button type="button" x-show="form.status !== st" @click="changeStatus(st)"
                                            :class="st === 'cancelled' ? 'border-red-300 text-red-600 hover:bg-red-50' : 'border-gray-300 text-gray-700 hover:bg-gray-50'"
                                            class="text-xs px-3 py-1.5 rounded border" x-text="lbl"></button>
                                </template>
                                <button type="button" @click="confirmDelete()" class="text-xs px-3 py-1.5 rounded border border-red-300 text-red-600 hover:bg-red-50">Supprimer</button>
                            </div>
                        </template>
                        <div class="flex gap-2 ml-auto">
                            <button type="button" @click="showForm = false" class="px-4 py-2 text-gray-500 text-sm">Annuler</button>
                            <button type="submit" class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700 text-sm" x-text="form.id ? 'Enregistrer' : 'Ajouter'"></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Formulaires cachés pour statut + suppression --}}
        <form method="POST" :action="`${basePath}/${form.id}/statut`" class="hidden" x-ref="statusForm">
            @csrf @method('PATCH')
            <input type="hidden" name="status" x-ref="statusInput">
        </form>
        <form method="POST" :action="`${basePath}/${form.id}`" class="hidden" x-ref="deleteForm">
            @csrf @method('DELETE')
        </form>

        {{-- Modal blocage de plage --}}
        <div x-show="showBlock" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showBlock = false">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                <h2 class="text-lg font-semibold mb-4">Bloquer une plage</h2>
                <form method="POST" action="{{ route('client.etablissement.agenda.blocage', $etablissement) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium mb-1">Praticien</label>
                        <select name="practitioner_id" required class="w-full border rounded-lg px-3 py-2">
                            @foreach($allPractitioners as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium mb-1">Début</label>
                            <input type="datetime-local" name="starts_at" :value="date + 'T09:00'" required class="w-full border rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Fin</label>
                            <input type="datetime-local" name="ends_at" :value="date + 'T12:00'" required class="w-full border rounded-lg px-3 py-2">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Motif (optionnel)</label>
                        <input type="text" name="reason" placeholder="Congé, formation, pause…" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="showBlock = false" class="px-4 py-2 text-gray-500">Annuler</button>
                        <button type="submit" class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700">Bloquer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('agenda', (cfg) => ({
                services: cfg.services,
                appointments: cfg.appointments,
                startHour: cfg.startHour,
                endHour: cfg.endHour,
                basePath: cfg.basePath,
                date: cfg.date,

                showForm: false,
                showBlock: false,
                form: {},

                // Tooltip enrichi au survol des cards RDV.
                tooltipFor: null,
                tooltipPos: { x: 0, y: 0 },
                tooltipTimer: null,

                statusActions: {
                    completed: 'Marquer honoré',
                    no_show: 'Marquer absent',
                    cancelled: 'Annuler le RDV',
                },

                init() {
                    this.resetForm();
                },

                resetForm(overrides = {}) {
                    this.form = Object.assign({
                        id: null,
                        practitioner_id: '',
                        service_id: '',
                        service_name: '',
                        duration_minutes: 30,
                        date: this.date,
                        time: '09:00',
                        customer_name: '',
                        customer_email: '',
                        customer_phone: '',
                        notes: '',
                        status: 'confirmed',
                        notify_customer: false,
                    }, overrides);
                },

                openCreate(overrides = {}) {
                    this.resetForm(overrides);
                    this.showForm = true;
                },

                openEdit(id) {
                    const a = this.appointments[id];
                    if (!a) return;
                    this.form = {
                        id: a.id,
                        practitioner_id: a.practitioner_id,
                        service_id: a.service_id || '',
                        service_name: a.service_name || '',
                        duration_minutes: a.duration_minutes,
                        date: a.date,
                        time: a.time,
                        customer_name: a.customer_name || '',
                        customer_email: a.customer_email || '',
                        customer_phone: a.customer_phone || '',
                        notes: a.notes || '',
                        status: a.status,
                        notify_customer: false,
                    };
                    this.showForm = true;
                },

                // En vue jour : date omise -> date courante de la page.
                // En vue semaine : on passe la date du jour cliqué.
                onColumnClick($event, practitionerId, date) {
                    const t = $event.target;
                    if (t.closest('[data-appointment-card]') || t.closest('[data-timeoff-card]')) return;
                    const rect = $event.currentTarget.getBoundingClientRect();
                    const y = $event.clientY - rect.top;
                    const absMin = Math.max(0, Math.min((this.endHour - this.startHour) * 60 - 15, Math.round(y / 15) * 15));
                    const hour = this.startHour + Math.floor(absMin / 60);
                    const min = absMin % 60;
                    const time = String(hour).padStart(2, '0') + ':' + String(min).padStart(2, '0');
                    this.openCreate({ practitioner_id: practitionerId, time, date: date || this.date });
                },

                onServiceChange() {
                    const s = this.services.find(x => x.id == this.form.service_id);
                    if (s) {
                        this.form.duration_minutes = s.duration;
                        this.form.service_name = s.name;
                    }
                },

                formAction() {
                    return this.form.id
                        ? `${this.basePath}/${this.form.id}`
                        : `${this.basePath}/manuel`;
                },

                statusLabel(st) {
                    return ({ confirmed: 'Confirmé', completed: 'Honoré', no_show: 'Absent', cancelled: 'Annulé' })[st] || st;
                },

                statusBadgeClass(st) {
                    return ({
                        confirmed: 'bg-blue-100 text-blue-800',
                        completed: 'bg-emerald-100 text-emerald-800',
                        no_show: 'bg-gray-200 text-gray-600',
                        cancelled: 'bg-red-100 text-red-700',
                    })[st] || 'bg-gray-100 text-gray-700';
                },

                // Heure de fin d'un RDV (time + duration_minutes), format HH:mm.
                endTime(a) {
                    if (!a?.time) return '';
                    const [h, m] = a.time.split(':').map(Number);
                    const total = h * 60 + m + (a.duration_minutes || 0);
                    const eh = Math.floor(total / 60) % 24;
                    const em = total % 60;
                    return String(eh).padStart(2, '0') + ':' + String(em).padStart(2, '0');
                },

                // Tooltip : placé à droite du RDV si possible, sinon à gauche.
                // Léger délai à l'apparition pour ne pas pop pendant un déplacement rapide.
                showTooltip(id, $event) {
                    clearTimeout(this.tooltipTimer);
                    const card = $event.currentTarget;
                    const rect = card.getBoundingClientRect();
                    const W = 288; // largeur tooltip ~ w-72
                    const H = 220; // estimation max hauteur
                    let x = rect.right + 8;
                    if (x + W > window.innerWidth - 8) x = rect.left - W - 8;
                    if (x < 8) x = 8;
                    let y = rect.top;
                    if (y + H > window.innerHeight - 8) y = window.innerHeight - H - 8;
                    if (y < 8) y = 8;
                    this.tooltipPos = { x, y };
                    this.tooltipTimer = setTimeout(() => { this.tooltipFor = id; }, 120);
                },

                hideTooltip() {
                    clearTimeout(this.tooltipTimer);
                    this.tooltipFor = null;
                },

                changeStatus(status) {
                    if (status === 'cancelled' && !confirm('Annuler ce rendez-vous ?')) return;
                    this.$refs.statusInput.value = status;
                    this.$refs.statusForm.submit();
                },

                confirmDelete() {
                    if (!confirm('Supprimer définitivement ce rendez-vous ?')) return;
                    this.$refs.deleteForm.submit();
                },
            }));
        });
    </script>
    @endpush
</x-layouts.app>
