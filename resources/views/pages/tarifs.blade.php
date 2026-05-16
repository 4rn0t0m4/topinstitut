<x-layouts.app
    title="Tarifs Pro - TopInstitut"
    description="Boostez la visibilité de votre institut de beauté sur TopInstitut. Forfaits Gratuit, Premium et Sponsorisé."
>
    <div class="max-w-5xl mx-auto px-4 py-12">
        <div class="text-center mb-12">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900">Pour les professionnels</h1>
            <p class="text-gray-500 mt-3">Choisissez le forfait qui correspond à vos besoins. Sans engagement.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Gratuit --}}
            <div class="bg-white rounded-lg border p-6 flex flex-col">
                <h2 class="text-lg font-semibold">Gratuit</h2>
                <p class="text-3xl font-bold mt-2">0 €<span class="text-sm font-normal text-gray-500">/mois</span></p>
                <p class="text-sm text-gray-500 mt-1">Pour démarrer</p>

                <ul class="space-y-2 mt-6 text-sm flex-1">
                    <li class="flex items-start gap-2"><span class="text-green-500">✓</span> Fiche établissement standard</li>
                    <li class="flex items-start gap-2"><span class="text-green-500">✓</span> Coordonnées et horaires</li>
                    <li class="flex items-start gap-2"><span class="text-green-500">✓</span> Réception des avis et messages</li>
                    <li class="flex items-start gap-2"><span class="text-green-500">✓</span> Jusqu'à <strong>3 photos</strong></li>
                    <li class="flex items-start gap-2 text-gray-400"><span>—</span> Pas de prestations détaillées</li>
                    <li class="flex items-start gap-2 text-gray-400"><span>—</span> Pas de badge vérifié</li>
                </ul>

                <a href="{{ route('etablissement.create') }}" class="mt-6 block text-center border-2 border-gray-300 text-gray-700 font-semibold py-2 rounded-lg hover:border-gray-400">Commencer gratuitement</a>
            </div>

            {{-- Premium (en avant) --}}
            <div class="bg-white rounded-lg border-2 border-pink-600 p-6 flex flex-col relative shadow-lg">
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-pink-600 text-white text-xs font-semibold px-3 py-1 rounded-full uppercase">Recommandé</span>
                <h2 class="text-lg font-semibold text-pink-600">Premium</h2>
                <p class="text-3xl font-bold mt-2">19 €<span class="text-sm font-normal text-gray-500">/mois</span></p>
                <p class="text-sm text-gray-500 mt-1">Tout ce qu'il faut pour convertir</p>

                <ul class="space-y-2 mt-6 text-sm flex-1">
                    <li class="flex items-start gap-2"><span class="text-green-500">✓</span> Tout du forfait Gratuit, plus :</li>
                    <li class="flex items-start gap-2"><span class="text-green-500">✓</span> <strong>Photos illimitées</strong></li>
                    <li class="flex items-start gap-2"><span class="text-green-500">✓</span> Prestations détaillées avec tarifs</li>
                    <li class="flex items-start gap-2"><span class="text-green-500">✓</span> <strong>Badge « Vérifié »</strong> bleu</li>
                    <li class="flex items-start gap-2"><span class="text-green-500">✓</span> Réponse aux avis clients</li>
                    <li class="flex items-start gap-2"><span class="text-green-500">✓</span> Position prioritaire dans la recherche</li>
                    <li class="flex items-start gap-2"><span class="text-green-500">✓</span> Dashboard statistiques de vues</li>
                </ul>

                <a href="{{ route('contact') }}?sujet=premium" class="mt-6 block text-center bg-pink-600 text-white font-semibold py-2 rounded-lg hover:bg-pink-700">Souscrire au Premium</a>
            </div>

            {{-- Sponsorisé --}}
            <div class="bg-white rounded-lg border p-6 flex flex-col">
                <h2 class="text-lg font-semibold text-amber-600">Sponsorisé</h2>
                <p class="text-3xl font-bold mt-2">+ 20 €<span class="text-sm font-normal text-gray-500">/mois</span></p>
                <p class="text-sm text-gray-500 mt-1">À ajouter au forfait Premium</p>

                <ul class="space-y-2 mt-6 text-sm flex-1">
                    <li class="flex items-start gap-2"><span class="text-green-500">✓</span> Badge <strong>« Sponsorisé »</strong> en or</li>
                    <li class="flex items-start gap-2"><span class="text-green-500">✓</span> Position <strong>n°1</strong> dans la recherche locale</li>
                    <li class="flex items-start gap-2"><span class="text-green-500">✓</span> Mise en avant sur la page de votre ville</li>
                    <li class="flex items-start gap-2"><span class="text-green-500">✓</span> Bordure dorée distinctive sur les cards</li>
                    <li class="flex items-start gap-2 text-gray-500"><span class="text-amber-500">i</span> Limité à 3 instituts par ville</li>
                </ul>

                <a href="{{ route('contact') }}?sujet=sponsor" class="mt-6 block text-center border-2 border-amber-500 text-amber-700 font-semibold py-2 rounded-lg hover:bg-amber-50">Devenir sponsor</a>
            </div>
        </div>

        <div class="mt-12 text-center">
            <h3 class="text-lg font-semibold mb-3">Une question ?</h3>
            <p class="text-gray-500 mb-4">Notre équipe vous accompagne pour choisir le bon forfait.</p>
            <a href="{{ route('contact') }}" class="inline-block bg-gray-800 text-white font-semibold px-6 py-2 rounded-lg hover:bg-gray-900">Nous contacter</a>
        </div>
    </div>
</x-layouts.app>
