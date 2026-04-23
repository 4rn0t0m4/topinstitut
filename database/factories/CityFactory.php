<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<City>
 */
class CityFactory extends Factory
{
    protected $model = City::class;

    public function definition(): array
    {
        $name = fake()->unique()->city();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(4),
            'postal_code' => fake()->numerify('#####'),
            'insee_code' => fake()->unique()->numerify('#####'),
            'department_code' => Department::factory(),
            'population' => fake()->numberBetween(1000, 200000),
            'latitude' => fake()->latitude(41, 51),
            'longitude' => fake()->longitude(-5, 9),
        ];
    }
}
