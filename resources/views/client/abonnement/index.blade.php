<x-layouts.app :noindex="true" title="Mes abonnements - TopInstitut">
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold">Mes abonnements</h1>
                <p class="text-sm text-gray-500 mt-1">Gérez les forfaits Premium de vos établissements.</p>
            </div>
            <a href="{{ route('client.dashboard') }}" class="text-sm text-gray-500 hover:text-pink-600">← Retour</a>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm">
                @foreach($errors->all() as $e) <p>{{ $e }}</p> @endforeach
            </div>
        @endif

        @if($establishments->isEmpty())
            <div class="bg-white rounded-lg border p-8 text-center">
                <p class="text-gray-600 mb-4">Vous ne gérez encore aucun établissement.</p>
                <a href="{{ route('etablissement.create') }}" class="bg-pink-600 text-white px-5 py-2 rounded-lg hover:bg-pink-700">Ajouter mon institut</a>
            </div>
        @else
            <div class="space-y-4">
                @foreach($establishments as $etab)
                    <div class="bg-white rounded-lg border p-5">
                        <div class="flex items-start justify-between gap-4 flex-wrap">
                            <div class="min-w-0">
                                <h2 class="font-semibold text-lg">
                                    <a href="{{ $etab->url }}" class="hover:text-pink-600">{{ $etab->name }}</a>
                                </h2>
                                <p class="text-sm text-gray-500">{{ $etab->type_label }} · {{ $etab->city }}</p>

                                <div class="mt-3 flex items-center gap-2 flex-wrap">
                                    @if($etab->is_premium)
                                        <span class="bg-pink-100 text-pink-700 text-xs font-semibold px-2 py-0.5 rounded-full">Premium actif</span>
                                        @if($etab->subscription_ends_at)
                                            <span class="text-xs text-gray-500">jusqu'au {{ $etab->subscription_ends_at->format('d/m/Y') }}</span>
                                        @endif
                                    @else
                                        <span class="bg-gray-100 text-gray-700 text-xs font-semibold px-2 py-0.5 rounded-full">Gratuit</span>
                                    @endif

                                    @if($etab->is_featured)
                                        <span class="bg-amber-100 text-amber-700 text-xs font-semibold px-2 py-0.5 rounded-full">Sponsorisé</span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-col gap-2 shrink-0">
                                @if(! $etab->is_premium)
                                    <form action="{{ route('client.abonnement.checkout', $etab) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="bg-pink-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-pink-700">Passer Premium — 9,90€/mois</button>
                                    </form>
                                @else
                                    <span class="text-xs text-green-600 text-right">✓ Abonnement actif</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if(auth()->user()->stripe_customer_id)
                <div class="mt-8 pt-6 border-t text-center">
                    <a href="{{ route('client.abonnement.portal') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-pink-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h.01M11 15h2m4 0h.01M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Gérer mes moyens de paiement et factures (Stripe)
                    </a>
                </div>
            @endif
        @endif
    </div>
</x-layouts.app>
