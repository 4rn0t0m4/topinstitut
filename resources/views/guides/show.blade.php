<x-layouts.app
    :title="$guide->title . ' - TopInstitut'"
    :description="$guide->meta_description ?: $guide->excerpt"
    :ogImage="$guide->cover_image"
    ogType="article"
>
    @push('jsonld')
    <x-breadcrumb-jsonld :items="[
        ['name' => 'Accueil', 'url' => '/'],
        ['name' => 'Guides', 'url' => '/guides'],
        ['name' => $guide->title],
    ]" />
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $guide->title,
        'description' => $guide->meta_description ?: $guide->excerpt,
        'image' => $guide->cover_image,
        'author' => ['@type' => 'Organization', 'name' => $guide->author],
        'datePublished' => ($guide->published_at ?? $guide->created_at)->toIso8601String(),
        'dateModified' => $guide->updated_at->toIso8601String(),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    @endpush

    <div class="max-w-3xl mx-auto px-4 py-8">
        <nav class="text-sm text-gray-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-pink-600">Accueil</a>
            <span class="mx-1">/</span>
            <a href="{{ route('guides.index') }}" class="hover:text-pink-600">Guides</a>
            <span class="mx-1">/</span>
            <span class="text-gray-900">{{ $guide->title }}</span>
        </nav>

        <article>
            @if($guide->cover_image)
                <img src="{{ $guide->cover_image }}" alt="{{ $guide->title }}" class="w-full h-64 sm:h-80 object-cover rounded-lg mb-6" decoding="async">
            @endif

            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-3">{{ $guide->title }}</h1>
            <p class="text-sm text-gray-400 mb-8">
                Par {{ $guide->author }} · {{ ($guide->published_at ?? $guide->created_at)->isoFormat('D MMMM Y') }}
            </p>

            <div class="text-gray-700 [&_h2]:text-2xl [&_h2]:font-semibold [&_h2]:mt-8 [&_h2]:mb-3 [&_h2]:text-gray-900 [&_h3]:text-xl [&_h3]:font-semibold [&_h3]:mt-6 [&_h3]:mb-2 [&_h3]:text-gray-900 [&_p]:my-3 [&_p]:leading-relaxed [&_a]:text-pink-600 [&_a]:underline hover:[&_a]:text-pink-700 [&_ul]:list-disc [&_ul]:ml-6 [&_ul]:my-3 [&_ol]:list-decimal [&_ol]:ml-6 [&_ol]:my-3 [&_li]:my-1 [&_strong]:font-semibold [&_strong]:text-gray-900 [&_img]:rounded-lg [&_img]:my-4 [&_blockquote]:border-l-4 [&_blockquote]:border-pink-300 [&_blockquote]:pl-4 [&_blockquote]:italic [&_blockquote]:text-gray-600">
                {!! $guide->body !!}
            </div>
        </article>

        @if($related->isNotEmpty())
            <div class="mt-12 pt-8 border-t">
                <h2 class="text-xl font-semibold mb-4">Autres guides</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    @foreach($related as $r)
                        <a href="{{ $r->url }}" class="block bg-white border rounded-lg p-4 hover:shadow-md transition">
                            <h3 class="font-medium text-gray-900 hover:text-pink-600 line-clamp-2">{{ $r->title }}</h3>
                            @if($r->excerpt)
                                <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $r->excerpt }}</p>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
