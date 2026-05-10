<x-layouts.app
    title="Vos instituts recommandés - Quiz TopInstitut"
    :noindex="true"
>
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Vos instituts recommandés</h1>
            <p class="text-gray-500 mt-2">D'après vos réponses, voici les 3 instituts qui collent le plus à vos envies.</p>
        </div>

        @if($matches->isEmpty())
            <div class="bg-white rounded-lg shadow-sm border p-8 text-center">
                <p class="text-gray-700 mb-4">Désolé, aucun institut ne correspond exactement à vos critères.</p>
                <p class="text-sm text-gray-500 mb-6">Essayez d'élargir la zone ou d'enlever certains filtres.</p>
                <a href="{{ route('quiz') }}" class="bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700">Refaire le quiz</a>
            </div>
        @else
            <div class="space-y-4">
                @foreach($matches as $i => $etab)
                    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
                        <div class="flex">
                            @if($firstPhoto = $etab->photos->first())
                                <img src="{{ $firstPhoto->url }}" alt="{{ $etab->name }}" class="w-32 sm:w-48 h-40 object-cover flex-shrink-0" loading="lazy" decoding="async">
                            @else
                                <div class="w-32 sm:w-48 h-40 bg-gray-100 flex-shrink-0 flex items-center justify-center text-gray-300">
                                    <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4z" clip-rule="evenodd"/></svg>
                                </div>
                            @endif

                            <div class="flex-1 p-4 min-w-0">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="bg-pink-600 text-white text-xs font-semibold px-2 py-0.5 rounded-full">N°{{ $i + 1 }}</span>
                                            @if(! empty($features))
                                                <span class="text-xs text-gray-500">Compatibilité <span class="font-semibold text-pink-600">{{ $etab->_quiz_match_pct }}%</span></span>
                                            @endif
                                        </div>
                                        <h2 class="text-lg font-semibold"><a href="{{ $etab->url }}" class="hover:text-pink-600">{{ $etab->name }}</a></h2>
                                        <p class="text-sm text-pink-600">{{ $etab->type_label }}</p>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        @if($etab->review_count > 0 || $etab->google_rating)
                                            @php $rating = $etab->review_count > 0 ? $etab->rating : $etab->google_rating; @endphp
                                            <div class="flex items-center gap-1">
                                                <x-star-rating :rating="$rating" size="w-4 h-4" />
                                                <span class="text-sm font-semibold">{{ number_format($rating, 1, ',', '') }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <p class="text-sm text-gray-500 mt-2">{{ $etab->city ?: '' }}</p>

                                @if(! empty($etab->features))
                                    <x-features-badges :etablissement="$etab" :compact="true" />
                                @endif

                                <div class="mt-3">
                                    <a href="{{ $etab->url }}" class="inline-block text-sm bg-pink-600 text-white px-4 py-1.5 rounded-lg hover:bg-pink-700">Voir la fiche →</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="text-center mt-8">
                <a href="{{ route('quiz') }}" class="text-sm text-pink-600 hover:underline">Refaire le quiz</a>
            </div>
        @endif
    </div>
</x-layouts.app>
