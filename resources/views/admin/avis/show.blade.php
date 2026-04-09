@extends('admin.layouts.app')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Détail de l'avis</h1>

    <div class="bg-white rounded-lg shadow-sm border p-6 max-w-2xl">
        <p class="text-sm text-gray-500">Par {{ $avis->user->pseudo }} le {{ $avis->created_at->format('d/m/Y') }}</p>
        <p class="text-sm text-gray-500">Sur : <a href="{{ $avis->etablissement->url }}" class="text-pink-600">{{ $avis->etablissement->titre }}</a></p>

        <div class="flex items-center gap-2 mt-3">
            <x-star-rating :rating="$avis->moyenne" />
            <span class="font-semibold">{{ number_format($avis->moyenne, 1, ',', '') }}/5</span>
        </div>

        <h2 class="text-lg font-semibold mt-4">{{ $avis->titre }}</h2>
        <p class="text-gray-700 mt-2">{{ $avis->contenu }}</p>

        <div class="grid grid-cols-3 gap-2 mt-4 text-sm">
            <div>Accueil: {{ $avis->note_accueil }}/5</div>
            <div>Qualité: {{ $avis->note_qualite }}/5</div>
            <div>Choix: {{ $avis->note_choix }}/5</div>
            <div>Prix: {{ $avis->note_prix }}/5</div>
            <div>Cadre: {{ $avis->note_cadre }}/5</div>
            <div>Propreté: {{ $avis->note_proprete }}/5</div>
        </div>

        <div class="flex gap-2 mt-6">
            <form action="{{ route('admin.avis.moderer', $avis) }}" method="POST">
                @csrf
                <input type="hidden" name="action" value="valider">
                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700">Valider</button>
            </form>
            <form action="{{ route('admin.avis.moderer', $avis) }}" method="POST">
                @csrf
                <input type="hidden" name="action" value="refuser">
                <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700">Refuser</button>
            </form>
        </div>
    </div>
@endsection
