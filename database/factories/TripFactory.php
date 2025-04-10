<?php

namespace Database\Factories;

use App\Models\Trip;
use Illuminate\Database\Eloquent\Factories\Factory;

class TripFactory extends Factory
{
    protected $model = Trip::class;

    public function definition()
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'destination' => $this->faker->city,
            'startDate' => now()->addDays(1),
            'endDate' => now()->addDays(5),
        ];
    }
}
