<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Establishment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Establishment>
 */
class EstablishmentFactory extends Factory
{
    protected $model = Establishment::class;

    public function definition(): array
    {
        $name = fake()->company();

        return [
            'type' => fake()->randomElement([0, 1, 2, 3]),
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(4),
            'email' => fake()->safeEmail(),
            'address' => fake()->streetAddress(),
            'postal_code' => fake()->numerify('#####'),
            'city' => fake()->city(),
            'latitude' => fake()->latitude(41, 51),
            'longitude' => fake()->longitude(-5, 9),
            'phone' => fake()->phoneNumber(),
            'tagline' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'rating' => fake()->randomFloat(1, 1, 5),
            'review_count' => fake()->numberBetween(0, 50),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function institut(): static
    {
        return $this->state(fn () => ['type' => 0]);
    }

    public function spa(): static
    {
        return $this->state(fn () => ['type' => 2]);
    }

    public function inCity(City $city): static
    {
        return $this->state(fn () => [
            'city_id' => $city->id,
            'department_code' => $city->department_code,
            'city' => $city->name,
            'postal_code' => $city->postal_code,
        ]);
    }
}
