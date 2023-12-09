<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Teacher\StoreTeacherRequest;
use App\Http\Requests\Teacher\UpdateTeacherRequest;
use App\Models\Period;
use App\Models\Teacher;
use App\Services\QueryService;
use Illuminate\Http\JsonResponse;

class TeacherController extends Controller
{
    /**
     * The model associated with the controller.
     * @var string|Teacher
     */
    protected string|Teacher $model = Teacher::class;

    /**
     * Get all teachers.
     * @return JsonResponse
     */
    public function getTeachers(): JsonResponse
    {
        return $this->index(QueryService::getAll($this->model));
    }

    /**
     * Store a new teacher.
     * @param StoreTeacherRequest $request
     * @return JsonResponse
     */
    public function storeTeacher(StoreTeacherRequest $request): JsonResponse
    {
        $values = $request->validated();
        return $this->store($values, $this->model, true);
    }

    /**
     * Destroy (delete) a teacher.
     * @param Teacher $teacher
     * @return JsonResponse
     */
    public function destroyTeacher(Teacher $teacher): JsonResponse
    {
        return $this->destroy($teacher);
    }

    /**
     * Update a teacher.
     * @param UpdateTeacherRequest $request
     * @param Teacher $teacher
     * @return JsonResponse
     */
    public function updateTeacher(UpdateTeacherRequest $request, Teacher $teacher): JsonResponse
    {
        return $this->update($teacher, $request->validated());
    }

    /**
     * Associate a teacher with a period.
     * @param Period $period
     * @param int $teacherId
     * @return void
     */
    public function associateWithPeriod(Period $period, int $teacherId): void
    {
        $period->teacher()->associate($teacherId)->save();
    }

    /**
     * Login a teacher.
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function loginTeacher(LoginRequest $request): JsonResponse
    {
        /* @var Teacher $teacher */
        $teacher = QueryService::findEntityByUserName(Teacher::class, $request->get('username'))->first();
        return $this->login(collect($request->validated()), $teacher);
    }
}
