@extends('admin.layouts.app')

@section('content')
    <h1 class="text-2xl font-bold mb-6">{{ $categorie->exists ? 'Modifier' : 'Créer' }} une catégorie</h1>

    <form method="POST" action="{{ $categorie->exists ? route('admin.categories.update', $categorie) : route('admin.categories.store') }}" class="bg-white rounded-lg shadow-sm border p-6 max-w-lg">
        @csrf
        @if($categorie->exists) @method('PUT') @endif

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Nom</label>
            <input type="text" name="nom" value="{{ old('nom', $categorie->nom) }}" required class="w-full border rounded-lg px-3 py-2">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Catégorie parente</label>
            <select name="parent_id" class="w-full border rounded-lg px-3 py-2">
                <option value="">Aucune (racine)</option>
                @foreach($parents as $parent)
                    <option value="{{ $parent->id }}" {{ old('parent_id', $categorie->parent_id) == $parent->id ? 'selected' : '' }}>{{ $parent->nom }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Description</label>
            <textarea name="description" rows="3" class="w-full border rounded-lg px-3 py-2">{{ old('description', $categorie->description) }}</textarea>
        </div>

        <button type="submit" class="bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700">Enregistrer</button>
    </form>
@endsection
