<?php

namespace Database\Factories;

use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeacherFactory extends Factory
{
    protected $model = Teacher::class;

    public function definition(): array
    {
        return [
            'username' => $this->faker->userName,
            'password' => $this->faker->password(8, 12),
            'full_name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
        ];
    }
}
