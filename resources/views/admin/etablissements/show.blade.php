@extends('admin.layouts.app')

@section('content')
    <h1 class="text-2xl font-bold mb-6">{{ $etablissement->titre }}</h1>
    <a href="{{ route('admin.etablissements.edit', $etablissement) }}" class="text-pink-600 hover:underline">Modifier</a>
@endsection
