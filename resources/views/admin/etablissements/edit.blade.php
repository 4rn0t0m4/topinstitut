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
                    <label class="block text-sm font-medium mb-1">Téléphone</label>
                    <input type="text" name="phone" value="{{ old('phone', $etablissement->phone) }}" class="w-full border rounded-lg px-3 py-2">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4" x-data="villeAutocomplete()" x-init="query = '{{ addslashes(old('city', $etablissement->city ?? '')) }}'; selectedId = '{{ old('city_id', $etablissement->city_id) }}'; selectedPostalCode = '{{ old('postal_code', $etablissement->postal_code) }}'">
                <div>
                    <label class="block text-sm font-medium mb-1">Code postal</label>
                    <input type="text" name="postal_code" x-model="selectedPostalCode" maxlength="5" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div class="relative" @click.outside="open = false">
                    <label class="block text-sm font-medium mb-1">Ville</label>
                    <input type="text" name="city" x-model="query" @input="search()" @focus="open = results.length > 0" autocomplete="off" class="w-full border rounded-lg px-3 py-2">
                    <input type="hidden" name="city_id" :value="selectedId">
                    <ul x-show="open" x-cloak class="absolute z-50 left-0 right-0 mt-1 bg-white border rounded-lg shadow-lg max-h-60 overflow-y-auto">
                        <template x-for="item in results" :key="item.id">
                            <li @click="select(item)" class="px-4 py-2 cursor-pointer hover:bg-pink-50 text-gray-900 text-sm" x-text="item.label"></li>
                        </template>
                    </ul>
                    @if($etablissement->exists && $etablissement->city_id)
                        <p class="text-xs text-green-600 mt-1">✓ Ville liée à la base ({{ $etablissement->cityRelation?->department?->name }})</p>
                    @else
                        <p class="text-xs text-amber-600 mt-1">Sélectionnez la ville dans la liste pour activer l'URL hiérarchique SEO.</p>
                    @endif
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

            @if($etablissement->exists)
                <div class="border-t pt-4 mt-2">
                    <h2 class="text-sm font-semibold uppercase text-gray-500 mb-3">Monétisation</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Abonnement</label>
                            <select name="subscription_tier" class="w-full border rounded-lg px-3 py-2">
                                <option value="free" @selected(old('subscription_tier', $etablissement->subscription_tier) === 'free')>Gratuit</option>
                                <option value="premium" @selected(old('subscription_tier', $etablissement->subscription_tier) === 'premium')>Premium</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Fin d'abonnement (laisser vide = illimité)</label>
                            <input type="datetime-local" name="subscription_ends_at" value="{{ old('subscription_ends_at', $etablissement->subscription_ends_at?->format('Y-m-d\TH:i')) }}" class="w-full border rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Sponsorisé jusqu'au</label>
                            <input type="datetime-local" name="featured_until" value="{{ old('featured_until', $etablissement->featured_until?->format('Y-m-d\TH:i')) }}" class="w-full border rounded-lg px-3 py-2">
                            <p class="text-xs text-gray-500 mt-1">Affichage prioritaire dans la recherche et la ville.</p>
                        </div>
                        <div class="flex items-end">
                            <label class="flex items-center gap-2 text-sm font-medium">
                                <input type="hidden" name="is_verified_owner" value="0">
                                <input type="checkbox" name="is_verified_owner" value="1" @checked(old('is_verified_owner', $etablissement->is_verified_owner)) class="rounded text-pink-600">
                                Propriétaire vérifié (badge bleu)
                            </label>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="flex gap-3 mt-6">
            <button type="submit" class="bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700">Enregistrer</button>
            <a href="{{ route('admin.etablissements.index') }}" class="text-gray-500 px-6 py-2">Annuler</a>
        </div>
    </form>
@endsection
