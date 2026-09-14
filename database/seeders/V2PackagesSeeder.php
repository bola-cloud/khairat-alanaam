<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class V2PackagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $packages = [
            [
                'en_Product_Name' => 'Grill Master Package',
                'en_Product_Slug' => 'grill-master-package',
                'fr_Product_Name' => 'حزمة سيد الشواء',
                'fr_Product_Slug' => 'حزمة-سيد-الشواء',
                'Primary_Image' => '6aa822f84007b1789403896.png',
                'Price' => 350.00,
                'Discount' => 10,
                'Discount_Price' => 315.00,
                'Quantity' => 50,
                'Status' => 1,
                'is_package' => 1,
                'type' => 1, // Physical
                'en_Description' => '<ul><li>2kg Ribs</li><li>1kg Kebab</li><li>500g Kofta</li></ul>',
                'fr_Description' => '<ul><li>٢ كيلو ريش ضأن</li><li>١ كيلو كباب بلدي</li><li>نصف كيلو كفتة مبهرة</li></ul>',
                'en_About' => '',
                'fr_About' => '',
                'en_ShippingReturn' => '',
                'fr_ShippingReturn' => '',
                'en_AdditionalInformation' => '',
                'fr_AdditionalInformation' => '',
                'Brand_Id' => null,
                'Category_Id' => null,
                'Voucher' => Str::random(6),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'en_Product_Name' => 'Family Feast',
                'en_Product_Slug' => 'family-feast',
                'fr_Product_Name' => 'وليمة العائلة',
                'fr_Product_Slug' => 'وليمة-العائلة',
                'Primary_Image' => '6aa822f84007b1789403896.png',
                'Price' => 500.00,
                'Discount' => 15,
                'Discount_Price' => 425.00,
                'Quantity' => 30,
                'Status' => 1,
                'is_package' => 1,
                'type' => 1,
                'en_Description' => '<ul><li>3kg Mixed Meat</li><li>2kg Ground Beef</li><li>Spices</li></ul>',
                'fr_Description' => '<ul><li>٣ كيلو لحم مشكل</li><li>٢ كيلو مفروم صافي</li><li>تشكيلة بهارات شواء مجانية</li></ul>',
                'en_About' => '',
                'fr_About' => '',
                'en_ShippingReturn' => '',
                'fr_ShippingReturn' => '',
                'en_AdditionalInformation' => '',
                'fr_AdditionalInformation' => '',
                'Brand_Id' => null,
                'Category_Id' => null,
                'Voucher' => Str::random(6),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'en_Product_Name' => 'Weekly Essentials',
                'en_Product_Slug' => 'weekly-essentials',
                'fr_Product_Name' => 'الأساسيات الأسبوعية',
                'fr_Product_Slug' => 'الأساسيات-الأسبوعية',
                'Primary_Image' => '6aa822f84007b1789403896.png',
                'Price' => 200.00,
                'Discount' => 0,
                'Discount_Price' => 200.00,
                'Quantity' => 100,
                'Status' => 1,
                'is_package' => 1,
                'type' => 1,
                'en_Description' => '<ul><li>1kg Beef Cubes</li><li>1kg Minced Meat</li><li>500g Liver</li></ul>',
                'fr_Description' => '<ul><li>١ كيلو مكعبات لحم بقر</li><li>١ كيلو مفروم</li><li>نصف كيلو كبدة طازجة</li></ul>',
                'en_About' => '',
                'fr_About' => '',
                'en_ShippingReturn' => '',
                'fr_ShippingReturn' => '',
                'en_AdditionalInformation' => '',
                'fr_AdditionalInformation' => '',
                'Brand_Id' => null,
                'Category_Id' => null,
                'Voucher' => Str::random(6),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ];

        DB::table('products')->insert($packages);
    }
}
