@extends('admin.layouts.app')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Suivi de l'import Google Places</h1>

    {{-- État du curseur --}}
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">État de la progression</h2>
        @if($cursor)
            @php
                $queryIndex = (int) $cursor->query_index;
                $totalQueries = count($searchTypes);
                $currentQuery = $searchTypes[$queryIndex] ?? '—';
            @endphp
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <div class="text-xs text-gray-500 uppercase">Requête en cours</div>
                    <div class="text-sm font-medium mt-1">{{ $queryIndex + 1 }}/{{ $totalQueries }}</div>
                    <div class="text-xs text-gray-600 mt-1">« {{ $currentQuery }} »</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 uppercase">Dernier département</div>
                    <div class="text-sm font-medium mt-1">{{ $cursor->last_department_code ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 uppercase">Cycles complets</div>
                    <div class="text-sm font-medium mt-1">{{ $cursor->cycle_count }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 uppercase">Total importés</div>
                    <div class="text-sm font-medium mt-1">{{ number_format($totalImported, 0, ',', ' ') }}</div>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-xs text-gray-500 mb-1">Progression de la requête courante</div>
                @php
                    $completedDepts = $stats->where('code', '<=', $cursor->last_department_code)->count();
                    $totalDepts = $stats->count();
                    $pct = $totalDepts ? round(100 * $completedDepts / $totalDepts) : 0;
                @endphp
                <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-pink-600" style="width: {{ $pct }}%"></div>
                </div>
                <div class="text-xs text-gray-500 mt-1">{{ $completedDepts }}/{{ $totalDepts }} départements parcourus ({{ $pct }}%)</div>
            </div>
            <p class="text-xs text-gray-400 mt-3">Dernière mise à jour : {{ $cursor->updated_at ? \Carbon\Carbon::parse($cursor->updated_at)->format('d/m/Y H:i') : '—' }}</p>
        @else
            <p class="text-gray-500">Aucun import lancé pour l'instant.</p>
        @endif
    </div>

    {{-- Détail par département --}}
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3">Code</th>
                    <th class="text-left px-4 py-3">Département</th>
                    <th class="text-right px-4 py-3">Établissements importés</th>
                    <th class="text-left px-4 py-3">Dernier import</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stats as $row)
                    @php $isCurrent = $cursor && $row->code === $cursor->last_department_code; @endphp
                    <tr class="border-b {{ $isCurrent ? 'bg-pink-50' : 'hover:bg-gray-50' }}">
                        <td class="px-4 py-3 font-mono text-gray-600">
                            {{ $row->code }}
                            @if($isCurrent)
                                <span class="ml-1 bg-pink-600 text-white text-xs px-1.5 py-0.5 rounded-full">actuel</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $row->name }}</td>
                        <td class="px-4 py-3 text-right {{ $row->imported_count > 0 ? 'font-medium' : 'text-gray-400' }}">
                            {{ $row->imported_count }}
                        </td>
                        <td class="px-4 py-3 text-gray-500">
                            {{ $row->last_import_at ? \Carbon\Carbon::parse($row->last_import_at)->format('d/m/Y H:i') : '—' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
