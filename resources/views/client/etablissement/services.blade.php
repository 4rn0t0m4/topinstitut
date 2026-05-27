<x-layouts.app :noindex="true" :title="'Prestations & tarifs - ' . $etablissement->name">
    <div class="max-w-4xl mx-auto px-4 py-8">
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

        <form method="POST" action="{{ route('client.etablissement.prestations.update', $etablissement) }}"
              x-data="{ services: {{ Illuminate\Support\Js::from($etablissement->services->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'duration_minutes' => $s->duration_minutes, 'price' => $s->price, 'description' => $s->description, 'is_bookable' => (bool) $s->is_bookable])->values()) }} }"
              class="bg-white rounded-lg shadow-sm border p-6">
            @csrf @method('PUT')

            <div class="hidden sm:grid grid-cols-12 gap-2 text-xs text-gray-500 mb-2 px-1">
                <span class="col-span-4">Prestation</span>
                <span class="col-span-2 text-center">Durée (min)</span>
                <span class="col-span-2 text-center">Prix</span>
                <span class="col-span-3">Description</span>
                <span class="col-span-1 text-center">Résa</span>
            </div>

            <div class="space-y-3">
                <template x-for="(svc, i) in services" :key="i">
                    <div class="grid grid-cols-12 gap-2 items-center border-b sm:border-0 pb-3 sm:pb-0">
                        <input type="hidden" :name="`services[${i}][id]`" :value="svc.id">
                        <div class="col-span-12 sm:col-span-4">
                            <input type="text" :name="`services[${i}][name]`" x-model="svc.name" placeholder="Nom (Manucure, Épilation jambes...)" class="w-full border rounded-lg px-3 py-2 text-sm" required>
                        </div>
                        <div class="col-span-4 sm:col-span-2">
                            <input type="number" min="5" max="600" step="5" :name="`services[${i}][duration_minutes]`" x-model.number="svc.duration_minutes" placeholder="min" class="w-full border rounded-lg px-3 py-2 text-sm text-center" required>
                        </div>
                        <div class="col-span-4 sm:col-span-2">
                            <input type="text" :name="`services[${i}][price]`" x-model="svc.price" placeholder="45€" class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div class="col-span-10 sm:col-span-3">
                            <input type="text" :name="`services[${i}][description]`" x-model="svc.description" placeholder="Description (optionnel)" class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div class="col-span-1 hidden sm:flex items-center justify-center gap-2">
                            <input type="hidden" :name="`services[${i}][is_bookable]`" value="0">
                            <input type="checkbox" :name="`services[${i}][is_bookable]`" value="1" x-model="svc.is_bookable" title="Réservable en ligne" class="rounded">
                            <button type="button" @click="services.splice(i, 1)" title="Supprimer" class="text-red-400 hover:text-red-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <label class="col-span-12 sm:hidden flex items-center gap-2 text-sm">
                            <input type="hidden" :name="`services[${i}][is_bookable]`" value="0">
                            <input type="checkbox" :name="`services[${i}][is_bookable]`" value="1" x-model="svc.is_bookable" class="rounded">
                            Réservable en ligne
                            <button type="button" @click="services.splice(i, 1)" class="ml-auto text-red-500 hover:text-red-700">Supprimer</button>
                        </label>
                    </div>
                </template>

                <p x-show="services.length === 0" class="text-sm text-gray-500 italic">Aucune prestation pour l'instant.</p>

                <button type="button" @click="services.push({id:null,name:'',duration_minutes:30,price:'',description:'',is_bookable:true})" class="mt-2 inline-flex items-center gap-1 text-sm text-pink-600 hover:text-pink-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Ajouter une prestation
                </button>
            </div>

            <p class="text-xs text-gray-400 mt-4">La case « Résa » rend la prestation réservable en ligne (durée utilisée pour calculer les créneaux).</p>

            <button type="submit" class="mt-6 bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700">Enregistrer</button>
        </form>
    </div>
</x-layouts.app>
