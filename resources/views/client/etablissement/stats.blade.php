<x-layouts.app :noindex="true" :title="'Statistiques - ' . $etablissement->name">
    @php
        $series = $summary['views_series'];
        $maxViews = max($series ?: [0]);
        // Hauteur de barre en % (min 2% pour qu'une valeur >0 reste visible).
        $barPct = function (int $v) use ($maxViews) {
            if ($maxViews === 0) return 0;
            $p = round($v * 100 / $maxViews, 1);
            return $v > 0 ? max(2, $p) : 0;
        };

        $eventList = [
            'phone_click' => ['Clics téléphone', '📞'],
            'directions_click' => ['Clics itinéraire', '🗺️'],
            'website_click' => ['Clics site web', '🌐'],
            'gallery_open' => ['Ouvertures galerie', '🖼️'],
            'booking_modal_open' => ['Ouvertures réservation', '📅'],
        ];

        $phoneClicks = (int) ($summary['events']['phone_click'] ?? 0);
        $modalOpens = (int) ($summary['events']['booking_modal_open'] ?? 0);
    @endphp

    <div class="max-w-7xl mx-auto px-4 py-6">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-bold">Statistiques</h1>
                <p class="text-sm text-gray-500">
                    {{ $etablissement->name }} ·
                    du {{ $summary['start']->locale('fr')->isoFormat('D MMM') }}
                    au {{ $summary['end']->locale('fr')->isoFormat('D MMM YYYY') }}
                </p>
            </div>

            {{-- Sélecteur de période --}}
            <div class="inline-flex border rounded-lg overflow-hidden text-sm">
                @foreach([7 => '7 jours', 30 => '30 jours', 90 => '90 jours'] as $d => $label)
                    <a href="{{ route('client.etablissement.stats', [$etablissement, 'days' => $d]) }}"
                       class="px-3 py-1.5 {{ $days === $d ? 'bg-pink-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }} {{ ! $loop->first ? 'border-l' : '' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Cartes KPI --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
            <div class="bg-white border rounded-lg p-4">
                <div class="text-xs text-gray-500 uppercase tracking-wide">Vues de la fiche</div>
                <div class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($summary['views'], 0, ',', ' ') }}</div>
                @if($summary['best_day'])
                    <div class="text-xs text-gray-400 mt-1">
                        Pic le {{ \Illuminate\Support\Carbon::parse($summary['best_day']['date'])->locale('fr')->isoFormat('D MMM') }}
                        ({{ $summary['best_day']['views'] }})
                    </div>
                @endif
            </div>
            <div class="bg-white border rounded-lg p-4">
                <div class="text-xs text-gray-500 uppercase tracking-wide">RDV pris en ligne</div>
                <div class="text-3xl font-bold text-pink-600 mt-1">{{ number_format($summary['bookings'], 0, ',', ' ') }}</div>
                <div class="text-xs text-gray-400 mt-1">hors annulations</div>
            </div>
            <div class="bg-white border rounded-lg p-4">
                <div class="text-xs text-gray-500 uppercase tracking-wide">Taux de conversion</div>
                <div class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($summary['conversion_rate'], 1, ',', ' ') }} %</div>
                <div class="text-xs text-gray-400 mt-1">RDV / vues</div>
            </div>
            <div class="bg-white border rounded-lg p-4">
                <div class="text-xs text-gray-500 uppercase tracking-wide">Clics téléphone</div>
                <div class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($phoneClicks, 0, ',', ' ') }}</div>
                <div class="text-xs text-gray-400 mt-1">{{ $modalOpens }} ouverture(s) réservation</div>
            </div>
        </div>

        {{-- Graphique des vues --}}
        <div class="bg-white border rounded-lg p-4 mb-6">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold text-gray-900">Évolution des vues</h2>
                <span class="text-xs text-gray-400">{{ count($series) }} jours · max {{ $maxViews }}</span>
            </div>

            @if($summary['views'] === 0)
                <p class="text-sm text-gray-500 py-8 text-center">Aucune vue enregistrée sur la période. Patientez quelques jours pour voir des chiffres.</p>
            @else
                <div class="flex items-end gap-[2px] h-40 border-b border-gray-200">
                    @foreach($series as $date => $count)
                        @php $c = \Illuminate\Support\Carbon::parse($date); @endphp
                        <div class="flex-1 flex items-end h-full group relative">
                            <div class="w-full bg-pink-500 hover:bg-pink-600 rounded-t transition cursor-default"
                                 style="height: {{ $barPct($count) }}%;"
                                 title="{{ $c->locale('fr')->isoFormat('dddd D MMMM') }} : {{ $count }} vue(s)">
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="flex justify-between text-[10px] text-gray-400 mt-1 px-1">
                    <span>{{ $summary['start']->locale('fr')->isoFormat('D MMM') }}</span>
                    <span>{{ $summary['end']->locale('fr')->isoFormat('D MMM') }}</span>
                </div>
            @endif
        </div>

        {{-- Détail des évènements --}}
        <div class="bg-white border rounded-lg overflow-hidden">
            <div class="px-4 py-3 border-b bg-gray-50">
                <h2 class="font-semibold text-gray-900">Engagement & conversions</h2>
                <p class="text-xs text-gray-500">Actions des visiteurs sur la fiche (hors bots, hors visites du propriétaire).</p>
            </div>
            <div class="divide-y">
                @foreach($eventList as $type => [$label, $emoji])
                    @php $val = (int) ($summary['events'][$type] ?? 0); @endphp
                    <div class="flex items-center justify-between gap-4 px-4 py-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="text-xl">{{ $emoji }}</span>
                            <span class="text-sm text-gray-700">{{ $label }}</span>
                        </div>
                        <span class="text-sm font-semibold {{ $val > 0 ? 'text-gray-900' : 'text-gray-300' }}">
                            {{ number_format($val, 0, ',', ' ') }}
                        </span>
                    </div>
                @endforeach
                <div class="flex items-center justify-between gap-4 px-4 py-3 bg-pink-50/40">
                    <div class="flex items-center gap-3">
                        <span class="text-xl">✅</span>
                        <span class="text-sm text-gray-700 font-medium">Réservations confirmées</span>
                    </div>
                    <span class="text-sm font-bold text-pink-600">{{ number_format($summary['bookings'], 0, ',', ' ') }}</span>
                </div>
            </div>
        </div>

        <p class="text-xs text-gray-400 mt-4 text-center">
            Les statistiques excluent les bots et vos propres visites quand vous êtes connecté.
            Mises à jour en temps réel.
        </p>
    </div>
</x-layouts.app>
