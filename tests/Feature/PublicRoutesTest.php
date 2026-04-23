<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Department;
use App\Models\Establishment;
use App\Models\EstablishmentSlug;
use Tests\TestCase;

class PublicRoutesTest extends TestCase
{
    public function test_homepage_returns_200(): void
    {
        $this->get('/')->assertStatus(200);
    }

    public function test_recherche_returns_200(): void
    {
        $this->get('/recherche')->assertStatus(200);
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
        $dept = Department::first();
        if (! $dept) {
            $this->markTestSkipped('No department in DB');
        }

        $this->get('/departement-'.$dept->slug)->assertStatus(200);
    }

    public function test_ville_page_returns_200(): void
    {
        $city = City::whereHas('establishments', fn ($q) => $q->where('is_active', true))->first();
        if (! $city) {
            $this->markTestSkipped('No city with active establishments');
        }

        $this->get('/les-instituts-de-beaute-a-'.$city->slug)->assertStatus(200);
    }

    public function test_etablissement_page_returns_200(): void
    {
        $etab = Establishment::active()->first();
        if (! $etab) {
            $this->markTestSkipped('No active establishment');
        }

        $this->get($etab->url)->assertStatus(200);
    }

    public function test_legacy_html_redirects_301(): void
    {
        $etab = Establishment::active()->first();
        if (! $etab) {
            $this->markTestSkipped('No active establishment');
        }

        $this->get('/'.Establishment::TYPE_SLUGS[$etab->type].'/'.$etab->slug.'.html')
            ->assertRedirect($etab->url)
            ->assertStatus(301);
    }

    public function test_old_slug_redirects_301(): void
    {
        $slug = EstablishmentSlug::whereHas('establishment', fn ($q) => $q->where('is_active', true))->first();
        if (! $slug) {
            $this->markTestSkipped('No old slug in DB');
        }

        $etab = $slug->establishment;
        $this->get('/'.Establishment::TYPE_SLUGS[$etab->type].'/'.$slug->slug)
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
