<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Period;

class PeriodsTableSeeder extends Seeder
{
    public function run()
    {
        Period::factory()->count(5)->create();
    }
}
