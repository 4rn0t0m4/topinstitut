<x-layouts.app title="Page introuvable - TopInstitut" description="La page que vous cherchez n'existe pas ou a été déplacée.">
    <div class="max-w-4xl mx-auto px-4 py-16 text-center">
        <p class="text-8xl font-bold text-pink-200 mb-4">404</p>
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Page introuvable</h1>
        <p class="text-gray-500 mb-8">La page que vous cherchez n'existe pas, a été déplacée ou supprimée.</p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-6">
            <a href="{{ route('home') }}" class="bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700">Retour à l'accueil</a>
            <a href="{{ route('recherche') }}" class="border border-pink-600 text-pink-600 px-6 py-2 rounded-lg hover:bg-pink-50">Rechercher un institut</a>
        </div>

        <div x-data="{ locating: false, error: '' }" class="mb-12">
            <button type="button"
                    @click="
                        if (!navigator.geolocation) { error = 'Géolocalisation non supportée.'; return; }
                        locating = true; error = '';
                        navigator.geolocation.getCurrentPosition(
                            pos => { window.location.href = '{{ route('recherche') }}?lat=' + pos.coords.latitude + '&lng=' + pos.coords.longitude + '&r=10'; },
                            () => { locating = false; error = 'Autorisation refusée ou position indisponible.'; },
                            { timeout: 10000 }
                        );
                    "
                    :disabled="locating"
                    class="inline-flex items-center gap-2 text-sm text-pink-600 hover:text-pink-700 underline disabled:opacity-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                <span x-show="!locating">Trouver les instituts près de moi</span>
                <span x-show="locating">Localisation en cours...</span>
            </button>
            <p x-show="error" x-text="error" class="text-red-500 text-xs mt-2" x-cloak></p>
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
