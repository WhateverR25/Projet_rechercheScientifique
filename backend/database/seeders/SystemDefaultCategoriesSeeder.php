<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemDefaultCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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

        // Insert as system default categories (user_id = null)
        foreach ($defaultCategories as $category) {
            DB::table('categories')->insert(array_merge($category, [
                'user_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('System default categories seeded successfully!');
    }
}
