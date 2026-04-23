<x-layouts.app :noindex="true" title="Mes favoris - TopInstitut">
    <div class="max-w-5xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Mes favoris</h1>

        @if($favorites->isNotEmpty())
            <p class="text-sm text-gray-500 mb-4">{{ $favorites->total() }} établissement(s) en favori.</p>
            <div class="space-y-4">
                @foreach($favorites as $etab)
                    <x-etablissement-card :etablissement="$etab" />
                @endforeach
            </div>
            <div class="mt-6">{{ $favorites->links() }}</div>
        @else
            <div class="bg-white border rounded-lg p-8 text-center">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                <p class="text-gray-500">Vous n'avez pas encore de favoris.</p>
                <p class="text-sm text-gray-400 mt-1">Cliquez sur ♥ sur une fiche pour la garder sous la main.</p>
            </div>
        @endif
    </div>
</x-layouts.app>
