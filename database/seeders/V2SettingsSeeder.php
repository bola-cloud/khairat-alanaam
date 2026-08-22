<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;
use App\Models\SeoSetting;

class V2SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Settings for Logo and Favicon
        $settings = [
            ['slug' => 'logo', 'value' => 'assets/images/logo.png'],
            ['slug' => 'footer_logo', 'value' => 'assets/images/logo.png'],
            ['slug' => 'favicon', 'value' => 'assets/images/favicon.png'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['slug' => $setting['slug']],
                ['value' => $setting['value']]
            );
        }

        // SEO Settings for different pages
        $seoSettings = [
            [
                'slug' => 'front',
                'title' => 'خيرات الأنعام - الرئيسية',
                'description' => 'الرئيسية لمتجر خيرات الأنعام المتخصص في بيع اللحوم الطازجة.',
                'keywords' => 'لحوم, طازجة, عمان, مسقط, غنم, بقر'
            ],
            [
                'slug' => 'front.store',
                'title' => 'خيرات الأنعام - المتجر',
                'description' => 'تصفح جميع منتجات اللحوم الطازجة والمبردة في متجرنا.',
                'keywords' => 'متجر, لحوم, طازجة, شراء, عمان'
            ],
            [
                'slug' => 'front.product_details',
                'title' => 'خيرات الأنعام - تفاصيل المنتج',
                'description' => 'تفاصيل منتجات اللحوم وأسعارها.',
                'keywords' => 'تفاصيل, منتج, لحوم'
            ],
            [
                'slug' => 'login',
                'title' => 'تسجيل الدخول - خيرات الأنعام',
                'description' => 'سجل الدخول لحسابك للوصول إلى طلباتك وعناوينك.',
                'keywords' => 'دخول, تسجيل, حساب'
            ],
            [
                'slug' => 'user.sign.up',
                'title' => 'إنشاء حساب جديد - خيرات الأنعام',
                'description' => 'انضم إلينا الآن وتمتع بأفضل تشكيلة لحوم.',
                'keywords' => 'تسجيل, حساب جديد'
            ],
        ];

        foreach ($seoSettings as $seo) {
            SeoSetting::updateOrCreate(
                ['slug' => $seo['slug']],
                [
                    'title' => $seo['title'],
                    'description' => $seo['description'],
                    'keywords' => $seo['keywords'],
                ]
            );
        }

        // Add FAQs
        $faqs = [
            [
                'question' => 'كيف يتم شحن اللحوم الطازجة؟',
                'answer' => 'نقوم بتغليف اللحوم الطازجة في صناديق مبردة مخصصة مع ثلج جاف لضمان وصولها إليك بأعلى درجات الجودة والبرودة.',
                'question_fr' => 'How is fresh meat shipped?',
                'answer_fr' => 'We package fresh meat in custom refrigerated boxes with dry ice to ensure it reaches you in the highest quality and temperature.',
            ],
            [
                'question' => 'ما هي مدة الصلاحية للحوم عند استلامها؟',
                'answer' => 'تمتد صلاحية اللحوم الطازجة لدينا من 3 إلى 5 أيام في الثلاجة، وتصل إلى 6 أشهر عند تجميدها فور استلامها.',
                'question_fr' => 'What is the shelf life of the meat upon delivery?',
                'answer_fr' => 'Our fresh meat has a shelf life of 3 to 5 days in the refrigerator, and up to 6 months if frozen immediately upon delivery.',
            ],
            [
                'question' => 'هل جميع اللحوم حلال؟',
                'answer' => 'نعم، نضمن لك أن جميع اللحوم المعروضة مذبوحة على الشريعة الإسلامية وموثقة بشهادات حلال رسمية.',
                'question_fr' => 'Is all the meat Halal?',
                'answer_fr' => 'Yes, we guarantee that all offered meats are slaughtered according to Islamic Sharia and are documented with official Halal certificates.',
            ],
            [
                'question' => 'من أين يتم استيراد لحوم الواغيو؟',
                'answer' => 'نستورد لحوم الواغيو الفاخرة من أفضل المزارع المعتمدة في أستراليا واليابان لضمان الجودة الفائقة والتجزع المثالي.',
                'question_fr' => 'Where is the Wagyu meat imported from?',
                'answer_fr' => 'We import premium Wagyu meat from top certified farms in Australia and Japan to ensure superior quality and perfect marbling.',
            ]
        ];

        foreach ($faqs as $faq) {
            \App\Models\Faq::updateOrCreate(
                ['question' => $faq['question']],
                [
                    'answer' => $faq['answer'],
                    'question_fr' => $faq['question_fr'],
                    'answer_fr' => $faq['answer_fr']
                ]
            );
        }

        // Add Dummy Hero Advertises
        $heroAds = [
            [
                'en_title' => 'Fresh Khairat Al-An\'aam',
                'en_subtitle' => 'Global Standards in Your Hands',
                'en_small_description' => 'We offer you the best from local and global farms, expertly cut to be the essential ingredient for your daily table and special occasions.',
                'ar_title' => 'خيرات الأنعام الطازجة',
                'ar_subtitle' => 'بمعايير عالمية بين يديك',
                'ar_small_description' => 'نقدم لك أفضل ما في المزارع المحلية والعالمية، مقطوعة بحرفية لتكون المكون الأساسي لمائدتك اليومية ومناسباتك الخاصة.',
                'link' => '#',
                'image' => 'hero-bg.jpg', // Assuming you have this image in public/assets/images
                'location' => 'hero',
                'status' => 1,
                'display_order' => 1
            ],
            [
                'en_title' => 'Premium Quality Meat',
                'en_subtitle' => 'Delivered to Your Doorstep',
                'en_small_description' => 'Experience the finest cuts of meat, carefully selected and delivered fresh to your home.',
                'ar_title' => 'لحوم عالية الجودة',
                'ar_subtitle' => 'تصلك حتى باب منزلك',
                'ar_small_description' => 'استمتع بأجود قطع اللحم، المختارة بعناية والتي تصلك طازجة إلى منزلك.',
                'link' => '#',
                'image' => 'hero-bg2.jpg',
                'location' => 'hero',
                'status' => 1,
                'display_order' => 2
            ]
        ];

        foreach ($heroAds as $ad) {
            \App\Models\Admin\Advertise::updateOrCreate(
                ['en_title' => $ad['en_title'], 'location' => 'hero'],
                $ad
            );
        }
    }
}
