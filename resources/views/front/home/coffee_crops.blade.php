@extends('front.layouts.new_design_layout')

@section('content')

@php
    $isRtl = app()->getLocale() != 'en';
    $dir = $isRtl ? 'rtl' : 'ltr';

    $parseProductData = function($product, $isRtl) {
        $desc = $product->localized_description;
        $origin = '';
        $roast = '';
        $type = '';
        $notes = '';
        $mainDesc = '';

        if ($isRtl) {
            // Arabic parsing
            preg_match('/المصدر والمنشأ:\s*(.*?)(?=(قصة التحميص:|النوع:|الإيحاءات:|الوصف:|$))/u', $desc, $m);
            $origin = isset($m[1]) ? trim($m[1]) : 'مرتفعات غنية بالتربة البركانية';
            preg_match('/قصة التحميص:\s*(.*?)(?=(النوع:|الإيحاءات:|الوصف:|$))/u', $desc, $m);
            $roast = isset($m[1]) ? trim($m[1]) : 'تحميص متوسط متوازن';
            preg_match('/النوع:\s*(.*?)(?=(الإيحاءات:|الوصف:|$))/u', $desc, $m);
            $type = isset($m[1]) ? trim($m[1]) : 'أرابيكا ١٠٠٪';
            preg_match('/الإيحاءات:\s*(.*?)(?=(الوصف:|$))/u', $desc, $m);
            $notes = isset($m[1]) ? trim($m[1]) : 'شوكولاتة، مكسرات، كاكاو';
            preg_match('/الوصف:\s*(.*)/u', $desc, $m);
            $mainDesc = isset($m[1]) ? trim($m[1]) : $desc;
        } else {
            // English parsing
            preg_match('/Source\/Origin:\s*(.*?)(?=(Roast story:|Type:|Notes:|Description:|$))/i', $desc, $m);
            $origin = isset($m[1]) ? trim($m[1]) : 'Highlands volcanic soil';
            preg_match('/Roast story:\s*(.*?)(?=(Type:|Notes:|Description:|$))/i', $desc, $m);
            $roast = isset($m[1]) ? trim($m[1]) : 'Medium roast';
            preg_match('/Type:\s*(.*?)(?=(Notes:|Description:|$))/i', $desc, $m);
            $type = isset($m[1]) ? trim($m[1]) : 'Arabica';
            preg_match('/Notes:\s*(.*?)(?=(Description:|$))/i', $desc, $m);
            $notes = isset($m[1]) ? trim($m[1]) : 'Chocolate, hazelnut, cocoa';
            preg_match('/Description:\s*(.*)/i', $desc, $m);
            $mainDesc = isset($m[1]) ? trim($m[1]) : $desc;
        }

        return [
            'origin' => $origin ?: ($isRtl ? 'مرتفعات غنية بالتربة البركانية' : 'Highlands volcanic soil'),
            'roast' => $roast ?: ($isRtl ? 'تحميص متوسط متوازن' : 'Medium roast'),
            'type' => $type ?: ($isRtl ? 'أرابيكا ١٠٠٪' : 'Arabica'),
            'notes' => $notes ?: ($isRtl ? 'شوكولاتة، مكسرات، كاكاو' : 'Chocolate, hazelnut, cocoa'),
            'mainDesc' => $mainDesc ?: $product->localized_about
        ];
    };

    $getCountryKey = function($parsedData) {
        $origin = strtolower($parsedData['origin']);
        if (str_contains($origin, 'brazil') || str_contains($origin, 'برازيل')) {
            return 'brazil';
        }
        if (str_contains($origin, 'ethiopia') || str_contains($origin, 'إثيوبيا') || str_contains($origin, 'اثيوبيا')) {
            return 'ethiopia';
        }
        if (str_contains($origin, 'colombia') || str_contains($origin, 'كولومبيا')) {
            return 'colombia';
        }
        if (str_contains($origin, 'yemen') || str_contains($origin, 'اليمن')) {
            return 'yemen';
        }
        if (str_contains($origin, 'costa') || str_contains($origin, 'كوستاريكا')) {
            return 'costarica';
        }
        return 'other';
    };

    $getCategoryKey = function($parsedData, $product) {
        $notes = strtolower($parsedData['notes']);
        $desc = strtolower($product->localized_description);
        $about = strtolower($product->localized_about);
        
        if (str_contains($desc, 'espresso') || str_contains($desc, 'إسبريسو') || str_contains($about, 'espresso')) {
            return 'espresso';
        }
        if (str_contains($desc, 'drip') || str_contains($desc, 'مقطرة') || str_contains($about, 'drip')) {
            return 'drip';
        }
        if (str_contains($desc, 'whole bean') || str_contains($desc, 'حبوب كاملة')) {
            return 'whole-bean';
        }
        return 'other';
    };
@endphp

<style>
    /* Cairo font integration for the page container */
    .coffee-page {
        font-family: 'Cairo', sans-serif;
    }

    /* Slider track CSS centering with CSS variables to ensure zero layout-shift or page-load calculation issues */
    .hero-slider-track {
        display: flex;
        padding-left: var(--side-padding);
        padding-right: var(--side-padding);
        gap: var(--slide-gap);
        transform: translateX(calc(-1 * var(--current-index) * (var(--slide-width) + var(--slide-gap))));
    }

    .hero-slider-track.transition-enabled {
        transition: transform 500ms cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Define responsive slide widths in viewport width (vw) units to make translation math 100% viewport-relative */
    .hero-slide {
        width: var(--slide-width);
        flex-shrink: 0;
        position: relative;
        border-radius: 24px;
        overflow: hidden;
        height: 200px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        border: 1px solid #f3f4f6;
        transition: all 500ms ease-out;
    }

    @media (min-width: 640px) {
        .hero-slide {
            border-radius: 32px;
            height: 300px;
        }
    }

    @media (min-width: 1024px) {
        .hero-slide {
            height: 360px;
        }
    }

    :root {
        --slide-width: 84vw;
        --slide-gap: 16px;
        --side-padding: 8vw; /* (100vw - 84vw) / 2 = 8vw padding on left and right to perfectly center active slide */
        --current-index: 1;  /* Initialized to 1 because index 0 is the cloned slide */
    }

    @media (min-width: 1024px) {
        :root {
            --slide-width: 80vw;
            --slide-gap: 24px;
            --side-padding: 10vw; /* (100vw - 80vw) / 2 = 10vw padding on left and right to perfectly center active slide */
        }
    }
</style>

<div class="coffee-page bg-white text-[#1A4231] overflow-hidden" dir="{{ $dir }}">

    <!-- White Spacer Gap between Navbar and Hero Section -->
    <div class="h-10 bg-white"></div>

    <!-- Hero Banner Slider Section (Peek stage padding carousel matching Figma layout) -->
    <section class="relative py-6 lg:py-10 w-full bg-white overflow-hidden">
        
        <!-- Slider Container with forced LTR direction to ensure consistent cross-browser offset calculations -->
        <div id="hero-slider-container" class="relative w-full overflow-hidden select-none" dir="ltr">
            
            <!-- Slides Track (with transition-enabled class active by default) -->
            <div id="hero-slider-track" class="hero-slider-track transition-enabled">
                @foreach($advertises as $index => $ad)
                    @php
                        $img = $ad->image;
                        $imgPublic = file_exists(public_path($img)) ? asset($img) : (isset($img) ? asset(PromotionImage() . $img) : '');
                    @endphp
                    @if($imgPublic)
                        <div class="hero-slide">
                            @if($ad->link)
                                <a href="{{ $ad->link }}" target="_blank" class="block w-full h-full">
                            @endif
                            <img src="{{ $imgPublic }}" class="absolute inset-0 w-full h-full object-cover" alt="{{ $isRtl ? $ad->ar_title : $ad->en_title }}">
                            @if($ad->link)
                                </a>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
            
            <!-- Navigation Arrows -->
            <button id="hero-slide-prev" class="absolute top-1/2 -translate-y-1/2 right-4 sm:right-8 lg:right-16 z-20 w-12 h-12 rounded-full bg-white/30 hover:bg-white/50 active:scale-95 text-[#1A4231] flex items-center justify-center backdrop-blur-md transition-all shadow-lg border border-white/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
            <button id="hero-slide-next" class="absolute top-1/2 -translate-y-1/2 left-4 sm:left-8 lg:left-16 z-20 w-12 h-12 rounded-full bg-white/30 hover:bg-white/50 active:scale-95 text-[#1A4231] flex items-center justify-center backdrop-blur-md transition-all shadow-lg border border-white/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            
        </div>
    </section>

    <!-- Main Content Section: Available Crops (المحاصيل المتاحة) -->
    <section class="py-12 lg:py-16 bg-white relative">
        <div class="container mx-auto px-4 lg:px-8 relative z-10">
            
            <!-- Section Header -->
            <div class="max-w-3xl mx-auto text-center mb-10 flex flex-col items-center justify-center">
                <h2 class="text-3xl lg:text-4xl font-black text-[#1A4231] mb-3">
                    {{ __('new_design.coffee_crops.section_title') }}
                </h2>
                <p class="text-gray-600 text-sm lg:text-base font-semibold leading-relaxed max-w-2xl">
                    {{ __('new_design.coffee_crops.section_subtitle') }}
                </p>
            </div>

            <!-- Filters Area -->
            <div class="flex flex-col items-center justify-center mb-12">
                <!-- Dropdown Filter Button -->
                <button id="open-filter-btn" class="border border-gray-300 px-6 py-2.5 rounded-full text-sm font-bold text-gray-700 hover:bg-gray-50 flex items-center justify-center gap-2 mb-6 shadow-sm transition-all active:scale-95">
                    <!-- Filter SVG -->
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span>{{ __('new_design.coffee_crops.btn_filter') }}</span>
                </button>

                <!-- Category Pills (Horizontal Scrollable / Centered Row) -->
                <div class="flex flex-wrap items-center justify-center gap-3">
                    <button data-category-filter="all" class="category-filter-pill bg-[#1A4231] text-white px-6 py-2 rounded-full text-sm font-bold shadow-md hover:opacity-95 transition-all">
                        {{ __('new_design.coffee_crops.filter_all') ?? 'الكل' }}
                    </button>
                    @foreach($subcategories as $sub)
                    <button data-category-filter="{{ $sub->id }}" class="category-filter-pill border border-gray-200 text-gray-600 hover:bg-gray-50 px-6 py-2 rounded-full text-sm font-bold transition-all">
                        {{ $sub->localized_name }}
                    </button>
                    @endforeach
                </div>
            </div>

            <!-- Products Grid/List Container -->
            <div id="crops-products-container" class="grid grid-cols-1 lg:grid-cols-2 gap-8 w-full mt-12 max-w-7xl mx-auto">
                @foreach($products as $index => $product)
                @php
                    $parsed = $parseProductData($product, $isRtl);
                    $cKey = $getCountryKey($parsed);
                    
                    // Alternating design layout: even index image on left (lg:flex-row), odd index image on right (lg:flex-row-reverse)
                    $layoutClass = ($index % 2 == 0) ? 'flex flex-col lg:flex-row' : 'flex flex-col lg:flex-row-reverse';
                @endphp
                <div class="crop-product-card bg-white border border-gray-150 rounded-[32px] shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden {{ $layoutClass }}"
                     data-country="{{ $cKey }}"
                     data-category="{{ $product->subcategory_id }}"
                     data-name="{{ strtolower($product->localized_name) }}"
                     data-rating="{{ ($product->id % 2 == 0) ? 5 : 4 }}"
                     data-sizes="{{ implode(',', $product->sizes->pluck('Size')->map('strtolower')->toArray()) }}">
                    <!-- Image -->
                    <a href="{{ route('single.product.new', $product->en_Product_Slug) }}" class="w-full lg:w-2/5 shrink-0 h-[240px] lg:h-auto min-h-[320px] relative block overflow-hidden group">
                        @php
                            $imgSrc = resolve_product_image($product->Primary_Image);
                        @endphp
                        <img src="{{ $imgSrc }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="{{ $product->localized_name }}" onerror="this.onerror=null;this.src='{{ asset('assets/elketar/coffee.png') }}';">
                    </a>
                    
                    <!-- Content Details -->
                    <div class="p-6 lg:p-8 flex flex-col justify-between flex-1 gap-6 text-start">
                        <div class="flex flex-col gap-2">
                            <h3 class="text-xl lg:text-2xl font-black text-[#1A4231]">
                                <a href="{{ route('single.product.new', $product->en_Product_Slug) }}" class="hover:underline">
                                    {{ $product->localized_name }}
                                </a>
                            </h3>
                            <span class="text-xs lg:text-sm font-semibold text-gray-500">
                                {{ $product->localized_about }}
                            </span>
                        </div>

                        <!-- Attributes List -->
                        <ul class="space-y-3.5 text-xs lg:text-sm leading-relaxed">
                            <li class="flex items-start gap-3">
                                <span class="w-5 h-5 rounded-full bg-[#1A4231]/5 flex items-center justify-center shrink-0 border border-[#1A4231]/10 mt-0.5">
                                    <svg class="w-3.5 h-3.5 text-[#1A4231]" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 002 2h2m4-3.5a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </span>
                                <div>
                                    <span class="font-bold text-[#1A4231]">{{ __('new_design.coffee_crops.attr_origin_lbl') }}:</span>
                                    <span class="text-[#6B7280] font-semibold">{{ $parsed['origin'] }}</span>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="w-5 h-5 rounded-full bg-[#1A4231]/5 flex items-center justify-center shrink-0 border border-[#1A4231]/10 mt-0.5">
                                    <svg class="w-3.5 h-3.5 text-[#1A4231]" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/></svg>
                                </span>
                                <div>
                                    <span class="font-bold text-[#1A4231]">{{ __('new_design.coffee_crops.attr_roast_lbl') }}:</span>
                                    <span class="text-[#6B7280] font-semibold">{{ $parsed['roast'] }}</span>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="w-5 h-5 rounded-full bg-[#1A4231]/5 flex items-center justify-center shrink-0 border border-[#1A4231]/10 mt-0.5">
                                    <svg class="w-3.5 h-3.5 text-[#1A4231]" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                </span>
                                <div>
                                    <span class="font-bold text-[#1A4231]">{{ __('new_design.coffee_crops.attr_type_lbl') }}:</span>
                                    <span class="text-[#6B7280] font-semibold">{{ $parsed['type'] }}</span>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="w-5 h-5 rounded-full bg-[#1A4231]/5 flex items-center justify-center shrink-0 border border-[#1A4231]/10 mt-0.5">
                                    <svg class="w-3.5 h-3.5 text-[#1A4231]" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                                </span>
                                <div>
                                    <span class="font-bold text-[#1A4231]">{{ __('new_design.coffee_crops.attr_notes_lbl') }}:</span>
                                    <span class="text-[#6B7280] font-semibold">{{ $parsed['notes'] }}</span>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="w-5 h-5 rounded-full bg-[#1A4231]/5 flex items-center justify-center shrink-0 border border-[#1A4231]/10 mt-0.5">
                                    <svg class="w-3.5 h-3.5 text-[#1A4231]" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                </span>
                                <div>
                                    <span class="font-bold text-[#1A4231]">{{ __('new_design.coffee_crops.attr_desc_lbl') }}:</span>
                                    <span class="text-[#6B7280] font-semibold">{{ $parsed['mainDesc'] }}</span>
                                </div>
                            </li>
                            <li class="flex items-center gap-3">
                                <span class="w-5 h-5 rounded-full bg-[#1A4231]/5 flex items-center justify-center shrink-0 border border-[#1A4231]/10">
                                    <svg class="w-3.5 h-3.5 text-[#1A4231]" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M12 16v1M3 12a9 9 0 1118 0 9 9 0 01-18 0z"/></svg>
                                </span>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-[#1A4231]">{{ __('new_design.coffee_crops.price_exclusive') }}:</span>
                                    <span class="text-xl font-extrabold text-[#1A4231]">{{ floatval($product->Price) }} {{ __('new_design.coffee_crops.currency') }}</span>
                                    @if($product->Discount > 0)
                                    <span class="text-sm font-semibold text-gray-400 line-through">{{ floatval($product->Discount_Price) }} {{ __('new_design.coffee_crops.currency') }}</span>
                                    @endif
                                </div>
                            </li>
                        </ul>

                        <!-- Card Action Buttons -->
                        <div class="flex items-center gap-3 w-full">
                            <button onclick="addToCart({{ $product->id }}, {{ $product->Discount > 0 ? ($product->Price - ($product->Price * $product->Discount / 100)) : $product->Price }})" class="flex-1 py-3 px-4 rounded-[14px] text-sm font-bold transition-all shadow-md text-white bg-[#1A4231] flex items-center justify-center gap-2 hover:bg-[#387C5F]">
                                <span>{{ __('new_design.coffee_crops.btn_buy') }}</span>
                                <svg class="w-4 h-4 transform {{ $isRtl ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                            </button>
                            <button onclick="openRatingModal({{ $product->id }})" class="flex-1 py-3 px-4 rounded-[14px] text-sm font-bold transition-all border border-gray-200 text-gray-700 bg-white flex items-center justify-center gap-2 hover:bg-gray-50">
                                <span>{{ __('new_design.coffee_crops.btn_rate') }}</span>
                                <svg class="w-4 h-4 transform {{ $isRtl ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach

                
            </div>
        </div>
    </section>

    <!-- White spacer before footer -->
    <div class="w-full h-16 lg:h-24 bg-white"></div>

</div>

<!-- Filter Modal (Premium backdrop and sliding transitions) -->
<div id="filter-modal" class="fixed inset-0 z-50 flex items-center justify-center opacity-0 pointer-events-none transition-all duration-300">
    <!-- Backdrop with Blur -->
    <div id="filter-modal-backdrop" class="absolute inset-0 bg-[#1A4231]/40 backdrop-blur-md"></div>
    
    <!-- Modal Card -->
    <div class="relative bg-white w-[92%] max-w-lg rounded-[32px] shadow-2xl p-6 lg:p-8 z-10 flex flex-col gap-6 max-h-[85vh] overflow-y-auto transform scale-95 translate-y-4 transition-all duration-300" dir="{{ $dir }}">
        
        <!-- Header (LTR styled for search title & close button) -->
        <div class="flex items-center justify-between border-b border-gray-100 pb-3" dir="ltr">
            <h3 class="text-lg lg:text-xl font-bold text-gray-800">
                {{ __('new_design.coffee_crops.modal_search_title') }}
            </h3>
            <button id="filter-modal-close" class="text-gray-400 hover:text-gray-600 transition-colors p-2 text-2xl font-bold leading-none">&times;</button>
        </div>
        
        <!-- Search Input Area -->
        <div class="flex flex-col gap-2 {{ $isRtl ? 'text-right' : 'text-left' }}">
            <input type="text" id="filter-search-input" placeholder="{{ __('new_design.coffee_crops.modal_search_placeholder') }}" class="w-full px-5 py-3.5 bg-gray-50 border border-gray-150 rounded-[16px] focus:outline-none focus:border-[#1A4231] focus:bg-white text-sm text-gray-800 placeholder-gray-400 transition-all font-semibold">
        </div>
        
        <!-- Country Filter -->
        <div class="flex flex-col gap-3 {{ $isRtl ? 'text-right' : 'text-left' }}">
            <span class="text-sm lg:text-base font-extrabold text-gray-800">{{ __('new_design.coffee_crops.modal_crop_country') }}</span>
            <div class="flex flex-wrap gap-2.5 {{ $isRtl ? 'justify-start flex-row-reverse' : 'justify-start' }}">
                <button class="filter-country-btn border border-gray-200 rounded-full px-5 py-2.5 text-xs lg:text-sm font-bold text-gray-600 bg-white hover:bg-gray-50 hover:border-gray-300 transition-all active:scale-95" data-country="brazil">{{ __('new_design.coffee_crops.country_brazil') ?? 'البرازيل' }}</button>
                <button class="filter-country-btn border border-gray-200 rounded-full px-5 py-2.5 text-xs lg:text-sm font-bold text-gray-600 bg-white hover:bg-gray-50 hover:border-gray-300 transition-all active:scale-95" data-country="ethiopia">{{ __('new_design.coffee_crops.country_ethiopia') ?? 'إثيوبيا' }}</button>
                <button class="filter-country-btn border border-gray-200 rounded-full px-5 py-2.5 text-xs lg:text-sm font-bold text-gray-600 bg-white hover:bg-gray-50 hover:border-gray-300 transition-all active:scale-95" data-country="colombia">{{ __('new_design.coffee_crops.country_colombia') ?? 'كولومبيا' }}</button>
                <button class="filter-country-btn border border-gray-200 rounded-full px-5 py-2.5 text-xs lg:text-sm font-bold text-gray-600 bg-white hover:bg-gray-50 hover:border-gray-300 transition-all active:scale-95" data-country="yemen">{{ __('new_design.coffee_crops.country_yemen') ?? 'اليمن' }}</button>
                <button class="filter-country-btn border border-gray-200 rounded-full px-5 py-2.5 text-xs lg:text-sm font-bold text-gray-600 bg-white hover:bg-gray-50 hover:border-gray-300 transition-all active:scale-95" data-country="costarica">{{ __('new_design.coffee_crops.country_costa_rica') ?? 'كوستاريكا' }}</button>
            </div>
        </div>
        
        <!-- Category Filter -->
        <div class="flex flex-col gap-3 {{ $isRtl ? 'text-right' : 'text-left' }}">
            <span class="text-sm lg:text-base font-extrabold text-gray-800">{{ __('new_design.coffee_crops.modal_crop_category') }}</span>
            <div class="flex flex-wrap gap-2.5 {{ $isRtl ? 'justify-start flex-row-reverse' : 'justify-start' }}">
                @foreach($subcategories as $sub)
                <button class="filter-category-btn border border-gray-200 rounded-full px-5 py-2.5 text-xs lg:text-sm font-bold text-gray-600 bg-white hover:bg-gray-50 hover:border-gray-300 transition-all active:scale-95" data-category="{{ $sub->id }}">{{ $sub->localized_name }}</button>
                @endforeach
            </div>
        </div>
        
        <!-- Size/Weight Filter -->
        <div class="flex flex-col gap-3 {{ $isRtl ? 'text-right' : 'text-left' }}">
            <span class="text-sm lg:text-base font-extrabold text-gray-800">{{ __('new_design.coffee_crops.modal_crop_weight') ?? 'الوزن / الحجم' }}</span>
            <div class="flex flex-wrap gap-2.5 {{ $isRtl ? 'justify-start flex-row-reverse' : 'justify-start' }}">
                @foreach(productSize() as $size)
                <button class="filter-size-btn border border-gray-200 rounded-full px-5 py-2.5 text-xs lg:text-sm font-bold text-gray-600 bg-white hover:bg-gray-50 hover:border-gray-300 transition-all active:scale-95" data-size="{{ strtolower($size->Size) }}">{{ $size->Size_ar ?: $size->Size }}</button>
                @endforeach
            </div>
        </div>
        
        <!-- Rating Filter -->
        <div class="flex flex-col gap-3 {{ $isRtl ? 'text-right' : 'text-left' }}">
            <span class="text-sm lg:text-base font-extrabold text-gray-800">{{ __('new_design.coffee_crops.modal_crop_rating') }}</span>
            <div class="flex items-center gap-1.5 {{ $isRtl ? 'justify-start flex-row-reverse' : 'justify-start' }}" id="rating-stars-wrapper">
                <!-- Rating Star Buttons -->
                <button data-star="1" class="star-btn text-3xl text-gray-300 hover:scale-110 active:scale-90 transition-transform">★</button>
                <button data-star="2" class="star-btn text-3xl text-gray-300 hover:scale-110 active:scale-90 transition-transform">★</button>
                <button data-star="3" class="star-btn text-3xl text-gray-300 hover:scale-110 active:scale-90 transition-transform">★</button>
                <button data-star="4" class="star-btn text-3xl text-gray-300 hover:scale-110 active:scale-90 transition-transform">★</button>
                <button data-star="5" class="star-btn text-3xl text-gray-300 hover:scale-110 active:scale-90 transition-transform">★</button>
            </div>
        </div>
        
        <!-- Save Changes Button -->
        <div class="pt-2">
            <button id="filter-modal-save" class="w-full py-4 rounded-[16px] bg-[#1A4231] hover:bg-[#2C624A] active:scale-[0.98] text-white font-extrabold flex items-center justify-center gap-2 shadow-lg hover:shadow-xl transition-all text-sm lg:text-base">
                <span>{{ __('new_design.coffee_crops.modal_btn_save') }}</span>
                <!-- Arrow left/right depending on locale -->
                <svg class="w-5 h-5 transform {{ $isRtl ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </button>
        </div>
        
    </div>
</div>

<!-- JavaScript for Interactive Hero Slide & Modal -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // --- Slider Setup ---
        const container = document.getElementById('hero-slider-container');
        const track = document.getElementById('hero-slider-track');
        const originalSlides = Array.from(track.children);
        const totalOriginal = originalSlides.length;
        const prevBtn = document.getElementById('hero-slide-prev');
        const nextBtn = document.getElementById('hero-slide-next');
        
        if (totalOriginal === 0) return;

        // Infinite Loop setup: Clone slides dynamically
        // Clone last slide and prepend to track
        const lastClone = originalSlides[totalOriginal - 1].cloneNode(true);
        lastClone.classList.add('slide-clone');
        track.insertBefore(lastClone, originalSlides[0]);

        // Clone first slide and append to track
        const firstClone = originalSlides[0].cloneNode(true);
        firstClone.classList.add('slide-clone');
        track.appendChild(firstClone);

        // Current index is 1 (pointing to Slide 1 original)
        let currentIndex = 1;
        let isTransitioning = false;

        function updateSliderPosition(animate = true) {
            if (animate) {
                track.classList.add('transition-enabled');
            } else {
                track.classList.remove('transition-enabled');
            }
            
            // Set index value to track CSS variable
            track.style.setProperty('--current-index', currentIndex);
            
            // Determine which index visually represents the active one (original slide index)
            let activeVisualIndex = currentIndex;
            if (currentIndex === 0) {
                activeVisualIndex = totalOriginal;
            } else if (currentIndex === totalOriginal + 1) {
                activeVisualIndex = 1;
            }
            
            // Apply scale and opacity styling dynamically
            const allSlides = Array.from(track.children);
            allSlides.forEach((slide, idx) => {
                if (idx === activeVisualIndex) {
                    slide.classList.remove('opacity-50', 'scale-95');
                    slide.classList.add('opacity-100', 'scale-100');
                } else {
                    slide.classList.remove('opacity-100', 'scale-100');
                    slide.classList.add('opacity-50', 'scale-95');
                }
            });
        }

        // Handle loop reset instantly when transition finishes on the clones
        track.addEventListener('transitionend', () => {
            isTransitioning = false;
            
            if (currentIndex === 0) {
                // Instantly teleport to original slide 3 (index totalOriginal) without transition
                currentIndex = totalOriginal;
                updateSliderPosition(false);
            } else if (currentIndex === totalOriginal + 1) {
                // Instantly teleport to original slide 1 (index 1) without transition
                currentIndex = 1;
                updateSliderPosition(false);
            }
        });

        // Navigation controls:
        // Left Arrow (exposes next element)
        nextBtn.addEventListener('click', () => {
            if (isTransitioning) return;
            isTransitioning = true;
            currentIndex++;
            updateSliderPosition(true);
        });

        // Right Arrow (exposes previous element)
        prevBtn.addEventListener('click', () => {
            if (isTransitioning) return;
            isTransitioning = true;
            currentIndex--;
            updateSliderPosition(true);
        });

        // Initialize immediately
        updateSliderPosition(false);
        
        // Force sync on window load and resize to ensure pixel-perfect rendering
        window.addEventListener('load', () => updateSliderPosition(false));
        window.addEventListener('resize', () => updateSliderPosition(false));

        // --- Filter Modal Setup ---
        const openModalBtn = document.getElementById('open-filter-btn');
        const modal = document.getElementById('filter-modal');
        const modalBackdrop = document.getElementById('filter-modal-backdrop');
        const modalCloseBtn = document.getElementById('filter-modal-close');
        const modalSaveBtn = document.getElementById('filter-modal-save');
        const modalBody = modal.querySelector('.relative.bg-white');

        function openModal() {
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modal.classList.add('opacity-100', 'pointer-events-auto');
            modalBody.classList.remove('scale-95', 'translate-y-4', 'opacity-0');
            modalBody.classList.add('scale-100', 'translate-y-0', 'opacity-100');
            document.body.classList.add('overflow-hidden');
        }

        function closeModal() {
            modal.classList.remove('opacity-100', 'pointer-events-auto');
            modal.classList.add('opacity-0', 'pointer-events-none');
            modalBody.classList.remove('scale-100', 'translate-y-0', 'opacity-100');
            modalBody.classList.add('scale-95', 'translate-y-4', 'opacity-0');
            document.body.classList.remove('overflow-hidden');
        }

        openModalBtn.addEventListener('click', openModal);
        modalBackdrop.addEventListener('click', closeModal);
        modalCloseBtn.addEventListener('click', closeModal);
        modalSaveBtn.addEventListener('click', closeModal);

        // Escape Key closing
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !modal.classList.contains('opacity-0')) {
                closeModal();
            }
        });

        // Active filters state
        let activeCategoryPill = 'all';
        let selectedCountries = [];
        let selectedCategories = [];
        let selectedSizes = [];
        let selectedStars = 0;
        let searchQuery = '';

        const productCards = document.querySelectorAll('.crop-product-card');

        function applyFilters() {
            productCards.forEach(card => {
                const cardCategory = card.getAttribute('data-category');
                const cardCountry = card.getAttribute('data-country');
                const cardName = (card.getAttribute('data-name') || '').toLowerCase().trim();
                const cardRating = parseInt(card.getAttribute('data-rating') || '0');

                // 1. Category Pill Filter
                if (activeCategoryPill !== 'all' && cardCategory !== activeCategoryPill) {
                    card.style.setProperty('display', 'none', 'important');
                    return;
                }

                // 2. Search Query Filter
                if (searchQuery && !cardName.includes(searchQuery)) {
                    card.style.setProperty('display', 'none', 'important');
                    return;
                }

                // 3. Modal Country Filter
                if (selectedCountries.length > 0 && !selectedCountries.includes(cardCountry)) {
                    card.style.setProperty('display', 'none', 'important');
                    return;
                }

                // 4. Modal Category Filter
                if (selectedCategories.length > 0 && !selectedCategories.includes(cardCategory)) {
                    card.style.setProperty('display', 'none', 'important');
                    return;
                }

                // 5. Modal Size Filter
                const cardSizes = card.getAttribute('data-sizes') ? card.getAttribute('data-sizes').split(',') : [];
                if (selectedSizes.length > 0) {
                    const hasMatch = selectedSizes.some(s => cardSizes.includes(s));
                    if (!hasMatch) {
                        card.style.setProperty('display', 'none', 'important');
                        return;
                    }
                }

                // 6. Modal Rating Filter
                if (selectedStars > 0 && cardRating < selectedStars) {
                    card.style.setProperty('display', 'none', 'important');
                    return;
                }

                // Show matching card
                card.style.display = 'flex';
            });
        }

        // Category Pills Toggle Selection (Horizontal Row)
        const categoryPills = document.querySelectorAll('.category-filter-pill');
        categoryPills.forEach(pill => {
            pill.addEventListener('click', () => {
                categoryPills.forEach(p => {
                    p.classList.remove('bg-[#1A4231]', 'text-white', 'shadow-md');
                    p.classList.add('border', 'border-gray-200', 'text-gray-600', 'bg-white');
                });
                pill.classList.remove('border', 'border-gray-200', 'text-gray-600', 'bg-white');
                pill.classList.add('bg-[#1A4231]', 'text-white', 'shadow-md');

                activeCategoryPill = pill.getAttribute('data-category-filter');
                applyFilters();
            });
        });

        // Country Pills Toggle Selection (Modal)
        const countryBtns = document.querySelectorAll('.filter-country-btn');
        countryBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const country = btn.getAttribute('data-country');
                const isActive = btn.classList.contains('border-[#1A4231]');
                if (isActive) {
                    btn.classList.remove('border-[#1A4231]', 'text-[#1A4231]', 'bg-[#1A4231]/5');
                    btn.classList.add('border-gray-200', 'text-gray-600', 'bg-white');
                    selectedCountries = selectedCountries.filter(c => c !== country);
                } else {
                    btn.classList.add('border-[#1A4231]', 'text-[#1A4231]', 'bg-[#1A4231]/5');
                    btn.classList.remove('border-gray-200', 'text-gray-600', 'bg-white');
                    if (!selectedCountries.includes(country)) {
                        selectedCountries.push(country);
                    }
                }
            });
        });

        // Category Pills Toggle Selection (Modal)
        const categoryBtns = document.querySelectorAll('.filter-category-btn');
        categoryBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const category = btn.getAttribute('data-category');
                const isActive = btn.classList.contains('border-[#1A4231]');
                if (isActive) {
                    btn.classList.remove('border-[#1A4231]', 'text-[#1A4231]', 'bg-[#1A4231]/5');
                    btn.classList.add('border-gray-200', 'text-gray-600', 'bg-white');
                    selectedCategories = selectedCategories.filter(c => c !== category);
                } else {
                    btn.classList.add('border-[#1A4231]', 'text-[#1A4231]', 'bg-[#1A4231]/5');
                    btn.classList.remove('border-gray-200', 'text-gray-600', 'bg-white');
                    if (!selectedCategories.includes(category)) {
                        selectedCategories.push(category);
                    }
                }
            });
        });

        // Size Pills Toggle Selection (Modal)
        const sizeBtns = document.querySelectorAll('.filter-size-btn');
        sizeBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const size = btn.getAttribute('data-size');
                const isActive = btn.classList.contains('border-[#1A4231]');
                if (isActive) {
                    btn.classList.remove('border-[#1A4231]', 'text-[#1A4231]', 'bg-[#1A4231]/5');
                    btn.classList.add('border-gray-200', 'text-gray-600', 'bg-white');
                    selectedSizes = selectedSizes.filter(s => s !== size);
                } else {
                    btn.classList.add('border-[#1A4231]', 'text-[#1A4231]', 'bg-[#1A4231]/5');
                    btn.classList.remove('border-gray-200', 'text-gray-600', 'bg-white');
                    if (!selectedSizes.includes(size)) {
                        selectedSizes.push(size);
                    }
                }
            });
        });

        // Search Input live typing handler
        const searchInput = document.getElementById('filter-search-input');
        searchInput.addEventListener('input', (e) => {
            searchQuery = e.target.value.toLowerCase().trim();
        });

        // Star Rating Selection & Hover Actions (Initialized to 0 stars filter by default)
        const starBtns = document.querySelectorAll('.star-btn');
        let selectedRating = 0;

        function renderStars(rating) {
            starBtns.forEach(btn => {
                const starVal = parseInt(btn.getAttribute('data-star'));
                if (starVal <= rating) {
                    btn.classList.remove('text-gray-300');
                    btn.classList.add('text-orange-400');
                } else {
                    btn.classList.remove('text-orange-400');
                    btn.classList.add('text-gray-300');
                }
            });
        }

        starBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                selectedRating = parseInt(btn.getAttribute('data-star'));
                renderStars(selectedRating);
            });
            btn.addEventListener('mouseenter', () => {
                const hoverRating = parseInt(btn.getAttribute('data-star'));
                renderStars(hoverRating);
            });
        });

        const starWrapper = document.getElementById('rating-stars-wrapper');
        starWrapper.addEventListener('mouseleave', () => {
            renderStars(selectedRating);
        });

        // Initial rating render (0 stars, no rating filter applied)
        renderStars(selectedRating);

        // Apply filters on save modal
        modalSaveBtn.addEventListener('click', () => {
            selectedStars = selectedRating;
            applyFilters();
            closeModal();
        });
    });
</script>

@endsection
