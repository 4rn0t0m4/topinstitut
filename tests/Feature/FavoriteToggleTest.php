<?php

namespace Tests\Feature;

use App\Models\Establishment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteToggleTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_toggle_favorite(): void
    {
        $user = User::factory()->create();
        $establishment = Establishment::factory()->create();

        $this->actingAs($user)
            ->postJson('/ajax/favorites/'.$establishment->id)
            ->assertOk()
            ->assertJson(['favorite' => true, 'authenticated' => true]);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'establishment_id' => $establishment->id,
        ]);

        $this->actingAs($user)
            ->postJson('/ajax/favorites/'.$establishment->id)
            ->assertOk()
            ->assertJson(['favorite' => false, 'authenticated' => true]);
    }

    public function test_anonymous_user_gets_null_favorite(): void
    {
        $establishment = Establishment::factory()->create();

        $this->postJson('/ajax/favorites/'.$establishment->id)
            ->assertOk()
            ->assertJson(['favorite' => null, 'authenticated' => false]);
    }
}
