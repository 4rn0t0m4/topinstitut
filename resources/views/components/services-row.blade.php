{{-- Doit être utilisé dans un <template x-for="item in ..." :key="item.index"> où item = { svc, index }. --}}
<div class="border rounded-lg p-3 bg-white">
    <input type="hidden" :name="`services[${item.index}][id]`" :value="item.svc.id">
    <div class="grid grid-cols-12 gap-2 items-center">
        <div class="col-span-12 sm:col-span-4">
            <input type="text" :name="`services[${item.index}][name]`" x-model="item.svc.name"
                   placeholder="Nom (Manucure...)"
                   class="w-full border rounded-lg px-3 py-2 text-sm" required>
        </div>
        <div class="col-span-6 sm:col-span-3">
            <select :name="`services[${item.index}][category_cid]`" x-model="item.svc.category_cid"
                    x-init="$nextTick(() => $el.value = item.svc.category_cid)"
                    required
                    class="w-full border rounded-lg px-3 py-2 text-sm">
                <option value="" disabled>— Choisir une catégorie —</option>
                <template x-for="cat in categories" :key="cat.cid">
                    <option :value="cat.cid" x-text="cat.name || '(sans nom)'"></option>
                </template>
            </select>
        </div>
        <div class="col-span-3 sm:col-span-2">
            <input type="number" min="5" max="600" step="5" :name="`services[${item.index}][duration_minutes]`"
                   x-model.number="item.svc.duration_minutes" placeholder="min" title="Durée en minutes"
                   class="w-full border rounded-lg px-3 py-2 text-sm text-center" required>
        </div>
        <div class="col-span-3 sm:col-span-2">
            <input type="text" :name="`services[${item.index}][price]`" x-model="item.svc.price"
                   placeholder="45€" class="w-full border rounded-lg px-3 py-2 text-sm">
        </div>
        <div class="col-span-12 sm:col-span-1 flex items-center justify-end sm:justify-center gap-3">
            <label class="flex items-center gap-1 text-xs text-gray-500" title="Réservable en ligne">
                <input type="hidden" :name="`services[${item.index}][is_bookable]`" value="0">
                <input type="checkbox" :name="`services[${item.index}][is_bookable]`" value="1"
                       x-model="item.svc.is_bookable" class="rounded">
                <span class="sm:hidden">Réservable</span>
            </label>
            <button type="button" @click="removeService(item.index)"
                    title="Supprimer la prestation"
                    class="text-red-400 hover:text-red-600 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
    <textarea :name="`services[${item.index}][description]`" x-model="item.svc.description"
              placeholder="Description (optionnel) — détails affichés sous le nom de la prestation"
              rows="2" class="w-full border rounded-lg px-3 py-2 text-sm mt-2"></textarea>
</div>
