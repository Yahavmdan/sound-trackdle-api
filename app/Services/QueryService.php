<?php

namespace App\Services;

use App\Models\Period;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class QueryService
{
    /**
     * Build a query to retrieve students based on optional teacher and period filters.
     * This method constructs a query to fetch students, optionally filtering them based on a specified teacher and/or period.
     * The resulting query can be further modified or executed to obtain the desired student data.
     * @param int|null $teacherId The ID of the teacher for whom to filter students.
     * @param int|null $periodId The ID of the period for which to filter students.
     * @return Builder The Eloquent query builder instance.
     */
    public static function indexStudent(?int $teacherId = null, ?int $periodId = null): Builder
    {
        $query = Student::query();

        $query->when($periodId, fn($q) => $q
            ->join('period_students', 'students.id', 'period_students.student_id')
            ->join('periods as periods_period_id', 'period_students.period_id', 'periods_period_id.id')
            ->where('periods_period_id.id', $periodId)
            ->select('students.*')
        );
        $query->when(($teacherId && $periodId), fn($q) => $q
            ->join('period_students as periods_teacher_id', 'students.id', 'periods_teacher_id.student_id')
            ->join('periods', 'periods_teacher_id.period_id', 'periods.id')
            ->join('teachers', 'periods.teacher_id', 'teachers.id')
            ->where('teachers.id', $teacherId)
        );

        return $query;
    }

    /**
     * Retrieve periods based on an optional teacher filter.
     * This method constructs a query to fetch periods, optionally filtering them based on a specified teacher.
     * If no teacher ID is provided, it returns all periods. If a teacher ID is provided, it retrieves periods
     * associated with that teacher using the getEntityById method.
     * @param int|null $teacherId The ID of the teacher for whom to filter periods. Default is null.
     * @return Builder The Eloquent query builder instance.
     */
    public static function indexPeriod(?int $teacherId = null): Builder
    {
        if (!$teacherId) {
            return Period::query();
        }
        return self::getEntityById(Period::class, 'teacher_id', $teacherId);
    }


    /**
     * Get all records for a given model.
     * @param string|Model $model The model class or instance.
     * @return Builder The query builder for the specified model.
     */
    public static function getAll(string|Model $model): Builder
    {
        return $model::query();
    }

    /**
     * Get records by a specific column and ID.
     * @param string|Model $model The model class or instance.
     * @param string $column The column to filter by.
     * @param int $id The ID to match.
     * @return Builder The query builder for the specified model with the column filter.
     */
    public static function getEntityById(string|Model $model, string $column, int $id): Builder
    {
        return $model::query()->where($column, $id);
    }

    /**
     * Find a model entity by username.
     * @param string|Model $model The model class or instance.
     * @param string $username The username to match.
     * @return Builder The query builder for retrieving the model entity by username.
     */
    public static function findEntityByUserName(string|Model $model, string $username): Builder
    {
        return $model::query()->where('username', $username);
    }

    /**
     * Get many-to-many relation record.
     * @param string|Model $model
     * @param string $fCol
     * @param string $sCol
     * @param int $firstId
     * @param int $secondId
     * @return Builder|null The query builder for retrieving the PeriodStudent record or null if not found.
     */
    public static function getManyToMany(string|Model $model, string $fCol, string $sCol, int $firstId, int $secondId): ?Builder
    {
        return $model::query()->where($fCol, $firstId)->where($sCol, $secondId);
    }

}
