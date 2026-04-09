@extends('admin.layouts.app')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Tableau de bord</h1>

    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <p class="text-sm text-gray-500">Établissements</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['etablissements'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <p class="text-sm text-gray-500">En attente validation</p>
            <p class="text-3xl font-bold text-orange-500 mt-1">{{ $stats['etablissements_en_attente'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <p class="text-sm text-gray-500">Avis en attente</p>
            <p class="text-3xl font-bold text-orange-500 mt-1">{{ $stats['avis_en_attente'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <p class="text-sm text-gray-500">Utilisateurs</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['utilisateurs'] }}</p>
        </div>
    </div>
@endsection
