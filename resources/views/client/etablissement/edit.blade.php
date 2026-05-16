<x-layouts.app :noindex="true" :title="'Modifier ' . $etablissement->name">
    <div class="max-w-2xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Coordonnées - {{ $etablissement->name }}</h1>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('client.etablissement.update', $etablissement) }}" class="bg-white rounded-lg shadow-sm border p-6">
            @csrf @method('PUT')
            <div class="grid gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Nom <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $etablissement->name) }}" required class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Adresse</label>
                    <input type="text" name="address" value="{{ old('address', $etablissement->address) }}" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div class="grid grid-cols-2 gap-4" x-data="villeAutocomplete()" x-init="query = @js(old('city', $etablissement->city ?? '')); selectedId = @js((string) old('city_id', $etablissement->city_id ?? '')); selectedPostalCode = @js((string) old('postal_code', $etablissement->postal_code ?? ''))">
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
                        @if($etablissement->city_id)
                            <p class="text-xs text-green-600 mt-1">✓ Ville liée ({{ $etablissement->cityRelation?->department?->name }})</p>
                        @else
                            <p class="text-xs text-amber-600 mt-1">Sélectionnez votre ville dans la liste pour optimiser le référencement.</p>
                        @endif
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Téléphone</label>
                        <input type="text" name="phone" value="{{ old('phone', $etablissement->phone) }}" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Portable</label>
                        <input type="text" name="mobile" value="{{ old('mobile', $etablissement->mobile) }}" class="w-full border rounded-lg px-3 py-2">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $etablissement->email) }}" class="w-full border rounded-lg px-3 py-2">
                </div>
            </div>
            <button type="submit" class="mt-6 bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700">Enregistrer</button>
        </form>
    </div>
</x-layouts.app>
