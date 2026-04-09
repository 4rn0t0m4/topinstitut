import Alpine from 'alpinejs';

window.Alpine = Alpine;

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
        } catch (e) {
            this.display = 'Erreur';
        }
        this.loading = false;
    },
}));

Alpine.start();
