@extends('admin.layouts.app')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Catégories</h1>
        <a href="{{ route('admin.categories.create') }}" class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700 text-sm">Ajouter</a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border">
        @forelse($categories as $cat)
            <div class="border-b last:border-0">
                <div class="flex justify-between items-center px-4 py-3">
                    <span class="font-medium">{{ $cat->nom }}</span>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.categories.edit', $cat) }}" class="text-pink-600 text-sm hover:underline">Modifier</a>
                        <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST" onsubmit="return confirm('Supprimer ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 text-sm hover:underline">Supprimer</button>
                        </form>
                    </div>
                </div>
                @if($cat->children->isNotEmpty())
                    @foreach($cat->children as $child)
                        <div class="flex justify-between items-center px-4 py-2 pl-8 bg-gray-50 border-t">
                            <span class="text-sm text-gray-700">{{ $child->nom }}</span>
                            <div class="flex gap-2">
                                <a href="{{ route('admin.categories.edit', $child) }}" class="text-pink-600 text-xs hover:underline">Modifier</a>
                                <form action="{{ route('admin.categories.destroy', $child) }}" method="POST" onsubmit="return confirm('Supprimer ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 text-xs hover:underline">Supprimer</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        @empty
            <p class="p-4 text-gray-500">Aucune catégorie.</p>
        @endforelse
    </div>
@endsection
