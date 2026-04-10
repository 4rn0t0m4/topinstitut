@props(['etablissement', 'rank' => null])

<div class="bg-white rounded-lg shadow-sm border p-4 hover:shadow-md transition">
    <div class="flex justify-between items-start">
        <div class="flex items-start gap-3">
            @if($rank)
                <span class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full bg-pink-100 text-pink-600 font-bold text-sm">{{ $rank }}</span>
            @endif
            <div>
            <a href="{{ $etablissement->url }}" class="text-lg font-semibold text-gray-900 hover:text-pink-600">
                {{ $etablissement->titre }}
            </a>
            <p class="text-sm text-gray-500 mt-1">{{ $etablissement->type_label }}</p>
            @if($etablissement->ville)
                <p class="text-sm text-gray-500">{{ $etablissement->adresse }} {{ $etablissement->cp }} {{ $etablissement->ville }}</p>
            @endif
            </div>
        </div>
        <div class="text-right">
            @if($etablissement->nb_avis > 0)
                <div class="flex items-center gap-1">
                    <x-star-rating :rating="$etablissement->moyenne" />
                    <span class="text-sm text-gray-500">{{ number_format($etablissement->moyenne, 1, ',', '') }}</span>
                </div>
                <p class="text-xs text-gray-400 mt-1">{{ $etablissement->nb_avis }} avis</p>
            @endif
        </div>
    </div>
    @if($etablissement->accroche)
        <p class="text-sm text-gray-600 mt-2">{{ Str::limit($etablissement->accroche, 120) }}</p>
    @endif
</div>
