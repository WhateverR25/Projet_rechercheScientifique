<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $defaultCategories = [
            ['name' => 'Alimentation', 'type' => 'expense', 'icon' => '🍔', 'color' => '#10b981'],
            ['name' => 'Transport', 'type' => 'expense', 'icon' => '🚗', 'color' => '#3b82f6'],
            ['name' => 'Logement', 'type' => 'expense', 'icon' => '🏠', 'color' => '#8b5cf6'],
            ['name' => 'Santé', 'type' => 'expense', 'icon' => '💊', 'color' => '#f43f5e'],
            ['name' => 'Loisirs', 'type' => 'expense', 'icon' => '🎮', 'color' => '#f59e0b'],
            ['name' => 'Vêtements', 'type' => 'expense', 'icon' => '👕', 'color' => '#ec4899'],
            ['name' => 'Éducation', 'type' => 'expense', 'icon' => '📚', 'color' => '#06b6d4'],
            ['name' => 'Abonnements', 'type' => 'expense', 'icon' => '📱', 'color' => '#14b8a6'],
            ['name' => 'Restaurant', 'type' => 'expense', 'icon' => '🍽️', 'color' => '#f97316'],
            ['name' => 'Salaire', 'type' => 'income', 'icon' => '💼', 'color' => '#10b981'],
            ['name' => 'Freelance', 'type' => 'income', 'icon' => '💻', 'color' => '#3b82f6'],
            ['name' => 'Investissements', 'type' => 'income', 'icon' => '📈', 'color' => '#8b5cf6'],
            ['name' => 'Autre revenu', 'type' => 'income', 'icon' => '🎁', 'color' => '#f59e0b'],
        ];

        foreach ($defaultCategories as $cat) {
            $user->categories()->create($cat);
        }

        $token = auth('api')->login($user);

        return response()->json([
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        return response()->json([
            'user' => auth('api')->user(),
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ]);
    }

    public function me(): JsonResponse
    {
        return response()->json(auth('api')->user());
    }

    public function logout(): JsonResponse
    {
        auth('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }
}
