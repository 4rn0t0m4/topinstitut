<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Department;
use App\Models\Establishment;
use App\Models\Photo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_type_filter_limits_results(): void
    {
        Establishment::factory()->institut()->create(['name' => 'Institut A']);
        Establishment::factory()->spa()->create(['name' => 'Spa B']);

        $this->get('/recherche?type=0')
            ->assertOk()
            ->assertSee('Institut A')
            ->assertDontSee('Spa B');
    }

    public function test_name_filter_matches_partial_name(): void
    {
        Establishment::factory()->create(['name' => 'Bel Institut']);
        Establishment::factory()->create(['name' => 'Autre chose']);

        $this->get('/recherche?nom=Bel')
            ->assertSee('Bel Institut')
            ->assertDontSee('Autre chose');
    }

    public function test_with_photos_filter_excludes_establishments_without_photos(): void
    {
        $withPhoto = Establishment::factory()->create(['name' => 'AvecPhoto']);
        Photo::factory()->create(['establishment_id' => $withPhoto->id, 'filename' => 'x.jpg']);

        Establishment::factory()->create(['name' => 'SansPhoto']);

        $this->get('/recherche?avec_photos=1')
            ->assertSee('AvecPhoto')
            ->assertDontSee('SansPhoto');
    }

    public function test_min_rating_filter(): void
    {
        Establishment::factory()->create(['name' => 'Top', 'rating' => 4.5]);
        Establishment::factory()->create(['name' => 'Moyen', 'rating' => 3.0, 'google_rating' => 3.5]);

        $this->get('/recherche?note_min=4')
            ->assertSee('Top')
            ->assertDontSee('Moyen');
    }
}
