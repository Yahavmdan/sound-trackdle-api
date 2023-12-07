<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property string name
 * @property int teacher_id
 */
class Period extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'teacher_id'
    ];

}
