import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.store('contactModal', { open: false });
Alpine.store('claimModal', { open: false });

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

Alpine.start();
