@extends('admin.layouts.app')

@section('content')
    <h1 class="text-2xl font-bold mb-6">{{ $etablissement->exists ? 'Modifier' : 'Créer' }} un établissement</h1>

    <form method="POST" action="{{ $etablissement->exists ? route('admin.etablissements.update', $etablissement) : route('admin.etablissements.store') }}" class="bg-white rounded-lg shadow-sm border p-6 max-w-2xl">
        @csrf
        @if($etablissement->exists) @method('PUT') @endif

        <div class="grid gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Titre</label>
                <input type="text" name="titre" value="{{ old('titre', $etablissement->titre) }}" required class="w-full border rounded-lg px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Type</label>
                <select name="type" required class="w-full border rounded-lg px-3 py-2">
                    @foreach(\App\Models\Etablissement::TYPE_LABELS as $key => $label)
                        <option value="{{ $key }}" {{ old('type', $etablissement->type) == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Adresse</label>
                    <input type="text" name="adresse" value="{{ old('adresse', $etablissement->adresse) }}" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Code postal</label>
                    <input type="text" name="cp" value="{{ old('cp', $etablissement->cp) }}" maxlength="5" class="w-full border rounded-lg px-3 py-2">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Ville</label>
                    <input type="text" name="ville" value="{{ old('ville', $etablissement->ville) }}" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Téléphone</label>
                    <input type="text" name="telephone" value="{{ old('telephone', $etablissement->telephone) }}" class="w-full border rounded-lg px-3 py-2">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $etablissement->email) }}" class="w-full border rounded-lg px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Description</label>
                <textarea name="description" rows="5" class="w-full border rounded-lg px-3 py-2">{{ old('description', $etablissement->description) }}</textarea>
            </div>

            @if($etablissement->exists)
                <div>
                    <label class="flex items-center gap-2">
                        <input type="hidden" name="valide" value="0">
                        <input type="checkbox" name="valide" value="1" {{ old('valide', $etablissement->valide) ? 'checked' : '' }} class="rounded">
                        <span class="text-sm font-medium">Validé</span>
                    </label>
                </div>
            @endif
        </div>

        <div class="flex gap-3 mt-6">
            <button type="submit" class="bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700">Enregistrer</button>
            <a href="{{ route('admin.etablissements.index') }}" class="text-gray-500 px-6 py-2">Annuler</a>
        </div>
    </form>
@endsection
