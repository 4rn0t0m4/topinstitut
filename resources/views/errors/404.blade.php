<x-layouts.app title="Page introuvable - TopInstitut" description="La page que vous cherchez n'existe pas ou a été déplacée.">
    <div class="max-w-4xl mx-auto px-4 py-16 text-center">
        <p class="text-8xl font-bold text-pink-200 mb-4">404</p>
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Page introuvable</h1>
        <p class="text-gray-500 mb-8">La page que vous cherchez n'existe pas, a été déplacée ou supprimée.</p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-12">
            <a href="{{ route('home') }}" class="bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700">Retour à l'accueil</a>
            <a href="{{ route('recherche') }}" class="border border-pink-600 text-pink-600 px-6 py-2 rounded-lg hover:bg-pink-50">Rechercher un institut</a>
        </div>

        @if(isset($suggestions) && $suggestions->isNotEmpty())
            <div class="text-left max-w-2xl mx-auto">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Instituts populaires</h2>
                <div class="space-y-3">
                    @foreach($suggestions as $etablissement)
                        <x-etablissement-card :etablissement="$etablissement" />
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
