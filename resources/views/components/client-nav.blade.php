<nav class="bg-white rounded-lg shadow-sm border p-4 mb-6">
    <ul class="space-y-2 text-sm">
        <li>
            <a href="{{ route('client.profil.edit') }}" class="text-gray-700 hover:text-pink-600 @if(request()->routeIs('client.profil.*')) font-semibold text-pink-600 @endif">
                Modifier mon profil
            </a>
        </li>
        <li>
            <a href="{{ route('client.abonnement.index') }}" class="text-gray-700 hover:text-pink-600 @if(request()->routeIs('client.abonnement.*')) font-semibold text-pink-600 @endif">
                Mes abonnements
            </a>
        </li>
        <li>
            <a href="{{ route('client.dashboard') }}" class="text-gray-700 hover:text-pink-600 @if(request()->routeIs('client.dashboard') || request()->routeIs('client.etablissement.*')) font-semibold text-pink-600 @endif">
                Gérer mes établissements
            </a>
        </li>
    </ul>
    <div class="border-t mt-4 pt-4">
        <a href="{{ route('logout') }}" class="text-gray-500 hover:text-pink-600 text-sm">
            Déconnexion
        </a>
    </div>
</nav>
