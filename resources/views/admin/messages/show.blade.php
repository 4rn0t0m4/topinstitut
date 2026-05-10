@extends('admin.layouts.app')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.messages.index') }}" class="text-sm text-gray-500 hover:text-pink-600">← Retour aux messages</a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border p-6 max-w-3xl">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h1 class="text-xl font-bold">{{ $message->name ?: 'Anonyme' }}</h1>
                <p class="text-sm text-gray-500">
                    <a href="mailto:{{ $message->email }}" class="text-pink-600 hover:underline">{{ $message->email }}</a>
                    @if($message->phone)
                        · {{ $message->phone }}
                    @endif
                </p>
                <p class="text-xs text-gray-400 mt-1">{{ $message->created_at->format('d/m/Y à H:i') }}</p>
            </div>
            @if($message->type === 'general')
                <span class="bg-blue-100 text-blue-700 text-xs px-2 py-1 rounded-full">Contact général</span>
            @elseif($message->type === 'booking')
                <span class="bg-purple-100 text-purple-700 text-xs px-2 py-1 rounded-full">Demande RDV</span>
            @else
                <span class="bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded-full">Contact établissement</span>
            @endif
        </div>

        @if($message->establishment)
            <div class="bg-pink-50 border border-pink-100 rounded-lg p-3 mb-4 text-sm">
                Concernant : <a href="{{ $message->establishment->url }}" target="_blank" class="text-pink-600 hover:underline font-medium">{{ $message->establishment->name }}</a>
            </div>
        @endif

        @if($message->type === 'booking')
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-4 text-sm">
                @if($message->requested_date)
                    <div><span class="text-gray-500">Date :</span> {{ $message->requested_date->format('d/m/Y') }}</div>
                @endif
                @if($message->requested_time)
                    <div><span class="text-gray-500">Heure :</span> {{ $message->requested_time }}</div>
                @endif
                @if($message->requested_service)
                    <div><span class="text-gray-500">Prestation :</span> {{ $message->requested_service }}</div>
                @endif
            </div>
        @endif

        <div class="prose prose-sm max-w-none">
            <p class="text-gray-700 whitespace-pre-wrap">{{ $message->content }}</p>
        </div>

        <div class="flex gap-3 mt-6 pt-6 border-t">
            <a href="mailto:{{ $message->email }}?subject=Re: votre message sur TopInstitut" class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700 text-sm">Répondre par email</a>
            <form action="{{ route('admin.messages.destroy', $message) }}" method="POST" onsubmit="return confirm('Supprimer ce message ?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 text-sm">Supprimer</button>
            </form>
        </div>
    </div>
@endsection
