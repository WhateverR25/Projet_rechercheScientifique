<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\IncomeController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AdminAuthController;
use Illuminate\Support\Facades\Route;

Route::middleware([\App\Http\Middleware\CheckMaintenanceMode::class])->group(function () {
    Route::prefix('auth')->group(function (): void {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);

        Route::middleware('auth:api')->group(function (): void {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('me', [AuthController::class, 'me']);
        });
    });

    Route::middleware('auth:api')->group(function (): void {
        Route::get('dashboard', DashboardController::class);
        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('incomes', IncomeController::class);
        Route::apiResource('expenses', ExpenseController::class);
        Route::apiResource('transactions', TransactionController::class);
    });
});

Route::prefix('admin')->group(function () {
    Route::post('login', [AdminAuthController::class, 'login']);
    
    Route::middleware('auth:admin')->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout']);
        Route::get('me', [AdminAuthController::class, 'me']);
        
        Route::get('dashboard', [AdminController::class, 'dashboard']);
        Route::get('users', [AdminController::class, 'users']);
        Route::get('users/{user}', [AdminController::class, 'showUser']);
        Route::patch('users/{user}/deactivate', [AdminController::class, 'deactivateUser']);
        Route::patch('users/{user}/reactivate', [AdminController::class, 'reactivateUser']);
        Route::delete('users/{user}', [AdminController::class, 'deleteUser']);
        
        Route::get('transactions', [AdminController::class, 'transactions']);

        Route::get('categories', [AdminController::class, 'categories']);
        Route::post('categories', [AdminController::class, 'createCategory']);
        Route::patch('categories/{category}', [AdminController::class, 'updateCategory']);
        Route::delete('categories/{category}', [AdminController::class, 'deleteCategory']);

        Route::get('user-categories', [AdminController::class, 'userCategories']);
        Route::delete('user-categories/{category}', [AdminController::class, 'deleteUserCategory']);
        
        Route::get('logs', [AdminController::class, 'logs']);
        
        Route::get('settings', [AdminController::class, 'getSettings']);
        Route::patch('settings', [AdminController::class, 'updateSettings']);
        Route::post('settings/clear-cache', [AdminController::class, 'clearCache']);
    });
});
