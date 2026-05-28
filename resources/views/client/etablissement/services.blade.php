<x-layouts.app :noindex="true" :title="'Prestations & tarifs - ' . $etablissement->name">
    @php
        $catData = $etablissement->serviceCategories->map(fn ($c) => [
            'cid' => 'c'.$c->id,
            'id' => $c->id,
            'name' => $c->name,
            'description' => $c->description,
            'services_count' => $c->services_count ?? 0,
        ])->values();
        $svcData = $etablissement->services->map(fn ($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'category_cid' => $s->service_category_id ? 'c'.$s->service_category_id : '',
            'duration_minutes' => $s->duration_minutes,
            'price' => $s->price,
            'description' => $s->description,
            'is_bookable' => (bool) $s->is_bookable,
        ])->values();
    @endphp

    <div class="max-w-4xl mx-auto px-4 py-8"
         x-data="catalogue({
             categories: {{ \Illuminate\Support\Js::from($catData) }},
             services: {{ \Illuminate\Support\Js::from($svcData) }},
         })">
        <h1 class="text-2xl font-bold mb-1">Prestations & tarifs</h1>
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

        <form method="POST" action="{{ route('client.etablissement.prestations.update', $etablissement) }}">
            @csrf @method('PUT')

            {{-- ───── Catégories ───── --}}
            <section class="bg-white rounded-lg shadow-sm border p-6 mb-6">
                <h2 class="font-semibold mb-1">Catégories</h2>
                <p class="text-sm text-gray-500 mb-4">Regroupez vos prestations (ex. Épilation, Soins du visage). L'ordre est celui affiché aux clients.</p>

                <div class="space-y-3">
                    <template x-for="(cat, i) in categories" :key="cat.cid">
                        <div class="flex items-start gap-2 border-b last:border-0 pb-3 last:pb-0">
                            <div class="flex flex-col pt-2 flex-shrink-0">
                                <button type="button" @click="moveCategory(i, -1)" :disabled="i === 0" class="text-gray-300 hover:text-gray-600 disabled:opacity-30 leading-none cursor-pointer" title="Monter">▲</button>
                                <button type="button" @click="moveCategory(i, 1)" :disabled="i === categories.length - 1" class="text-gray-300 hover:text-gray-600 disabled:opacity-30 leading-none cursor-pointer" title="Descendre">▼</button>
                            </div>
                            <input type="hidden" :name="`categories[${i}][cid]`" :value="cat.cid">
                            <input type="hidden" :name="`categories[${i}][id]`" :value="cat.id">
                            <div class="flex-1 space-y-2">
                                <input type="text" :name="`categories[${i}][name]`" x-model="cat.name" placeholder="Nom de la catégorie" required class="w-full border rounded-lg px-3 py-2 text-sm">
                                <textarea :name="`categories[${i}][description]`" x-model="cat.description" placeholder="Description (optionnel) — affichée sous le nom de la catégorie" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
                            </div>
                            <div class="flex flex-col items-end gap-1 flex-shrink-0 pt-2">
                                <span class="text-xs text-gray-400" x-show="cat.id" x-text="(cat.services_count || 0) + ' presta.'"></span>
                                <button type="button" @click="removeCategory(i)" title="Supprimer la catégorie" class="text-red-400 hover:text-red-600 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <p x-show="categories.length === 0" class="text-sm text-gray-500 italic mt-2">Aucune catégorie.</p>

                <button type="button" @click="addCategory()" class="mt-3 inline-flex items-center gap-1 text-sm text-pink-600 hover:text-pink-700 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Ajouter une catégorie
                </button>
                <p class="text-xs text-gray-400 mt-3">Supprimer une catégorie ne supprime pas ses prestations : elles deviennent « sans catégorie ».</p>
            </section>

            {{-- ───── Prestations regroupées par catégorie ───── --}}
            <section class="bg-white rounded-lg shadow-sm border p-6">
                <h2 class="font-semibold mb-4">Prestations</h2>

                <template x-for="group in groupedServices" :key="group.cid">
                    <div class="mb-6 last:mb-0">
                        <div class="flex items-center justify-between gap-2 mb-3 pb-2 border-b">
                            <h3 class="text-sm font-semibold text-gray-700">
                                <span x-text="group.name"></span>
                                <span class="text-xs font-normal text-gray-400 ml-1" x-text="'(' + group.items.length + ')'"></span>
                            </h3>
                            <button type="button" @click="addService(group.cid)" class="text-xs text-pink-600 hover:text-pink-700 cursor-pointer flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                Ajouter
                            </button>
                        </div>

                        <p x-show="group.items.length === 0" class="text-xs text-gray-400 italic">Aucune prestation dans cette catégorie.</p>

                        <div class="space-y-3">
                            <template x-for="item in group.items" :key="item.index">
                                <div class="border rounded-lg p-3 bg-gray-50/50">
                                    <input type="hidden" :name="`services[${item.index}][id]`" :value="item.svc.id">
                                    <div class="grid grid-cols-12 gap-2 items-center">
                                        <div class="col-span-12 sm:col-span-4">
                                            <input type="text" :name="`services[${item.index}][name]`" x-model="item.svc.name" placeholder="Nom (Manucure...)" class="w-full border rounded-lg px-3 py-2 text-sm bg-white" required>
                                        </div>
                                        <div class="col-span-6 sm:col-span-3">
                                            <select :name="`services[${item.index}][category_cid]`" x-model="item.svc.category_cid"
                                                    x-init="$nextTick(() => $el.value = item.svc.category_cid)"
                                                    class="w-full border rounded-lg px-3 py-2 text-sm bg-white">
                                                <option value="">— Sans catégorie —</option>
                                                <template x-for="cat in categories" :key="cat.cid">
                                                    <option :value="cat.cid" x-text="cat.name || '(sans nom)'"></option>
                                                </template>
                                            </select>
                                        </div>
                                        <div class="col-span-3 sm:col-span-2">
                                            <input type="number" min="5" max="600" step="5" :name="`services[${item.index}][duration_minutes]`" x-model.number="item.svc.duration_minutes" placeholder="min" title="Durée en minutes" class="w-full border rounded-lg px-3 py-2 text-sm text-center bg-white" required>
                                        </div>
                                        <div class="col-span-3 sm:col-span-2">
                                            <input type="text" :name="`services[${item.index}][price]`" x-model="item.svc.price" placeholder="45€" class="w-full border rounded-lg px-3 py-2 text-sm bg-white">
                                        </div>
                                        <div class="col-span-12 sm:col-span-1 flex items-center justify-end sm:justify-center gap-3">
                                            <label class="flex items-center gap-1 text-xs text-gray-500" title="Réservable en ligne">
                                                <input type="hidden" :name="`services[${item.index}][is_bookable]`" value="0">
                                                <input type="checkbox" :name="`services[${item.index}][is_bookable]`" value="1" x-model="item.svc.is_bookable" class="rounded">
                                                <span class="sm:hidden">Réservable</span>
                                            </label>
                                            <button type="button" @click="removeService(item.index)" title="Supprimer la prestation" class="text-red-400 hover:text-red-600 cursor-pointer">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                    <textarea :name="`services[${item.index}][description]`" x-model="item.svc.description" placeholder="Description (optionnel) — détails affichés sous le nom de la prestation" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm bg-white mt-2"></textarea>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <p class="text-xs text-gray-400 mt-4">La case à cocher rend la prestation réservable en ligne (durée utilisée pour calculer les créneaux).</p>
            </section>

            <button type="submit" class="mt-6 bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700 cursor-pointer">Enregistrer</button>
        </form>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('catalogue', (cfg) => ({
                categories: cfg.categories,
                services: cfg.services,
                nextCid: 1,

                // Regroupe les prestations par catégorie. Toujours inclut une rubrique
                // « Sans catégorie » à la fin (même vide) pour permettre d'y ajouter.
                get groupedServices() {
                    const buckets = new Map();
                    this.categories.forEach((cat) => {
                        buckets.set(cat.cid, { cid: cat.cid, name: cat.name || '(sans nom)', items: [] });
                    });
                    const sansCat = { cid: '', name: 'Sans catégorie', items: [] };
                    this.services.forEach((svc, index) => {
                        const bucket = buckets.get(svc.category_cid) || sansCat;
                        bucket.items.push({ svc, index });
                    });
                    return [...buckets.values(), sansCat];
                },

                addCategory() {
                    this.categories.push({ cid: 'new' + (this.nextCid++), id: null, name: '', description: '', services_count: 0 });
                },
                removeCategory(i) {
                    const cid = this.categories[i].cid;
                    this.services.forEach((s) => { if (s.category_cid === cid) s.category_cid = ''; });
                    this.categories.splice(i, 1);
                },
                moveCategory(i, dir) {
                    const j = i + dir;
                    if (j < 0 || j >= this.categories.length) return;
                    [this.categories[i], this.categories[j]] = [this.categories[j], this.categories[i]];
                },
                addService(categoryCid = '') {
                    this.services.push({ id: null, name: '', category_cid: categoryCid, duration_minutes: 30, price: '', description: '', is_bookable: true });
                },
                removeService(index) {
                    this.services.splice(index, 1);
                },
            }));
        });
    </script>
    @endpush
</x-layouts.app>
