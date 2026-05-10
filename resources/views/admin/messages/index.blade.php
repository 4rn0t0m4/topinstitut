@extends('admin.layouts.app')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Messages reçus</h1>
        <span class="text-sm text-gray-500">{{ $messages->total() }} message(s)</span>
    </div>

    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher (email, nom, contenu)..." class="flex-1 min-w-[200px] border rounded-lg px-3 py-2 text-sm">
        <select name="type" class="border rounded-lg px-3 py-2 text-sm">
            <option value="">Tous les types</option>
            <option value="general" {{ request('type') === 'general' ? 'selected' : '' }}>Contact général</option>
            <option value="contact" {{ request('type') === 'contact' ? 'selected' : '' }}>Contact établissement</option>
            <option value="booking" {{ request('type') === 'booking' ? 'selected' : '' }}>Demande RDV</option>
        </select>
        <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm">Filtrer</button>
    </form>

    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3">Date</th>
                    <th class="text-left px-4 py-3">Type</th>
                    <th class="text-left px-4 py-3">Expéditeur</th>
                    <th class="text-left px-4 py-3">Établissement</th>
                    <th class="text-left px-4 py-3">Contenu</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($messages as $msg)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $msg->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">
                            @if($msg->type === 'general')
                                <span class="bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full">Général</span>
                            @elseif($msg->type === 'booking')
                                <span class="bg-purple-100 text-purple-700 text-xs px-2 py-0.5 rounded-full">RDV</span>
                            @else
                                <span class="bg-gray-100 text-gray-700 text-xs px-2 py-0.5 rounded-full">Contact</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $msg->name ?: '—' }}</div>
                            <div class="text-xs text-gray-500">{{ $msg->email }}</div>
                        </td>
                        <td class="px-4 py-3 text-gray-500">
                            @if($msg->establishment)
                                <a href="{{ $msg->establishment->url }}" target="_blank" class="text-pink-600 hover:underline">{{ $msg->establishment->name }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-700 max-w-md truncate">{{ Str::limit($msg->content, 80) }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.messages.show', $msg) }}" class="text-pink-600 hover:underline">Voir</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Aucun message.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $messages->links() }}</div>
@endsection
