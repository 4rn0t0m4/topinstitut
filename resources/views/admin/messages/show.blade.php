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
            <div class="flex flex-col items-end gap-2">
                @if($message->type === 'general')
                    <span class="bg-blue-100 text-blue-700 text-xs px-2 py-1 rounded-full">Contact général</span>
                @elseif($message->type === 'booking')
                    <span class="bg-purple-100 text-purple-700 text-xs px-2 py-1 rounded-full">Demande RDV</span>
                @else
                    <span class="bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded-full">Contact établissement</span>
                @endif
                @if($message->handled_at)
                    <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full" title="Traité le {{ $message->handled_at->format('d/m/Y H:i') }}">✓ Traité</span>
                @else
                    <span class="bg-orange-100 text-orange-700 text-xs px-2 py-1 rounded-full">À traiter</span>
                @endif
            </div>
        </div>

        @if($message->establishment)
            <div class="bg-pink-50 border border-pink-100 rounded-lg p-3 mb-4 text-sm">
                Concernant : <a href="{{ $message->establishment->url }}" target="_blank" class="text-pink-600 hover:underline font-medium">{{ $message->establishment->name }}</a>
                @if($message->establishment->email)
                    <span class="text-gray-500"> · email : {{ $message->establishment->email }}</span>
                @else
                    <span class="text-orange-600"> · pas d'email renseigné</span>
                @endif
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

        @error('forward')
            <p class="text-red-600 text-sm mt-4">{{ $message }}</p>
        @enderror

        <div class="flex flex-wrap gap-2 mt-6 pt-6 border-t">
            <a href="mailto:{{ $message->email }}?subject=Re: votre message sur TopInstitut" class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700 text-sm">Répondre par email</a>

            @if($message->establishment && $message->establishment->email)
                <form action="{{ route('admin.messages.forward', $message) }}" method="POST" onsubmit="return confirm('Envoyer ce message à {{ $message->establishment->email }} ?')">
                    @csrf
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-sm">Transférer à l'établissement</button>
                </form>
            @endif

            <form action="{{ route('admin.messages.toggle-handled', $message) }}" method="POST">
                @csrf
                @if($message->handled_at)
                    <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm">Marquer non traité</button>
                @else
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm">Marquer comme traité</button>
                @endif
            </form>

            <form action="{{ route('admin.messages.destroy', $message) }}" method="POST" onsubmit="return confirm('Supprimer ce message ?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 text-sm">Supprimer</button>
            </form>
        </div>
    </div>

    @if($otherFromSender->isNotEmpty())
        <div class="mt-8 max-w-3xl">
            <h2 class="text-sm font-semibold text-gray-700 mb-3">Autres messages de {{ $message->email }} ({{ $otherFromSender->count() }})</h2>
            <div class="bg-white rounded-lg shadow-sm border divide-y">
                @foreach($otherFromSender as $other)
                    <a href="{{ route('admin.messages.show', $other) }}" class="flex items-center gap-3 p-3 hover:bg-gray-50 text-sm">
                        <span class="text-xs text-gray-400 w-24 shrink-0">{{ $other->created_at->format('d/m/Y H:i') }}</span>
                        @if($other->type === 'general')
                            <span class="bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full w-20 text-center">Général</span>
                        @elseif($other->type === 'booking')
                            <span class="bg-purple-100 text-purple-700 text-xs px-2 py-0.5 rounded-full w-20 text-center">RDV</span>
                        @else
                            <span class="bg-gray-100 text-gray-700 text-xs px-2 py-0.5 rounded-full w-20 text-center">Contact</span>
                        @endif
                        <span class="text-gray-700 truncate flex-1">{{ Str::limit($other->content, 100) }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
@endsection
