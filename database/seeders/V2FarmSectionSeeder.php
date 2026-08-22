<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin\SiteContent\HomepageSection;

class V2FarmSectionSeeder extends Seeder
{
    public function run(): void
    {
        $exists = HomepageSection::where('section_key', 'newdesign_farm')->exists();

        if (!$exists) {
            HomepageSection::create([
                'section_key' => 'newdesign_farm',
                'display_order' => 10,
                'status' => true,
                'content_en' => [
                    'title' => 'From the goodness of the earth and sea to your table',
                    'lead' => 'We are committed to the highest international quality standards in selecting products. Our meat is sourced from the best farms that follow natural and sustainable breeding methods to ensure rich flavor and unparalleled quality.',
                    'items' => [
                        [
                            'title' => 'Reliable Halal Certification',
                            'desc' => 'All our products are slaughtered and prepared according to Islamic law and certified by reliable authorities.',
                            'icon' => 'fas fa-shield-alt'
                        ],
                        [
                            'title' => 'Carefully Selected Farms',
                            'desc' => 'We ensure that our sources are from selected and reliable farms that follow natural farming and breeding methods free of hormones and pesticides.',
                            'icon' => 'fas fa-map-marker-alt'
                        ],
                        [
                            'title' => 'Integrated Cold Chain',
                            'desc' => 'An advanced logistics system that maintains an ideal temperature from the moment of harvest and processing until it reaches your door.',
                            'icon' => 'fas fa-truck-fast'
                        ]
                    ]
                ],
                'content_fr' => [
                    'title' => 'من خيرات الأرض والبحر لمائدتك',
                    'lead' => 'نلتزم بأعلى معايير الجودة العالمية في اختيار المنتجات. يتم توريد لحومنا من أفضل المزارع التي تتبع أساليب التربية الطبيعية والمستدامة لضمان نكهة غنية وجودة لا تضاهى.',
                    'items' => [
                        [
                            'title' => 'شهادة حلال موثوقة',
                            'desc' => 'جميع منتجاتنا مذبوحة ومجهزة وفقاً للشريعة الإسلامية ومعتمدة من جهات موثوقة.',
                            'icon' => 'fas fa-shield-alt'
                        ],
                        [
                            'title' => 'مزارع مختارة بعناية',
                            'desc' => 'نضمن أن مصادرنا من مزارع مختارة وموثوقة تتبع أساليب الزراعة والتربية الطبيعية الخالية من الهرمونات والمبيدات.',
                            'icon' => 'fas fa-map-marker-alt'
                        ],
                        [
                            'title' => 'سلسلة تبريد متكاملة',
                            'desc' => 'نظام لوجستي متطور يحافظ على درجة حرارة مثالية من لحظة الحصاد والتجهيز حتى وصولها لباب منزلك.',
                            'icon' => 'fas fa-truck-fast'
                        ]
                    ]
                ]
            ]);
        }
    }
}
