@extends('admin.layouts.app')

@section('content')
    <h1 class="text-2xl font-bold mb-6">{{ $etablissement->exists ? 'Modifier' : 'Créer' }} un établissement</h1>

    <form method="POST" action="{{ $etablissement->exists ? route('admin.etablissements.update', $etablissement) : route('admin.etablissements.store') }}" class="bg-white rounded-lg shadow-sm border p-6 max-w-2xl">
        @csrf
        @if($etablissement->exists) @method('PUT') @endif

        <div class="grid gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Nom</label>
                <input type="text" name="name" value="{{ old('name', $etablissement->name) }}" required class="w-full border rounded-lg px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Type</label>
                <select name="type" required class="w-full border rounded-lg px-3 py-2">
                    @foreach(\App\Models\Establishment::TYPE_LABELS as $key => $label)
                        <option value="{{ $key }}" {{ old('type', $etablissement->type) == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Adresse</label>
                    <input type="text" name="address" value="{{ old('address', $etablissement->address) }}" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Code postal</label>
                    <input type="text" name="postal_code" value="{{ old('postal_code', $etablissement->postal_code) }}" maxlength="5" class="w-full border rounded-lg px-3 py-2">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Ville</label>
                    <input type="text" name="city" value="{{ old('city', $etablissement->city) }}" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Téléphone</label>
                    <input type="text" name="phone" value="{{ old('phone', $etablissement->phone) }}" class="w-full border rounded-lg px-3 py-2">
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
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $etablissement->is_active) ? 'checked' : '' }} class="rounded">
                        <span class="text-sm font-medium">Validé</span>
                    </label>
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium mb-2">Caractéristiques</label>
                @php $currentFeatures = old('features', $etablissement->features ?? []); @endphp
                <div class="grid grid-cols-2 gap-2">
                    @foreach(\App\Models\Establishment::FEATURES as $key => $label)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="features[]" value="{{ $key }}" @checked(in_array($key, $currentFeatures)) class="rounded text-pink-600 focus:ring-pink-500">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="flex gap-3 mt-6">
            <button type="submit" class="bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700">Enregistrer</button>
            <a href="{{ route('admin.etablissements.index') }}" class="text-gray-500 px-6 py-2">Annuler</a>
        </div>
    </form>
@endsection
