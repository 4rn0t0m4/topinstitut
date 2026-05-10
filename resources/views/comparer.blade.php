<x-layouts.app title="Comparateur d'instituts - TopInstitut" :noindex="true">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">Comparateur d'instituts</h1>
            <a href="{{ route('recherche') }}" class="text-sm text-gray-500 hover:text-pink-600">← Retour à la recherche</a>
        </div>

        <div class="overflow-x-auto bg-white rounded-lg shadow-sm border">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b">
                        <th class="text-left p-4 font-medium text-gray-500 w-40">Critère</th>
                        @foreach($establishments as $etab)
                            <th class="text-left p-4 font-semibold align-top min-w-[220px]">
                                @if($firstPhoto = $etab->photos->first())
                                    <img src="{{ $firstPhoto->url }}" alt="{{ $etab->name }}" class="w-full h-32 object-cover rounded mb-2" loading="lazy" decoding="async">
                                @else
                                    <div class="w-full h-32 bg-gray-100 rounded mb-2 flex items-center justify-center text-gray-300">
                                        <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4z" clip-rule="evenodd"/></svg>
                                    </div>
                                @endif
                                <a href="{{ $etab->url }}" class="text-pink-600 hover:underline">{{ $etab->name }}</a>
                                <button type="button"
                                        x-data
                                        @click="$store.compare.toggle({{ $etab->id }}); window.location.href='{{ route('comparer') }}?ids=' + $store.compare.ids.join(',');"
                                        class="block text-xs text-gray-400 hover:text-red-600 mt-1">Retirer</button>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b">
                        <td class="p-4 text-gray-500">Type</td>
                        @foreach($establishments as $etab)
                            <td class="p-4">{{ $etab->type_label }}</td>
                        @endforeach
                    </tr>
                    <tr class="border-b bg-gray-50/50">
                        <td class="p-4 text-gray-500">Note</td>
                        @foreach($establishments as $etab)
                            <td class="p-4">
                                @if($etab->review_count > 0)
                                    <div class="flex items-center gap-1">
                                        <x-star-rating :rating="$etab->rating" size="w-4 h-4" />
                                        <span class="font-medium">{{ number_format($etab->rating, 1, ',', '') }}/5</span>
                                        <span class="text-xs text-gray-400">({{ $etab->review_count }})</span>
                                    </div>
                                @elseif($etab->google_rating)
                                    <div class="flex items-center gap-1">
                                        <x-star-rating :rating="$etab->google_rating" size="w-4 h-4" />
                                        <span class="font-medium">{{ number_format($etab->google_rating, 1, ',', '') }}/5</span>
                                        <span class="text-xs text-gray-400">Google</span>
                                    </div>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    <tr class="border-b">
                        <td class="p-4 text-gray-500">Adresse</td>
                        @foreach($establishments as $etab)
                            <td class="p-4 text-gray-700">{{ trim($etab->address.' '.$etab->postal_code.' '.$etab->city) }}</td>
                        @endforeach
                    </tr>
                    <tr class="border-b bg-gray-50/50">
                        <td class="p-4 text-gray-500">Statut</td>
                        @foreach($establishments as $etab)
                            <td class="p-4"><x-statut-ouverture :etablissement="$etab" /></td>
                        @endforeach
                    </tr>
                    <tr class="border-b">
                        <td class="p-4 text-gray-500">Téléphone</td>
                        @foreach($establishments as $etab)
                            <td class="p-4">{{ $etab->phone ?: '—' }}</td>
                        @endforeach
                    </tr>
                    <tr class="border-b bg-gray-50/50">
                        <td class="p-4 text-gray-500">Site web</td>
                        @foreach($establishments as $etab)
                            <td class="p-4">
                                @if($etab->website)
                                    <a href="{{ $etab->website }}" target="_blank" rel="nofollow noopener" class="text-pink-600 hover:underline">Visiter</a>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    <tr class="border-b">
                        <td class="p-4 text-gray-500">Caractéristiques</td>
                        @foreach($establishments as $etab)
                            <td class="p-4">
                                @if(! empty($etab->features))
                                    <x-features-badges :etablissement="$etab" :compact="true" />
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    <tr class="border-b bg-gray-50/50">
                        <td class="p-4 text-gray-500">Horaires aujourd'hui</td>
                        @foreach($establishments as $etab)
                            @php
                                $today = now()->dayOfWeekIso;
                                $sched = $etab->schedules->firstWhere('day_of_week', $today);
                            @endphp
                            <td class="p-4 text-gray-700">
                                @if($sched && ! $sched->is_closed && $sched->open_am)
                                    {{ substr($sched->open_am, 0, 5) }} – {{ substr($sched->close_pm ?? $sched->close_am, 0, 5) }}
                                @else
                                    <span class="text-gray-400">Fermé</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="p-4 text-gray-500">Action</td>
                        @foreach($establishments as $etab)
                            <td class="p-4">
                                <a href="{{ $etab->url }}" class="inline-block bg-pink-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-pink-700">Voir la fiche</a>
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
