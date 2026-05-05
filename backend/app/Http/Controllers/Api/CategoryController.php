<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $userId = auth('api')->id();

        // Get both system default categories (user_id IS NULL) and user's own categories
        $categories = Category::where(function ($query) use ($userId) {
            $query->whereNull('user_id')
                  ->orWhere('user_id', $userId);
        })->latest()->get();

        return response()->json($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:income,expense,both'],
            'color' => ['nullable', 'string', 'max:20'],
            'icon' => ['nullable', 'string', 'max:10'],
        ]);

        $category = auth('api')->user()->categories()->create($validated);

        return response()->json($category, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category): JsonResponse
    {
        // Allow viewing system default categories (user_id IS NULL) or own categories
        abort_unless($category->user_id === null || $category->user_id === auth('api')->id(), 403);

        return response()->json($category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        // Only allow editing own categories, not system defaults
        abort_unless($category->user_id !== null && $category->user_id === auth('api')->id(), 403);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'type' => ['sometimes', 'required', 'in:income,expense,both'],
            'color' => ['nullable', 'string', 'max:20'],
            'icon' => ['nullable', 'string', 'max:10'],
        ]);

        $category->update($validated);

        return response()->json($category->refresh());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): JsonResponse
    {
        // Only allow deleting own categories, not system defaults
        abort_unless($category->user_id !== null && $category->user_id === auth('api')->id(), 403);
        $category->delete();

        return response()->json([], 204);
    }
}
