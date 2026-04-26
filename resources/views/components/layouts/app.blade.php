<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Favicons --}}
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <meta name="theme-color" content="#db2777">
    <title>{{ $title ?? 'TopInstitut - Annuaire des instituts de beauté' }}</title>
    <meta name="description" content="{{ $description ?? 'Trouvez les meilleurs instituts de beauté, spas, esthéticiennes et thalassos près de chez vous.' }}">
    <link rel="canonical" href="{{ $canonical ?? url()->current() }}">
    {{-- Open Graph --}}
    <meta property="og:title" content="{{ $title ?? 'TopInstitut - Annuaire des instituts de beauté' }}">
    <meta property="og:description" content="{{ $description ?? 'Trouvez les meilleurs instituts de beauté, spas, esthéticiennes et thalassos près de chez vous.' }}">
    <meta property="og:url" content="{{ $canonical ?? url()->current() }}">
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:site_name" content="TopInstitut">
    @if(isset($ogImage))
        <meta property="og:image" content="{{ $ogImage }}">
    @endif
    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $title ?? 'TopInstitut - Annuaire des instituts de beauté' }}">
    <meta name="twitter:description" content="{{ $description ?? 'Trouvez les meilleurs instituts de beauté, spas, esthéticiennes et thalassos près de chez vous.' }}">
    @if(isset($ogImage))
        <meta name="twitter:image" content="{{ $ogImage }}">
    @endif
    @if(isset($noindex))
        <meta name="robots" content="noindex, nofollow">
    @endif
    @auth
        <meta name="auth-user" content="{{ auth()->id() }}">
        <meta name="auth-favorites" content="{{ auth()->user()->favorites()->pluck('establishment_id')->implode(',') }}">
    @endauth
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
    @production
        @if(! isset($noindex))
            {{-- Google Analytics --}}
            <script async src="https://www.googletagmanager.com/gtag/js?id=G-XDTGPZW1WL"></script>
            <script>
                window.dataLayer = window.dataLayer || [];
                function gtag(){dataLayer.push(arguments);}
                gtag('js', new Date());
                gtag('config', 'G-XDTGPZW1WL');
            </script>
        @endif
    @endproduction
</head>
<body class="min-h-screen bg-gray-50 flex flex-col overflow-x-hidden" x-data="{ mobileMenu: false }">
    {{-- Header --}}
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20 gap-4 sm:gap-6">
                <a href="{{ route('home') }}" class="flex items-center flex-shrink-0 min-w-0">
                    <img src="{{ asset('logo-top-institut-rect.jpg') }}" alt="TopInstitut" class="h-10 sm:h-12 w-auto max-h-12">
                </a>

                <nav class="hidden md:flex items-center gap-6 text-sm">
                    <a href="{{ route('home') }}" class="text-gray-700 hover:text-pink-600">Accueil</a>
                    <a href="{{ route('recherche') }}" class="text-gray-700 hover:text-pink-600">Rechercher</a>
                    <a href="{{ route('etablissement.create') }}" class="text-gray-700 hover:text-pink-600">Ajouter un institut</a>
                    <a href="{{ route('contact') }}" class="text-gray-700 hover:text-pink-600">Contact</a>
                </nav>

                <div class="hidden md:flex items-center gap-4 text-sm">
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

                <button type="button"
                        @click="mobileMenu = !mobileMenu"
                        class="md:hidden inline-flex items-center justify-center p-2 -mr-2 rounded-lg text-gray-700 hover:bg-gray-100"
                        :aria-expanded="mobileMenu"
                        aria-label="Menu">
                    <svg x-show="!mobileMenu" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg x-show="mobileMenu" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div x-show="mobileMenu" x-cloak class="md:hidden border-t py-3 space-y-1 text-sm">
                <a href="{{ route('home') }}" class="block px-2 py-2 rounded-lg text-gray-700 hover:bg-gray-50 hover:text-pink-600">Accueil</a>
                <a href="{{ route('recherche') }}" class="block px-2 py-2 rounded-lg text-gray-700 hover:bg-gray-50 hover:text-pink-600">Rechercher</a>
                <a href="{{ route('etablissement.create') }}" class="block px-2 py-2 rounded-lg text-gray-700 hover:bg-gray-50 hover:text-pink-600">Ajouter un institut</a>
                <a href="{{ route('contact') }}" class="block px-2 py-2 rounded-lg text-gray-700 hover:bg-gray-50 hover:text-pink-600">Contact</a>
                <div class="border-t pt-2 mt-2 space-y-1">
                    @auth
                        <a href="{{ route('client.dashboard') }}" class="block px-2 py-2 rounded-lg text-gray-700 hover:bg-gray-50 hover:text-pink-600">Mon espace</a>
                        @if(auth()->user()->is_admin)
                            <a href="{{ route('admin.dashboard') }}" class="block px-2 py-2 rounded-lg text-gray-700 hover:bg-gray-50 hover:text-pink-600">Admin</a>
                        @endif
                        <a href="{{ route('logout') }}" class="block px-2 py-2 rounded-lg text-gray-500 hover:bg-gray-50 hover:text-pink-600">Déconnexion</a>
                    @else
                        <a href="{{ route('login') }}" class="block px-2 py-2 rounded-lg text-gray-700 hover:bg-gray-50 hover:text-pink-600">Connexion</a>
                        <a href="{{ route('register') }}" class="block px-2 py-2 rounded-lg bg-pink-600 text-white text-center hover:bg-pink-700">Inscription</a>
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

    @if(isset($errors) && $errors->any())
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
    @stack('jsonld')
    @stack('scripts')
</body>
</html>
