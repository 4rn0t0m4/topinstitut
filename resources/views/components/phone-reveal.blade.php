@props(['phone', 'etablissementId', 'label' => 'Téléphone', 'portable' => false])

@php
    $encoded = App\Services\AudiotelService::encode($phone);
    $masked = substr($phone, 0, 4) . '......';
@endphp

<div x-data="phoneReveal('{{ $encoded }}', {{ $etablissementId }})">
    <template x-if="!revealed && !loading">
        <button @click="reveal()" class="w-full flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-5 rounded-lg transition cursor-pointer">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/>
            </svg>
            <span>{{ $masked }}</span>
            <span class="text-xs bg-green-500 px-2 py-0.5 rounded-full">Afficher le n°</span>
        </button>
    </template>

    <template x-if="loading">
        <div class="w-full flex items-center justify-center gap-2 bg-green-500 text-white font-semibold py-3 px-5 rounded-lg">
            <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
            Chargement...
        </div>
    </template>

    <template x-if="revealed">
        <div>
            <template x-if="mobile">
                <a :href="'tel:' + tel" class="w-full flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white font-bold text-lg py-3 px-5 rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/>
                    </svg>
                    <span x-text="display"></span>
                </a>
            </template>
            <template x-if="!mobile">
                <div class="w-full flex items-center justify-center gap-2 bg-green-600 text-white font-bold text-lg py-3 px-5 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/>
                    </svg>
                    <span x-text="display"></span>
                </div>
            </template>
            <template x-if="code">
                <p class="text-sm text-gray-500 text-center mt-1" x-text="'Code : ' + code"></p>
            </template>
            <template x-if="premium">
                <p class="text-xs text-gray-400 text-center mt-1"> Ce n° valable 5 min est un n° de mise en relation.</p>
            </template>
        </div>
    </template>
</div>
