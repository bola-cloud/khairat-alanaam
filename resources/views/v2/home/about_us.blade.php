@extends('v2.layouts.app')

@php
    $lang = app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? 'ar' : 'en';
@endphp

@section('title', $title ?? __('v2_layout.nav_about'))

@section('content')
<style>
    .about-hero {
        position: relative;
        background: url('{{ asset("v2/img/about-hero-bg.jpg") }}') center center / cover no-repeat;
        padding: 160px 20px;
        text-align: center;
        color: #fff;
        margin-bottom: 60px;
    }
    .about-hero::before {
        content: '';
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0, 0, 0, 0.6);
    }
    .about-hero-content {
        position: relative;
        z-index: 1;
        max-width: 800px;
        margin: 0 auto;
    }
    .about-hero-content h1 {
        font-size: 42px;
        font-weight: 800;
        margin-bottom: 20px;
        line-height: 1.3;
    }
    .about-hero-content p {
        font-size: 18px;
        line-height: 1.6;
        opacity: 0.9;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 80px;
    }
    .feature-box {
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 30px 20px;
        text-align: center;
        transition: 0.3s;
    }
    .feature-box:hover {
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        border-color: #eee;
    }
    .feature-icon {
        width: 60px;
        height: 60px;
        background: rgba(227, 38, 54, 0.05);
        color: var(--primary-color);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        font-size: 24px;
    }
    .feature-box h3 {
        font-size: 16px;
        font-weight: 800;
        color: #333;
        margin: 0 0 8px;
    }
    .feature-box p {
        font-size: 13px;
        color: var(--text-light);
        margin: 0;
    }

    .split-section {
        display: flex;
        align-items: center;
        gap: 60px;
        margin-bottom: 80px;
        flex-direction: {{ $lang == 'ar' || $lang == 'fr' ? 'row-reverse' : 'row' }};
    }
    .split-image {
        flex: 1;
        position: relative;
    }
    .split-image > img:not(.split-image-inset) {
        width: 100%;
        height: 500px;
        object-fit: cover;
        border-radius: 16px;
        box-shadow: 0 15px 30px rgba(0,0,0,0.1);
    }
    img.split-image-inset {
        position: absolute;
        bottom: -20px;
        {{ $lang == 'ar' || $lang == 'fr' ? 'right: -20px;' : 'left: -20px;' }}
        width: 50%;
        aspect-ratio: 1/1;
        object-fit: cover;
        border-radius: 12px;
        border: 6px solid #fff;
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .split-content {
        flex: 1;
    }
    .split-content h2 {
        font-size: 32px;
        font-weight: 800;
        color: #333;
        margin: 0 0 20px;
        line-height: 1.3;
    }
    .split-content > p {
        font-size: 16px;
        color: var(--text-light);
        margin-bottom: 30px;
        line-height: 1.6;
    }

    .list-item {
        display: flex;
        align-items: flex-start;
        gap: 15px;
        margin-bottom: 25px;
    }
    .list-icon {
        width: 40px;
        height: 40px;
        background: rgba(227, 38, 54, 0.05);
        color: var(--primary-color);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }
    .list-content h4 {
        margin: 0 0 5px;
        font-size: 16px;
        font-weight: 800;
        color: #333;
    }
    .list-content p {
        margin: 0;
        font-size: 14px;
        color: var(--text-light);
        line-height: 1.5;
    }

    .cta-section {
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 50px 20px;
        text-align: center;
        max-width: 800px;
        margin: 0 auto 80px;
    }
    .cta-badge {
        display: inline-block;
        background: rgba(227, 38, 54, 0.1);
        color: var(--primary-color);
        padding: 6px 15px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        margin-bottom: 15px;
    }
    .cta-section h2 {
        font-size: 32px;
        font-weight: 800;
        color: #333;
        margin: 0 0 20px;
    }
    .cta-section p {
        font-size: 16px;
        color: var(--text-light);
        margin: 0 auto 30px;
        line-height: 1.6;
        max-width: 600px;
    }
    .cta-buttons {
        display: flex;
        justify-content: center;
        gap: 15px;
    }

    @media (max-width: 992px) {
        .features-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .split-section {
            flex-direction: column;
        }
        .split-image-inset {
            display: none;
        }
    }
    @media (max-width: 576px) {
        .features-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        .feature-box {
            padding: 20px 10px;
        }
        .feature-box h3 {
            font-size: 13px;
            margin-bottom: 5px;
        }
        .feature-box p {
            font-size: 10px;
        }
        .feature-icon {
            width: 40px;
            height: 40px;
            font-size: 18px;
            margin-bottom: 10px;
        }
        
        .about-hero-content h1 {
            font-size: 28px;
        }
        .cta-buttons {
            flex-direction: column;
        }
    }
</style>

<!-- Hero Section -->
@php
    $heroBanner = isset($settings['about_hero_image']) && $settings['about_hero_image'] != '' 
        ? asset(aboutUsPage() . $settings['about_hero_image']) 
        : asset('v2/img/about-banner.jpg');
@endphp
<div class="about-hero" style="background-image: url('{{ $heroBanner }}');">
    <div class="about-hero-content">
        <h1>{!! $settings['about_hero_title_'.$lang] ?? 'متأصلة في الجودة، مسلمة بالشغف' !!}</h1>
        <p>{!! $settings['about_hero_desc_'.$lang] ?? 'تأسست خيرات الأنعام في عام 2008. وتعد واحدة من أبرز الشركات في عمان في توريد ومعالجة وتسويق اللحوم الحمراء عالية الجودة.' !!}</p>
    </div>
</div>

<div class="v2-container">
    
    <!-- Features Grid -->
    <div class="features-grid">
        <div class="feature-box">
            <div class="feature-icon"><i class="far fa-clock"></i></div>
            <h3>{{ $settings['about_feature1_title_'.$lang] ?? 'توصيل خلال ساعتين' }}</h3>
            <p>{{ $settings['about_feature1_desc_'.$lang] ?? 'توصيل طازج خلال ساعتين' }}</p>
        </div>
        <div class="feature-box">
            <div class="feature-icon"><i class="fas fa-award"></i></div>
            <h3>{{ $settings['about_feature2_title_'.$lang] ?? 'معتمد حلال ١٠٠٪' }}</h3>
            <p>{{ $settings['about_feature2_desc_'.$lang] ?? 'معتمد وقابل للتتبع' }}</p>
        </div>
        <div class="feature-box">
            <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
            <h3>{{ $settings['about_feature3_title_'.$lang] ?? 'جودة مضمونة' }}</h3>
            <p>{{ $settings['about_feature3_desc_'.$lang] ?? 'إرجاع واستبدال مجاني' }}</p>
        </div>
        <div class="feature-box">
            <div class="feature-icon"><i class="fas fa-truck"></i></div>
            <h3>{{ $settings['about_feature4_title_'.$lang] ?? 'توصيل طازج' }}</h3>
            <p>{{ $settings['about_feature4_desc_'.$lang] ?? 'نتحكم بدرجة حرارته' }}</p>
        </div>
    </div>

    <!-- Split Section -->
    <div class="split-section">
        <div class="split-image">
            @php
                $midImg1 = isset($settings['about_middle_image_1']) && $settings['about_middle_image_1'] != '' 
                    ? asset(aboutUsPage() . $settings['about_middle_image_1']) 
                    : asset('assets/images/placeholder.png');
                $midImg2 = isset($settings['about_middle_image_2']) && $settings['about_middle_image_2'] != '' 
                    ? asset(aboutUsPage() . $settings['about_middle_image_2']) 
                    : asset('assets/images/placeholder.png');
            @endphp
            <img src="{{ $midImg1 }}" alt="Farms" onerror="this.src='{{ asset('assets/images/placeholder.png') }}'">
            <img src="{{ $midImg2 }}" class="split-image-inset" alt="Meat" onerror="this.src='{{ asset('assets/images/placeholder.png') }}'">
        </div>
        <div class="split-content">
            <h2>{!! $settings['about_section_title_'.$lang] ?? 'من خيرات الأرض والبحر لمائدتك' !!}</h2>
            <p>{!! $settings['about_section_desc_'.$lang] ?? 'نلتزم بأعلى معايير الجودة العالمية في اختيار المنتجات. يتم توريد لحومنا من أفضل المزارع التي تتبع أساليب التربية الطبيعية والمستدامة لضمان نكهة غنية وجودة لا تضاهى.' !!}</p>
            
            <div class="list-item">
                <div class="list-icon"><i class="fas fa-check-circle"></i></div>
                <div class="list-content">
                    <h4>{{ $settings['about_list1_title_'.$lang] ?? 'شهادة حلال موثوقة' }}</h4>
                    <p>{{ $settings['about_list1_desc_'.$lang] ?? 'جميع منتجاتنا مذبوحة ومجهزة وفقاً للشريعة الإسلامية ومعتمدة من جهات موثوقة.' }}</p>
                </div>
            </div>
            
            <div class="list-item">
                <div class="list-icon"><i class="fas fa-map-marker-alt"></i></div>
                <div class="list-content">
                    <h4>{{ $settings['about_list2_title_'.$lang] ?? 'مزارع مختارة بعناية' }}</h4>
                    <p>{{ $settings['about_list2_desc_'.$lang] ?? 'نضمن أن مصادرنا من مزارع مختارة وموثوقة تتبع أساليب الزراعة والتربية الطبيعية الخالية من الهرمونات والمبيدات.' }}</p>
                </div>
            </div>
            
            <div class="list-item">
                <div class="list-icon"><i class="fas fa-snowflake"></i></div>
                <div class="list-content">
                    <h4>{{ $settings['about_list3_title_'.$lang] ?? 'سلسلة تبريد متكاملة' }}</h4>
                    <p>{{ $settings['about_list3_desc_'.$lang] ?? 'نظام لوجستي متطور يحافظ على درجة حرارة مثالية من لحظة الحصاد والتجهيز حتى وصولها لباب منزلك.' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="cta-section">
        <span class="cta-badge">{{ $settings['about_cta_subtitle_'.$lang] ?? 'سيد الجزارة' }}</span>
        <h2>{!! $settings['about_cta_title_'.$lang] ?? 'اختبر الجودة الفاخرة' !!}</h2>
        <p>{!! $settings['about_cta_desc_'.$lang] ?? 'ذق الفرق الذي يحدثه التراث والخبرة والمعايير التي لا تساوم. اكتشف السبب في أن العملاء المتميزين في جميع أنحاء عمان يثقون بمنتجاتنا.' !!}</p>
        
        <div class="cta-buttons">
            <a href="{{ route('front.store') }}" class="btn-primary" style="padding: 14px 30px; font-weight: 700; border-radius: 8px;">@lang('v2_home.shop_premium', ['default' => 'تسوق مختاراتنا الفاخرة'])</a>
            <a href="{{ route('front.store') }}?sale=1" class="btn-outline" style="padding: 14px 30px; font-weight: 700; border-radius: 8px; background: #f9f9f9; color: var(--text-color); border: 1px solid #eee; text-decoration: none;">@lang('v2_home.view_offers', ['default' => 'عرض العروض'])</a>
        </div>
    </div>
</div>

@endsection
