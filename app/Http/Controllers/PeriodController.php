<?php

namespace App\Http\Controllers;

use App\Http\Requests\Period\StorePeriodRequest;
use App\Http\Requests\Period\UpdatePeriodRequest;
use App\Models\Period;
use App\Services\QueryService;
use Illuminate\Http\JsonResponse;

class PeriodController extends Controller
{
    /**
     * The model associated with the controller.
     * @var string|Period
     */
    protected string|Period $model = Period::class;

    /**
     * Get all periods.
     * @return JsonResponse
     */
    public function getPeriods(): JsonResponse
    {
        return $this->index(QueryService::getAll($this->model));
    }

    /**
     * Get periods by teacher.
     * @param int $teacherId
     * @return JsonResponse
     */
    public function getPeriodsByTeacher(int $teacherId): JsonResponse
    {
        return $this->index(QueryService::getEntityById($this->model, 'teacher_id', $teacherId));
    }

    /**
     * Store a new period.
     * @param StorePeriodRequest $request
     * @return JsonResponse
     */
    public function storePeriod(StorePeriodRequest $request): JsonResponse
    {
        $values = $request->validated();
        return $this->store($values, $this->model);
    }

    /**
     * Destroy (delete) a period.
     * @param Period $period
     * @return JsonResponse
     */
    public function destroyPeriod(Period $period): JsonResponse
    {
        return $this->destroy($period);
    }

    /**
     * Update a period.
     * @param UpdatePeriodRequest $request
     * @param Period $period
     * @return JsonResponse
     */
    public function updatePeriod(UpdatePeriodRequest $request, Period $period): JsonResponse
    {
        return $this->update($period, $request->validated());
    }
}
