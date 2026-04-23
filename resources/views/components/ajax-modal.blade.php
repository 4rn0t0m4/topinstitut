@props([
    'store',
    'title',
    'subtitle' => null,
    'action',
    'successTitle' => 'Envoyé !',
    'successMessage' => 'Votre message a bien été envoyé.',
    'submitLabel' => 'Envoyer',
    'submitLoadingLabel' => 'Envoi en cours...',
])

<div x-data="{ sent: false, sending: false, error: '' }"
     x-show="$store['{{ $store }}'].open"
     @keydown.escape.window="$store['{{ $store }}'].open = false"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="fixed inset-0 bg-black/50" @click="$store['{{ $store }}'].open = false"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg p-6 z-10 max-h-[90vh] overflow-y-auto" @click.stop>
        <button @click="$store['{{ $store }}'].open = false" type="button" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 cursor-pointer">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <h2 class="text-xl font-bold text-gray-900 mb-1">{{ $title }}</h2>
        @if($subtitle)
            <p class="text-sm text-gray-500 mb-4">{{ $subtitle }}</p>
        @else
            <div class="mb-4"></div>
        @endif

        <div x-show="sent" class="text-center py-8">
            <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-lg font-semibold text-gray-900">{{ $successTitle }}</p>
            <p class="text-sm text-gray-500 mt-1">{{ $successMessage }}</p>
            <button @click="$store['{{ $store }}'].open = false" type="button" class="mt-4 bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700 cursor-pointer">Fermer</button>
        </div>

        <form x-show="!sent" @submit.prevent="
            sending = true; error = '';
            fetch($el.action, { method: 'POST', body: new FormData($el), headers: { 'Accept': 'application/json' } })
                .then(async r => {
                    const d = await r.json().catch(() => ({}));
                    if (r.ok) sent = true;
                    else error = d.message || (d.errors ? Object.values(d.errors).flat().join(' ') : 'Erreur');
                })
                .catch(() => { error = 'Erreur réseau.'; })
                .finally(() => { sending = false; });
        " action="{{ $action }}">
            @csrf
            {{ $slot }}
            <p x-show="error" x-text="error" class="text-red-500 text-sm mb-3" x-cloak></p>
            <button type="submit" :disabled="sending" class="w-full bg-pink-600 text-white font-semibold py-3 rounded-lg hover:bg-pink-700 transition disabled:opacity-50 cursor-pointer">
                <span x-show="!sending">{{ $submitLabel }}</span>
                <span x-show="sending">{{ $submitLoadingLabel }}</span>
            </button>
        </form>
    </div>
</div>
