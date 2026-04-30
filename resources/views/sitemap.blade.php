{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ url('/') }}</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>{{ url('/recherche') }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
    </url>
    @foreach($departments as $dept)
    <url>
        <loc>{{ url('/' . $dept->slug) }}</loc>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    @endforeach
    @foreach($cities as $city)
    <url>
        <loc>{{ url('/' . ($city->department_slug ?? $city->department?->slug) . '/' . $city->slug) }}</loc>
        <changefreq>weekly</changefreq>
        <priority>0.6</priority>
    </url>
    @endforeach
    @foreach($establishments as $etab)
    <url>
        <loc>{{ url($etab->url) }}</loc>
        <lastmod>{{ $etab->updated_at->toW3cString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
    @endforeach
    @foreach($prestations as $pv)
    <url>
        <loc>{{ url('/' . $pv['dept_slug'] . '/' . $pv['city_slug'] . '/' . $pv['slug']) }}</loc>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    @endforeach
</urlset>
