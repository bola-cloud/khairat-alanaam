<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class V2OccasionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $occasions = [
            [
                'en_Category_Name' => 'Camping Supplies',
                'en_Category_Slug' => 'camping-supplies',
                'fr_Category_Name' => 'لوازم التخييم',
                'fr_Category_Slug' => 'لوازم-التخييم',
                'Category_Icon' => 'category-image-1.jpg',
                'status' => 1,
                'is_occasion' => 1,
                'show_on_home' => 1,
                'order' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'en_Category_Name' => 'Daily Groceries',
                'en_Category_Slug' => 'daily-groceries',
                'fr_Category_Name' => 'البقالة اليومية',
                'fr_Category_Slug' => 'البقالة-اليومية',
                'Category_Icon' => 'category-image-2.jpg',
                'status' => 1,
                'is_occasion' => 1,
                'show_on_home' => 1,
                'order' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'en_Category_Name' => 'BBQ Needs',
                'en_Category_Slug' => 'bbq-needs',
                'fr_Category_Name' => 'احتياجات الشواء',
                'fr_Category_Slug' => 'احتياجات-الشواء',
                'Category_Icon' => 'category-image-3.jpg',
                'status' => 1,
                'is_occasion' => 1,
                'show_on_home' => 1,
                'order' => 3,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ];

        DB::table('categories')->insert($occasions);
    }
}
