<x-layouts.app :noindex="true" title="Annulation du rendez-vous">
    <div class="max-w-xl mx-auto px-4 py-16 text-center">
        @if($cancellable)
            <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mb-6">
                <svg class="w-10 h-10 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-3">Rendez-vous annulé</h1>
            <p class="text-gray-600 mb-8">Votre rendez-vous chez <strong>{{ $establishment->name }}</strong> du
                {{ $appointment->starts_at->locale('fr')->isoFormat('dddd D MMMM YYYY [à] HH[h]mm') }} a bien été annulé.</p>
        @elseif($appointment->status === 'cancelled')
            <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mb-6">
                <svg class="w-10 h-10 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-3">Rendez-vous déjà annulé</h1>
            <p class="text-gray-600 mb-8">Ce rendez-vous a déjà été annulé.</p>
        @else
            <div class="inline-flex items-center justify-center w-20 h-20 bg-amber-100 rounded-full mb-6">
                <svg class="w-10 h-10 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-3">Annulation impossible</h1>
            <p class="text-gray-600 mb-8">Ce rendez-vous est déjà passé et ne peut plus être annulé. Contactez directement l'établissement si besoin.</p>
        @endif

        <a href="{{ $establishment->url }}" class="bg-pink-600 text-white px-5 py-2 rounded-lg hover:bg-pink-700">Retour à la fiche</a>
    </div>
</x-layouts.app>
