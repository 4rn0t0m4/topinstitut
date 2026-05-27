<x-layouts.app :noindex="true" :title="'Catégories de prestations - ' . $etablissement->name">
    @php
        $catData = $etablissement->serviceCategories->map(fn ($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'services_count' => $c->services_count ?? 0,
        ])->values();
    @endphp

    <div class="max-w-2xl mx-auto px-4 py-8">
        <a href="{{ route('client.etablissement.prestations', $etablissement) }}" class="text-sm text-pink-600 hover:underline">&larr; Retour aux prestations</a>
        <h1 class="text-2xl font-bold mt-2 mb-1">Catégories de prestations</h1>
        <p class="text-sm text-gray-500 mb-6">Regroupez vos prestations par catégorie (ex. Épilation, Soins du visage). L'ordre ci-dessous est celui affiché aux clients.</p>

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

        <form method="POST" action="{{ route('client.etablissement.categories.update', $etablissement) }}"
              class="bg-white rounded-lg shadow-sm border p-6"
              x-data="{
                  categories: {{ \Illuminate\Support\Js::from($catData) }},
                  move(i, dir) {
                      const j = i + dir;
                      if (j < 0 || j >= this.categories.length) return;
                      const tmp = this.categories[i];
                      this.categories[i] = this.categories[j];
                      this.categories[j] = tmp;
                  },
              }">
            @csrf @method('PUT')

            <div class="space-y-2">
                <template x-for="(cat, i) in categories" :key="i">
                    <div class="flex items-center gap-2">
                        <div class="flex flex-col">
                            <button type="button" @click="move(i, -1)" :disabled="i === 0" class="text-gray-300 hover:text-gray-600 disabled:opacity-30 leading-none" title="Monter">▲</button>
                            <button type="button" @click="move(i, 1)" :disabled="i === categories.length - 1" class="text-gray-300 hover:text-gray-600 disabled:opacity-30 leading-none" title="Descendre">▼</button>
                        </div>
                        <input type="hidden" :name="`categories[${i}][id]`" :value="cat.id">
                        <input type="text" :name="`categories[${i}][name]`" x-model="cat.name" placeholder="Nom de la catégorie" required
                               class="flex-1 border rounded-lg px-3 py-2 text-sm">
                        <span class="text-xs text-gray-400 w-20 text-right" x-show="cat.id" x-text="(cat.services_count || 0) + ' presta.'"></span>
                        <button type="button" @click="categories.splice(i, 1)" title="Supprimer" class="text-red-400 hover:text-red-600 flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>
            </div>

            <p x-show="categories.length === 0" class="text-sm text-gray-500 italic">Aucune catégorie pour l'instant.</p>

            <button type="button" @click="categories.push({id:null,name:'',services_count:0})" class="mt-3 inline-flex items-center gap-1 text-sm text-pink-600 hover:text-pink-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Ajouter une catégorie
            </button>

            <p class="text-xs text-gray-400 mt-4">Supprimer une catégorie ne supprime pas les prestations : elles deviennent simplement « sans catégorie ».</p>

            <button type="submit" class="mt-6 bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700">Enregistrer</button>
        </form>
    </div>
</x-layouts.app>
