<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <title>Admin - {{ $title ?? 'TopInstitut' }}</title>
    <meta name="robots" content="noindex, nofollow">
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body class="min-h-screen bg-gray-100" x-data="{ sidebarOpen: true }">
    <div class="flex">
        {{-- Sidebar --}}
        <aside class="w-64 bg-gray-900 text-white min-h-screen" x-show="sidebarOpen">
            <div class="p-4">
                <a href="{{ route('admin.dashboard') }}" class="text-xl font-bold text-pink-400">TopInstitut</a>
                <span class="text-xs text-gray-500 block">Administration</span>
            </div>
            <nav class="mt-4">
                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm hover:bg-gray-800 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-800 text-pink-400' : '' }}">
                    Tableau de bord
                </a>
                <a href="{{ route('admin.etablissements.index') }}" class="block px-4 py-2 text-sm hover:bg-gray-800 {{ request()->routeIs('admin.etablissements.*') ? 'bg-gray-800 text-pink-400' : '' }}">
                    Établissements
                </a>
                <a href="{{ route('admin.avis.index') }}" class="block px-4 py-2 text-sm hover:bg-gray-800 {{ request()->routeIs('admin.avis.*') ? 'bg-gray-800 text-pink-400' : '' }}">
                    Avis
                </a>
                <a href="{{ route('admin.categories.index') }}" class="block px-4 py-2 text-sm hover:bg-gray-800 {{ request()->routeIs('admin.categories.*') ? 'bg-gray-800 text-pink-400' : '' }}">
                    Catégories
                </a>
                <a href="{{ route('admin.imports.index') }}" class="block px-4 py-2 text-sm hover:bg-gray-800 {{ request()->routeIs('admin.imports.*') ? 'bg-gray-800 text-pink-400' : '' }}">
                    Imports Google
                </a>
                <div class="border-t border-gray-800 mt-4 pt-4">
                    <a href="{{ route('home') }}" class="block px-4 py-2 text-sm text-gray-400 hover:bg-gray-800">Retour au site</a>
                    <a href="{{ route('logout') }}" class="block px-4 py-2 text-sm text-gray-400 hover:bg-gray-800">Déconnexion</a>
                </div>
            </nav>
        </aside>

        {{-- Main content --}}
        <div class="flex-1">
            <header class="bg-white shadow-sm px-6 py-4 flex items-center justify-between">
                <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <span class="text-sm text-gray-600">{{ auth()->user()->username }}</span>
            </header>

            @if(session('success'))
                <div class="mx-6 mt-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <main class="p-6">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
