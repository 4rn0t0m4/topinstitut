<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Department;
use App\Models\Establishment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_returns_200(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_recherche_returns_200(): void
    {
        $this->get('/recherche')->assertOk();
    }

    public function test_connexion_returns_200(): void
    {
        $this->get('/connexion')->assertOk();
    }

    public function test_inscription_returns_200(): void
    {
        $this->get('/inscription')->assertOk();
    }

    public function test_404_returns_custom_page(): void
    {
        $this->get('/page-qui-nexiste-pas')
            ->assertNotFound()
            ->assertSee('Page introuvable');
    }

    public function test_sitemap_returns_xml(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
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

    public function test_departement_page_returns_200(): void
    {
        $dept = Department::factory()->create(['slug' => 'test-dept']);

        $this->get('/test-dept')->assertOk();
    }

    public function test_city_page_returns_200(): void
    {
        $dept = Department::factory()->create(['slug' => 'test-dept']);
        City::factory()->create(['slug' => 'test-ville', 'department_code' => $dept->code]);

        $this->get('/test-dept/test-ville')->assertOk();
    }

    public function test_establishment_page_returns_200(): void
    {
        $dept = Department::factory()->create(['slug' => 'test-dept']);
        $city = City::factory()->create(['slug' => 'test-ville', 'department_code' => $dept->code]);
        $etab = Establishment::factory()->institut()->inCity($city)->create(['slug' => 'mon-etab']);

        $this->get($etab->url)->assertOk();
    }
}
