import Alpine from 'alpinejs';
import './lazy-map.js';
import villeAutocomplete from './ville-autocomplete.js';

window.Alpine = Alpine;

Alpine.store('contactModal', { open: false });
Alpine.store('claimModal', { open: false });
Alpine.store('bookingModal', { open: false });
Alpine.store('rdvModal', { open: false });

// Tracking évènement fiche (clic téléphone, ouverture galerie, modale RDV…).
// Fire-and-forget : ne bloque jamais l'UX, l'erreur est silencieuse.
window.trackEtablissementEvent = (etablissementId, eventType) => {
    if (!etablissementId || !eventType) return;
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    try {
        fetch('/etablissements/' + etablissementId + '/event', {
            method: 'POST',
            keepalive: true,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ event_type: eventType }),
        }).catch(() => {});
    } catch (e) { /* noop */ }
};

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

Alpine.store('compare', {
    ids: JSON.parse(localStorage.getItem('compare') || '[]'),
    max: 3,

    has(id) { return this.ids.includes(id); },

    persist() { localStorage.setItem('compare', JSON.stringify(this.ids)); },

    toggle(id) {
        if (this.ids.includes(id)) {
            this.ids = this.ids.filter(x => x !== id);
        } else {
            if (this.ids.length >= this.max) return;
            this.ids.push(id);
        }
        this.persist();
    },

    clear() { this.ids = []; this.persist(); },
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
        // Stat clic téléphone (intent fort, on track au moment de la révélation).
        window.trackEtablissementEvent?.(etablissementId, 'phone_click');
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

// Sous-menu d'ancres sticky sur la fiche établissement : highlight de la
// section actuellement à l'écran + scroll doux au clic. Pas d'IntersectionObserver
// pour rester prédictible avec la marge de la barre.
Alpine.data('ficheNav', () => ({
    active: '',
    sections: [],
    navOffset: 64,

    init() {
        this.sections = Array.from(document.querySelectorAll('[data-fiche-section]'));
        if (!this.sections.length) return;
        this.active = this.sections[0].id;
        const onScroll = () => {
            const y = window.scrollY + this.navOffset + 4;
            let current = this.sections[0].id;
            for (const s of this.sections) {
                if (s.offsetTop <= y) current = s.id;
                else break;
            }
            if (current !== this.active) this.active = current;
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    },

    goto(id) {
        const el = document.getElementById(id);
        if (!el) return;
        this.active = id;
        const y = el.getBoundingClientRect().top + window.scrollY - this.navOffset + 1;
        window.scrollTo({ top: y, behavior: 'smooth' });
        if (history.replaceState) history.replaceState(null, '', '#' + id);
    },
}));

Alpine.data('villeAutocomplete', villeAutocomplete);

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
