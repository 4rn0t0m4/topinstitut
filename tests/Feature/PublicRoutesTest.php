<?php

namespace Tests\Feature;

use App\Models\Departement;
use App\Models\Etablissement;
use App\Models\EtablissementSlug;
use App\Models\Ville;
use Tests\TestCase;

class PublicRoutesTest extends TestCase
{
    public function test_homepage_returns_200(): void
    {
        $this->get('/')->assertStatus(200);
    }

    public function test_recherche_returns_200(): void
    {
        $this->get('/recherche_institut.html')->assertStatus(200);
    }

    public function test_connexion_returns_200(): void
    {
        $this->get('/connexion')->assertStatus(200);
    }

    public function test_inscription_returns_200(): void
    {
        $this->get('/inscription')->assertStatus(200);
    }

    public function test_departement_page_returns_200(): void
    {
        $dept = Departement::first();
        if (! $dept) {
            $this->markTestSkipped('No departement in DB');
        }

        $this->get('/departement-'.$dept->departement_url.'.html')->assertStatus(200);
    }

    public function test_ville_page_returns_200(): void
    {
        $ville = Ville::whereHas('etablissements', fn ($q) => $q->where('valide', true))->first();
        if (! $ville) {
            $this->markTestSkipped('No ville with valid etablissements');
        }

        $this->get('/les-instituts-de-beaute-a-'.$ville->url.'.html')->assertStatus(200);
    }

    public function test_etablissement_page_returns_200(): void
    {
        $etab = Etablissement::valide()->first();
        if (! $etab) {
            $this->markTestSkipped('No valid etablissement');
        }

        $this->get($etab->url)->assertStatus(200);
    }

    public function test_old_slug_redirects_301(): void
    {
        $slug = EtablissementSlug::whereHas('etablissement', fn ($q) => $q->where('valide', true))->first();
        if (! $slug) {
            $this->markTestSkipped('No old slug in DB');
        }

        $etab = $slug->etablissement;
        $this->get('/'.Etablissement::TYPE_SLUGS[$etab->type].'/'.$slug->slug.'.html')
            ->assertRedirect($etab->url)
            ->assertStatus(301);
    }

    public function test_wrong_type_redirects_301(): void
    {
        $etab = Etablissement::valide()->where('type', 0)->first();
        if (! $etab) {
            $this->markTestSkipped('No type 0 etablissement');
        }

        $this->get('/spa/'.$etab->slug.'.html')
            ->assertRedirect($etab->url)
            ->assertStatus(301);
    }

    public function test_404_returns_custom_page(): void
    {
        $this->get('/page-qui-nexiste-pas')
            ->assertStatus(404)
            ->assertSee('Page introuvable');
    }

    public function test_sitemap_returns_xml(): void
    {
        $this->get('/sitemap.xml')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'text/xml; charset=UTF-8');
    }

    public function test_admin_requires_auth(): void
    {
        $this->get('/admin')->assertRedirect('/connexion');
    }

    public function test_client_requires_auth(): void
    {
        $this->get('/espace-client')->assertRedirect('/connexion');
    }
}
