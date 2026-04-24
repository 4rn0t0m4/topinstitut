@props(['etablissement'])

@php
    $status = $etablissement->opening_status;
    $next = $etablissement->next_opening;
@endphp

@if($status !== 'unknown')
    <span class="inline-flex items-center gap-1 text-sm font-medium
        @if($status === 'open') text-green-600
        @elseif($status === 'closing_soon') text-orange-500
        @elseif($status === 'opening_soon') text-blue-500
        @else text-red-500
        @endif
    ">
        <span class="w-2 h-2 rounded-full
            @if($status === 'open') bg-green-500
            @elseif($status === 'closing_soon') bg-orange-400
            @elseif($status === 'opening_soon') bg-blue-400
            @else bg-red-400
            @endif
        "></span>
        @if($status === 'open')
            Ouvert
        @elseif($status === 'closing_soon')
            Ferme bientôt
        @elseif($status === 'opening_soon')
            Ouvre bientôt
        @else
            Fermé
            @if($next)
                <span class="font-normal text-gray-500 ml-1">· {{ $next }}</span>
            @endif
        @endif
    </span>
@endif
