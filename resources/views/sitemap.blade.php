{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ url('/') }}</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>{{ url('/recherche_institut.html') }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
    </url>
    @foreach($departements as $dept)
    <url>
        <loc>{{ url('/departement-' . $dept->departement_url . '.html') }}</loc>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    @endforeach
    @foreach($villes as $ville)
    <url>
        <loc>{{ url('/les-instituts-de-beaute-a-' . $ville->url . '.html') }}</loc>
        <changefreq>weekly</changefreq>
        <priority>0.6</priority>
    </url>
    @endforeach
    @foreach($etablissements as $etab)
    <url>
        <loc>{{ url('/' . \App\Models\Etablissement::TYPE_SLUGS[$etab->type] . '/' . $etab->slug . '.html') }}</loc>
        <lastmod>{{ $etab->updated_at->toW3cString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
    @endforeach
</urlset>
