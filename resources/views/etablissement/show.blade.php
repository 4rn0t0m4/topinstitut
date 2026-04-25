<x-layouts.app
    :title="$establishment->name . ' - ' . $establishment->type_label . ' à ' . $establishment->city . ' - TopInstitut'"
    :description="$establishment->name . ', ' . strtolower($establishment->type_label) . ' à ' . $establishment->city . ($establishment->review_count > 0 ? '. Note : ' . number_format($establishment->rating, 1, ',', '') . '/5 (' . $establishment->review_count . ' avis)' : '') . '. Adresse, horaires, avis et coordonnées.'"
>
    @php
        $cityRel = $establishment->cityRelation;
        $deptRel = $cityRel?->department;
    @endphp

    @if($establishment->latitude && $establishment->longitude)
        @push('head')
            {{-- Leaflet CSS : chargé en non-bloquant (carte en bas de page) --}}
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9/dist/leaflet.css" media="print" onload="this.media='all'">
            <noscript><link rel="stylesheet" href="https://unpkg.com/leaflet@1.9/dist/leaflet.css"></noscript>
        @endpush
    @endif

    @if($establishment->photos->isNotEmpty())
        @push('head')
            {{-- Préload de la photo LCP (première de la galerie) --}}
            <link rel="preload" as="image" href="{{ $establishment->photos->first()->url }}" fetchpriority="high">
        @endpush
    @endif

    @push('jsonld')
    <x-breadcrumb-jsonld :items="array_filter([
        ['name' => 'Accueil', 'url' => '/'],
        $deptRel ? ['name' => $deptRel->name, 'url' => '/' . $deptRel->slug] : null,
        $cityRel && $deptRel ? ['name' => $cityRel->name, 'url' => '/' . $deptRel->slug . '/' . $cityRel->slug] : null,
        $cityRel && $deptRel ? ['name' => $establishment->type_label, 'url' => '/' . $deptRel->slug . '/' . $cityRel->slug . '/' . $establishment->type_slug] : null,
        ['name' => $establishment->name],
    ])" />
    @endpush

    {{-- Schema.org LocalBusiness --}}
    @php
        $schemaLocal = [
            '@context' => 'https://schema.org',
            '@type' => 'BeautySalon',
            'name' => $establishment->name,
            'description' => $establishment->tagline ?: Str::limit(strip_tags($establishment->description ?? ''), 200),
            'url' => url($establishment->url),
            'telephone' => $establishment->phone,
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $establishment->address,
                'postalCode' => $establishment->postal_code,
                'addressLocality' => $establishment->city,
                'addressCountry' => 'FR',
            ],
        ];
        if ($establishment->latitude) {
            $schemaLocal['geo'] = ['@type' => 'GeoCoordinates', 'latitude' => $establishment->latitude, 'longitude' => $establishment->longitude];
        }
        if ($establishment->review_count > 0) {
            $schemaLocal['aggregateRating'] = ['@type' => 'AggregateRating', 'ratingValue' => number_format($establishment->rating, 1, '.', ''), 'bestRating' => '5', 'worstRating' => '1', 'ratingCount' => $establishment->review_count];
        }

        // OpeningHoursSpecification
        $dayMap = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];
        $openingHours = [];
        foreach ($establishment->schedules as $s) {
            if ($s->is_closed || !isset($dayMap[$s->day_of_week])) continue;
            $spec = ['@type' => 'OpeningHoursSpecification', 'dayOfWeek' => $dayMap[$s->day_of_week]];
            if ($s->open_am && $s->close_am) {
                $spec['opens'] = substr($s->open_am, 0, 5);
                $spec['closes'] = substr($s->close_pm ?? $s->close_am, 0, 5);
            }
            $openingHours[] = $spec;
        }
        if (!empty($openingHours)) {
            $schemaLocal['openingHoursSpecification'] = $openingHours;
        }

        // Individual Review schemas
        $reviews = [];
        foreach ($establishment->approvedReviews->take(10) as $review) {
            $reviews[] = [
                '@type' => 'Review',
                'author' => ['@type' => 'Person', 'name' => $review->reviewer_name],
                'datePublished' => $review->created_at->toIso8601String(),
                'name' => $review->title,
                'reviewBody' => Str::limit($review->content, 300),
                'reviewRating' => ['@type' => 'Rating', 'ratingValue' => number_format($review->average_rating, 1, '.', ''), 'bestRating' => '5', 'worstRating' => '1'],
            ];
        }
        if (!empty($reviews)) {
            $schemaLocal['review'] = $reviews;
        }
    @endphp
    @push('jsonld')
    <script type="application/ld+json">
    {!! json_encode($schemaLocal, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
    @endpush

    <div class="max-w-7xl mx-auto px-4 py-8">
        {{-- Breadcrumb --}}
        <nav class="text-sm text-gray-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-pink-600">Accueil</a>
            @if($deptRel)
                <span class="mx-1">/</span>
                <span>{{ $deptRel->region }}</span>
                <span class="mx-1">/</span>
                <a href="{{ route('departement.show', $deptRel->slug) }}" class="hover:text-pink-600">{{ $deptRel->name }}</a>
            @endif
            @if($cityRel && $deptRel)
                <span class="mx-1">/</span>
                <a href="{{ route('ville.show', [$deptRel->slug, $cityRel->slug]) }}" class="hover:text-pink-600">{{ $cityRel->name }}</a>
                <span class="mx-1">/</span>
                <a href="/{{ $deptRel->slug }}/{{ $cityRel->slug }}/{{ $establishment->type_slug }}" class="hover:text-pink-600">{{ $establishment->type_label }}</a>
            @endif
            <span class="mx-1">/</span>
            <span class="text-gray-900">{{ $establishment->name }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Main content --}}
            <div class="lg:col-span-2 min-w-0">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 break-words">{{ $establishment->name }}</h1>
                <div class="flex items-center gap-3 mt-1">
                    <span class="text-pink-600">{{ $establishment->type_label }}</span>
                    <x-statut-ouverture :etablissement="$establishment" />
                </div>

                @if($establishment->review_count > 0)
                    <div class="flex items-center gap-2 mt-3">
                        <x-star-rating :rating="$establishment->rating" />
                        <span class="font-semibold">{{ number_format($establishment->rating, 1, ',', '') }}/5</span>
                        <span class="text-gray-500">({{ $establishment->review_count }} avis)</span>
                    </div>
                @elseif($establishment->google_rating)
                    <div class="flex items-center gap-2 mt-3">
                        <x-star-rating :rating="$establishment->google_rating" />
                        <span class="font-semibold">{{ number_format($establishment->google_rating, 1, ',', '') }}/5</span>
                        @if($establishment->google_review_count > 0)
                            <span class="text-gray-500">({{ $establishment->google_review_count }} avis Google)</span>
                        @endif
                    </div>
                @endif

                @if($establishment->city_rank > 0 && $totalInCity > 1)
                    <p class="text-sm text-gray-600 mt-2">
                        Classé <span class="font-semibold text-pink-600">n°{{ $establishment->city_rank }}</span>
                        sur {{ $totalInCity }} instituts de beauté à {{ $establishment->city }}
                    </p>
                @endif

                {{-- Photos --}}
                @if($establishment->photos->isNotEmpty())
                    @php
                        $photoUrls = $establishment->photos->pluck('url')->values()->all();
                    @endphp
                    <div class="mt-6 grid grid-cols-2 md:grid-cols-3 gap-2">
                        @foreach($establishment->photos as $i => $photo)
                            <button type="button"
                                    @click="$store.lightbox.show(@js($photoUrls), {{ $i }})"
                                    class="block group relative w-full min-w-0 overflow-hidden rounded-lg cursor-pointer">
                                <img src="{{ $photo->url }}"
                                     alt="{{ $establishment->name }}"
                                     width="400" height="300"
                                     @if($i === 0) fetchpriority="high" decoding="async" @else loading="lazy" decoding="async" @endif
                                     class="object-cover h-40 sm:h-48 w-full max-w-full transition group-hover:scale-105">
                            </button>
                        @endforeach
                    </div>
                @endif

                {{-- Coordonnées & carte --}}
                <div class="mt-8 bg-white border rounded-lg p-6">
                    <h2 class="text-xl font-semibold mb-4">Coordonnées</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="space-y-3">
                            @if($establishment->address)
                                <div>
                                    <p class="text-sm text-gray-500">Adresse</p>
                                    <p class="text-sm font-medium">{{ $establishment->address }}<br>{{ $establishment->postal_code }} {{ $establishment->city }}</p>
                                </div>
                            @endif

                            @if($establishment->phone)
                                <x-phone-reveal :phone="$establishment->phone" :etablissement-id="$establishment->id" label="Téléphone" />
                            @endif

                            @if($establishment->mobile)
                                <x-phone-reveal :phone="$establishment->mobile" :etablissement-id="$establishment->id" label="Portable" :portable="true" />
                            @endif

                            <div class="pt-2 space-y-2">
                                <button @click="$store.bookingModal.open = true" type="button" class="w-full flex items-center justify-center gap-2 bg-pink-600 text-white font-semibold py-3 px-5 rounded-lg hover:bg-pink-700 transition cursor-pointer">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0V10.5h18v8.25"/></svg>
                                    Prendre RDV
                                </button>
                                <button @click="$store.contactModal.open = true" type="button" class="w-full flex items-center justify-center gap-2 bg-white border-2 border-pink-600 text-pink-600 font-semibold py-3 px-5 rounded-lg hover:bg-pink-50 transition cursor-pointer">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                                    Contacter
                                </button>
                            </div>
                            @if(!$establishment->owners->contains(auth()->id()))
                                <div class="pt-1">
                                    @auth
                                        <button @click="$store.claimModal.open = true" type="button" class="w-full text-center text-sm text-gray-500 hover:text-pink-600 transition cursor-pointer underline">
                                            Vous êtes le propriétaire ? Revendiquez cet établissement
                                        </button>
                                    @else
                                        <a href="{{ route('login') }}" class="block w-full text-center text-sm text-gray-500 hover:text-pink-600 transition underline">
                                            Vous êtes le propriétaire ? Connectez-vous pour revendiquer
                                        </a>
                                    @endauth
                                </div>
                            @endif
                        </div>

                        @if($establishment->latitude && $establishment->longitude)
                            <div class="rounded-lg overflow-hidden border" id="map" style="min-height: 220px;"></div>
                        @endif
                    </div>
                </div>

                {{-- Description --}}
                @if($establishment->description)
                    <div class="mt-8">
                        <h2 class="text-xl font-semibold mb-3">Présentation</h2>
                        <div class="prose max-w-none text-gray-700 break-words">{!! nl2br(e($establishment->description)) !!}</div>
                    </div>
                @endif

                {{-- Prestations & tarifs --}}
                @if($establishment->services && count($establishment->services) > 0)
                    <div class="mt-8">
                        <h2 class="text-xl font-semibold mb-3">Prestations & tarifs</h2>
                        <div class="bg-white border rounded-lg divide-y">
                            @foreach($establishment->services as $service)
                                <div class="flex items-center justify-between gap-4 px-4 py-3">
                                    <div class="min-w-0 flex-1">
                                        <div class="font-medium text-gray-900">{{ $service['name'] ?? '' }}</div>
                                        @if(! empty($service['description']))
                                            <div class="text-sm text-gray-500 mt-0.5">{{ $service['description'] }}</div>
                                        @endif
                                        @if(! empty($service['duration']))
                                            <div class="text-xs text-gray-400 mt-1">{{ $service['duration'] }}</div>
                                        @endif
                                    </div>
                                    @if(isset($service['price']) && $service['price'] !== '')
                                        <div class="flex-shrink-0 text-pink-600 font-semibold whitespace-nowrap">{{ $service['price'] }}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @elseif($establishment->pricing)
                    <div class="mt-8">
                        <h2 class="text-xl font-semibold mb-3">Tarifs</h2>
                        <div class="prose max-w-none text-gray-700 break-words">{!! nl2br(e($establishment->pricing)) !!}</div>
                    </div>
                @endif

                {{-- Horaires --}}
                @if($establishment->schedules->isNotEmpty())
                    @php
                        $dayLabels = [1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi', 7 => 'Dimanche'];
                    @endphp
                    <div class="mt-8">
                        <h2 class="text-xl font-semibold mb-3">Horaires</h2>
                        <table class="w-full text-sm">
                            @foreach($establishment->schedules as $s)
                                <tr class="border-b">
                                    <td class="py-2 font-medium">{{ $dayLabels[$s->day_of_week] ?? '' }}</td>
                                    <td class="py-2 text-gray-600">
                                        @if($s->is_closed)
                                            <span class="text-red-500">Fermé</span>
                                        @else
                                            {{ $s->open_am ? substr($s->open_am, 0, 5) . ' - ' . substr($s->close_am, 0, 5) : '' }}
                                            @if($s->open_pm)
                                                / {{ substr($s->open_pm, 0, 5) }} - {{ substr($s->close_pm, 0, 5) }}
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                @endif

                {{-- FAQ --}}
                @if($establishment->faqs->isNotEmpty())
                    <div class="mt-8" x-data="{ open: null }">
                        <h2 class="text-xl font-semibold mb-3">Questions fréquentes</h2>
                        <div class="bg-white border rounded-lg divide-y">
                            @foreach($establishment->faqs as $i => $faq)
                                <div>
                                    <button type="button" @click="open = open === {{ $i }} ? null : {{ $i }}" class="w-full flex items-center justify-between gap-4 px-4 py-3 text-left hover:bg-gray-50 transition">
                                        <span class="font-medium text-gray-900">{{ $faq->question }}</span>
                                        <svg class="w-5 h-5 text-gray-400 transition-transform flex-shrink-0" :class="open === {{ $i }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                    <div x-show="open === {{ $i }}" x-cloak class="px-4 pb-3 text-sm text-gray-700">{!! nl2br(e($faq->answer)) !!}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @push('jsonld')
                    <script type="application/ld+json">
                    {!! json_encode([
                        '@context' => 'https://schema.org',
                        '@type' => 'FAQPage',
                        'mainEntity' => $establishment->faqs->map(fn ($f) => [
                            '@type' => 'Question',
                            'name' => $f->question,
                            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f->answer],
                        ])->values()->all(),
                    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
                    </script>
                    @endpush
                @endif

                {{-- Actualités --}}
                @if($establishment->news->isNotEmpty())
                    <div class="mt-8">
                        <h2 class="text-xl font-semibold mb-3">Actualités</h2>
                        @foreach($establishment->news as $item)
                            <div class="bg-pink-50 border border-pink-100 rounded-lg p-4 mb-3">
                                <h3 class="font-semibold text-pink-700">{{ $item->title }}</h3>
                                @if($item->content)
                                    <p class="text-sm text-gray-700 mt-1">{{ $item->content }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Google Reviews --}}
                @if(!empty($establishment->google_reviews))
                    <div class="mt-8">
                        <h2 class="text-xl font-semibold mb-3 flex items-center gap-2">
                            Avis Google
                            <span class="inline-flex items-center gap-1 text-sm font-normal bg-white border rounded-full px-3 py-0.5">
                                <svg class="w-4 h-4" viewBox="0 0 48 48"><path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303c-1.649 4.657-6.08 8-11.303 8-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"/><path fill="#FF3D00" d="M6.306 14.691l6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691z"/><path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238A11.91 11.91 0 0 1 24 36c-5.202 0-9.619-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z"/><path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303a12.04 12.04 0 0 1-4.087 5.571l.003-.002 6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917z"/></svg>
                                <span class="font-semibold">{{ number_format($establishment->google_rating, 1, ',', '') }}/5</span>
                                <span class="text-gray-500">({{ $establishment->google_review_count }})</span>
                            </span>
                        </h2>
                        <div class="space-y-3">
                            @foreach($establishment->google_reviews as $gr)
                                <div class="bg-white border rounded-lg p-4">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex items-center gap-2">
                                            @if(!empty($gr['photo']))
                                                <img src="{{ $gr['photo'] }}" alt="" class="w-8 h-8 rounded-full" referrerpolicy="no-referrer">
                                            @endif
                                            <span class="font-semibold text-sm">{{ $gr['author'] }}</span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <x-star-rating :rating="$gr['rating']" size="w-4 h-4" />
                                            @if(!empty($gr['date']))
                                                <span class="text-xs text-gray-400 ml-2">{{ \Carbon\Carbon::parse($gr['date'])->format('d/m/Y') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-700">{{ $gr['text'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Reviews --}}
                <div class="mt-8">
                    <h2 class="text-xl font-semibold mb-3">Avis des utilisateurs ({{ $establishment->approvedReviews->count() }})</h2>

                    @foreach($establishment->approvedReviews as $review)
                        <div class="bg-white border rounded-lg p-4 mb-4" id="avis-{{ $review->id }}">
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="font-semibold">{{ $review->reviewer_name }}</span>
                                    <span class="text-sm text-gray-400 ml-2">{{ $review->created_at->format('d/m/Y') }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <x-star-rating :rating="$review->average_rating" size="w-4 h-4" />
                                    <span class="text-sm font-semibold">{{ number_format($review->average_rating, 1, ',', '') }}</span>
                                </div>
                            </div>

                            <h3 class="font-medium mt-2">{{ $review->title }}</h3>
                            <p class="text-sm text-gray-700 mt-1">{{ $review->content }}</p>

                            <div class="grid grid-cols-3 sm:grid-cols-6 gap-2 mt-3 text-xs text-gray-500">
                                <div>Accueil: {{ $review->rating_welcome }}/5</div>
                                <div>Qualité: {{ $review->rating_quality }}/5</div>
                                <div>Choix: {{ $review->rating_variety }}/5</div>
                                <div>Prix: {{ $review->rating_price }}/5</div>
                                <div>Ambiance: {{ $review->rating_ambiance }}/5</div>
                                <div>Propreté: {{ $review->rating_cleanliness }}/5</div>
                            </div>

                            @if($review->reply)
                                <div class="bg-gray-50 rounded-lg p-3 mt-3 text-sm">
                                    <span class="font-medium text-pink-600">Réponse de l'établissement :</span>
                                    <p class="text-gray-700 mt-1">{{ $review->reply }}</p>
                                </div>
                            @endif
                        </div>
                    @endforeach

                    {{-- Review form --}}
                    <div class="bg-white border rounded-lg p-6 mt-6" x-data="{ submitError: '' }">
                        <h3 class="text-lg font-semibold mb-4">Donner votre avis</h3>
                        <form action="{{ route('avis.store') }}" method="POST" @submit="
                            const ratings = ['rating_welcome','rating_quality','rating_variety','rating_price','rating_ambiance','rating_cleanliness'];
                            const missing = ratings.filter(n => !$el.querySelector('[name='+n+']').value || $el.querySelector('[name='+n+']').value === '0');
                            if (missing.length) { submitError = 'Veuillez attribuer toutes les notes (étoiles).'; $event.preventDefault(); return; }
                            submitError = '';
                        ">
                            @csrf
                            <input type="hidden" name="establishment_id" value="{{ $establishment->id }}">

                            @guest
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Votre pseudo <span class="text-red-500">*</span></label>
                                        <input type="text" name="author_name" required class="w-full border rounded-lg px-3 py-2" value="{{ old('author_name') }}" placeholder="Ex: Marie75">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Votre email <span class="text-red-500">*</span></label>
                                        <input type="email" name="author_email" required class="w-full border rounded-lg px-3 py-2" value="{{ old('author_email') }}" placeholder="Pour confirmer votre avis">
                                        <p class="text-xs text-gray-400 mt-1">Un email de confirmation vous sera envoyé. Votre adresse ne sera pas publiée.</p>
                                    </div>
                                </div>
                            @endguest

                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-1">Titre <span class="text-red-500">*</span></label>
                                <input type="text" name="title" required class="w-full border rounded-lg px-3 py-2" value="{{ old('title') }}">
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-1">Votre avis <span class="text-red-500">*</span></label>
                                <textarea name="content" rows="4" required class="w-full border rounded-lg px-3 py-2">{{ old('content') }}</textarea>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-4">
                                @foreach(['welcome' => 'Accueil', 'quality' => 'Qualité', 'variety' => 'Choix', 'price' => 'Prix', 'ambiance' => 'Ambiance', 'cleanliness' => 'Propreté'] as $key => $label)
                                    <x-star-rating-input name="rating_{{ $key }}" :label="$label" :value="old('rating_' . $key, 0)" />
                                @endforeach
                            </div>

                            <p x-show="submitError" x-text="submitError" class="text-red-500 text-sm mb-3" x-cloak></p>
                            <button type="submit" class="bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700">Envoyer mon avis</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div>
                {{-- Categories --}}
                @if($establishment->categories->isNotEmpty())
                    <div class="bg-white rounded-lg shadow-sm border p-6 sticky top-4">
                        <h2 class="font-semibold text-lg mb-3">Prestations</h2>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($establishment->categories as $cat)
                                <span class="text-xs bg-pink-50 text-pink-700 px-2 py-1 rounded">{{ $cat->name }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Nearby --}}
                @if($nearby instanceof \Illuminate\Support\Collection && $nearby->isNotEmpty())
                    <div class="mt-6">
                        <h3 class="font-semibold text-gray-900 mb-3">À proximité</h3>
                        <div class="space-y-3">
                            @foreach($nearby as $close)
                                <x-etablissement-card :etablissement="$close" />
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    {{-- Contact Modal --}}
    <x-ajax-modal store="contactModal"
                  :title="'Contacter ' . $establishment->name"
                  :action="route('contact.etablissement.send', $establishment)"
                  success-message="L'établissement recevra votre message par email."
                  submit-label="Envoyer le message">
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Votre nom</label>
            <input type="text" name="name" value="{{ auth()->user()?->username }}" class="w-full border rounded-lg px-3 py-2">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Votre email <span class="text-red-500">*</span></label>
            <input type="email" name="email" value="{{ auth()->user()?->email }}" required class="w-full border rounded-lg px-3 py-2">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Message <span class="text-red-500">*</span></label>
            <textarea name="content" rows="5" required class="w-full border rounded-lg px-3 py-2" placeholder="Votre message..."></textarea>
        </div>
    </x-ajax-modal>

    {{-- Booking Modal --}}
    <x-ajax-modal store="bookingModal"
                  title="Prendre RDV"
                  :subtitle="'chez ' . $establishment->name"
                  :action="route('booking.store', $establishment)"
                  success-title="Demande envoyée !"
                  success-message="L'établissement vous contactera pour confirmer."
                  submit-label="Envoyer la demande">
        <div class="grid grid-cols-2 gap-3 mb-3">
            <div>
                <label class="block text-sm font-medium mb-1">Nom <span class="text-red-500">*</span></label>
                <input type="text" name="name" required value="{{ auth()->user()?->username }}" class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Téléphone</label>
                <input type="tel" name="phone" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="06...">
            </div>
        </div>
        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Email <span class="text-red-500">*</span></label>
            <input type="email" name="email" required value="{{ auth()->user()?->email }}" class="w-full border rounded-lg px-3 py-2 text-sm">
        </div>
        <div class="grid grid-cols-2 gap-3 mb-3">
            <div>
                <label class="block text-sm font-medium mb-1">Date souhaitée <span class="text-red-500">*</span></label>
                <input type="date" name="requested_date" required min="{{ now()->format('Y-m-d') }}" class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Horaire <span class="text-red-500">*</span></label>
                <select name="requested_time" required class="w-full border rounded-lg px-3 py-2 text-sm">
                    <option value="">Choisir...</option>
                    <option value="matin">Matin</option>
                    <option value="midi">Midi</option>
                    <option value="apres-midi">Après-midi</option>
                    <option value="soir">Soir</option>
                </select>
            </div>
        </div>
        @if($establishment->categories->isNotEmpty())
            <div class="mb-3">
                <label class="block text-sm font-medium mb-1">Prestation</label>
                <select name="requested_service" class="w-full border rounded-lg px-3 py-2 text-sm">
                    <option value="">À préciser</option>
                    @foreach($establishment->categories as $cat)
                        <option value="{{ $cat->name }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Message (optionnel)</label>
            <textarea name="content" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Précisions..."></textarea>
        </div>
    </x-ajax-modal>

    {{-- Lightbox --}}
    <div x-show="$store.lightbox.open"
         x-cloak
         @keydown.escape.window="$store.lightbox.close()"
         @keydown.arrow-left.window="$store.lightbox.prev()"
         @keydown.arrow-right.window="$store.lightbox.next()"
         x-data="{ startX: null, startY: null }"
         class="fixed inset-0 z-[60] bg-black/90 flex items-center justify-center"
         @click.self="$store.lightbox.close()"
         @touchstart="startX = $event.touches[0].clientX; startY = $event.touches[0].clientY"
         @touchend="
            const dx = $event.changedTouches[0].clientX - startX;
            const dy = $event.changedTouches[0].clientY - startY;
            if (Math.abs(dx) > 50 && Math.abs(dx) > Math.abs(dy)) {
                dx > 0 ? $store.lightbox.prev() : $store.lightbox.next();
            }
         ">
        <button @click="$store.lightbox.close()" class="absolute top-4 right-4 text-white hover:text-gray-300 z-10">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <button @click="$store.lightbox.prev()" x-show="$store.lightbox.photos.length > 1" class="absolute left-4 text-white hover:text-gray-300 z-10 p-2">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        </button>
        <img :src="$store.lightbox.photos[$store.lightbox.current]" :alt="'Photo ' + ($store.lightbox.current + 1)" class="max-h-[90vh] max-w-[90vw] object-contain select-none">
        <button @click="$store.lightbox.next()" x-show="$store.lightbox.photos.length > 1" class="absolute right-4 text-white hover:text-gray-300 z-10 p-2">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
        </button>
        <div x-show="$store.lightbox.photos.length > 1" class="absolute bottom-4 left-1/2 -translate-x-1/2 text-white text-sm bg-black/50 px-3 py-1 rounded-full">
            <span x-text="$store.lightbox.current + 1"></span> / <span x-text="$store.lightbox.photos.length"></span>
        </div>
    </div>

    {{-- Claim Modal --}}
    @auth
    <x-ajax-modal store="claimModal"
                  title="Revendiquer cet établissement"
                  :subtitle="$establishment->name"
                  :action="route('revendication.store', $establishment)"
                  success-message="Notre équipe vérifiera votre demande dans les plus brefs délais."
                  submit-label="Envoyer ma demande">
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Nom du gérant <span class="text-red-500">*</span></label>
            <input type="text" name="manager_name" required class="w-full border rounded-lg px-3 py-2" placeholder="Prénom et nom">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">N° SIRET</label>
            <input type="text" name="siret" maxlength="14" class="w-full border rounded-lg px-3 py-2" placeholder="14 chiffres (facultatif)">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Message complémentaire</label>
            <textarea name="message" rows="3" class="w-full border rounded-lg px-3 py-2" placeholder="Informations supplémentaires..."></textarea>
        </div>
        <p class="text-xs text-gray-400 mb-4">Les demandes de propriété sont systématiquement vérifiées par notre équipe de modérateurs.</p>
    </x-ajax-modal>
    @endauth

    @if($establishment->latitude && $establishment->longitude)
        @push('head')
            <style>
                .ti-marker { background: transparent; border: 0; filter: drop-shadow(0 2px 4px rgba(0,0,0,.25)); transition: transform .15s; }
                .ti-marker:hover { transform: scale(1.15); z-index: 1000 !important; }
            </style>
        @endpush
        <script src="https://unpkg.com/leaflet@1.9/dist/leaflet.js"></script>
        <script src="https://unpkg.com/leaflet.gridlayer.googlemutant@latest/dist/Leaflet.GoogleMutant.js"></script>
        <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_PLACES_API_KEY') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var map = L.map('map', { scrollWheelZoom: false }).setView([{{ $establishment->latitude }}, {{ $establishment->longitude }}], 15);
                L.gridLayer.googleMutant({ type: 'roadmap', maxZoom: 20 }).addTo(map);

                var pinIcon = L.divIcon({
                    className: 'ti-marker',
                    html: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 44" width="32" height="44">'
                        + '<path d="M16 0C7.2 0 0 7.2 0 16c0 11 16 28 16 28s16-17 16-28C32 7.2 24.8 0 16 0z" fill="#ec4899" stroke="#fff" stroke-width="2"/>'
                        + '<circle cx="16" cy="16" r="6" fill="#fff"/>'
                        + '</svg>',
                    iconSize: [32, 44],
                    iconAnchor: [16, 44],
                    popupAnchor: [0, -40],
                });

                L.marker([{{ $establishment->latitude }}, {{ $establishment->longitude }}], { icon: pinIcon })
                    .addTo(map)
                    .bindPopup('<strong>{{ e($establishment->name) }}</strong>');
            });
        </script>
    @endif
</x-layouts.app>
