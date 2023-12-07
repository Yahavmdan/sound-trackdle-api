<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;


/**
 * @property string username
 * @property string password
 * @property string full_name
 * @property int grade
 * @property int id
 */
class Student extends Model
{
    public string $entity = 'student';

    use HasApiTokens, HasFactory;

    protected $fillable = [
        'username',
        'password',
        'full_name',
        'grade',
    ];

    protected $hidden = [
        'password'
    ];

}
