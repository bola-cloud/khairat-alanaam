<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Admin\Category;
use Illuminate\Support\Str;

class V2PremiumCutsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cuts = [
            [
                'en_Category_Name' => 'Chuck',
                'fr_Category_Name' => 'تشاك',
                'en_Description' => 'Perfect for grilling',
                'fr_Description' => 'مثالي للشواء',
                'Category_Icon' => 'category-image-1.jpg',
                'is_cut' => 1,
                'show_on_home' => 0,
                'order' => 1,
            ],
            [
                'en_Category_Name' => 'Brisket',
                'fr_Category_Name' => 'لحم الصدر',
                'en_Description' => 'Perfect for slow cooking',
                'fr_Description' => 'مثالي للطهي البطيء',
                'Category_Icon' => 'category-image-2.jpg',
                'is_cut' => 1,
                'show_on_home' => 0,
                'order' => 2,
            ],
            [
                'en_Category_Name' => 'T-Bone',
                'fr_Category_Name' => 'T-Bone',
                'en_Description' => 'Two cuts in one',
                'fr_Description' => 'قطعتان في واحدة',
                'Category_Icon' => 'category-image-3.jpg',
                'is_cut' => 1,
                'show_on_home' => 0,
                'order' => 3,
            ],
            [
                'en_Category_Name' => 'Sirloin',
                'fr_Category_Name' => 'لحم السيرلوين',
                'en_Description' => 'Lean and delicious',
                'fr_Description' => 'خالي من الدهون ولذيذ',
                'Category_Icon' => 'category-image-4.jpg',
                'is_cut' => 1,
                'show_on_home' => 0,
                'order' => 4,
            ],
            [
                'en_Category_Name' => 'Rib',
                'fr_Category_Name' => 'لحم الضلع',
                'en_Description' => 'Rich marbling and distinct flavor',
                'fr_Description' => 'توزيع دهني غني ونكهة مميزة',
                'Category_Icon' => 'category-image-5.jpg',
                'is_cut' => 1,
                'show_on_home' => 1,
                'order' => 5,
            ],
            [
                'en_Category_Name' => 'Tenderloin',
                'fr_Category_Name' => 'لحم الخاصرة',
                'en_Description' => 'The most tender cut of meat',
                'fr_Description' => 'أكثر قطع اللحم طراوة',
                'Category_Icon' => 'category-image-6.jpg',
                'is_cut' => 1,
                'show_on_home' => 1,
                'order' => 6,
            ],
        ];

        foreach ($cuts as $cutData) {
            $existing = Category::where('en_Category_Name', $cutData['en_Category_Name'])->first();
            if (!$existing) {
                Category::create([
                    'en_Category_Name' => $cutData['en_Category_Name'],
                    'fr_Category_Name' => $cutData['fr_Category_Name'],
                    'en_Category_Slug' => Str::slug($cutData['en_Category_Name']),
                    'fr_Category_Slug' => Str::slug($cutData['fr_Category_Name']),
                    'en_Description' => $cutData['en_Description'],
                    'fr_Description' => $cutData['fr_Description'],
                    'Category_Icon' => $cutData['Category_Icon'],
                    'is_cut' => $cutData['is_cut'],
                    'show_on_home' => $cutData['show_on_home'],
                    'order' => $cutData['order'],
                    'Status' => 1,
                ]);
            } else {
                $existing->update([
                    'is_cut' => 1,
                    'en_Description' => $cutData['en_Description'],
                    'fr_Description' => $cutData['fr_Description'],
                    'show_on_home' => $cutData['show_on_home'],
                ]);
            }
        }
    }
}
