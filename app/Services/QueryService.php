<?php

namespace App\Services;

use App\Models\PeriodStudent;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class QueryService
{
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
     * Get students associated with a specific teacher and period.
     * @param int $teacherId The ID of the teacher.
     * @return Builder The query builder for retrieving students based on teacher and period.
     */
    public static function getStudentsByTeacherPeriod(int $teacherId): Builder
    {
        return Student::query()
            ->join('period_students', 'students.id', 'period_students.student_id')
            ->join('periods', 'period_students.period_id', 'periods.id')
            ->join('teachers', 'periods.teacher_id', 'teachers.id')
            ->where('teachers.id', $teacherId)
            ->select('students.*');
    }

    /**
     * Get the PeriodStudent record for a given student and period.
     * @param int $studentId The ID of the student.
     * @param int $periodId The ID of the period.
     * @return Builder|null The query builder for retrieving the PeriodStudent record or null if not found.
     */
    public static function getPeriodStudent(int $studentId, int $periodId): ?Builder
    {
        return PeriodStudent::query()
            ->where('student_id', $studentId)
            ->where('period_id', $periodId);
    }

}
