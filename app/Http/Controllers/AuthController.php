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
        $executed = RateLimiter::attempt(
            'login:'.$request->ip(),
            5,
            function() use ($request) {
                $response = $this->authService->login($request->validated());
                return $response ?: response()->json(['error' => 'Credenciais inválidas'], 401);
            },
            60
        );

        if (!$executed) {
            return response()->json([
                'error' => 'Muitas tentativas. Tente novamente em '.RateLimiter::availableIn('login:'.$request->ip()).' segundos'
            ], 429);
        }

        return $executed;
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
