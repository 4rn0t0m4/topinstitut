<x-layouts.app :noindex="true" :title="'Agenda - ' . $etablissement->name">
    <div class="max-w-6xl mx-auto px-4 py-8"
         x-data="agenda({
             services: {{ Illuminate\Support\Js::from($services->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'duration' => $s->duration_minutes])) }},
             date: '{{ $date->format('Y-m-d') }}',
         })">

        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-bold">Agenda</h1>
                <p class="text-sm text-gray-500">{{ $etablissement->name }}</p>
            </div>
            <div class="flex gap-2">
                <button type="button" @click="openManual()" class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700 text-sm">+ Rendez-vous</button>
                <button type="button" @click="openBlock()" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 text-sm">Bloquer une plage</button>
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

        {{-- Navigation date --}}
        <div class="flex items-center justify-center gap-3 mb-6">
            <a href="{{ route('client.etablissement.agenda', [$etablissement, 'date' => $date->copy()->subDay()->format('Y-m-d')]) }}" class="px-3 py-1.5 border rounded-lg hover:bg-gray-50">&larr;</a>
            <form method="GET" class="flex items-center gap-2">
                <input type="date" name="date" value="{{ $date->format('Y-m-d') }}" onchange="this.form.submit()" class="border rounded-lg px-3 py-1.5">
            </form>
            <a href="{{ route('client.etablissement.agenda', [$etablissement, 'date' => $date->copy()->addDay()->format('Y-m-d')]) }}" class="px-3 py-1.5 border rounded-lg hover:bg-gray-50">&rarr;</a>
            <a href="{{ route('client.etablissement.agenda', $etablissement) }}" class="text-sm text-pink-600 hover:underline ml-2">Aujourd'hui</a>
        </div>
        <p class="text-center font-medium text-gray-700 mb-6 capitalize">{{ $date->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</p>

        @if($practitioners->isEmpty())
            <div class="bg-white border border-dashed rounded-lg p-8 text-center text-gray-500">
                Aucun praticien actif. <a href="{{ route('client.etablissement.praticiens', $etablissement) }}" class="text-pink-600 hover:underline">Ajoutez-en un</a> pour gérer l'agenda.
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($practitioners as $p)
                    <div class="bg-white border rounded-lg overflow-hidden">
                        <div class="bg-gray-50 px-4 py-2 border-b font-semibold text-sm">{{ $p->name }}</div>
                        <div class="divide-y">
                            @forelse($p->appointments as $rdv)
                                <div class="px-4 py-3">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <div class="font-medium text-gray-900">{{ $rdv->starts_at->format('H:i') }} – {{ $rdv->ends_at->format('H:i') }}</div>
                                            <div class="text-sm text-gray-600 truncate">{{ $rdv->customer_name }}</div>
                                            <div class="text-xs text-gray-400">{{ $rdv->service_name }}</div>
                                            @if($rdv->customer_phone)<div class="text-xs text-gray-400">{{ $rdv->customer_phone }}</div>@endif
                                            @if($rdv->notes)<div class="text-xs text-gray-500 mt-1 italic">{{ $rdv->notes }}</div>@endif
                                        </div>
                                        @php
                                            $badge = ['confirmed' => 'bg-blue-50 text-blue-700', 'completed' => 'bg-green-50 text-green-700', 'no_show' => 'bg-gray-100 text-gray-500'][$rdv->status] ?? 'bg-gray-100';
                                        @endphp
                                        <span class="flex-shrink-0 text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $badge }}">{{ $rdv->status_label }}</span>
                                    </div>
                                    <div class="flex gap-2 mt-2">
                                        @foreach(['completed' => 'Honoré', 'no_show' => 'Absent', 'cancelled' => 'Annuler'] as $st => $lbl)
                                            @if($rdv->status !== $st)
                                                <form method="POST" action="{{ route('client.etablissement.agenda.statut', [$etablissement, $rdv]) }}">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="status" value="{{ $st }}">
                                                    <button type="submit" class="text-xs {{ $st === 'cancelled' ? 'text-red-500 hover:text-red-700' : 'text-pink-600 hover:underline' }}">{{ $lbl }}</button>
                                                </form>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <div class="px-4 py-6 text-center text-sm text-gray-400">Aucun rendez-vous</div>
                            @endforelse

                            @foreach($p->timeOffs as $off)
                                <div class="px-4 py-2 bg-amber-50 flex items-center justify-between">
                                    <span class="text-xs text-amber-700">
                                        Bloqué {{ $off->starts_at->format('H:i') }}–{{ $off->ends_at->format('H:i') }}
                                        @if($off->reason)· {{ $off->reason }}@endif
                                    </span>
                                    <form method="POST" action="{{ route('client.etablissement.agenda.blocage.destroy', [$etablissement, $p, $off]) }}" onsubmit="return confirm('Débloquer cette plage ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs text-amber-700 hover:text-amber-900">✕</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Modal RDV manuel --}}
        <div x-show="showManual" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showManual = false">
            <div class="bg-white rounded-lg shadow-xl max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto">
                <h2 class="text-lg font-semibold mb-4">Ajouter un rendez-vous</h2>
                <form method="POST" action="{{ route('client.etablissement.agenda.manuel', $etablissement) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium mb-1">Praticien</label>
                        <select name="practitioner_id" required class="w-full border rounded-lg px-3 py-2">
                            @foreach($practitioners as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Prestation</label>
                        <select x-model="manualServiceId" @change="onServiceChange()" name="service_id" class="w-full border rounded-lg px-3 py-2">
                            <option value="">Autre / libre</option>
                            <template x-for="s in services" :key="s.id">
                                <option :value="s.id" x-text="s.name"></option>
                            </template>
                        </select>
                    </div>
                    <div x-show="!manualServiceId">
                        <label class="block text-sm font-medium mb-1">Nom de la prestation</label>
                        <input type="text" name="service_name" x-model="manualServiceName" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-sm font-medium mb-1">Durée (min)</label>
                            <input type="number" name="duration_minutes" x-model.number="manualDuration" min="5" max="600" step="5" required class="w-full border rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Date</label>
                            <input type="date" name="date" x-model="manualDate" required class="w-full border rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Heure</label>
                            <input type="time" name="time" required class="w-full border rounded-lg px-3 py-2">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Client</label>
                        <input type="text" name="customer_name" required class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Téléphone</label>
                        <input type="tel" name="customer_phone" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Note</label>
                        <input type="text" name="notes" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="showManual = false" class="px-4 py-2 text-gray-500">Annuler</button>
                        <button type="submit" class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal blocage de plage --}}
        <div x-show="showBlock" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showBlock = false">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                <h2 class="text-lg font-semibold mb-4">Bloquer une plage</h2>
                <form method="POST" action="{{ route('client.etablissement.agenda.blocage', $etablissement) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium mb-1">Praticien</label>
                        <select name="practitioner_id" required class="w-full border rounded-lg px-3 py-2">
                            @foreach($practitioners as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium mb-1">Début</label>
                            <input type="datetime-local" name="starts_at" :value="manualDate + 'T09:00'" required class="w-full border rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Fin</label>
                            <input type="datetime-local" name="ends_at" :value="manualDate + 'T12:00'" required class="w-full border rounded-lg px-3 py-2">
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
                showManual: false,
                showBlock: false,
                manualServiceId: '',
                manualServiceName: '',
                manualDuration: 30,
                manualDate: cfg.date,

                openManual() { this.showManual = true; },
                openBlock() { this.showBlock = true; },
                onServiceChange() {
                    const s = this.services.find(x => x.id == this.manualServiceId);
                    if (s) { this.manualDuration = s.duration; this.manualServiceName = s.name; }
                },
            }));
        });
    </script>
    @endpush
</x-layouts.app>
