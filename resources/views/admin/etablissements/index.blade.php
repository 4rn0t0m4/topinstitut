@extends('admin.layouts.app')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Établissements</h1>
        <a href="{{ route('admin.etablissements.create') }}" class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700 text-sm">Ajouter</a>
    </div>

    <form method="GET" class="flex gap-3 mb-6">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher..." class="border rounded-lg px-3 py-2 text-sm">
        <select name="valide" class="border rounded-lg px-3 py-2 text-sm">
            <option value="">Tous</option>
            <option value="1" {{ request('valide') === '1' ? 'selected' : '' }}>Validés</option>
            <option value="0" {{ request('valide') === '0' ? 'selected' : '' }}>En attente</option>
        </select>
        <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm">Filtrer</button>
    </form>

    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3">Titre</th>
                    <th class="text-left px-4 py-3">Type</th>
                    <th class="text-left px-4 py-3">Ville</th>
                    <th class="text-center px-4 py-3">Statut</th>
                    <th class="text-center px-4 py-3">Avis</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($etablissements as $etab)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ $etab->titre }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $etab->type_label }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $etab->ville }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($etab->valide)
                                <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full">Validé</span>
                            @else
                                <span class="bg-orange-100 text-orange-700 text-xs px-2 py-1 rounded-full">En attente</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center text-gray-500">{{ $etab->nb_avis }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.etablissements.edit', $etab) }}" class="text-pink-600 hover:underline">Modifier</a>
                            @unless($etab->valide)
                                <form action="{{ route('admin.etablissements.valider', $etab) }}" method="POST" class="inline ml-2">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:underline">Valider</button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $etablissements->withQueryString()->links() }}</div>
@endsection
