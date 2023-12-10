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
    public function index(): JsonResponse
    {
        return $this->indexData(QueryService::getAll($this->model));
    }

    /**
     * Show the details of a teacher record.
     * @param Teacher $teacher The teacher record to show the details for.
     * @return JsonResponse The response containing the details of the teacher record.
     */
    public function show(Teacher $teacher): JsonResponse
    {
        return $this->indexData(QueryService::getEntityById($teacher, 'id', $teacher->id));
    }

    /**
     * Store a new teacher.
     * @param StoreTeacherRequest $request
     * @return JsonResponse
     */
    public function store(StoreTeacherRequest $request): JsonResponse
    {
        $values = $request->validated();
        return $this->storeData($values, $this->model, true);
    }

    /**
     * Destroy (delete) a teacher.
     * @param Teacher $teacher
     * @return JsonResponse
     */
    public function destroy(Teacher $teacher): JsonResponse
    {
        return $this->deleteData($teacher);
    }

    /**
     * Update a teacher.
     * @param UpdateTeacherRequest $request
     * @param Teacher $teacher
     * @return JsonResponse
     */
    public function update(UpdateTeacherRequest $request, Teacher $teacher): JsonResponse
    {
        return $this->updateData($teacher, $request->validated());
    }

    /**
     * Associate a teacher with a period.
     * @param Period $period
     * @param int $teacherId
     * @return void
     */
    public function associatePeriod(Period $period, int $teacherId): void
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
        return $this->login($request->validated(), $teacher);
    }
}
