<?php

namespace Database\Seeders;

use App\Models\Admin\Advertise;
use Illuminate\Database\Seeder;

class HeroAdvertiseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Remove old advertises (optional if we want to clear all, but the user requested "remove existing data")
        Advertise::truncate();

        // Seed the new hero advertise based on the design text
        \Illuminate\Support\Facades\DB::table('advertises')->insert([
            'location' => 'hero',
            'status' => 1,
            'display_order' => 1,
            'image' => 'hero-bg.jpg', // No specific image was provided, so we use the default hero-bg
            'Image_One' => 'hero-bg.jpg', // Required field
            'Image_Two' => 'hero-bg.jpg', // Required field
            'ar_title' => 'خيرات الأنعام الطازجة',
            'ar_subtitle' => 'بمعايير عالمية بين يديك',
            'ar_small_description' => 'نقدم لك أفضل ما في المزارع المحلية والعالمية، مقطوعة بحرفية لتكون المكون الأساسي لمائدتك اليومية ومناسباتك الخاصة.',
            'en_title' => 'Fresh Khairat Al-An\'aam',
            'en_subtitle' => 'Global Standards at Your Fingertips',
            'en_small_description' => 'We offer you the best of local and international farms, expertly cut to be the essential ingredient for your daily table and special occasions.',
            'link' => '#',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
