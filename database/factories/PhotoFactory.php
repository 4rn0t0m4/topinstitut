<?php

namespace Database\Factories;

use App\Models\Establishment;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Photo>
 */
class PhotoFactory extends Factory
{
    protected $model = Photo::class;

    public function definition(): array
    {
        return [
            'establishment_id' => Establishment::factory(),
            'filename' => 'photo-'.fake()->uuid().'.jpg',
            'sort_order' => 0,
        ];
    }
}
