<?php

namespace App\Http\Services;

use App\Http\Repositories\AuthRepository;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AuthService
{
    protected $authRepository;

    public function __construct(AuthRepository $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    public function register($request)
    {
        $request['password'] = Hash::make($request['password']);
        return $this->authRepository->register($request);
    }

    public function login($request)
    {
        $user = $this->authRepository->login($request['email']);

        if (!$user || !Hash::check($request['password'], $user->password)) {
            throw new UnauthorizedHttpException('', 'Credenciais inválidas');
        }

        return $this->authRepository->createAuthToken($user);
    }

    public function logout()
    {
        $this->authRepository->logout();
    }

}
