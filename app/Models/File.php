<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property int id
 * @property string name
 * @property int year
 * @property string file_path
 * @property int played_at
 * @property string type
 * @property string main_actor
 * @property string genre
 * @property int|null tmdb_id
 * @property string|null source_url
 * @property float|null clip_start
 * @property string token
 * @property string|null difficulty
 * @property string|null poster_url
 * @property string|null imdb_id
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
        'tmdb_id',
        'source_url',
        'clip_start',
        'difficulty',
        'poster_url',
        'imdb_id',
    ];

    protected static function booted(): void
    {
        static::creating(function (File $file) {
            $file->token ??= Str::random(32);
        });
    }

    public function imdbUrl(): ?string
    {
        return $this->imdb_id ? "https://www.imdb.com/title/{$this->imdb_id}/" : null;
    }

    /** Rows that have an uploaded clip. */
    public function scopePlayable(Builder $query): Builder
    {
        return $query->whereNotNull('file_path')->where('file_path', '!=', '');
    }
}
