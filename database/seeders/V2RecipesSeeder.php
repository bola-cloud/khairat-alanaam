<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin\Recipe;
use Illuminate\Support\Str;

class V2RecipesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $recipes = [
            [
                'en_title' => 'Premium Beef Tenderloin',
                'fr_title' => 'تندرلوين البقر الفاخر',
                'en_description' => 'Your comprehensive guide to cooking the finest types of meat to a perfect doneness at home.',
                'fr_description' => 'دليلك الشامل لطهي أفخر أنواع اللحوم بدرجة استواء مثالية في المنزل',
                'time_to_cook' => 20,
                'difficulty' => 'medium',
                'image' => 'recipe-1.jpg',
                'status' => 1,
            ],
            [
                'en_title' => 'Grilled Chicken with Herbs and Lemon',
                'fr_title' => 'دجاج مشوي بالأعشاب والليمون',
                'en_description' => 'A classic recipe that brings out the flavor of fresh chicken with a refreshing herb and lemon marinade.',
                'fr_description' => 'وصفة كلاسيكية تبرز نكهة الدجاج الطازج مع تتبيلة الأعشاب والليمون المنعشة.',
                'time_to_cook' => 25,
                'difficulty' => 'easy',
                'image' => 'recipe-2.jpg',
                'status' => 1,
            ],
            [
                'en_title' => 'Salmon Fillet with Dill and Lemon Sauce',
                'fr_title' => 'فيليه السلمون بصلصة الشبت والليمون',
                'en_description' => 'How to prepare Omega-3 rich salmon fillet with delicious dill sauce for a healthy meal.',
                'fr_description' => 'طريقة تحضير فيليه السلمون الغني بالأوميجا 3 مع صلصة الشبت اللذيذة لوجبة صحية.',
                'time_to_cook' => 15,
                'difficulty' => 'medium',
                'image' => 'recipe-3.jpg',
                'status' => 1,
            ],
        ];

        foreach ($recipes as $recipeData) {
            $existing = Recipe::where('en_title', $recipeData['en_title'])->first();
            if (!$existing) {
                Recipe::create([
                    'en_title' => $recipeData['en_title'],
                    'fr_title' => $recipeData['fr_title'],
                    'slug' => Str::slug($recipeData['en_title']) . '-' . time(),
                    'en_description' => $recipeData['en_description'],
                    'fr_description' => $recipeData['fr_description'],
                    'time_to_cook' => $recipeData['time_to_cook'],
                    'difficulty' => $recipeData['difficulty'],
                    'image' => $recipeData['image'],
                    'status' => $recipeData['status'],
                ]);
            }
        }
    }
}
