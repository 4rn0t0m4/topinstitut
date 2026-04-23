<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'TopInstitut' }}</title>
    <meta name="robots" content="noindex, nofollow">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center" x-data>
    <div class="w-full max-w-md px-6 py-8">
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="text-2xl font-bold text-pink-600">TopInstitut</a>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-6">
            {{ $slot }}
        </div>
    </div>
</body>
</html>
