<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property string name
 * @property int year
 * @property string file_path
 * @property int played_at
 * @property string type
 * @property string main_actor
 * @property string genre
 */
class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'year',
        'file_path',
        'played_at',
        'type',
        'main_actor',
        'genre',
    ];
}
