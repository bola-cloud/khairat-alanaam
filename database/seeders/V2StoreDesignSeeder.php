<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin\Category;
use App\Models\Admin\Product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class V2StoreDesignSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Remove existing products and categories as requested
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Product::truncate();
        Category::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create or find a category
        $category = Category::firstOrCreate([
            'en_Category_Slug' => 'brazilian-beef'
        ], [
            'fr_Category_Slug' => 'brazilian-beef-ar',
            'en_Category_Name' => 'Brazilian Beef',
            'fr_Category_Name' => 'لحم بقر برازيلي',
            'en_Description' => 'Premium Brazilian Beef',
            'fr_Description' => 'لحم بقر برازيلي فاخر',
            'Status' => 1,
            'Category_Icon' => 'icon.png'
        ]);

        $coffeeCategory = Category::firstOrCreate([
            'en_Category_Slug' => 'coffee-crops'
        ], [
            'fr_Category_Slug' => 'coffee-crops-ar',
            'en_Category_Name' => 'Coffee Crops',
            'fr_Category_Name' => 'محاصيل القهوة',
            'en_Description' => 'Premium Coffee Crops',
            'fr_Description' => 'محاصيل قهوة فاخرة',
            'Status' => 1,
            'Category_Icon' => 'icon2.png'
        ]);

        // Products Data
        $productsData = [
            [
                'fr_Product_Name' => 'شرائح لحم ضأن طازجة جودة ممتازة 500 جرام',
                'en_Product_Name' => 'Fresh Lamb Chops Premium 500g',
                'Price' => 22.50,
                'Quantity' => 50,
                'category_id' => $category->id,
            ],
            [
                'fr_Product_Name' => 'لحم بقر برازيلي ممتاز للبرجر والشوي وزن 1 كيلو جرام',
                'en_Product_Name' => 'Premium Brazilian Beef for Burger 1kg',
                'Price' => 45.00,
                'Quantity' => 0, // Out of stock to test badge
                'category_id' => $category->id,
            ],
            [
                'fr_Product_Name' => 'اثيوبي يرجاشيف 250 جرام - محصول فاخر ذو إيحاءات فاكهية',
                'en_Product_Name' => 'Ethiopian Yirgacheffe 250g',
                'Price' => 5.800,
                'Quantity' => 20,
                'category_id' => $coffeeCategory->id,
            ],
            [
                'fr_Product_Name' => 'اندونيسيا 250 جرام',
                'en_Product_Name' => 'Indonesia 250g',
                'Price' => 6.500,
                'Quantity' => 15,
                'category_id' => $coffeeCategory->id,
            ],
            [
                'fr_Product_Name' => 'اثيوبي شاكيسو 250 جرام',
                'en_Product_Name' => 'Ethiopian Shakiso 250g',
                'Price' => 5.500,
                'Quantity' => 10,
                'category_id' => $coffeeCategory->id,
            ],
            [
                'fr_Product_Name' => 'لحم بقر مفروم ناعم طازج ومبرد 500 جرام جاهز للطبخ المباشر',
                'en_Product_Name' => 'Fresh Minced Beef 500g',
                'Price' => 18.00,
                'Quantity' => 100,
                'category_id' => $category->id,
            ],
            [
                'fr_Product_Name' => 'ريش غنم أسترالي مبرد 500 جرام',
                'en_Product_Name' => 'Australian Lamb Chops 500g',
                'Price' => 35.00,
                'Quantity' => 5, // Limited stock
                'category_id' => $category->id,
            ],
        ];

        foreach ($productsData as $data) {
            Product::create([
                'en_Product_Name' => $data['en_Product_Name'],
                'fr_Product_Name' => $data['fr_Product_Name'],
                'en_Product_Slug' => Str::slug($data['en_Product_Name']) . '-' . rand(100, 999),
                'fr_Product_Slug' => Str::slug($data['en_Product_Name']) . '-ar-' . rand(100, 999),
                'ItemTag' => 'New',
                'Category_Id' => $data['category_id'],
                'Quantity' => $data['Quantity'],
                'Price' => $data['Price'],
                'Status' => 1,
                'Featured_Product' => 1,
                'Primary_Image' => 'default.png',
                'en_About' => 'Description goes here',
                'fr_About' => 'الوصف هنا',
                'en_Description' => 'Long description',
                'fr_Description' => 'وصف طويل',
                'en_ShippingReturn' => 'N/A',
                'fr_ShippingReturn' => 'N/A',
                'en_AdditionalInformation' => 'N/A',
                'fr_AdditionalInformation' => 'N/A',
                'Voucher' => 'no',
                'cost' => 10.00,
                'barcode' => '0000',
                'unit' => 'piece',
                'alert_quantity' => 1,
                'Sold' => 0,
            ]);
        }
    }
}
