<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Department;
use App\Models\Establishment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HierarchicalUrlsTest extends TestCase
{
    use RefreshDatabase;

    public function test_establishment_url_uses_hierarchical_format(): void
    {
        $dept = Department::factory()->create(['code' => '14', 'slug' => 'calvados']);
        $city = City::factory()->create(['slug' => 'caen', 'department_code' => $dept->code]);
        $etab = Establishment::factory()->institut()->inCity($city)->create(['slug' => 'test-slug']);

        $this->assertSame('/calvados/caen/institut-de-beaute/test-slug', $etab->url);

        $this->get($etab->url)->assertOk();
    }

    public function test_legacy_flat_url_redirects_to_hierarchical(): void
    {
        $dept = Department::factory()->create(['slug' => 'calvados']);
        $city = City::factory()->create(['slug' => 'caen', 'department_code' => $dept->code]);
        $etab = Establishment::factory()->institut()->inCity($city)->create(['slug' => 'mon-etab']);

        $this->get('/institut-de-beaute/mon-etab')
            ->assertRedirect($etab->url)
            ->assertStatus(301);
    }

    public function test_legacy_html_suffix_redirects(): void
    {
        $dept = Department::factory()->create(['slug' => 'paris']);
        $city = City::factory()->create(['slug' => 'paris-01', 'department_code' => $dept->code]);
        $etab = Establishment::factory()->spa()->inCity($city)->create(['slug' => 'zen-spa']);

        $this->get('/spa/zen-spa.html')
            ->assertRedirect('/spa/zen-spa')
            ->assertStatus(301);
    }

    public function test_city_page_verifies_department_match(): void
    {
        $dept1 = Department::factory()->create(['slug' => 'calvados']);
        $dept2 = Department::factory()->create(['slug' => 'rhone']);
        $city = City::factory()->create(['slug' => 'caen', 'department_code' => $dept1->code]);

        $this->get('/calvados/caen')->assertOk();
        $this->get('/rhone/caen')->assertNotFound();
    }

    public function test_department_page_loads(): void
    {
        $dept = Department::factory()->create(['slug' => 'calvados']);

        $this->get('/calvados')->assertOk();
    }
}
