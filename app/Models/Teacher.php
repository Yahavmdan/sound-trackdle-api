<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int id
 * @property string username
 * @property string password,
 * @property string full_name
 * @property string email
 */
class Teacher extends Authenticatable
{
    use HasApiTokens, HasFactory;

    public string $entity = 'teacher';
    protected $fillable = ['username', 'password', 'full_name', 'email'];
    protected $hidden = ['password'];
    protected $casts = ['password' => 'hashed'];

}
