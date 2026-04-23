<x-layouts.app :noindex="true" :title="'Prestations & tarifs - ' . $etablissement->name">
    <div class="max-w-3xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-1">Prestations & tarifs</h1>
        <p class="text-sm text-gray-500 mb-6">{{ $etablissement->name }}</p>

        <form method="POST" action="{{ route('client.etablissement.prestations.update', $etablissement) }}"
              x-data='{ services: @json($etablissement->services ?? []) }'
              class="bg-white rounded-lg shadow-sm border p-6">
            @csrf @method('PUT')

            <div class="space-y-3">
                <template x-for="(svc, i) in services" :key="i">
                    <div class="grid grid-cols-12 gap-2 items-start">
                        <div class="col-span-12 sm:col-span-4">
                            <input type="text" :name="`services[${i}][name]`" x-model="svc.name" placeholder="Nom (Manucure, Épilation jambes...)" class="w-full border rounded-lg px-3 py-2 text-sm" required>
                        </div>
                        <div class="col-span-6 sm:col-span-2">
                            <input type="text" :name="`services[${i}][duration]`" x-model="svc.duration" placeholder="Durée" class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div class="col-span-6 sm:col-span-2">
                            <input type="text" :name="`services[${i}][price]`" x-model="svc.price" placeholder="Prix (45€)" class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div class="col-span-11 sm:col-span-3">
                            <input type="text" :name="`services[${i}][description]`" x-model="svc.description" placeholder="Description (optionnel)" class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div class="col-span-1">
                            <button type="button" @click="services.splice(i, 1)" class="text-red-500 hover:text-red-700 p-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                </template>

                <p x-show="services.length === 0" class="text-sm text-gray-500 italic">Aucune prestation pour l'instant.</p>

                <button type="button" @click="services.push({name:'',duration:'',price:'',description:''})" class="mt-2 inline-flex items-center gap-1 text-sm text-pink-600 hover:text-pink-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Ajouter une prestation
                </button>
            </div>

            <button type="submit" class="mt-6 bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700">Enregistrer</button>
        </form>
    </div>
</x-layouts.app>
