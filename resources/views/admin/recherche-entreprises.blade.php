@extends('admin.layouts.app')

@section('content')
<div x-data="rechercheEntreprises()">
    <h1 class="text-2xl font-bold mb-6">Recherche d'établissements</h1>

    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <form @submit.prevent="search()" class="grid sm:grid-cols-3 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium mb-1">Recherche</label>
                <input type="text" x-model="query" class="w-full border rounded-lg px-3 py-2" placeholder="institut de beauté Caen, spa Lyon, ...">
            </div>
            <div class="flex items-end">
                <button type="submit" :disabled="loading" class="w-full bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700 disabled:opacity-50">
                    <span x-show="!loading">Rechercher</span>
                    <span x-show="loading">Recherche...</span>
                </button>
            </div>
        </form>
    </div>

    <template x-if="searched">
        <div>
            <p class="text-sm text-gray-500 mb-4"><span x-text="results.length"></span> résultat(s)</p>

            <template x-if="results.length === 0 && !loading">
                <p class="text-gray-500">Aucun résultat.</p>
            </template>

            <template x-for="r in results" :key="r.place_id">
                <div class="bg-white rounded-lg shadow-sm border p-5 mb-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-bold text-lg text-gray-900" x-text="r.nom"></h3>
                            <p class="text-sm text-gray-500" x-text="r.adresse"></p>
                        </div>
                        <div class="text-right flex-shrink-0 ml-4" x-show="r.note">
                            <span class="text-lg font-bold text-yellow-500" x-text="r.note"></span><span class="text-sm text-gray-400">/5</span>
                            <p class="text-xs text-gray-400" x-text="r.nb_avis + ' avis'"></p>
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-4 text-sm">
                        <template x-if="r.telephone">
                            <div>
                                <p class="text-gray-500">Téléphone</p>
                                <p class="font-medium" x-text="r.telephone"></p>
                            </div>
                        </template>
                        <template x-if="r.site_web">
                            <div>
                                <p class="text-gray-500">Site web</p>
                                <a :href="r.site_web" target="_blank" class="text-pink-600 hover:underline text-xs break-all" x-text="r.site_web"></a>
                            </div>
                        </template>
                        <template x-if="r.description">
                            <div>
                                <p class="text-gray-500">Description</p>
                                <p class="font-medium" x-text="r.description"></p>
                            </div>
                        </template>
                        <template x-if="r.latitude">
                            <div>
                                <p class="text-gray-500">Coordonnées</p>
                                <p class="font-medium text-xs" x-text="r.latitude + ', ' + r.longitude"></p>
                            </div>
                        </template>
                        <template x-if="r.google_maps_url">
                            <div>
                                <p class="text-gray-500">Google Maps</p>
                                <a :href="r.google_maps_url" target="_blank" class="text-pink-600 hover:underline text-xs">Voir sur la carte</a>
                            </div>
                        </template>
                        <template x-if="r.nb_photos > 0">
                            <div>
                                <p class="text-gray-500">Photos</p>
                                <p class="font-medium" x-text="r.nb_photos + ' photo(s)'"></p>
                            </div>
                        </template>
                    </div>

                    <template x-if="r.horaires && r.horaires.length > 0">
                        <div class="mt-4 border-t pt-3">
                            <p class="text-sm text-gray-500 mb-1">Horaires</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-0.5 text-xs text-gray-700">
                                <template x-for="h in r.horaires" :key="h">
                                    <p x-text="h"></p>
                                </template>
                            </div>
                            <template x-if="r.ouvert_maintenant !== null">
                                <p class="mt-1 text-xs font-medium" :class="r.ouvert_maintenant ? 'text-green-600' : 'text-red-500'" x-text="r.ouvert_maintenant ? 'Ouvert actuellement' : 'Fermé actuellement'"></p>
                            </template>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </template>
</div>

<script>
function rechercheEntreprises() {
    return {
        query: '',
        results: [],
        loading: false,
        searched: false,

        async search() {
            if (!this.query) return;
            this.loading = true;
            this.searched = true;
            try {
                const res = await fetch('/admin/pj/google?q=' + encodeURIComponent(this.query));
                const data = await res.json();
                this.results = data.results || [];
            } catch (e) {
                this.results = [];
            }
            this.loading = false;
        },
    };
}
</script>
@endsection
