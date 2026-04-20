<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Income;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IncomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        return response()->json(auth('api')->user()->incomes()->with('category')->latest('received_at')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $user = auth('api')->user();

        $validated = $request->validate([
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query
                    ->where('user_id', $user->id)
                    ->whereIn('type', ['income', 'both'])),
            ],
            'amount' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:255'],
            'received_at' => ['required', 'date'],
        ]);

        $income = $user->incomes()->create($validated);

        return response()->json($income->load('category'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Income $income): JsonResponse
    {
        abort_unless($income->user_id === auth('api')->id(), 403);

        return response()->json($income->load('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Income $income): JsonResponse
    {
        abort_unless($income->user_id === auth('api')->id(), 403);
        $user = auth('api')->user();

        $validated = $request->validate([
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query
                    ->where('user_id', $user->id)
                    ->whereIn('type', ['income', 'both'])),
            ],
            'amount' => ['sometimes', 'required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:255'],
            'received_at' => ['sometimes', 'required', 'date'],
        ]);

        $income->update($validated);

        return response()->json($income->refresh()->load('category'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Income $income): JsonResponse
    {
        abort_unless($income->user_id === auth('api')->id(), 403);
        $income->delete();

        return response()->json([], 204);
    }
}
