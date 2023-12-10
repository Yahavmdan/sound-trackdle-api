<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Student\IndexStudentRequest;
use App\Http\Requests\Student\StoreStudentRequest;
use App\Http\Requests\Student\UpdateStudentRequest;
use App\Models\Period;
use App\Models\Student;
use App\Services\QueryService;
use Exception;
use Illuminate\Http\JsonResponse;

class StudentController extends Controller
{
    /**
     * The model associated with the controller.
     * @var string|Student
     */
    protected string|Student $model = Student::class;

    /**
     * Get students based on different criteria.
     * @param IndexStudentRequest $request
     * @return JsonResponse
     */
    public function index(IndexStudentRequest $request): JsonResponse
    {
        $teacherId = $request->get('teacher_id');
        $periodId = $request->get('period_id');
        if ($teacherId && !$periodId) return $this->errorResponse();
        return $this->indexData(QueryService::indexStudent($teacherId, $periodId));
    }

    /**
     * Show the details of a student record.
     * @param Student $student The student record to show the details for.
     * @return JsonResponse The response containing the details of the student record.
     */
    public function show(Student $student): JsonResponse
    {
        return $this->indexData(QueryService::getEntityById($student, 'id', $student->id));
    }

    /**
     * Store a new student.
     * @param StoreStudentRequest $request
     * @return JsonResponse
     */
    public function store(StoreStudentRequest $request): JsonResponse
    {
        $values = $request->validated();
        return $this->storeData($values, $this->model, true);
    }

    /**
     * Destroy (delete) a student.
     * @param Student $student
     * @return JsonResponse
     */
    public function destroy(Student $student): JsonResponse
    {
        return $this->deleteData($student);
    }

    /**
     * Update a student.
     * @param UpdateStudentRequest $request
     * @param Student $student
     * @return JsonResponse
     */
    public function update(UpdateStudentRequest $request, Student $student): JsonResponse
    {
        return $this->updateData($student, $request->validated());
    }

    /**
     * Associate a student with a period.
     * @param int $studentId
     * @param Period $period
     * @return JsonResponse
     */
    public function attachPeriod(int $studentId, Period $period): JsonResponse
    {
        try {
            $period->students()->syncWithoutDetaching([$studentId]);
            return $this->okResponse();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Remove a student from a period.
     * @param int $studentId
     * @param Period $period
     * @return JsonResponse
     */
    public function detachPeriod(int $studentId, Period $period): JsonResponse
    {
        try {
            $period->students()->detach([$studentId]);
            return $this->okResponse();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Login a student.
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function loginStudent(LoginRequest $request): JsonResponse
    {
        /* @var Student $student */
        $student = QueryService::findEntityByUserName(Student::class, $request->get('username'))->first();
        return $this->login($request->validated(), $student);
    }
}
