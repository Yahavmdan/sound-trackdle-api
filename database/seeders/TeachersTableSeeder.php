<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Teacher;

class TeachersTableSeeder extends Seeder
{
    public function run(): void
    {
        Teacher::factory()->count(7)->create();
    }
}
