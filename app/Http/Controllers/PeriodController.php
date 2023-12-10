<?php

namespace App\Http\Controllers;

use App\Http\Requests\Period\IndexPeriodRequest;
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
     * Retrieve periods based on an optional teacher filter.
     * This method constructs a query to fetch periods, optionally filtering them based on a specified teacher.
     * If no teacher ID is provided, it returns all periods. If a teacher ID is provided, it retrieves periods
     * associated with that teacher.
     * @param IndexPeriodRequest $request
     * @return JsonResponse
     */
    public function index(IndexPeriodRequest $request): JsonResponse
    {
        $teacherId = $request->get('teacher_id');
        return $this->indexData(QueryService::indexPeriod($teacherId));
    }

    /**
     * Show the details of a period record.
     * @param Period $period The period record to show the details for.
     * @return JsonResponse The response containing the details of the period record.
     */
    public function show(Period $period): JsonResponse
    {
        return $this->indexData(QueryService::getEntityById($period, 'id', $period->id));
    }

    /**
     * Store a new period.
     * @param StorePeriodRequest $request
     * @return JsonResponse
     */
    public function store(StorePeriodRequest $request): JsonResponse
    {
        $values = $request->validated();
        return $this->storeData($values, $this->model);
    }

    /**
     * Destroy (delete) a period.
     * @param Period $period
     * @return JsonResponse
     */
    public function destroy(Period $period): JsonResponse
    {
        return $this->deleteData($period);
    }

    /**
     * Update a period.
     * @param UpdatePeriodRequest $request
     * @param Period $period
     * @return JsonResponse
     */
    public function update(UpdatePeriodRequest $request, Period $period): JsonResponse
    {
        return $this->updateData($period, $request->validated());
    }
}
