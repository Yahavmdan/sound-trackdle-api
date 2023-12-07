<?php

namespace App\Http\Controllers;

use App\Http\Requests\Teacher\StoreTeacherRequest;
use App\Http\Requests\Teacher\UpdateTeacherRequest;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller
{
    protected string | Teacher $model = Teacher::class;
    protected string $table = 'teachers';

    function getTeachers(): JsonResponse
    {
        return $this->index(DB::table($this->table));
    }

    function storeTeacher(StoreTeacherRequest $request): JsonResponse
    {
        $values = $request->validated();
        $values['password'] = Hash::make($values['password']);
        return $this->save($values, $this->model, true);
    }

    function destroyTeacher(Teacher $teacher): JsonResponse
    {
        return $this->delete($teacher);
    }

    function updateTeacher(UpdateTeacherRequest $request, Teacher $teacher): JsonResponse
    {
        return $this->update($teacher, $request->validated());
    }
}
