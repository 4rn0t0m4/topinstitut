@extends('admin.layouts.app')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Avis en attente de modération</h1>

    <div class="space-y-4">
        @forelse($avis as $a)
            <div class="bg-white rounded-lg shadow-sm border p-4">
                <div class="flex justify-between items-start">
                    <div>
                        <span class="font-semibold">{{ $a->user->pseudo }}</span>
                        <span class="text-gray-400 text-sm ml-2">{{ $a->created_at->format('d/m/Y H:i') }}</span>
                        <span class="text-gray-400 text-sm ml-2">sur <a href="{{ route('admin.etablissements.show', $a->etablissement) }}" class="text-pink-600 hover:underline">{{ $a->etablissement->titre }}</a></span>
                    </div>
                    <div class="flex items-center gap-1">
                        <x-star-rating :rating="$a->moyenne" size="w-4 h-4" />
                        <span class="text-sm">{{ number_format($a->moyenne, 1, ',', '') }}</span>
                    </div>
                </div>
                <h3 class="font-medium mt-2">{{ $a->titre }}</h3>
                <p class="text-sm text-gray-700 mt-1">{{ $a->contenu }}</p>
                <div class="flex gap-2 mt-4">
                    <form action="{{ route('admin.avis.moderer', $a) }}" method="POST">
                        @csrf
                        <input type="hidden" name="action" value="valider">
                        <button type="submit" class="bg-green-600 text-white text-sm px-4 py-1.5 rounded-lg hover:bg-green-700">Valider</button>
                    </form>
                    <form action="{{ route('admin.avis.moderer', $a) }}" method="POST">
                        @csrf
                        <input type="hidden" name="action" value="refuser">
                        <button type="submit" class="bg-red-600 text-white text-sm px-4 py-1.5 rounded-lg hover:bg-red-700">Refuser</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-gray-500">Aucun avis en attente.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $avis->links() }}</div>
@endsection
