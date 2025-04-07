<?php

namespace App\Http\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthRepository
{
    public function register($request)
    {
        $user = User::create($request);
        $user->assignRole('user');
        return $user;
    }

    public function login($request)
    {
        return User::where('email', $request)->first();
    }

    public function createAuthToken($user)
    {
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ];
    }

    public function logout()
    {
        Auth::user()->tokens()->delete();
    }
}
