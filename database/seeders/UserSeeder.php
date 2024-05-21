<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{

    public function run(): void
    {
        $user = new User();
        $user->username = 'admin';
        $user->password = Hash::make(env('USER_PASSWORD'));
        $user->save();
    }
}


