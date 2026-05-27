@php
    $sections = [
        'Pilotage' => [
            ['client.dashboard', 'client.dashboard|client.etablissement.*', 'Tableau de bord', 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
        ],
        'Mon compte' => [
            ['client.profil.edit', 'client.profil.*', 'Mon profil', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
            ['client.abonnement.index', 'client.abonnement.*', 'Mes abonnements', 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
        ],
        'Mon activité' => [
            ['client.mes-avis', 'client.mes-avis', 'Mes avis', 'M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z'],
            ['client.favoris', 'client.favoris', 'Mes favoris', 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
        ],
    ];
@endphp

<nav class="bg-white rounded-2xl shadow-sm border border-gray-100 p-3 sticky top-6">
    <div class="flex items-center gap-2 px-3 py-2 mb-1">
        <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-pink-500 to-fuchsia-500 text-white flex items-center justify-center font-bold text-sm">
            {{ mb_strtoupper(mb_substr(auth()->user()->username ?? 'M', 0, 1)) }}
        </span>
        <div class="min-w-0">
            <div class="text-sm font-semibold text-gray-900 truncate">{{ auth()->user()->username }}</div>
            <div class="text-xs text-gray-400">Espace pro</div>
        </div>
    </div>

    @foreach($sections as $label => $links)
        <div class="mt-3 first:mt-1">
            <div class="px-3 mb-1 text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $label }}</div>
            <ul class="space-y-0.5">
                @foreach($links as [$route, $pattern, $title, $icon])
                    @php $active = request()->routeIs(...explode('|', $pattern)); @endphp
                    <li>
                        <a href="{{ route($route) }}"
                           class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm transition
                                  {{ $active ? 'bg-pink-50 text-pink-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-pink-600' }}">
                            <svg class="w-5 h-5 flex-shrink-0 {{ $active ? 'text-pink-600' : 'text-gray-400 group-hover:text-pink-500' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
                            </svg>
                            {{ $title }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach

    <div class="border-t border-gray-100 mt-3 pt-2">
        <a href="{{ route('logout') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm text-gray-400 hover:bg-gray-50 hover:text-pink-600 transition">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            Déconnexion
        </a>
    </div>
</nav>
