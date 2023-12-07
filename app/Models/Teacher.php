<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;


/**
 * @property string username
 * @property string password
 * @property string full_name
 * @property string email
 * @property int id
 */
class Teacher extends Model
{
    public string $entity = 'teacher';

    use HasApiTokens, HasFactory;

    protected $fillable = [
        'username',
        'password',
        'full_name',
        'email',
    ];

    protected $hidden = [
        'password'
    ];

    public function periods(): HasMany
    {
        return $this->hasMany(Period::class);
    }
}
