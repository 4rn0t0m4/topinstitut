<x-layouts.app :noindex="true" :title="'Prendre rendez-vous - ' . $establishment->name">
    <div class="max-w-2xl mx-auto px-4 py-8"
         x-data="bookingFlow({
             services: {{ Illuminate\Support\Js::from($establishment->services->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'duration_label' => $s->duration_label, 'price' => $s->price])) }},
             practitioners: {{ Illuminate\Support\Js::from($establishment->practitioners->map(fn($p) => ['id' => $p->id, 'name' => $p->name])) }},
             slotsUrl: '{{ route('rdv.slots', $establishment) }}',
         })">
        <a href="{{ $establishment->url }}" class="text-sm text-pink-600 hover:underline">&larr; {{ $establishment->name }}</a>
        <h1 class="text-2xl font-bold mt-2 mb-6">Prendre rendez-vous</h1>

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        {{-- Fil d'étapes --}}
        <div class="flex items-center gap-2 mb-6 text-xs">
            <template x-for="(label, i) in ['Prestation','Praticien','Créneau','Coordonnées']" :key="i">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full flex items-center justify-center font-semibold"
                          :class="step > i+1 ? 'bg-green-500 text-white' : (step === i+1 ? 'bg-pink-600 text-white' : 'bg-gray-200 text-gray-500')"
                          x-text="i+1"></span>
                    <span class="hidden sm:inline" :class="step === i+1 ? 'font-semibold text-gray-900' : 'text-gray-400'" x-text="label"></span>
                    <span x-show="i < 3" class="text-gray-300">—</span>
                </div>
            </template>
        </div>

        {{-- Étape 1 : prestation --}}
        <div x-show="step === 1" class="space-y-2">
            <h2 class="font-semibold mb-3">Choisissez une prestation</h2>
            <template x-for="s in services" :key="s.id">
                <button type="button" @click="selectService(s)"
                        class="w-full text-left bg-white border rounded-lg p-4 hover:border-pink-400 flex justify-between items-center">
                    <span>
                        <span class="font-medium text-gray-900" x-text="s.name"></span>
                        <span class="block text-sm text-gray-500" x-text="s.duration_label"></span>
                    </span>
                    <span class="text-pink-600 font-semibold" x-text="s.price"></span>
                </button>
            </template>
        </div>

        {{-- Étape 2 : praticien --}}
        <div x-show="step === 2" x-cloak class="space-y-2">
            <h2 class="font-semibold mb-3">Avec qui ?</h2>
            <button type="button" @click="selectPractitioner(null)" class="w-full text-left bg-white border rounded-lg p-4 hover:border-pink-400">
                <span class="font-medium text-gray-900">Sans préférence</span>
                <span class="block text-sm text-gray-500">Premier praticien disponible</span>
            </button>
            <template x-for="p in practitioners" :key="p.id">
                <button type="button" @click="selectPractitioner(p.id)" class="w-full text-left bg-white border rounded-lg p-4 hover:border-pink-400">
                    <span class="font-medium text-gray-900" x-text="p.name"></span>
                </button>
            </template>
            <button type="button" @click="step = 1" class="mt-3 text-sm text-gray-500 hover:underline">&larr; Retour</button>
        </div>

        {{-- Étape 3 : date + créneaux --}}
        <div x-show="step === 3" x-cloak>
            <h2 class="font-semibold mb-3">Choisissez un créneau</h2>
            <input type="date" x-model="date" :min="minDate" :max="maxDate" @change="loadSlots()" class="border rounded-lg px-3 py-2 mb-4">

            <div x-show="loading" class="text-sm text-gray-500">Recherche des disponibilités…</div>
            <div x-show="!loading && date && slots.length === 0" class="text-sm text-gray-500">Aucun créneau disponible ce jour-là. Essayez une autre date.</div>

            <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                <template x-for="slot in slots" :key="slot">
                    <button type="button" @click="selectSlot(slot)"
                            class="border rounded-lg py-2 text-sm hover:border-pink-400 hover:bg-pink-50"
                            x-text="slot"></button>
                </template>
            </div>
            <button type="button" @click="step = 2" class="mt-4 text-sm text-gray-500 hover:underline">&larr; Retour</button>
        </div>

        {{-- Étape 4 : coordonnées --}}
        <div x-show="step === 4" x-cloak>
            <h2 class="font-semibold mb-1">Vos coordonnées</h2>
            <p class="text-sm text-gray-500 mb-4">
                <span x-text="selectedService?.name"></span> —
                <span x-text="formatDate(date)"></span> à <span x-text="time"></span>
            </p>

            <form method="POST" action="{{ route('rdv.store', $establishment) }}" class="bg-white border rounded-lg p-6 space-y-4">
                @csrf
                <x-honeypot />
                <input type="hidden" name="service_id" :value="selectedService?.id">
                <input type="hidden" name="practitioner_id" :value="selectedPractitioner ?? ''">
                <input type="hidden" name="date" :value="date">
                <input type="hidden" name="time" :value="time">

                <div>
                    <label class="block text-sm font-medium mb-1">Nom complet <span class="text-red-500">*</span></label>
                    <input type="text" name="customer_name" value="{{ old('customer_name') }}" required class="w-full border rounded-lg px-3 py-2">
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="customer_email" value="{{ old('customer_email') }}" required class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Téléphone</label>
                        <input type="tel" name="customer_phone" value="{{ old('customer_phone') }}" class="w-full border rounded-lg px-3 py-2">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Note (optionnel)</label>
                    <textarea name="notes" rows="2" class="w-full border rounded-lg px-3 py-2">{{ old('notes') }}</textarea>
                </div>

                <div class="flex items-center justify-between">
                    <button type="button" @click="step = 3" class="text-sm text-gray-500 hover:underline">&larr; Retour</button>
                    <button type="submit" class="bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700">Confirmer le rendez-vous</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('bookingFlow', (cfg) => ({
                step: 1,
                services: cfg.services,
                practitioners: cfg.practitioners,
                slotsUrl: cfg.slotsUrl,
                selectedService: null,
                selectedPractitioner: null,
                date: '',
                time: '',
                slots: [],
                loading: false,
                minDate: new Date().toISOString().slice(0, 10),
                maxDate: new Date(Date.now() + 60 * 86400000).toISOString().slice(0, 10),

                selectService(s) { this.selectedService = s; this.step = 2; },
                selectPractitioner(id) { this.selectedPractitioner = id; this.step = 3; this.slots = []; this.date = ''; },
                selectSlot(slot) { this.time = slot; this.step = 4; },

                async loadSlots() {
                    if (!this.date || !this.selectedService) return;
                    this.loading = true;
                    this.slots = [];
                    try {
                        const params = new URLSearchParams({ service_id: this.selectedService.id, date: this.date });
                        if (this.selectedPractitioner) params.append('practitioner_id', this.selectedPractitioner);
                        const res = await fetch(this.slotsUrl + '?' + params.toString());
                        const data = await res.json();
                        this.slots = data.slots || [];
                    } catch (e) {
                        this.slots = [];
                    } finally {
                        this.loading = false;
                    }
                },

                formatDate(d) {
                    if (!d) return '';
                    return new Date(d + 'T00:00:00').toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' });
                },
            }));
        });
    </script>
    @endpush
</x-layouts.app>
