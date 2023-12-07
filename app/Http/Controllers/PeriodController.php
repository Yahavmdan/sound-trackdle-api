<?php

namespace App\Http\Controllers;

use App\Http\Requests\Period\StorePeriodRequest;
use App\Http\Requests\Period\UpdatePeriodRequest;
use App\Models\Period;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PeriodController extends Controller
{
    protected string | Period $model = Period::class;
    protected string $table = 'periods';

    function getPeriods(): JsonResponse
    {
        return $this->index(DB::table($this->table));
    }

    function getPeriodsByTeacher(int $teacherId): JsonResponse
    {
        return $this->index(DB::table($this->table)->where('teacher_id', $teacherId));
    }

    function storePeriod(StorePeriodRequest $request, Teacher $teacher): JsonResponse
    {
        $values = $request->validated();
        $values['teacher_id'] = $teacher->id;
        return $this->save($values, $this->model);
    }

    function destroyPeriod(Period $period): JsonResponse
    {
        return $this->delete($period);
    }

    function updatePeriod(UpdatePeriodRequest $request, Period $period): JsonResponse
    {
        return $this->update($period, $request->validated());
    }
}
