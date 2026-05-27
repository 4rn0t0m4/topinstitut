<x-layouts.app :noindex="true" title="Rendez-vous confirmé">
    <div class="max-w-xl mx-auto px-4 py-16 text-center">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-6">
            <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </div>

        <h1 class="text-2xl font-bold text-gray-900 mb-3">Rendez-vous confirmé !</h1>
        <p class="text-gray-600 mb-6">Un email de confirmation a été envoyé à <strong>{{ $appointment->customer_email }}</strong>.</p>

        <div class="bg-white border rounded-lg p-6 text-left space-y-2 mb-8">
            <div class="flex justify-between"><span class="text-gray-500">Établissement</span><span class="font-medium">{{ $establishment->name }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Prestation</span><span class="font-medium">{{ $appointment->service_name }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Praticien</span><span class="font-medium">{{ $appointment->practitioner->name }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Date</span><span class="font-medium">{{ $appointment->starts_at->locale('fr')->isoFormat('dddd D MMMM YYYY [à] HH[h]mm') }}</span></div>
        </div>

        <a href="{{ $establishment->url }}" class="bg-pink-600 text-white px-5 py-2 rounded-lg hover:bg-pink-700">Retour à la fiche</a>
    </div>
</x-layouts.app>
