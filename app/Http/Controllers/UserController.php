<?php

namespace App\Http\Controllers;


use App\Http\Requests\User\LoginRequest;
use App\Models\User;
use App\Services\QueryService;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function loginUser(LoginRequest $request): JsonResponse
    {
        /* @var User $user */
        $user = QueryService::getEntityByUserName(User::class, $request->get('username'));
        return $this->login($request->validated(), $user);
    }
}
