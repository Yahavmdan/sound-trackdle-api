<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int id
 * @property string username
 * @property string password,
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    public string $entity = 'user';

    protected $fillable = [
        'username',
        'password',
    ];

    protected $hidden = [
        'password'
    ];

    protected $casts = [
        'password' => 'hashed'
    ];
}
