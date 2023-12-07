<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Teacher;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function index(Builder $table): JsonResponse
    {
        try {
            return $this->indexResponse($table);
        } catch (Exception $e) {
            return $this->badResponse($e);
        }
    }

    public function save(array $values, string $model, ?bool $hasToken = false): JsonResponse
    {
        try {
            $model = new $model($values);
            $model->save();
            if (!$hasToken) return $this->okResponse();
            return $this->authenticatableResponse($model);
        } catch (Exception $e) {
            return $this->badResponse($e);
        }
    }

    public function update(Model $model, array $data): JsonResponse
    {
        try {
            $model->update($data);
            return $this->okResponse();
        } catch (Exception $e) {
            return $this->badResponse($e);
        }
    }

    public function delete(Model $model): JsonResponse
    {
        try {
            $model->delete();
            return $this->okResponse();
        } catch (Exception $e) {
            return $this->badResponse($e);
        }
    }

    private function indexResponse(Builder $table): JsonResponse
    {
        return response()->json($table->get());
    }

    private function okResponse(): JsonResponse
    {
        return response()->json([
            'status code' => Response::HTTP_OK,
            'message' => 'success'
        ]);
    }

    private function badResponse(Exception $e): JsonResponse
    {
        return response()->json([
            'status code' => Response::HTTP_BAD_REQUEST,
            'exception' => $e,
            'message' => 'bad request'
        ]);
    }

    private function authenticatableResponse(Teacher|Student $model): JsonResponse
    {
        return response()->json([
            'status code' => Response::HTTP_OK,
            'message' => 'success',
            'token' => $model->createToken($model->id, [$model->entity])->plainTextToken
        ]);
    }
}
