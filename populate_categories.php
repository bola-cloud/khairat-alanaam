<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$data = [
    'لحوم طازجة' => [
        'en' => 'Fresh Meat',
        'subs' => ['كيني', 'استرالي', 'مارينو', 'نقانق']
    ],
    'دواجن' => [
        'en' => 'Poultry',
        'subs' => ['الدجاج باوزانه 100', '900', '800 غرام', 'صدر', 'افخاد', 'اجنحة']
    ],
    'اسماك' => [
        'en' => 'Fish',
        'subs' => ['كنعد', 'جيذر', 'روبيان']
    ],
    'مقبلات' => [
        'en' => 'Appetizers',
        'subs' => ['زيتون مخلل', 'مربى', 'زيتون اسود']
    ],
    'صوصات' => [
        'en' => 'Sauces',
        'subs' => ['صوص الصبار', 'صوص البرغر', 'صوص البرغر الحار']
    ],
    'مصنعات' => [
        'en' => 'Processed',
        'subs' => ['روبيان مجفف صغير', 'روبيان مجفف وسط', 'روبيان مجفف كبير', 'سمن بقر عماني']
    ]
];

foreach ($data as $ar => $info) {
    $slug = \Illuminate\Support\Str::slug($info['en']);
    $cat = \App\Models\Admin\Category::firstOrCreate(
        ['fr_Category_Name' => $ar],
        [
            'en_Category_Name' => $info['en'], 
            'Status' => 1,
            'en_Category_Slug' => $slug,
            'fr_Category_Slug' => $slug
        ]
    );

    foreach ($info['subs'] as $sub) {
        // We use DB table because there is no model? Wait, there is no Subcategory model registered in App\Models\Admin.
        // Let's use DB facade
        $exists = \Illuminate\Support\Facades\DB::table('subcategories')
            ->where('category_id', $cat->id)
            ->where('name_ar', $sub)
            ->exists();
            
        if (!$exists) {
            \Illuminate\Support\Facades\DB::table('subcategories')->insert([
                'category_id' => $cat->id,
                'name_ar' => $sub,
                'name' => $sub, // Assuming English name is same if not provided
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
echo "Categories and subcategories populated successfully!\n";
