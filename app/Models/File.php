<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int id
 * @property string name
 * @property int year
 * @property string file_path
 * @property bool is_recently_played
 * @property string type
 */
class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'year',
        'file_path',
        'is_recently_played',
        'type',
    ];
}
