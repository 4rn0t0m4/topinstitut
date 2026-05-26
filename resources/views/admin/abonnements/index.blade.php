@extends('admin.layouts.app')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Abonnements</h1>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow-sm border p-4">
            <p class="text-xs text-gray-500 uppercase">Premium actifs</p>
            <p class="text-2xl font-bold text-pink-600 mt-1">{{ $stats['premium_count'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-4">
            <p class="text-xs text-gray-500 uppercase">Sponsorisés actifs</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['sponsorise_count'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-4">
            <p class="text-xs text-gray-500 uppercase">MRR Premium</p>
            <p class="text-2xl font-bold mt-1">{{ number_format($stats['mrr_premium'], 2, ',', ' ') }} €</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-4">
            <p class="text-xs text-gray-500 uppercase">MRR Sponsorisé</p>
            <p class="text-2xl font-bold mt-1">{{ number_format($stats['mrr_sponsorise'], 2, ',', ' ') }} €</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-4 bg-gray-900 text-white">
            <p class="text-xs text-gray-300 uppercase">MRR total estimé</p>
            <p class="text-2xl font-bold mt-1">{{ number_format($stats['mrr_total'], 2, ',', ' ') }} €</p>
        </div>
    </div>

    {{-- Premium actifs --}}
    <section class="bg-white rounded-lg shadow-sm border mb-8">
        <header class="px-4 py-3 border-b flex items-center justify-between">
            <h2 class="font-semibold">Premium actifs ({{ $premiumActifs->count() }})</h2>
        </header>
        @if($premiumActifs->isEmpty())
            <p class="px-4 py-6 text-sm text-gray-500">Aucun abonnement Premium actif.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-2">Établissement</th>
                            <th class="px-4 py-2">Ville</th>
                            <th class="px-4 py-2">Propriétaire</th>
                            <th class="px-4 py-2">Fin abonnement</th>
                            <th class="px-4 py-2">Sponsorisé jusqu'au</th>
                            <th class="px-4 py-2">Stripe</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($premiumActifs as $etab)
                            <tr>
                                <td class="px-4 py-2">
                                    <a href="{{ $etab->url }}" class="text-pink-600 hover:underline" target="_blank">{{ $etab->name }}</a>
                                </td>
                                <td class="px-4 py-2 text-gray-600">{{ $etab->city }}</td>
                                <td class="px-4 py-2 text-gray-600">
                                    @forelse($etab->owners as $owner)
                                        <a href="mailto:{{ $owner->email }}" class="text-pink-600 hover:underline">{{ $owner->email }}</a>@if(!$loop->last), @endif
                                    @empty
                                        <span class="text-gray-400">—</span>
                                    @endforelse
                                </td>
                                <td class="px-4 py-2 text-gray-600">{{ $etab->subscription_ends_at?->format('d/m/Y') ?? 'Illimité' }}</td>
                                <td class="px-4 py-2">
                                    @if($etab->is_featured)
                                        <span class="text-amber-600 font-medium">{{ $etab->featured_until->format('d/m/Y') }}</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-xs">
                                    @if($etab->stripe_subscription_id)
                                        <a href="https://dashboard.stripe.com/subscriptions/{{ $etab->stripe_subscription_id }}" target="_blank" class="text-pink-600 hover:underline">{{ Str::limit($etab->stripe_subscription_id, 18) }}</a>
                                    @else
                                        <span class="text-gray-400">manuel</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-right">
                                    <a href="{{ route('admin.etablissements.edit', $etab) }}" class="text-pink-600 hover:underline text-xs">Modifier</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    {{-- Sponsorisés actifs --}}
    @if($sponsorisesActifs->isNotEmpty())
        <section class="bg-white rounded-lg shadow-sm border mb-8">
            <header class="px-4 py-3 border-b">
                <h2 class="font-semibold">Sponsorisés actifs ({{ $sponsorisesActifs->count() }})</h2>
            </header>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-2">Établissement</th>
                            <th class="px-4 py-2">Ville</th>
                            <th class="px-4 py-2">Fin sponsorisation</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($sponsorisesActifs as $etab)
                            <tr>
                                <td class="px-4 py-2">
                                    <a href="{{ $etab->url }}" class="text-pink-600 hover:underline" target="_blank">{{ $etab->name }}</a>
                                </td>
                                <td class="px-4 py-2 text-gray-600">{{ $etab->city }}</td>
                                <td class="px-4 py-2 text-amber-600 font-medium">{{ $etab->featured_until->format('d/m/Y') }}</td>
                                <td class="px-4 py-2 text-right">
                                    <a href="{{ route('admin.etablissements.edit', $etab) }}" class="text-pink-600 hover:underline text-xs">Modifier</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    {{-- Expirés récents --}}
    @if($expires->isNotEmpty())
        <section class="bg-white rounded-lg shadow-sm border">
            <header class="px-4 py-3 border-b">
                <h2 class="font-semibold text-gray-700">Premium expirés ({{ $expires->count() }})</h2>
                <p class="text-xs text-gray-500">50 derniers — pensez à relancer ces établissements.</p>
            </header>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-2">Établissement</th>
                            <th class="px-4 py-2">Ville</th>
                            <th class="px-4 py-2">Propriétaire</th>
                            <th class="px-4 py-2">Expiré le</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($expires as $etab)
                            <tr>
                                <td class="px-4 py-2"><a href="{{ $etab->url }}" target="_blank" class="text-pink-600 hover:underline">{{ $etab->name }}</a></td>
                                <td class="px-4 py-2 text-gray-600">{{ $etab->city }}</td>
                                <td class="px-4 py-2 text-gray-600">
                                    @foreach($etab->owners as $owner)
                                        <a href="mailto:{{ $owner->email }}" class="text-pink-600 hover:underline">{{ $owner->email }}</a>@if(!$loop->last), @endif
                                    @endforeach
                                </td>
                                <td class="px-4 py-2 text-red-600">{{ $etab->subscription_ends_at?->format('d/m/Y') }}</td>
                                <td class="px-4 py-2 text-right">
                                    <a href="{{ route('admin.etablissements.edit', $etab) }}" class="text-pink-600 hover:underline text-xs">Modifier</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif
@endsection
