@extends('admin.layouts.app')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Guides</h1>
        <a href="{{ route('admin.guides.create') }}" class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700 text-sm">Nouveau guide</a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3">Titre</th>
                    <th class="text-left px-4 py-3">Statut</th>
                    <th class="text-left px-4 py-3">Publié le</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($guides as $guide)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ $guide->title }}</td>
                        <td class="px-4 py-3">
                            @if($guide->is_published)
                                <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full">Publié</span>
                            @else
                                <span class="bg-orange-100 text-orange-700 text-xs px-2 py-1 rounded-full">Brouillon</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ optional($guide->published_at)->format('d/m/Y') ?: '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.guides.edit', $guide) }}" class="text-pink-600 hover:underline">Modifier</a>
                            @if($guide->is_published)
                                <a href="{{ $guide->url }}" target="_blank" class="text-gray-500 hover:underline ml-2">Voir</a>
                            @endif
                            <form action="{{ route('admin.guides.destroy', $guide) }}" method="POST" class="inline ml-2" onsubmit="return confirm('Supprimer ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">Aucun guide.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $guides->links() }}</div>
@endsection
