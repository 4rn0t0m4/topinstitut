@props(['etablissement', 'compact' => false])

@php
    $features = $etablissement->features ?? [];
@endphp

@if(! empty($features))
    <div class="flex flex-wrap gap-1.5 {{ $compact ? '' : 'mt-3' }}">
        @foreach($features as $key)
            @if(isset(\App\Models\Establishment::FEATURES[$key]))
                <span class="inline-flex items-center gap-1 bg-pink-50 text-pink-700 text-xs font-medium px-2 py-0.5 rounded-full border border-pink-100">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    {{ \App\Models\Establishment::FEATURES[$key] }}
                </span>
            @endif
        @endforeach
    </div>
@endif
