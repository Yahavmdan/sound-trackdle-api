<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Teacher;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Builder $entity): JsonResponse
    {
        try {
            return $this->indexResponse($entity);
        } catch (Exception $e) {
            return $this->badResponse($e);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(array $values, string $entity, ?bool $hasToken = false): JsonResponse
    {
        try {
            $entity = new $entity($values);
            $entity->save();
            if (!$hasToken) return $this->okResponse();
            return $this->authenticationResponse($entity);
        } catch (Exception $e) {
            return $this->badResponse($e);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Model $entity, array $values): JsonResponse
    {
        try {
            $entity->update($values);
            return $this->okResponse();
        } catch (Exception $e) {
            return $this->badResponse($e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Builder|Model $entity): JsonResponse
    {
        try {
            $entity->delete();
            return $this->okResponse();
        } catch (Exception $e) {
            return $this->badResponse($e);
        }
    }

    /**
     * Generates a JSON response containing the data retrieved from the given Eloquent query builder.
     * @param Builder $model
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
            'status code' => Response::HTTP_OK,
            'message' => $message ?? 'success'
        ]);
    }

    /**
     * Generates a JSON response indicating a bad request with a 400 Bad Request status.
     * @param Exception|null $e The exception that triggered the bad request.
     * @return JsonResponse The JSON response with a bad request message, status code, and exception details.
     */
    public function badResponse(?Exception $e = null, ?string $message = null): JsonResponse
    {
        return response()->json([
            'status code' => Response::HTTP_BAD_REQUEST,
            'exception' => $e ?? null,
            'message' => $message ?? 'bad request'
        ]);
    }

    /**
     * Generates a JSON response indicating a successful authentication with a 200 OK status.
     * @param Teacher|Student $model The authenticated user model (Teacher or Student).
     * @return JsonResponse The JSON response with a success message, status code, and authentication token.
     */
    private function authenticationResponse(Teacher|Student $model): JsonResponse
    {
        return response()->json([
            'status code' => Response::HTTP_OK,
            'message' => 'success',
            'token' => $model->createToken($model->full_name, [$model->entity])->plainTextToken
        ]);
    }

    /**
     * Login to the app.
     * @param Collection $values
     * @param Teacher|Student|null $entity
     * @return JsonResponse
     */
    public function login(Collection $values, Teacher | Student $entity = null): JsonResponse
    {
        if (!$entity || !Hash::check($values->get('password'), $entity->password)) {
            return $this->badResponse();
        }

        return $this->authenticationResponse($entity);
    }
}
