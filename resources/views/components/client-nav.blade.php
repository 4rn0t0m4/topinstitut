<nav class="bg-white rounded-lg shadow-sm border p-4 mb-4">
    <h2 class="font-semibold mb-4 text-gray-800">Menu</h2>
    <ul class="space-y-2 text-sm">
        <li>
            <a href="{{ route('client.profil.edit') }}" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 hover:text-pink-600 @if(request()->routeIs('client.profil.*')) bg-pink-50 font-semibold text-pink-600 @endif">
                Modifier mon profil
            </a>
        </li>
        <li>
            <a href="{{ route('client.abonnement.index') }}" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 hover:text-pink-600 @if(request()->routeIs('client.abonnement.*')) bg-pink-50 font-semibold text-pink-600 @endif">
                Mes abonnements
            </a>
        </li>
        <li>
            <a href="{{ route('client.dashboard') }}" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 hover:text-pink-600 @if(request()->routeIs('client.dashboard') || request()->routeIs('client.etablissement.*')) bg-pink-50 font-semibold text-pink-600 @endif">
                Gérer mes établissements
            </a>
        </li>
    </ul>
    <div class="border-t mt-4 pt-4">
        <a href="{{ route('logout') }}" class="block px-3 py-2 rounded-lg text-gray-500 hover:bg-gray-50 hover:text-pink-600 text-sm">
            Déconnexion
        </a>
    </div>
</nav>
