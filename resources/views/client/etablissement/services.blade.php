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

            {{-- Prestations sans catégorie (données héritées) — l'utilisateur doit assigner --}}
            <section x-show="uncategorized.length > 0" x-cloak class="bg-amber-50 border border-amber-200 rounded-lg p-5 mb-6">
                <h2 class="font-semibold text-amber-800 mb-1">⚠ Prestations à rattacher à une catégorie</h2>
                <p class="text-sm text-amber-700 mb-3">Ces prestations doivent être assignées à une catégorie avant d'enregistrer.</p>
                <div class="space-y-3">
                    <template x-for="item in uncategorized" :key="item.index">
                        <x-services-row />
                    </template>
                </div>
            </section>

            {{-- Bloc principal : catégories avec leurs prestations --}}
            <section class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold">Catégories & prestations</h2>
                    <button type="button" @click="addCategory()" class="inline-flex items-center gap-1 text-sm text-pink-600 hover:text-pink-700 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Ajouter une catégorie
                    </button>
                </div>

                <p x-show="categories.length === 0" class="text-sm text-gray-500 italic py-6 text-center">
                    Aucune catégorie. Créez-en une pour commencer à ajouter des prestations.
                </p>

                <div class="space-y-6">
                    <template x-for="(cat, ci) in categories" :key="cat.cid">
                        <div class="bg-pink-50/40 rounded-lg p-4">
                            {{-- En-tête de catégorie : juste les champs, pas de cadre supplémentaire --}}
                            <div class="flex items-start gap-2 mb-4">
                                <div class="flex flex-col pt-2 flex-shrink-0">
                                    <button type="button" @click="moveCategory(ci, -1)" :disabled="ci === 0" class="text-gray-300 hover:text-gray-600 disabled:opacity-30 leading-none cursor-pointer" title="Monter">▲</button>
                                    <button type="button" @click="moveCategory(ci, 1)" :disabled="ci === categories.length - 1" class="text-gray-300 hover:text-gray-600 disabled:opacity-30 leading-none cursor-pointer" title="Descendre">▼</button>
                                </div>
                                <input type="hidden" :name="`categories[${ci}][cid]`" :value="cat.cid">
                                <input type="hidden" :name="`categories[${ci}][id]`" :value="cat.id">
                                <div class="flex-1 space-y-2">
                                    <input type="text" :name="`categories[${ci}][name]`" x-model="cat.name" placeholder="Nom de la catégorie (Épilation, Soins du visage...)" required class="w-full border-0 bg-white rounded-lg px-3 py-2 text-base font-semibold focus:ring-2 focus:ring-pink-200">
                                    <textarea :name="`categories[${ci}][description]`" x-model="cat.description" placeholder="Description (optionnel)" rows="2" class="w-full border-0 bg-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-pink-200"></textarea>
                                </div>
                                <div class="flex-shrink-0 pt-2">
                                    <button type="button"
                                            @click="removeCategory(ci)"
                                            :disabled="serviceCountForCategory(cat.cid) > 0"
                                            :title="serviceCountForCategory(cat.cid) > 0 ? 'Impossible : la catégorie contient encore des prestations' : 'Supprimer la catégorie'"
                                            :class="serviceCountForCategory(cat.cid) > 0 ? 'text-gray-300 cursor-not-allowed' : 'text-red-400 hover:text-red-600 cursor-pointer'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </div>

                            {{-- Prestations : séparées par des hairlines, pas de cadres individuels --}}
                            <div class="bg-white rounded-lg divide-y">
                                <template x-for="item in servicesForCategory(cat.cid)" :key="item.index">
                                    <x-services-row />
                                </template>
                            </div>

                            <p x-show="servicesForCategory(cat.cid).length === 0" class="text-xs text-gray-400 italic mt-2">Aucune prestation pour le moment.</p>

                            <button type="button" @click="addService(cat.cid)" class="mt-3 inline-flex items-center gap-1 text-sm text-pink-600 hover:text-pink-700 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                Ajouter une prestation
                            </button>
                        </div>
                    </template>
                </div>

                <p class="text-xs text-gray-400 mt-4">La case « Réa » à droite de chaque prestation la rend réservable en ligne. La durée est utilisée pour calculer les créneaux libres.</p>
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

                /** Prestations sans catégorie (données héritées). Indexées dans this.services. */
                get uncategorized() {
                    return this.services
                        .map((svc, index) => ({ svc, index }))
                        .filter((item) => ! item.svc.category_cid);
                },

                /** Prestations rattachées à une catégorie donnée, avec leur index global. */
                servicesForCategory(cid) {
                    return this.services
                        .map((svc, index) => ({ svc, index }))
                        .filter((item) => item.svc.category_cid === cid);
                },

                serviceCountForCategory(cid) {
                    return this.services.filter((s) => s.category_cid === cid).length;
                },

                addCategory() {
                    this.categories.push({ cid: 'new' + (this.nextCid++), id: null, name: '', description: '', services_count: 0 });
                },
                removeCategory(i) {
                    if (this.serviceCountForCategory(this.categories[i].cid) > 0) return;
                    this.categories.splice(i, 1);
                },
                moveCategory(i, dir) {
                    const j = i + dir;
                    if (j < 0 || j >= this.categories.length) return;
                    [this.categories[i], this.categories[j]] = [this.categories[j], this.categories[i]];
                },
                addService(categoryCid) {
                    if (! categoryCid) return; // sécurité : pas d'ajout sans catégorie
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
