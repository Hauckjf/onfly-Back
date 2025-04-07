<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Services\AuthService;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request)
    {
        try {
            $response = $this->authService->register($request->validated());
            return response()->json($response, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function login(LoginRequest $request)
    {
        $key = 'login:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json([
                'error' => 'Muitas tentativas. Tente novamente em ' . RateLimiter::availableIn($key) . ' segundos'
            ], 429);
        }

        RateLimiter::hit($key, 60);

        $response = $this->authService->login($request->validated());

        if (!$response) {
            return response()->json(['error' => 'Credenciais inválidas'], 401);
        }

        RateLimiter::clear($key);
        return $response;
    }

    public function logout()
    {
        try {
            $this->authService->logout();
            return response()->json(['message' => 'Logout realizado com sucesso']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
