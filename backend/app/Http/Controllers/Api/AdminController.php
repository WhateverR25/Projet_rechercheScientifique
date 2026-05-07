<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Category;
use App\Models\AdminLog;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    private function logAction($action, $description)
    {
        AdminLog::create([
            'action_type' => $action,
            'description' => $description
        ]);
    }

    public function dashboard(): JsonResponse
    {
        $totalUsers = User::count();
        $newUsersThisMonth = User::whereMonth('created_at', Carbon::now()->month)
                                 ->whereYear('created_at', Carbon::now()->year)->count();
        $totalTransactions = DB::table('incomes')->count() + DB::table('expenses')->count();
        $activeCategories = Category::whereNotNull('user_id')->count();

        // Users registrations last 6 months
        $registrations = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $count = User::whereMonth('created_at', $date->month)
                         ->whereYear('created_at', $date->year)->count();
            $registrations->push([
                'label' => $date->translatedFormat('F'),
                'count' => $count
            ]);
        }

        // Transactions last 30 days
        $transactions = collect();
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $incomes = DB::table('incomes')->whereDate('date', $date)->count();
            $expenses = DB::table('expenses')->whereDate('date', $date)->count();
            $transactions->push([
                'date' => $date,
                'count' => $incomes + $expenses
            ]);
        }

        return response()->json([
            'stats' => [
                'totalUsers' => $totalUsers,
                'newUsersThisMonth' => $newUsersThisMonth,
                'totalTransactions' => $totalTransactions,
                'activeCategories' => $activeCategories,
            ],
            'charts' => [
                'registrations' => $registrations,
                'transactions' => $transactions
            ]
        ]);
    }

    public function users(Request $request): JsonResponse
    {
        $query = User::withCount(['incomes', 'expenses']);

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);
        $users->getCollection()->transform(function ($user) {
            $user->transactions_count = $user->incomes_count + $user->expenses_count;
            return $user;
        });

        return response()->json($users);
    }

    public function showUser(User $user): JsonResponse
    {
        $user->loadCount(['incomes', 'expenses']);
        $user->transactions_count = $user->incomes_count + $user->expenses_count;
        return response()->json($user);
    }

    public function deactivateUser(User $user): JsonResponse
    {
        $user->status = 'inactive';
        $user->save();

        $this->logAction('USER_DEACTIVATED', "Désactivation de l'utilisateur : {$user->email}");

        return response()->json(['message' => 'User deactivated']);
    }

    public function reactivateUser(User $user): JsonResponse
    {
        $user->status = 'active';
        $user->save();

        $this->logAction('USER_REACTIVATED', "Réactivation de l'utilisateur : {$user->email}");

        return response()->json(['message' => 'User reactivated']);
    }

    public function deleteUser(User $user): JsonResponse
    {
        $email = $user->email;
        $user->delete();

        $this->logAction('USER_DELETED', "Suppression de l'utilisateur : {$email}");

        return response()->json(['message' => 'User deleted']);
    }

    public function transactions(Request $request): JsonResponse
    {
        $incomes = DB::table('incomes')
            ->join('users', 'incomes.user_id', '=', 'users.id')
            ->join('categories', 'incomes.category_id', '=', 'categories.id')
            ->select('incomes.id', 'incomes.amount', 'incomes.description', 'incomes.date', 'users.name as user_name', 'users.email as user_email', 'categories.name as category_name', DB::raw("'income' as type"));

        $expenses = DB::table('expenses')
            ->join('users', 'expenses.user_id', '=', 'users.id')
            ->join('categories', 'expenses.category_id', '=', 'categories.id')
            ->select('expenses.id', 'expenses.amount', 'expenses.description', 'expenses.date', 'users.name as user_name', 'users.email as user_email', 'categories.name as category_name', DB::raw("'expense' as type"));

        $query = $incomes->union($expenses);

        $transactionsQuery = DB::query()->fromSub($query, 'transactions');

        if ($request->has('type') && $request->type != '') {
            $transactionsQuery->where('type', $request->type);
        }

        if ($request->has('category') && $request->category != '') {
            $transactionsQuery->where('category_name', 'like', '%' . $request->category . '%');
        }

        if ($request->has('date') && $request->date != '') {
            $transactionsQuery->whereDate('date', $request->date);
        }

        $transactions = $transactionsQuery->orderBy('date', 'desc')->paginate(20);

        return response()->json($transactions);
    }

    public function categories(): JsonResponse
    {
        $categories = Category::whereNull('user_id')->get();
        return response()->json($categories);
    }

    public function userCategories(): JsonResponse
    {
        $categories = Category::whereNotNull('user_id')
            ->where('is_system_default', false)
            ->with('user:id,name,email')
            ->orderBy('user_id')
            ->get();
        return response()->json($categories);
    }

    public function deleteUserCategory(Category $category): JsonResponse
    {
        abort_unless($category->user_id !== null, 403);
        $categoryName = $category->name;
        $category->delete();

        $this->logAction('USER_CATEGORY_DELETED', "Suppression de la catégorie utilisateur : {$categoryName}");

        return response()->json(null, 204);
    }

    public function createCategory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense,both',
            'icon' => 'required|string',
            'color' => 'required|string'
        ]);

        $category = Category::create(array_merge($validated, ['user_id' => null]));

        $this->logAction('CATEGORY_CREATED', "Création de la catégorie par défaut : {$category->name}");

        return response()->json($category, 201);
    }

    public function updateCategory(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:income,expense,both',
            'icon' => 'sometimes|string',
            'color' => 'sometimes|string'
        ]);

        $category->update($validated);

        $this->logAction('CATEGORY_EDITED', "Modification de la catégorie par défaut : {$category->name}");

        return response()->json($category);
    }

    public function deleteCategory(Category $category): JsonResponse
    {
        $categoryName = $category->name;
        $category->delete();

        $this->logAction('CATEGORY_DELETED', "Suppression de la catégorie par défaut : {$categoryName}");

        return response()->json(null, 204);
    }

    public function logs(): JsonResponse
    {
        $logs = AdminLog::orderBy('created_at', 'desc')->limit(100)->get();
        return response()->json($logs);
    }

    public function getSettings(): JsonResponse
    {
        $maintenance = SystemSetting::where('key', 'maintenance_mode')->first();
        return response()->json([
            'maintenance_mode' => $maintenance ? $maintenance->value === 'true' : false,
            'version' => config('app.version', '1.0.0')
        ]);
    }

    public function updateSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'maintenance_mode' => 'required|boolean'
        ]);

        SystemSetting::updateOrCreate(
            ['key' => 'maintenance_mode'],
            ['value' => $validated['maintenance_mode'] ? 'true' : 'false']
        );

        $status = $validated['maintenance_mode'] ? 'activé' : 'désactivé';
        $this->logAction('MAINTENANCE_TOGGLED', "Le mode maintenance a été {$status}");

        return response()->json(['message' => 'Settings updated']);
    }

    public function clearCache(): JsonResponse
    {
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        \Illuminate\Support\Facades\Artisan::call('view:clear');

        $this->logAction('CACHE_CLEARED', "Le cache système a été vidé");

        return response()->json(['message' => 'Cache cleared successfully']);
    }
}
