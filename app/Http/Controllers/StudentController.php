<?php

namespace App\Http\Controllers;

use App\Http\Requests\Student\StoreStudentRequest;
use App\Http\Requests\Student\UpdateStudentRequest;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    protected string | Student $model = Student::class;
    protected string $table = 'students';

    function getStudents(): JsonResponse
    {
        return $this->index(DB::table($this->table));
    }

    function storeStudent(StoreStudentRequest $request): JsonResponse
    {
        $values = $request->validated();
        $values['password'] = Hash::make($values['password']);
        return $this->save($values, $this->model);
    }

    function destroyStudent(Student $student): JsonResponse
    {
        return $this->delete($student);
    }

    function updateStudent(UpdateStudentRequest $request, Teacher $teacher): JsonResponse
    {
        return $this->update($teacher, $request->validated());
    }
}
