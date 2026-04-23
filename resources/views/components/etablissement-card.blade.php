@props(['etablissement', 'rank' => null])

@php
    $firstPhoto = $etablissement->photos->first();
    $photoUrl = $firstPhoto?->url;
@endphp

<div class="relative bg-white rounded-lg shadow-sm border hover:shadow-md transition overflow-hidden">
    <button type="button"
            @click.stop.prevent="$store.favorites.toggle({{ $etablissement->id }})"
            :aria-pressed="$store.favorites.has({{ $etablissement->id }})"
            class="absolute top-2 right-2 z-20 w-9 h-9 bg-white/90 backdrop-blur hover:bg-white rounded-full shadow flex items-center justify-center transition">
        <svg class="w-5 h-5 transition" :class="$store.favorites.has({{ $etablissement->id }}) ? 'text-pink-600 fill-pink-600' : 'text-gray-400 fill-transparent'" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
    </button>
    <a href="{{ $etablissement->url }}" class="block">
    <div class="flex gap-4 items-stretch">
        {{-- Photo --}}
        <div class="flex-shrink-0 w-32 sm:w-40 h-32 sm:h-40 bg-gray-100 relative">
            @if($photoUrl)
                <img src="{{ $photoUrl }}" alt="{{ $etablissement->name }}" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
            @else
                <div class="absolute inset-0 flex items-center justify-center text-gray-300">
                    <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/></svg>
                </div>
            @endif
            @if($rank)
                <span class="absolute top-2 left-2 w-8 h-8 flex items-center justify-center rounded-full bg-pink-600 text-white font-bold text-sm shadow z-10">{{ $rank }}</span>
            @endif
        </div>

        {{-- Content --}}
        <div class="flex-1 p-4 min-w-0">
            <div class="flex justify-between items-start gap-3">
                <div class="min-w-0">
                    <h3 class="text-lg font-semibold text-gray-900 hover:text-pink-600 truncate">{{ $etablissement->name }}</h3>
                    <div class="flex items-center gap-2 mt-1 flex-wrap">
                        <span class="text-sm text-gray-500">{{ $etablissement->type_label }}</span>
                        <x-statut-ouverture :etablissement="$etablissement" />
                    </div>
                    @if($etablissement->city)
                        <p class="text-sm text-gray-500 mt-1 truncate">{{ $etablissement->address }} {{ $etablissement->postal_code }} {{ $etablissement->city }}</p>
                    @endif
                </div>
                <div class="text-right flex-shrink-0">
                    @if($etablissement->review_count > 0)
                        <div class="flex items-center gap-1">
                            <x-star-rating :rating="$etablissement->rating" />
                            <span class="text-sm text-gray-500">{{ number_format($etablissement->rating, 1, ',', '') }}</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">{{ $etablissement->review_count }} avis</p>
                    @elseif($etablissement->google_rating)
                        <div class="flex items-center gap-1">
                            <x-star-rating :rating="$etablissement->google_rating" />
                            <span class="text-sm text-gray-500">{{ number_format($etablissement->google_rating, 1, ',', '') }}</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">{{ $etablissement->google_review_count }} avis Google</p>
                    @endif
                </div>
            </div>
            @if($etablissement->tagline)
                <p class="text-sm text-gray-600 mt-2 line-clamp-2">{{ Str::limit($etablissement->tagline, 120) }}</p>
            @endif
        </div>
    </div>
    </a>
</div>
