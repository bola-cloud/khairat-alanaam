<?php

namespace Database\Seeders;

use App\Models\Admin\Category;
use App\Models\Subcategory;
use App\Models\Admin\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoryAndSubcategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categoriesData = [
            [
                'name_ar' => 'لحوم طازجة',
                'name_en' => 'Fresh Meat',
                'subcategories' => ['كيني', 'أسترالي', 'مارينو', 'نقانق']
            ],
            [
                'name_ar' => 'دواجن',
                'name_en' => 'Poultry',
                'subcategories' => ['800 غرام', '900 غرام', '1000 غرام', 'صدر', 'أفخاذ', 'أجنحة']
            ],
            [
                'name_ar' => 'أسماك',
                'name_en' => 'Fish',
                'subcategories' => ['كنعد', 'جيذر', 'روبيان']
            ],
            [
                'name_ar' => 'مقبلات',
                'name_en' => 'Appetizers',
                'subcategories' => ['زيتون مخلل', 'مربى', 'زيتون أسود']
            ],
            [
                'name_ar' => 'صوصات',
                'name_en' => 'Sauces',
                'subcategories' => ['صوص الصبار', 'صوص البرغر', 'صوص البرغر الحار']
            ],
            [
                'name_ar' => 'مصنعات',
                'name_en' => 'Processed',
                'subcategories' => ['روبيان مجفف صغير', 'روبيان مجفف وسط', 'روبيان مجفف كبير', 'سمن بقر عماني']
            ]
        ];

        // Ensure order continues from existing categories to show at the end
        $maxOrder = Category::max('order') ?? 0;

        foreach ($categoriesData as $index => $catData) {
            $cat = Category::create([
                'en_Category_Name' => $catData['name_en'],
                'en_Category_Slug' => Str::slug($catData['name_en']),
                'fr_Category_Name' => $catData['name_ar'],
                'fr_Category_Slug' => str_replace(' ', '-', $catData['name_ar']),
                'en_Description' => $catData['name_en'] . ' Category',
                'fr_Description' => 'قسم ' . $catData['name_ar'],
                'Status' => 1,
                'order' => $maxOrder + $index + 1,
                'show_on_home' => 1,
                'is_cut' => 0,
                'is_occasion' => 0
            ]);

            foreach ($catData['subcategories'] as $subcatName) {
                $subcat = Subcategory::create([
                    'category_id' => $cat->id,
                    'name' => 'EN ' . $subcatName,
                    'name_ar' => $subcatName,
                    'status' => 1
                ]);

                $price = rand(20, 50);
                $discount = rand(5, 20); // 5% to 20% discount
                $discountPrice = $price - ($price * ($discount / 100));
                
                // Create a dummy product for each subcategory
                Product::create([
                    'Category_Id' => $cat->id,
                    'subcategory_id' => $subcat->id,
                    'en_Product_Name' => 'Dummy Product ' . $subcatName,
                    'fr_Product_Name' => 'منتج تجريبي ' . $subcatName,
                    'en_Product_Slug' => Str::slug('dummy product ' . $subcatName) . '-' . rand(1000, 9999),
                    'Brand_Id' => 1,
                    'Price' => $price,
                    'Discount_Price' => $discountPrice,
                    'Discount' => $discount,
                    'Quantity' => 100,
                    'en_About' => 'Dummy description',
                    'fr_About' => 'وصف تجريبي',
                    'en_Description' => 'Dummy full description',
                    'fr_Description' => 'وصف كامل تجريبي',
                    'en_ShippingReturn' => 'Shipping rules',
                    'fr_ShippingReturn' => 'قواعد الشحن',
                    'en_AdditionalInformation' => 'Additional info',
                    'fr_AdditionalInformation' => 'معلومات إضافية',
                    'Primary_Image' => '6aa822f84007b1789403896.png',
                    'Voucher' => 'no',
                    'Status' => 1,
                    'ItemTag' => 'New',
                ]);
            }
        }
    }
}
