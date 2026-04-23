<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Department>
 */
class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        $name = 'Dept '.fake()->unique()->word();

        return [
            'code' => (string) fake()->unique()->numberBetween(1, 95),
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'region' => 'Region '.fake()->word(),
            'article' => 'de ',
            'latitude' => fake()->latitude(41, 51),
            'longitude' => fake()->longitude(-5, 9),
            'zoom' => 10,
        ];
    }
}
