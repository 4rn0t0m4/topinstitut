@extends('admin.layouts.app')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Demandes de revendication</h1>

    @if($revendications->isEmpty())
        <p class="text-gray-500">Aucune demande en attente.</p>
    @else
        <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Établissement</th>
                        <th class="px-4 py-3 text-left font-medium">Demandeur</th>
                        <th class="px-4 py-3 text-left font-medium">Nom gérant</th>
                        <th class="px-4 py-3 text-left font-medium">SIRET</th>
                        <th class="px-4 py-3 text-left font-medium">Date</th>
                        <th class="px-4 py-3 text-left font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($revendications as $rev)
                        <tr class="border-t">
                            <td class="px-4 py-3">
                                <a href="{{ $rev->etablissement->url }}" target="_blank" class="text-pink-600 hover:underline">{{ $rev->etablissement->titre }}</a>
                            </td>
                            <td class="px-4 py-3">{{ $rev->user->email }}</td>
                            <td class="px-4 py-3">{{ $rev->nom_gerant }}</td>
                            <td class="px-4 py-3">{{ $rev->siret ?: '-' }}</td>
                            <td class="px-4 py-3">{{ $rev->created_at->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <form action="{{ route('admin.revendications.moderer', $rev) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="action" value="approuver">
                                        <button class="bg-green-600 text-white text-xs px-3 py-1 rounded hover:bg-green-700">Approuver</button>
                                    </form>
                                    <form action="{{ route('admin.revendications.moderer', $rev) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="action" value="refuser">
                                        <button class="bg-red-600 text-white text-xs px-3 py-1 rounded hover:bg-red-700">Refuser</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $revendications->links() }}</div>
    @endif
@endsection
