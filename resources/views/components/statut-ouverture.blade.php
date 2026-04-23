@props(['etablissement'])

@php
    $statut = $etablissement->statut_ouverture;
    $prochaine = $etablissement->prochaine_ouverture;
@endphp

@if($statut !== 'inconnu')
    <span class="inline-flex items-center gap-1 text-sm font-medium
        @if($statut === 'ouvert') text-green-600
        @elseif($statut === 'ferme_bientot') text-orange-500
        @elseif($statut === 'ouvre_bientot') text-blue-500
        @else text-red-500
        @endif
    ">
        <span class="w-2 h-2 rounded-full
            @if($statut === 'ouvert') bg-green-500
            @elseif($statut === 'ferme_bientot') bg-orange-400
            @elseif($statut === 'ouvre_bientot') bg-blue-400
            @else bg-red-400
            @endif
        "></span>
        @if($statut === 'ouvert')
            Ouvert
        @elseif($statut === 'ferme_bientot')
            Ferme bientôt
        @elseif($statut === 'ouvre_bientot')
            Ouvre bientôt
        @else
            Fermé
            @if($prochaine)
                <span class="font-normal text-gray-500 ml-1">· {{ $prochaine }}</span>
            @endif
        @endif
    </span>
@endif
