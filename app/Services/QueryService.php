<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

class QueryService
{
    /**
     * Get records by a specific column and ID.
     * @param string|Model $model The model class or instance.
     * @param string $column The column to filter by.
     * @param int $id The ID to match.
     * @return Model The query builder for retrieving the model entity by username.
     */
    public static function getEntityById(string|Model $model, string $column, int $id): Model
    {
        return $model::query()->where($column, $id)->first();
    }

    /**
     * Find a model entity by username.
     * @param string|Model $model The model class or instance.
     * @param string $username The username to match.
     * @return Model The query builder for retrieving the model entity by username.
     */
    public static function getEntityByUserName(string|Model $model, string $username): Model
    {
        return $model::query()->where('username', $username)->first();
    }

}
