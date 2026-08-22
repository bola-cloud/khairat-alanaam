<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class V2PagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $settings = [
            // Contact Us
            'contact_map_iframe' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d11624.966953932402!2d58.4063!3d23.5859!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3e91f173c38b29f7%3A0x6b772d1f0ec843f8!2sMuscat%2C%20Oman!5e0!3m2!1sen!2s!4v1680000000000!5m2!1sen!2s" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>',
            'contact_address_ar' => 'مسقط، سلطنة عمان',
            'contact_address_en' => 'Muscat, Sultanate of Oman',
            'contact_phone' => '+968 24 123 456',
            'contact_email' => 'info@khairat-alan3am.com',
            
            // About Us - Arabic
            'about_hero_title_ar' => 'متأصلة في الجودة، مسلمة بالشغف',
            'about_hero_desc_ar' => 'تأسست خيرات الأنعام في عام 2008. وتعد واحدة من أبرز الشركات في عمان في توريد ومعالجة وتسويق اللحوم الحمراء عالية الجودة.',
            'about_feature1_title_ar' => 'توصيل خلال ساعتين',
            'about_feature1_desc_ar' => 'توصيل طازج خلال ساعتين',
            'about_feature2_title_ar' => 'معتمد حلال ١٠٠٪',
            'about_feature2_desc_ar' => 'معتمد وقابل للتتبع',
            'about_feature3_title_ar' => 'جودة مضمونة',
            'about_feature3_desc_ar' => 'إرجاع واستبدال مجاني',
            'about_feature4_title_ar' => 'توصيل طازج',
            'about_feature4_desc_ar' => 'نتحكم بدرجة حرارته',
            'about_section_title_ar' => 'من خيرات الأرض والبحر لمائدتك',
            'about_section_desc_ar' => 'نلتزم بأعلى معايير الجودة العالمية في اختيار المنتجات. يتم توريد لحومنا من أفضل المزارع التي تتبع أساليب التربية الطبيعية والمستدامة لضمان نكهة غنية وجودة لا تضاهى.',
            'about_list1_title_ar' => 'شهادة حلال موثوقة',
            'about_list1_desc_ar' => 'جميع منتجاتنا مذبوحة ومجهزة وفقاً للشريعة الإسلامية ومعتمدة من جهات موثوقة.',
            'about_list2_title_ar' => 'مزارع مختارة بعناية',
            'about_list2_desc_ar' => 'نضمن أن مصادرنا من مزارع مختارة وموثوقة تتبع أساليب الزراعة والتربية الطبيعية الخالية من الهرمونات والمبيدات.',
            'about_list3_title_ar' => 'سلسلة تبريد متكاملة',
            'about_list3_desc_ar' => 'نظام لوجستي متطور يحافظ على درجة حرارة مثالية من لحظة الحصاد والتجهيز حتى وصولها لباب منزلك.',
            'about_cta_subtitle_ar' => 'سيد الجزارة',
            'about_cta_title_ar' => 'اختبر الجودة الفاخرة',
            'about_cta_desc_ar' => 'ذق الفرق الذي يحدثه التراث والخبرة والمعايير التي لا تساوم. اكتشف السبب في أن العملاء المتميزين في جميع أنحاء عمان يثقون بمنتجاتنا.',
            
            // About Us - English
            'about_hero_title_en' => 'Rooted in quality, delivered with passion',
            'about_hero_desc_en' => 'Founded in 2008, Khairat Al-An\'aam is one of the leading companies in Oman for supplying, processing, and marketing high-quality red meat.',
            'about_feature1_title_en' => 'Delivery within 2 hours',
            'about_feature1_desc_en' => 'Fresh delivery in 2 hours',
            'about_feature2_title_en' => '100% Halal Certified',
            'about_feature2_desc_en' => 'Certified and traceable',
            'about_feature3_title_en' => 'Guaranteed Quality',
            'about_feature3_desc_en' => 'Free returns and replacements',
            'about_feature4_title_en' => 'Fresh Delivery',
            'about_feature4_desc_en' => 'Temperature controlled',
            'about_section_title_en' => 'From the bounty of earth and sea to your table',
            'about_section_desc_en' => 'We adhere to the highest global quality standards in product selection. Our meat is sourced from top farms that practice natural and sustainable breeding for rich flavor and unmatched quality.',
            'about_list1_title_en' => 'Trusted Halal Certification',
            'about_list1_desc_en' => 'All our products are slaughtered and prepared according to Islamic law and certified by trusted authorities.',
            'about_list2_title_en' => 'Carefully Selected Farms',
            'about_list2_desc_en' => 'We ensure our sources are selected and trusted farms that follow natural breeding and farming practices, free from hormones and pesticides.',
            'about_list3_title_en' => 'Integrated Cold Chain',
            'about_list3_desc_en' => 'An advanced logistics system maintains an optimal temperature from harvest and preparation until it reaches your door.',
            'about_cta_subtitle_en' => 'Master Butcher',
            'about_cta_title_en' => 'Experience Premium Quality',
            'about_cta_desc_en' => 'Taste the difference that heritage, expertise, and uncompromising standards make. Discover why discerning customers across Oman trust us.',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['slug' => $key],
                ['value' => $value]
            );
        }
    }
}
