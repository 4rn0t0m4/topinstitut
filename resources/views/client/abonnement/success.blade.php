<x-layouts.app :noindex="true" title="Abonnement activé">
    <div class="py-16 text-center">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-6">
            <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </div>

        <h1 class="text-2xl font-bold text-gray-900 mb-3">Merci ! Votre abonnement est actif.</h1>
        <p class="text-gray-600 mb-2">L'abonnement Premium est en cours d'activation pour <strong>{{ $establishment->name }}</strong>.</p>
        <p class="text-sm text-gray-400 mb-8">L'activation peut prendre quelques secondes le temps que notre système enregistre votre paiement.</p>

        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ $establishment->url }}" class="bg-pink-600 text-white px-5 py-2 rounded-lg hover:bg-pink-700">Voir ma fiche</a>
            <a href="{{ route('client.abonnement.index') }}" class="bg-white border border-gray-300 text-gray-700 px-5 py-2 rounded-lg hover:bg-gray-50">Retour à mes abonnements</a>
        </div>
    </div>
</x-layouts.app>
