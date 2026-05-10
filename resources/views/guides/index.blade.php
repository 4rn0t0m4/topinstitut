<x-layouts.app
    title="Guides beauté & bien-être - TopInstitut"
    description="Guides pratiques sur la beauté, le bien-être, les soins esthétiques. Conseils, comparatifs, top des meilleurs instituts."
>
    <div class="max-w-6xl mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Guides beauté & bien-être</h1>
        <p class="text-gray-500 mb-8">Conseils pratiques, comparatifs et tops pour bien choisir vos soins.</p>

        @if($guides->isEmpty())
            <p class="text-gray-500">Aucun guide disponible pour le moment.</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($guides as $guide)
                    <article class="bg-white rounded-lg shadow-sm border overflow-hidden hover:shadow-md transition">
                        <a href="{{ $guide->url }}" class="block">
                            @if($guide->cover_image)
                                <img src="{{ $guide->cover_image }}" alt="{{ $guide->title }}" loading="lazy" decoding="async" width="400" height="200" class="w-full h-44 object-cover">
                            @else
                                <div class="w-full h-44 bg-gradient-to-br from-pink-100 to-pink-200 flex items-center justify-center">
                                    <svg class="w-12 h-12 text-pink-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                </div>
                            @endif
                            <div class="p-5">
                                <h2 class="font-semibold text-lg text-gray-900 hover:text-pink-600 line-clamp-2">{{ $guide->title }}</h2>
                                @if($guide->excerpt)
                                    <p class="text-sm text-gray-500 mt-2 line-clamp-3">{{ $guide->excerpt }}</p>
                                @endif
                                <p class="text-xs text-gray-400 mt-3">{{ ($guide->published_at ?? $guide->created_at)->isoFormat('D MMMM Y') }}</p>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>

            <div class="mt-8">{{ $guides->links() }}</div>
        @endif
    </div>
</x-layouts.app>
