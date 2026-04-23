<x-layouts.app :noindex="true" title="Mon espace - TopInstitut">
    <div class="max-w-5xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Mon espace</h1>

        <div class="grid md:grid-cols-3 gap-6">
            {{-- Profile card --}}
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h2 class="font-semibold mb-3">Mon profil</h2>
                <p class="text-sm text-gray-600">{{ $user->username }}</p>
                <p class="text-sm text-gray-500">{{ $user->email }}</p>
                <p class="text-sm text-gray-500 mt-2">{{ $user->reviews_count ?? 0 }} avis publiés</p>
                <a href="{{ route('client.profil.edit') }}" class="text-pink-600 text-sm hover:underline mt-3 inline-block">Modifier mon profil</a>
            </div>

            {{-- Establishments --}}
            <div class="md:col-span-2">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="font-semibold">Mes établissements</h2>
                </div>

                @forelse($etablissements as $etab)
                    <div class="bg-white rounded-lg shadow-sm border p-4 mb-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <a href="{{ $etab->url }}" class="font-medium hover:text-pink-600">{{ $etab->name }}</a>
                                <p class="text-sm text-gray-500">{{ $etab->type_label }} - {{ $etab->city }}</p>
                            </div>
                            <span class="text-sm text-gray-400">{{ $etab->review_count ?? 0 }} avis</span>
                        </div>
                        <div class="flex flex-wrap gap-x-3 gap-y-1 mt-3 text-sm">
                            <a href="{{ route('client.etablissement.edit', $etab) }}" class="text-pink-600 hover:underline">Coordonnées</a>
                            <a href="{{ route('client.etablissement.presentation', $etab) }}" class="text-pink-600 hover:underline">Présentation</a>
                            <a href="{{ route('client.etablissement.prestations', $etab) }}" class="text-pink-600 hover:underline">Prestations</a>
                            <a href="{{ route('client.etablissement.horaires', $etab) }}" class="text-pink-600 hover:underline">Horaires</a>
                            <a href="{{ route('client.etablissement.photos', $etab) }}" class="text-pink-600 hover:underline">Photos</a>
                            <a href="{{ route('client.etablissement.faq', $etab) }}" class="text-pink-600 hover:underline">FAQ</a>
                            <a href="{{ route('client.etablissement.avis', $etab) }}" class="text-pink-600 hover:underline">Avis</a>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500">Vous ne gérez aucun établissement.</p>
                @endforelse
            </div>
        </div>

        <div class="mt-6 flex gap-4 text-sm">
            <a href="{{ route('client.mes-avis') }}" class="text-pink-600 hover:underline">Voir mes avis</a>
            <a href="{{ route('client.favoris') }}" class="text-pink-600 hover:underline">Mes favoris</a>
        </div>
    </div>
</x-layouts.app>
