<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user('api');

        $totalIncome = (float) $user->incomes()->sum('amount');
        $totalExpense = (float) $user->expenses()->sum('amount');
        $balance = $totalIncome - $totalExpense;
        $driver = DB::connection()->getDriverName();
        $monthExpression = match ($driver) {
            'pgsql' => "TO_CHAR(%s, 'YYYY-MM')",
            'sqlite' => "strftime('%%Y-%%m', %s)",
            default => "DATE_FORMAT(%s, '%%Y-%%m')",
        };

        $monthlyIncome = $user->incomes()
            ->selectRaw(sprintf($monthExpression, 'received_at').' as month')
            ->selectRaw('SUM(amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $monthlyExpenses = $user->expenses()
            ->selectRaw(sprintf($monthExpression, 'spent_at').' as month')
            ->selectRaw('SUM(amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $expenseByCategory = $user->expenses()
            ->leftJoin('categories', 'expenses.category_id', '=', 'categories.id')
            ->selectRaw("COALESCE(categories.name, 'Uncategorized') as name")
            ->selectRaw("COALESCE(categories.color, '#6b7280') as color")
            ->selectRaw('SUM(expenses.amount) as total')
            ->groupBy('name', 'color')
            ->orderByDesc('total')
            ->get();

        return response()->json([
            'total_income' => round($totalIncome, 2),
            'total_expense' => round($totalExpense, 2),
            'balance' => round($balance, 2),
            'savings_rate' => $totalIncome > 0 ? round(($balance / $totalIncome) * 100, 1) : 0,
            'monthly_income' => $monthlyIncome,
            'monthly_expenses' => $monthlyExpenses,
            'expense_by_category' => $expenseByCategory,
            'recent_transactions' => $user->incomes()->latest('received_at')->limit(5)->get()->map(fn ($income) => [
                'id' => $income->id,
                'type' => 'income',
                'description' => $income->description ?? 'Income',
                'amount' => (float) $income->amount,
                'date' => $income->received_at,
            ])->merge(
                $user->expenses()->latest('spent_at')->limit(5)->get()->map(fn ($expense) => [
                    'id' => $expense->id,
                    'type' => 'expense',
                    'description' => $expense->description ?? 'Expense',
                    'amount' => (float) $expense->amount,
                    'date' => $expense->spent_at,
                ])
            )->sortByDesc('date')->values()->take(8),
        ]);
    }
}
