<?php

namespace Tests\Unit;

use App\Models\Establishment;
use App\Models\User;
use App\Services\FavoriteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_toggle_adds_then_removes_favorite(): void
    {
        $user = User::factory()->create();
        $establishment = Establishment::factory()->create();
        $service = new FavoriteService;

        $this->assertTrue($service->toggle($user, $establishment));
        $this->assertDatabaseHas('favorites', ['user_id' => $user->id, 'establishment_id' => $establishment->id]);

        $this->assertFalse($service->toggle($user, $establishment));
        $this->assertDatabaseMissing('favorites', ['user_id' => $user->id, 'establishment_id' => $establishment->id]);
    }
}
