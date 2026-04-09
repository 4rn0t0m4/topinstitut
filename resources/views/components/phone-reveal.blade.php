@props(['phone', 'etablissementId', 'label' => 'Téléphone', 'portable' => false])

@php
    $encoded = App\Services\AudiotelService::encode($phone);
    $masked = substr($phone, 0, 4) . '......';
@endphp

<div x-data="phoneReveal('{{ $encoded }}', {{ $etablissementId }})" class="mb-3">
    <p class="text-sm text-gray-500">{{ $label }}</p>

    <template x-if="!revealed">
        <button @click="reveal()" class="inline-flex items-center gap-1.5 text-sm font-medium text-pink-600 hover:text-pink-700 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/>
            </svg>
            <span>{{ $masked }}</span>
            <span class="text-xs bg-pink-50 px-1.5 py-0.5 rounded">Afficher</span>
        </button>
    </template>

    <template x-if="revealed">
        <div class="text-sm font-medium">
            <template x-if="mobile">
                <a :href="'tel:' + tel" class="text-pink-600 hover:underline" x-text="display"></a>
            </template>
            <template x-if="!mobile">
                <span x-text="display"></span>
            </template>
            <template x-if="code">
                <span class="text-gray-500 ml-1" x-text="'Code : ' + code"></span>
            </template>
            <template x-if="premium">
                <p class="text-xs text-gray-400 mt-1">Service <span x-text="tarif || '1,99 €/appel'"></span>. Ce n° valable 5 min n'est pas le n° du destinataire mais le n° du service de mise en relation. <a href="http://mise-en-relation.svaplus.fr/" target="_blank" class="underline">Pourquoi ce numéro ?</a></p>
            </template>
        </div>
    </template>

    <template x-if="loading">
        <span class="text-sm text-gray-400">Chargement...</span>
    </template>
</div>
