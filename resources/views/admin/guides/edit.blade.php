@extends('admin.layouts.app')

@section('content')
    <h1 class="text-2xl font-bold mb-6">{{ $guide->exists ? 'Modifier' : 'Créer' }} un guide</h1>

    <form method="POST" action="{{ $guide->exists ? route('admin.guides.update', $guide) : route('admin.guides.store') }}" class="bg-white rounded-lg shadow-sm border p-6 max-w-3xl">
        @csrf
        @if($guide->exists) @method('PUT') @endif

        <div class="grid gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Titre <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $guide->title) }}" required class="w-full border rounded-lg px-3 py-2">
                @error('title') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Résumé (excerpt)</label>
                <textarea name="excerpt" rows="2" class="w-full border rounded-lg px-3 py-2" placeholder="Court extrait pour la liste / les partages réseaux">{{ old('excerpt', $guide->excerpt) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Image de couverture (URL)</label>
                <input type="text" name="cover_image" value="{{ old('cover_image', $guide->cover_image) }}" placeholder="https://… ou /chemin/local.jpg" class="w-full border rounded-lg px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Contenu HTML <span class="text-red-500">*</span></label>
                <textarea name="body" rows="20" required class="w-full border rounded-lg px-3 py-2 font-mono text-sm">{{ old('body', $guide->body) }}</textarea>
                <p class="text-xs text-gray-500 mt-1">HTML autorisé. Astuce : commence avec un &lt;p&gt;, &lt;h2&gt;, &lt;ul&gt;, &lt;a href&gt;…</p>
                @error('body') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Auteur</label>
                    <input type="text" name="author" value="{{ old('author', $guide->author ?: 'TopInstitut') }}" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Meta description (SEO, ≤160 car.)</label>
                    <input type="text" name="meta_description" maxlength="160" value="{{ old('meta_description', $guide->meta_description) }}" class="w-full border rounded-lg px-3 py-2">
                </div>
            </div>

            <div>
                <label class="flex items-center gap-2">
                    <input type="hidden" name="is_published" value="0">
                    <input type="checkbox" name="is_published" value="1" {{ old('is_published', $guide->is_published) ? 'checked' : '' }} class="rounded text-pink-600">
                    <span class="text-sm font-medium">Publié</span>
                </label>
            </div>
        </div>

        <div class="flex gap-3 mt-6">
            <button type="submit" class="bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700">Enregistrer</button>
            <a href="{{ route('admin.guides.index') }}" class="text-gray-500 px-6 py-2">Annuler</a>
        </div>
    </form>
@endsection
