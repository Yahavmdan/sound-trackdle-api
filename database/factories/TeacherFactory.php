<?php

namespace Database\Factories;

use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class TeacherFactory extends Factory
{
    protected $model = Teacher::class;

    public function definition(): array
    {
        return [
            'username' => $this->faker->userName,
            'password' => Hash::make('password'),
            'full_name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
        ];
    }
}
