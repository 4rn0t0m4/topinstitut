import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.store('contactModal', { open: false });
Alpine.store('claimModal', { open: false });
Alpine.store('bookingModal', { open: false });

Alpine.store('favorites', {
    ids: (function () {
        const auth = document.querySelector('meta[name="auth-favorites"]')?.content;
        if (auth) {
            return auth.split(',').filter(Boolean).map(Number);
        }
        return JSON.parse(localStorage.getItem('favorites') || '[]');
    })(),
    authenticated: !!document.querySelector('meta[name="auth-user"]'),

    has(id) { return this.ids.includes(id); },

    persist() { localStorage.setItem('favorites', JSON.stringify(this.ids)); },

    async toggle(id) {
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        try {
            const res = await fetch('/ajax/favorites/' + id, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
            });
            const data = await res.json();
            if (data.authenticated) {
                if (data.favorite) {
                    if (!this.ids.includes(id)) this.ids.push(id);
                } else {
                    this.ids = this.ids.filter(x => x !== id);
                }
            } else {
                if (this.ids.includes(id)) {
                    this.ids = this.ids.filter(x => x !== id);
                } else {
                    this.ids.push(id);
                }
                this.persist();
            }
        } catch (e) {
            // Network error : fallback to localStorage toggle
            if (this.ids.includes(id)) {
                this.ids = this.ids.filter(x => x !== id);
            } else {
                this.ids.push(id);
            }
            this.persist();
        }
    },
});

Alpine.store('lightbox', {
    open: false,
    photos: [],
    current: 0,
    show(photos, index) {
        this.photos = photos;
        this.current = index;
        this.open = true;
    },
    next() { this.current = (this.current + 1) % this.photos.length; },
    prev() { this.current = (this.current - 1 + this.photos.length) % this.photos.length; },
    close() { this.open = false; },
});

Alpine.data('phoneReveal', (encoded, etablissementId) => ({
    revealed: false,
    loading: false,
    display: '',
    tel: '',
    code: null,
    premium: false,
    tarif: '',
    mobile: false,

    async reveal() {
        this.loading = true;
        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            const res = await fetch('/ajax/phone', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    phone: encoded,
                    etablissement_id: etablissementId,
                }),
            });
            const data = await res.json();
            this.display = data.phone;
            this.tel = data.tel;
            this.code = data.code || null;
            this.premium = data.premium;
            this.tarif = data.tarif || '';
            this.mobile = data.mobile;
            this.revealed = true;
            // Déclencher l'appel automatiquement
            this.$nextTick(() => {
                window.location.href = 'tel:' + this.tel;
            });
        } catch (e) {
            this.display = 'Erreur';
        }
        this.loading = false;
    },
}));

Alpine.data('villeAutocomplete', () => ({
    query: '',
    results: [],
    open: false,
    debounceTimer: null,

    search() {
        clearTimeout(this.debounceTimer);
        if (this.query.length < 2) {
            this.results = [];
            this.open = false;
            return;
        }
        this.debounceTimer = setTimeout(async () => {
            const res = await fetch('/ajax/villes?q=' + encodeURIComponent(this.query));
            this.results = await res.json();
            this.open = this.results.length > 0;
        }, 200);
    },

    select(item) {
        this.query = item.value;
        this.open = false;
        this.results = [];
    },
}));

Alpine.data('prestationAutocomplete', () => ({
    query: '',
    selectedId: '',
    results: [],
    open: false,
    debounceTimer: null,

    search() {
        clearTimeout(this.debounceTimer);
        if (this.query.length < 2) {
            this.results = [];
            this.open = false;
            this.selectedId = '';
            return;
        }
        this.debounceTimer = setTimeout(async () => {
            const res = await fetch('/ajax/categories?q=' + encodeURIComponent(this.query));
            this.results = await res.json();
            this.open = this.results.length > 0;
        }, 200);
    },

    select(item) {
        this.query = item.name;
        this.selectedId = item.id;
        this.open = false;
        this.results = [];
    },
}));

Alpine.start();
