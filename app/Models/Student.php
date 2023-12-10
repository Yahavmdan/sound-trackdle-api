<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property string username
 * @property string password
 * @property string full_name
 * @property int grade
 * @property int id
 */

class Student extends Authenticatable
{
    use HasApiTokens, HasFactory;

    public string $entity   = 'student';

    protected $fillable = [
        'username',
        'password',
        'full_name',
        'grade'
    ];

    protected $hidden = [
        'password'
    ];

    protected $casts = [
        'password' => 'hashed'
    ];
}
