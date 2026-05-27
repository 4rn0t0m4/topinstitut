@props(['establishment'])

<div x-data="bookingFlow({
         services: {{ Illuminate\Support\Js::from($establishment->services->where('is_bookable', true)->sortBy(fn($s) => sprintf('%05d-%05d', $s->category?->sort_order ?? 99999, $s->sort_order))->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'category' => $s->category?->name, 'duration_label' => $s->duration_label, 'price' => $s->price_label])->values()) }},
         practitioners: {{ Illuminate\Support\Js::from($establishment->practitioners->where('is_active', true)->map(fn($p) => ['id' => $p->id, 'name' => $p->name])->values()) }},
         slotsUrl: '{{ route('rdv.slots', $establishment) }}',
     })"
     x-show="$store.rdvModal.open"
     @keydown.escape.window="$store.rdvModal.open = false"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="fixed inset-0 bg-black/50" @click="$store.rdvModal.open = false"></div>

    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg z-10 max-h-[90vh] overflow-y-auto" @click.stop>
        <div class="sticky top-0 bg-white border-b px-6 py-4 flex items-center justify-between rounded-t-xl">
            <h2 class="text-lg font-bold text-gray-900">Prendre rendez-vous</h2>
            <button @click="$store.rdvModal.open = false" type="button" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Confirmation (après réservation) --}}
        <div x-show="confirmed" x-cloak class="p-8 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-5">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Rendez-vous confirmé !</h3>
            <p class="text-sm text-gray-500 mb-5">Un email de confirmation a été envoyé à <strong x-text="confirmed?.email"></strong>.</p>
            <div class="bg-gray-50 border rounded-lg p-4 text-left space-y-2 text-sm mb-6">
                <div class="flex justify-between gap-4"><span class="text-gray-500">Prestation</span><span class="font-medium text-right" x-text="confirmed?.service"></span></div>
                <div class="flex justify-between gap-4"><span class="text-gray-500">Praticien</span><span class="font-medium text-right" x-text="confirmed?.practitioner"></span></div>
                <div class="flex justify-between gap-4"><span class="text-gray-500">Date</span><span class="font-medium text-right" x-text="confirmed?.date"></span></div>
            </div>
            <button @click="$store.rdvModal.open = false" type="button" class="bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700 cursor-pointer">Fermer</button>
        </div>

        <div class="p-6" x-show="!confirmed">
            {{-- Fil d'étapes --}}
            <div class="flex items-center gap-2 mb-6 text-xs">
                <template x-for="(label, i) in ['Prestation','Praticien','Créneau','Coordonnées']" :key="i">
                    <div class="flex items-center gap-2">
                        <button type="button" @click="goStep(i+1)" :disabled="i+1 > step"
                                class="flex items-center gap-2" :class="i+1 < step ? 'cursor-pointer group' : 'cursor-default'">
                            <span class="w-6 h-6 rounded-full flex items-center justify-center font-semibold transition"
                                  :class="step > i+1 ? 'bg-green-500 text-white group-hover:bg-green-600' : (step === i+1 ? 'bg-pink-600 text-white' : 'bg-gray-200 text-gray-500')"
                                  x-text="i+1"></span>
                            <span class="hidden sm:inline" :class="step === i+1 ? 'font-semibold text-gray-900' : (i+1 < step ? 'text-gray-500 group-hover:text-pink-600' : 'text-gray-400')" x-text="label"></span>
                        </button>
                        <span x-show="i < 3" class="text-gray-300">—</span>
                    </div>
                </template>
            </div>

            {{-- Étape 1 : prestation --}}
            <div x-show="step === 1">
                <template x-for="group in groupedServices" :key="group.category">
                    <div class="mb-5">
                        <h3 x-show="group.category" class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2" x-text="group.category"></h3>
                        <div class="space-y-2">
                            <template x-for="s in group.items" :key="s.id">
                                <button type="button" @click="selectService(s)"
                                        class="w-full text-left bg-white border rounded-lg p-4 hover:border-pink-400 hover:bg-pink-50/40 cursor-pointer flex justify-between items-center transition">
                                    <span>
                                        <span class="font-medium text-gray-900" x-text="s.name"></span>
                                        <span class="block text-sm text-gray-500" x-text="s.duration_label"></span>
                                    </span>
                                    <span class="text-pink-600 font-semibold" x-text="s.price"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Étape 2 : praticien --}}
            <div x-show="step === 2" x-cloak class="space-y-2">
                <button type="button" @click="selectPractitioner(null)" class="w-full text-left bg-white border rounded-lg p-4 hover:border-pink-400 hover:bg-pink-50/40 cursor-pointer transition">
                    <span class="font-medium text-gray-900">Sans préférence</span>
                    <span class="block text-sm text-gray-500">Premier praticien disponible</span>
                </button>
                <template x-for="p in practitioners" :key="p.id">
                    <button type="button" @click="selectPractitioner(p.id)" class="w-full text-left bg-white border rounded-lg p-4 hover:border-pink-400 hover:bg-pink-50/40 cursor-pointer transition">
                        <span class="font-medium text-gray-900" x-text="p.name"></span>
                    </button>
                </template>
                <button type="button" @click="step = 1" class="mt-3 text-sm text-gray-500 hover:underline">&larr; Retour</button>
            </div>

            {{-- Étape 3 : date + créneaux --}}
            <div x-show="step === 3" x-cloak>
                <input type="date" x-model="date" :min="minDate" :max="maxDate" @change="loadSlots()" class="border rounded-lg px-3 py-2 mb-4">
                <div x-show="loading" class="text-sm text-gray-500">Recherche des disponibilités…</div>
                <div x-show="!loading && date && slots.length === 0" class="text-sm text-gray-500">Aucun créneau disponible ce jour-là. Essayez une autre date.</div>
                <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                    <template x-for="slot in slots" :key="slot">
                        <button type="button" @click="selectSlot(slot)" class="border rounded-lg py-2 text-sm hover:border-pink-400 hover:bg-pink-50 cursor-pointer transition" x-text="slot"></button>
                    </template>
                </div>
                <button type="button" @click="step = 2" class="mt-4 text-sm text-gray-500 hover:underline">&larr; Retour</button>
            </div>

            {{-- Étape 4 : coordonnées --}}
            <div x-show="step === 4" x-cloak>
                <p class="text-sm text-gray-500 mb-4">
                    <span x-text="selectedService?.name"></span> — <span x-text="formatDate(date)"></span> à <span x-text="time"></span>
                </p>
                <form method="POST" action="{{ route('rdv.store', $establishment) }}" class="space-y-4" @submit.prevent="submit($event)">
                    @csrf
                    <x-honeypot />
                    <input type="hidden" name="service_id" :value="selectedService?.id">
                    <input type="hidden" name="practitioner_id" :value="selectedPractitioner ?? ''">
                    <input type="hidden" name="date" :value="date">
                    <input type="hidden" name="time" :value="time">

                    <p x-show="submitError" x-text="submitError" x-cloak class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2"></p>

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
                        <button type="submit" :disabled="submitting" class="bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700 cursor-pointer disabled:opacity-60">
                            <span x-show="!submitting">Confirmer le rendez-vous</span>
                            <span x-show="submitting">Confirmation…</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@once
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
                submitting: false,
                submitError: '',
                confirmed: null,
                minDate: new Date().toISOString().slice(0, 10),
                maxDate: new Date(Date.now() + 60 * 86400000).toISOString().slice(0, 10),

                get groupedServices() {
                    const groups = [];
                    const byCat = {};
                    this.services.forEach((s) => {
                        const cat = s.category || '';
                        if (!byCat[cat]) { byCat[cat] = { category: cat, items: [] }; groups.push(byCat[cat]); }
                        byCat[cat].items.push(s);
                    });
                    return groups;
                },

                goStep(n) { if (n < this.step) this.step = n; },
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

                async submit(e) {
                    this.submitting = true;
                    this.submitError = '';
                    try {
                        const res = await fetch(e.target.action, {
                            method: 'POST',
                            body: new FormData(e.target),
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        });
                        if (res.ok) {
                            const data = await res.json();
                            this.confirmed = data.appointment;
                        } else {
                            const data = await res.json().catch(() => ({}));
                            this.submitError = data.message || 'Une erreur est survenue. Merci de réessayer.';
                        }
                    } catch (err) {
                        this.submitError = 'Une erreur est survenue. Merci de réessayer.';
                    } finally {
                        this.submitting = false;
                    }
                },
            }));
        });
    </script>
    @endpush
@endonce
