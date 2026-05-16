<x-layouts.app title="Ajouter un institut - TopInstitut">
    <div class="max-w-2xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Ajouter un institut de beauté</h1>
        <p class="text-gray-600 mb-6">Votre établissement sera vérifié avant publication.</p>

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

        <form method="POST" action="{{ route('etablissement.store') }}" class="bg-white rounded-lg shadow-sm border p-6">
            @csrf
            <x-honeypot />
            <div class="grid gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Nom de l'établissement <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Type <span class="text-red-500">*</span></label>
                    <select name="type" required class="w-full border rounded-lg px-3 py-2">
                        @foreach(\App\Models\Establishment::TYPE_LABELS as $key => $label)
                            <option value="{{ $key }}" {{ old('type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Adresse</label>
                    <input type="text" name="address" value="{{ old('address') }}" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div class="grid grid-cols-2 gap-4" x-data="villeAutocomplete()" x-init="query = '{{ old('city') }}'; selectedId = '{{ old('city_id') }}'; selectedPostalCode = '{{ old('postal_code') }}'">
                    <div>
                        <label class="block text-sm font-medium mb-1">Code postal</label>
                        <input type="text" name="postal_code" x-model="selectedPostalCode" maxlength="5" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div class="relative" @click.outside="open = false">
                        <label class="block text-sm font-medium mb-1">Ville</label>
                        <input type="text" name="city" x-model="query" @input="search()" @focus="open = results.length > 0" autocomplete="off" required class="w-full border rounded-lg px-3 py-2">
                        <input type="hidden" name="city_id" :value="selectedId">
                        <ul x-show="open" x-cloak class="absolute z-50 left-0 right-0 mt-1 bg-white border rounded-lg shadow-lg max-h-60 overflow-y-auto">
                            <template x-for="item in results" :key="item.id">
                                <li @click="select(item)" class="px-4 py-2 cursor-pointer hover:bg-pink-50 text-gray-900 text-sm" x-text="item.label"></li>
                            </template>
                        </ul>
                        <p class="text-xs text-gray-500 mt-1">Sélectionnez votre ville dans la liste qui apparaît.</p>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Téléphone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Description</label>
                    <textarea name="description" rows="5" class="w-full border rounded-lg px-3 py-2">{{ old('description') }}</textarea>
                </div>
            </div>
            <button type="submit" class="mt-6 bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700">Enregistrer</button>
        </form>
    </div>
</x-layouts.app>
