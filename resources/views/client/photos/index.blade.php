<x-layouts.app :noindex="true" :title="'Photos - ' . $etablissement->name">
    <div class="px-2 py-3"
         x-data="photoUploader({
             url: '{{ route('client.etablissement.photos.store', $etablissement) }}',
             csrf: '{{ csrf_token() }}',
         })">
        <h1 class="text-xl font-bold mb-1">Photos</h1>
        <p class="text-sm text-gray-500 mb-4">{{ $etablissement->name }}</p>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-3 py-2 rounded-lg mb-4 text-sm">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded-lg mb-4 text-sm">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        {{-- Drop zone --}}
        <label for="photo-input"
               @dragover.prevent="dragging = true"
               @dragleave.prevent="dragging = false"
               @drop.prevent="onDrop($event)"
               :class="dragging ? 'border-pink-500 bg-pink-50' : 'border-gray-300 hover:border-pink-400 hover:bg-pink-50/40'"
               class="block border-2 border-dashed rounded-xl p-6 text-center cursor-pointer transition mb-4">
            <input id="photo-input" type="file" name="photos[]" accept="image/*" multiple class="hidden" @change="onFiles($event.target.files)">
            <svg class="w-10 h-10 mx-auto text-pink-400 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
            <p class="text-sm font-medium text-gray-700">Glissez vos photos ici, ou <span class="text-pink-600 underline">cliquez pour parcourir</span></p>
            <p class="text-xs text-gray-400 mt-1">JPG, PNG ou WEBP — jusqu'à 5 Mo par fichier · plusieurs fichiers acceptés</p>
        </label>

        {{-- File de téléversement --}}
        <div x-show="queue.length > 0" x-cloak class="bg-white border rounded-lg divide-y mb-6">
            <template x-for="item in queue" :key="item.id">
                <div class="flex items-center gap-3 px-3 py-2">
                    <img :src="item.preview" class="w-10 h-10 rounded object-cover flex-shrink-0" alt="">
                    <div class="flex-1 min-w-0">
                        <div class="text-sm truncate" x-text="item.name"></div>
                        <div class="h-1 bg-gray-100 rounded mt-1 overflow-hidden">
                            <div class="h-full bg-pink-500 transition-all" :style="`width: ${item.progress}%`"></div>
                        </div>
                    </div>
                    <span class="text-xs flex-shrink-0"
                          :class="item.status === 'error' ? 'text-red-500' : (item.status === 'done' ? 'text-green-600' : 'text-gray-400')"
                          x-text="item.label"></span>
                </div>
            </template>
        </div>

        {{-- Grille photos existantes --}}
        @if($photos->isNotEmpty())
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-sm font-semibold text-gray-700">{{ count($photos) }} photo{{ count($photos) > 1 ? 's' : '' }}</h2>
                <span x-show="reorderState" x-cloak class="text-xs text-gray-400" x-text="reorderState"></span>
            </div>
            <div id="photos-grid"
                 x-init="initSortable($el)"
                 class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2">
                @foreach($photos as $photo)
                    <div data-id="{{ $photo->id }}" class="relative group aspect-square cursor-move">
                        <img src="{{ $photo->url }}" alt="" loading="lazy" draggable="false" class="absolute inset-0 w-full h-full object-cover rounded-lg pointer-events-none">
                        <form action="{{ route('client.etablissement.photos.destroy', [$etablissement, $photo]) }}" method="POST" class="absolute top-1.5 right-1.5 opacity-0 group-hover:opacity-100 transition" onsubmit="return confirm('Supprimer cette photo ?')">
                            @csrf @method('DELETE')
                            <button type="submit" title="Supprimer" class="bg-white/95 hover:bg-red-50 text-red-600 w-7 h-7 rounded-full shadow flex items-center justify-center cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
            <p class="text-xs text-gray-400 mt-2">Astuce : glissez-déposez les photos pour modifier l'ordre.</p>
        @else
            <p class="text-sm text-gray-500 italic">Aucune photo pour l'instant.</p>
        @endif
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15/Sortable.min.js" defer></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('photoUploader', (cfg) => ({
                queue: [],
                dragging: false,
                nextId: 1,
                reorderState: '',

                initSortable(el) {
                    const start = () => {
                        if (typeof Sortable === 'undefined') { setTimeout(start, 80); return; }
                        Sortable.create(el, {
                            animation: 150,
                            ghostClass: 'opacity-40',
                            onEnd: () => this.saveOrder(el),
                        });
                    };
                    start();
                },
                async saveOrder(el) {
                    const order = Array.from(el.querySelectorAll('[data-id]')).map((n) => Number(n.dataset.id));
                    this.reorderState = 'Enregistrement…';
                    try {
                        const res = await fetch('{{ route('client.etablissement.photos.reorder', $etablissement) }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': cfg.csrf },
                            body: JSON.stringify({ order }),
                        });
                        this.reorderState = res.ok ? '✓ Ordre enregistré' : '⚠ Échec de la sauvegarde';
                    } catch (e) {
                        this.reorderState = '⚠ Échec de la sauvegarde';
                    }
                    setTimeout(() => { this.reorderState = ''; }, 1800);
                },

                onFiles(fileList) {
                    Array.from(fileList).forEach((file) => {
                        if (! file.type.startsWith('image/')) return;
                        const item = {
                            id: this.nextId++,
                            file,
                            name: file.name,
                            preview: URL.createObjectURL(file),
                            progress: 0,
                            status: 'pending',
                            label: 'En attente',
                        };
                        this.queue.push(item);
                        this.upload(item);
                    });
                },
                onDrop(event) {
                    this.dragging = false;
                    this.onFiles(event.dataTransfer.files);
                },
                upload(item) {
                    item.status = 'uploading';
                    item.label = 'Envoi…';
                    const fd = new FormData();
                    fd.append('photo', item.file);
                    fd.append('_token', cfg.csrf);

                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', cfg.url);
                    xhr.setRequestHeader('Accept', 'application/json');
                    xhr.upload.addEventListener('progress', (e) => {
                        if (e.lengthComputable) item.progress = Math.round((e.loaded / e.total) * 100);
                    });
                    xhr.onload = () => {
                        if (xhr.status >= 200 && xhr.status < 300) {
                            item.status = 'done';
                            item.label = '✓';
                            item.progress = 100;
                            // Recharge la page quand toutes les uploads sont terminées
                            if (this.queue.every((q) => q.status === 'done' || q.status === 'error')) {
                                setTimeout(() => window.location.reload(), 400);
                            }
                        } else {
                            item.status = 'error';
                            item.label = 'Échec';
                        }
                    };
                    xhr.onerror = () => { item.status = 'error'; item.label = 'Erreur réseau'; };
                    xhr.send(fd);
                },
            }));
        });
    </script>
    @endpush
</x-layouts.app>
