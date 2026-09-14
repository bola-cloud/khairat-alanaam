<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Admin\Category;
use App\Models\Admin\Product;
use Illuminate\Support\Str;

class V2OccasionProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $occasions = Category::where('is_occasion', 1)->get();

        foreach ($occasions as $occasion) {
            // Check if there are already products for this occasion
            $existingProduct = Product::where('Category_Id', $occasion->id)->first();
            
            if (!$existingProduct) {
                // Create a dummy product for this occasion
                $productNameEn = 'Premium ' . $occasion->en_Category_Name . ' Pack';
                $productNameFr = 'باقة ' . $occasion->fr_Category_Name . ' المميزة';
                
                $price = rand(15, 50);
                $discount = rand(5, 15);
                $discountPrice = $price - ($price * ($discount / 100));

                $product = Product::create([
                    'en_Product_Name' => $productNameEn,
                    'fr_Product_Name' => $productNameFr,
                    'en_Product_Slug' => Str::slug($productNameEn) . '-' . time(),
                    'fr_Product_Slug' => Str::slug($productNameFr) . '-' . time(),
                    'en_About' => 'High quality items for ' . $occasion->en_Category_Name,
                    'fr_About' => 'عناصر عالية الجودة مخصصة لـ ' . $occasion->fr_Category_Name,
                    'en_Description' => 'High quality items for ' . $occasion->en_Category_Name,
                    'fr_Description' => 'عناصر عالية الجودة مخصصة لـ ' . $occasion->fr_Category_Name,
                    'en_ShippingReturn' => 'Standard shipping',
                    'fr_ShippingReturn' => 'شحن عادي',
                    'en_AdditionalInformation' => 'None',
                    'fr_AdditionalInformation' => 'لا يوجد',
                    'Voucher' => '',
                    'ItemTag' => 'Occasion',
                    'Category_Id' => $occasion->id,
                    'Price' => $price,
                    'Discount_Price' => $discountPrice,
                    'Discount' => $discount,
                    'Quantity' => 50,
                    'Status' => 1,
                    'Primary_Image' => '6aa822f84007b1789403896.png',
                    'Image2' => '6aa822f84007b1789403896.png',
                    'Image3' => '6aa822f84007b1789403896.png',
                    'Image4' => '6aa822f84007b1789403896.png',
                    'Image5' => '6aa822f84007b1789403896.png',
                    'type' => 1,
                    'is_package' => 0,
                    'Today_Special' => 0,
                ]);
            }
        }
    }
}
