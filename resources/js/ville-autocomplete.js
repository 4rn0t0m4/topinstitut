export default () => ({
    query: '',
    selectedId: '',
    selectedPostalCode: '',
    results: [],
    open: false,
    debounceTimer: null,

    search() {
        clearTimeout(this.debounceTimer);
        this.selectedId = '';
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
        this.selectedId = item.id ?? '';
        this.selectedPostalCode = item.postal_code ?? '';
        this.open = false;
        this.results = [];
    },
});
