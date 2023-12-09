<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Student\StoreStudentRequest;
use App\Http\Requests\Student\UpdateStudentRequest;
use App\Models\PeriodStudent;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\QueryService;
use Illuminate\Http\JsonResponse;

class StudentController extends Controller
{
    /**
     * The model associated with the controller.
     * @var string|Student
     */
    protected string|Student $model = Student::class;

    /**
     * Get all students.
     * @return JsonResponse
     */
    public function getStudents(): JsonResponse
    {
        return $this->index(QueryService::getAll($this->model));
    }

    /**
     * Get students by teacher and period.
     * @param int $teacherId
     * @return JsonResponse
     */
    public function getStudentsByTeacherPeriod(int $teacherId): JsonResponse
    {
        return $this->index(QueryService::getStudentsByTeacherPeriod($teacherId));
    }

    /**
     * Get students by period.
     * @param int $periodId
     * @return JsonResponse
     */
    public function getStudentsByPeriod(int $periodId): JsonResponse
    {
        $periodStudents = QueryService::getEntityById(PeriodStudent::class, 'period_id', $periodId)->pluck('student_id');
        return $this->index(Student::query()->whereIn('id', $periodStudents->toArray()));
    }

    /**
     * Store a new student.
     * @param StoreStudentRequest $request
     * @return JsonResponse
     */
    public function storeStudent(StoreStudentRequest $request): JsonResponse
    {
        $values = $request->validated();
        return $this->store($values, $this->model, true);
    }

    /**
     * Destroy (delete) a student.
     * @param Student $student
     * @return JsonResponse
     */
    public function destroyStudent(Student $student): JsonResponse
    {
        return $this->destroy($student);
    }

    /**
     * Update a student.
     * @param UpdateStudentRequest $request
     * @param Teacher $teacher
     * @return JsonResponse
     */
    public function updateStudent(UpdateStudentRequest $request, Teacher $teacher): JsonResponse
    {
        return $this->update($teacher, $request->validated());
    }

    /**
     * Associate a student with a period.
     * @param int $studentId
     * @param int $periodId
     * @return JsonResponse
     */
    public function associateWithPeriod(int $studentId, int $periodId): JsonResponse
    {
        if (QueryService::getPeriodStudent($studentId, $periodId)->first()) {
            return $this->badResponse(null, 'Student is already in this period');
        }

        $model = new PeriodStudent();
        $model->period()->associate($periodId);
        $model->student()->associate($studentId);
        $model->save();

        return $this->okResponse();
    }

    /**
     * Remove a student from a period.
     * @param int $studentId
     * @param int $periodId
     * @return JsonResponse
     */
    public function removeFromPeriod(int $studentId, int $periodId): JsonResponse
    {
        $periodStudent = QueryService::getPeriodStudent($studentId, $periodId);

        if (!$periodStudent->first()) {
            return $this->badResponse(null, 'Student is not in this period');
        }

        return $this->destroy($periodStudent);
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

        return $this->login(collect($request->validated()), $student);
    }
}
