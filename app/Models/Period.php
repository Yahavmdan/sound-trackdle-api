<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int id
 * @property string name
 * @property int teacher_id
 */
class Period extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];


    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

}
