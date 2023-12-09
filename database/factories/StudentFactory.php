<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'username' => $this->faker->userName,
            'password' => $this->faker->password,
            'full_name' => $this->faker->name,
            'grade' => $this->faker->numberBetween(0, 12),
        ];
    }
}
