<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'TopInstitut - Annuaire des instituts de beauté' }}</title>
    <meta name="description" content="{{ $description ?? 'Trouvez les meilleurs instituts de beauté, spas, esthéticiennes et thalassos près de chez vous.' }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-gray-50 flex flex-col" x-data>
    {{-- Header --}}
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="{{ route('home') }}" class="text-xl font-bold text-pink-600">TopInstitut</a>

                <nav class="hidden md:flex items-center gap-6 text-sm">
                    <a href="{{ route('home') }}" class="text-gray-700 hover:text-pink-600">Accueil</a>
                    <a href="{{ route('recherche') }}" class="text-gray-700 hover:text-pink-600">Rechercher</a>
                    <a href="{{ route('etablissement.create') }}" class="text-gray-700 hover:text-pink-600">Ajouter un institut</a>
                    <a href="{{ route('contact') }}" class="text-gray-700 hover:text-pink-600">Contact</a>
                </nav>

                <div class="flex items-center gap-4 text-sm">
                    @auth
                        <a href="{{ route('client.dashboard') }}" class="text-gray-700 hover:text-pink-600">Mon espace</a>
                        @if(auth()->user()->is_admin)
                            <a href="{{ route('admin.dashboard') }}" class="text-gray-700 hover:text-pink-600">Admin</a>
                        @endif
                        <a href="{{ route('logout') }}" class="text-gray-500 hover:text-pink-600">Déconnexion</a>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-pink-600">Connexion</a>
                        <a href="{{ route('register') }}" class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700">Inscription</a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 mt-4">
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="max-w-7xl mx-auto px-4 mt-4">
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Content --}}
    <main class="flex-1">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="bg-gray-800 text-gray-300 mt-12">
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="font-semibold text-white mb-3">TopInstitut</h3>
                    <p class="text-sm">L'annuaire des instituts de beauté en France.</p>
                </div>
                <div>
                    <h3 class="font-semibold text-white mb-3">Liens utiles</h3>
                    <ul class="text-sm space-y-1">
                        <li><a href="{{ route('etablissement.create') }}" class="hover:text-white">Ajouter un institut</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-white">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-semibold text-white mb-3">Informations légales</h3>
                    <ul class="text-sm space-y-1">
                        <li><a href="{{ route('mentions-legales') }}" class="hover:text-white">Mentions légales</a></li>
                        <li><a href="{{ route('confidentialite') }}" class="hover:text-white">Confidentialité</a></li>
                        <li><a href="{{ route('cgv') }}" class="hover:text-white">CGV</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-4 text-sm text-center">
                &copy; {{ date('Y') }} TopInstitut. Tous droits réservés.
            </div>
        </div>
    </footer>
</body>
</html>
