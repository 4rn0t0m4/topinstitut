<?php

namespace Database\Factories;

use App\Models\Establishment;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Schedule>
 */
class ScheduleFactory extends Factory
{
    protected $model = Schedule::class;

    public function definition(): array
    {
        return [
            'establishment_id' => Establishment::factory(),
            'day_of_week' => fake()->numberBetween(1, 7),
            'open_am' => '09:00',
            'close_am' => '12:00',
            'open_pm' => '14:00',
            'close_pm' => '18:00',
            'is_closed' => false,
        ];
    }

    public function closed(): static
    {
        return $this->state(fn () => [
            'is_closed' => true,
            'open_am' => null, 'close_am' => null,
            'open_pm' => null, 'close_pm' => null,
        ]);
    }
}
