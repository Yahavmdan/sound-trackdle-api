<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Display a listing of the resource.
     */
    public function indexData(Builder $entity): JsonResponse
    {
        try {
            return $this->indexResponse($entity);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeData(array $values, string $entity, ?bool $authenticatable = false): JsonResponse
    {
        try {
            $entity = new $entity($values);
            $entity->save();
            if (!$authenticatable) return $this->okResponse();
            return $this->authenticationResponse($entity);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateData(Model $entity, array $values): JsonResponse
    {
        try {
            $entity->update($values);
            return $this->okResponse();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function deleteData(Builder|Model $entity): JsonResponse
    {
        try {
            $entity->delete();
            return $this->okResponse();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Generates a JSON response containing the data retrieved from the given Eloquent query builder.
     * @param Builder $entity
     * @return JsonResponse The JSON response containing the data.
     */
    private function indexResponse(Builder $entity): JsonResponse
    {
        return response()->json($entity->get());
    }

    /**
     * Generates a JSON response indicating a successful operation with a 200 OK status.
     * @return JsonResponse The JSON response with a success message and status code.
     */
    public function okResponse(?string $message = null): JsonResponse
    {
        return response()->json([
            'code' => Response::HTTP_OK,
            'message' => $message ?? 'success'
        ]);
    }

    /**
     * Generates a JSON response indicating a bad request with a 400 Bad Request status.
     * @param Exception|null $e The exception that triggered the bad request.
     * @return JsonResponse The JSON response with a bad request message, status code, and exception details.
     */
    public function errorResponse(?Exception $e = null, ?string $message = null): JsonResponse
    {
        return response()->json([
            'code' => Response::HTTP_BAD_REQUEST,
            'exception' => $e ?? null,
            'message' => $message ?? 'bad request'
        ]);
    }

    /**
     * Generates a JSON response indicating a successful authentication with a 200 OK status.
     * @param User $model The authenticated user model (Teacher or Student).
     * @return JsonResponse The JSON response with a success message, status code, and authentication token.
     */
    private function authenticationResponse(User $model): JsonResponse
    {
        return response()->json([
            'code' => Response::HTTP_OK,
            'message' => 'success',
            'token' => $model->createToken($model->username, [$model->entity])->plainTextToken
        ]);
    }

    /**
     * Login to the app.
     * @param array $values
     * @param User|null $model
     * @return JsonResponse
     */
    public function login(array $values, User $model = null): JsonResponse
    {
        if (!$model || !Auth::guard($model->entity.'s')->attempt($values)) {
            return $this->errorResponse();
        }
        return $this->authenticationResponse($model);
    }
}
