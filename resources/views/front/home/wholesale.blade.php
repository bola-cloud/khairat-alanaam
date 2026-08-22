@extends('front.layouts.new_design_layout')

@section('content')

@php
    $isRtl = app()->getLocale() != 'en';
    $dir = $isRtl ? 'rtl' : 'ltr';
@endphp

<style>
    /* Premium Cairo Styling */
    .wholesale-page {
        font-family: 'Cairo', sans-serif;
    }
    /* Responsive custom grids */
    .why-wholesale-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 24px;
    }
    @media (min-width: 640px) {
        .why-wholesale-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (min-width: 1024px) {
        .why-wholesale-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }
    /* Reusable Premium Section Spacing Utility */
    .premium-section-gap {
        margin-bottom: 4rem; /* 64px on mobile */
    }
    @media (min-width: 1024px) {
        .premium-section-gap {
            margin-bottom: 6rem; /* 96px on desktop */
        }
    }
</style>

<div class="wholesale-page bg-[#F9F8F6] text-[#1A4231] overflow-hidden" dir="{{ $dir }}">

    @if(isset($is_approved) && $is_approved)
        <!-- B2B CATALOG PAGE (Approved Users) -->
        <section class="py-12 lg:py-16 px-4 sm:px-6 lg:px-8 bg-[#1A4231] text-white relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-black/20 to-transparent z-0"></div>
            <div class="container mx-auto relative z-10">
                <div class="flex flex-col lg:flex-row items-center justify-between gap-6">
                    <div class="text-start">
                        <span class="inline-block bg-[#FBF0D8] text-[#1A4231] font-bold text-xs px-3 py-1 rounded-full mb-3 uppercase tracking-wider">
                            {{ $isRtl ? 'بوابة الأعمال B2B' : 'B2B Portal' }}
                        </span>
                        <h1 class="text-3xl lg:text-5xl font-black text-[#FBF0D8] mb-2">{{ $isRtl ? 'كتالوج منتجات الجملة' : 'Wholesale Products Catalog' }}</h1>
                        <p class="text-white/80 font-medium max-w-2xl">{{ $isRtl ? 'مرحباً بك في بوابة شركاء الأعمال. استعرض مجموعتنا المتميزة وتواصل معنا لطلبات الجملة الخاصة بك.' : 'Welcome to your dedicated B2B portal. Browse our premium selection and contact us for your bulk orders.' }}</p>
                    </div>
                    <div class="flex gap-4">
                        <a href="https://wa.me/{{ env('WHATSAPP_NUMBER', '+96890000000') }}" target="_blank" class="bg-[#25D366] text-white px-6 py-3 rounded-xl font-bold flex items-center justify-center gap-2 hover:bg-[#128C7E] transition-colors shadow-lg whitespace-nowrap">
                            <i class="fab fa-whatsapp text-xl"></i>
                            <span>{{ $isRtl ? 'تواصل عبر واتساب' : 'Contact via WhatsApp' }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Product Grid -->
        <section class="py-12 lg:py-20 px-4 sm:px-6 lg:px-8 bg-[#F9F8F6]">
            <div class="container mx-auto">
                @if(isset($products) && $products->isEmpty())
                    <div class="text-center py-20 bg-white rounded-3xl shadow-sm border border-gray-100">
                        <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-2xl font-bold text-[#1A4231] mb-2">{{ $isRtl ? 'لا توجد منتجات متاحة حالياً.' : 'No products available right now.' }}</h3>
                    </div>
                @else
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 lg:gap-8">
                        @foreach($products as $product)
                            <!-- Product Card -->
                            <div class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 group flex flex-col">
                                <div class="relative aspect-square overflow-hidden bg-gray-50 flex items-center justify-center p-4">
                                    <img src="{{ resolve_product_image($product->Primary_Image) }}" alt="{{ $product->localized_name }}" class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500">
                                </div>
                                <div class="p-5 flex flex-col flex-grow text-start">
                                    <span class="text-xs font-bold text-gray-400 mb-1 uppercase tracking-wide">{{ $product->category?->localized_name }}</span>
                                    <h3 class="text-lg font-bold text-[#1A4231] mb-2 line-clamp-2 leading-tight">
                                        {{ $product->localized_name }}
                                    </h3>
                                    <div class="mt-auto pt-4 flex items-center justify-between">
                                        @php
                                            $whatsappMsg = urlencode(($isRtl ? 'أريد الاستفسار عن أسعار الجملة لمنتج: ' : 'I want to inquire about wholesale prices for: ') . $product->localized_name);
                                        @endphp
                                        <a href="https://wa.me/{{ env('WHATSAPP_NUMBER', '+96890000000') }}?text={{ $whatsappMsg }}" target="_blank" class="w-full bg-[#25D366]/10 text-[#128C7E] hover:bg-[#25D366] hover:text-white px-4 py-2.5 rounded-xl text-sm font-bold flex items-center justify-center gap-2 transition-colors">
                                            <i class="fab fa-whatsapp text-lg"></i>
                                            <span>{{ $isRtl ? 'استفسر عبر الواتساب' : 'Inquire via WhatsApp' }}</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

    @else
        <!-- Hero Banner Section -->
    <section class="py-12 lg:py-16 px-4 sm:px-6 lg:px-8">
        <div class="container mx-auto">
            <!-- Hero Wavy Card -->
            <div class="relative overflow-hidden rounded-[40px] shadow-2xl min-h-[400px] lg:min-h-[500px] flex items-end p-8 lg:p-16 text-white" 
                 style="background-image: url('{{ asset('assets/elketar/gradient_image_b2b.png') }}'); background-size: cover; background-position: center;">
                
                <!-- Darkened gradient overlay for perfect readability -->
                <div class="absolute inset-0 bg-gradient-to-t from-[#1A4231]/95 via-[#1A4231]/40 to-[#1A4231]/10 z-0"></div>

                <div class="relative z-10 max-w-4xl text-start">
                    <!-- B2B Badge -->
                    <span class="inline-block bg-[#FBF0D8] text-[#1A4231] font-bold text-xs lg:text-sm px-4 py-1.5 rounded-full mb-4 shadow-md uppercase tracking-wider">
                        {{ __('new_design.wholesale.hero_badge') }}
                    </span>
                    
                    <!-- Heading -->
                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-[#FBF0D8] leading-tight mb-4 drop-shadow-sm">
                        {{ __('new_design.wholesale.hero_title') }}
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section (لماذا تختار القطار لعملك؟) -->
    <section class="py-20 lg:py-24 bg-white" style="background-image: url('{{ asset('assets/elketar/Impact Numbers Section.png') }}'); background-size: cover; background-position: center;">
        <div class="container mx-auto px-4 lg:px-8">
            
            <!-- Section Header -->
            <div class="max-w-3xl mx-auto text-center mb-16 text-white">
                <h2 class="text-3xl lg:text-5xl font-black text-[#FBF0D8] mb-4">
                    {{ __('new_design.wholesale.why_title') }}
                </h2>
                <p class="text-white/90 text-base lg:text-lg font-semibold max-w-2xl mx-auto leading-relaxed">
                    {{ __('new_design.wholesale.why_subtitle') }}
                </p>
            </div>

            <!-- Features Grid -->
            <div class="why-wholesale-grid">
                
                <!-- Feature 1: Fresh Roasting -->
                <div class="bg-white/95 backdrop-blur-md rounded-[28px] p-8 shadow-xl hover:scale-[1.03] transition-all duration-300 border border-white/20 flex flex-col items-center text-center">
                    <div class="w-14 h-14 rounded-full bg-[#1A4231]/10 flex items-center justify-center text-[#1A4231] mb-6">
                        <!-- Flame / Roast SVG Icon -->
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14l-1.121 2.121z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-[#1A4231] mb-4">{{ __('new_design.wholesale.card1_title') }}</h3>
                    <p class="text-gray-600 text-sm leading-relaxed font-medium">{{ __('new_design.wholesale.card1_desc') }}</p>
                </div>

                <!-- Feature 2: Equipment Supply -->
                <div class="bg-white/95 backdrop-blur-md rounded-[28px] p-8 shadow-xl hover:scale-[1.03] transition-all duration-300 border border-white/20 flex flex-col items-center text-center">
                    <div class="w-14 h-14 rounded-full bg-[#1A4231]/10 flex items-center justify-center text-[#1A4231] mb-6">
                        <!-- Machine / Tools SVG Icon -->
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 00-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-[#1A4231] mb-4">{{ __('new_design.wholesale.card2_title') }}</h3>
                    <p class="text-gray-600 text-sm leading-relaxed font-medium">{{ __('new_design.wholesale.card2_desc') }}</p>
                </div>

                <!-- Feature 3: Barista Training -->
                <div class="bg-white/95 backdrop-blur-md rounded-[28px] p-8 shadow-xl hover:scale-[1.03] transition-all duration-300 border border-white/20 flex flex-col items-center text-center">
                    <div class="w-14 h-14 rounded-full bg-[#1A4231]/10 flex items-center justify-center text-[#1A4231] mb-6">
                        <!-- Cap / Education SVG Icon -->
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14v7"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-[#1A4231] mb-4">{{ __('new_design.wholesale.card3_title') }}</h3>
                    <p class="text-gray-600 text-sm leading-relaxed font-medium">{{ __('new_design.wholesale.card3_desc') }}</p>
                </div>

                <!-- Feature 4: Technical Maintenance -->
                <div class="bg-white/95 backdrop-blur-md rounded-[28px] p-8 shadow-xl hover:scale-[1.03] transition-all duration-300 border border-white/20 flex flex-col items-center text-center">
                    <div class="w-14 h-14 rounded-full bg-[#1A4231]/10 flex items-center justify-center text-[#1A4231] mb-6">
                        <!-- Wrench / Tool SVG Icon -->
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-[#1A4231] mb-4">{{ __('new_design.wholesale.card4_title') }}</h3>
                    <p class="text-gray-600 text-sm leading-relaxed font-medium">{{ __('new_design.wholesale.card4_desc') }}</p>
                </div>

            </div>
        </div>
    </section>

    <!-- Wholesale Order Form Section -->
    <section class="py-20 lg:py-28 relative bg-[#EDEAE3] premium-section-gap" 
             style="background-image: url('{{ asset('assets/elketar/Group 62.png') }}'); background-size: cover; background-position: center;">
        
        <!-- Subtle Overlay -->
        <div class="absolute inset-0 bg-[#EDEAE3]/20 z-0"></div>

        <div class="container mx-auto px-4 lg:px-8 relative z-10">
            <div class="max-w-6xl mx-auto bg-white rounded-[40px] overflow-hidden shadow-2xl border border-gray-100">
                
                <!-- Inner Grid: Left/Right panels -->
                <div class="grid grid-cols-1 lg:grid-cols-12">
                    
                    <!-- Side Info Badge Column (Green Background) -->
                    <div class="lg:col-span-4 bg-[#1A4231] text-white p-8 lg:p-12 flex flex-col justify-between relative overflow-hidden" 
                         style="background-image: url('{{ asset('assets/elketar/Impact Numbers Section.png') }}'); background-size: cover; background-position: center;">
                        
                        <div class="absolute inset-0 bg-[#1a4231]/30 z-0"></div>

                        <div class="relative z-10 flex flex-col gap-6">
                            
                            <!-- Small Brand Badge -->
                            <div class="w-14 h-14 rounded-full bg-white/10 backdrop-blur-md flex items-center justify-center text-white mb-4">
                                <img src="{{ asset('assets/elketar/logo.png') }}" class="w-10 h-10 object-contain invert brightness-0" alt="Al-Katar Brand Logo">
                            </div>

                            <h3 class="text-2xl lg:text-3xl font-black text-[#FBF0D8] leading-tight">
                                {{ __('new_design.wholesale.form_badge_title') }}
                            </h3>
                            
                            <p class="text-white/85 text-sm lg:text-base font-semibold leading-relaxed">
                                {{ __('new_design.wholesale.form_badge_desc') }}
                            </p>

                            <!-- Bullet Benefits list -->
                            <ul class="space-y-4 mt-6 text-sm font-semibold text-white/95">
                                <li class="flex items-center gap-3">
                                    <span class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center text-[#FBF0D8] shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    </span>
                                    <span>{{ __('new_design.wholesale.form_bullet1') }}</span>
                                </li>
                                <li class="flex items-center gap-3">
                                    <span class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center text-[#FBF0D8] shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    </span>
                                    <span>{{ __('new_design.wholesale.form_bullet2') }}</span>
                                </li>
                                <li class="flex items-center gap-3">
                                    <span class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center text-[#FBF0D8] shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    </span>
                                    <span>{{ __('new_design.wholesale.form_bullet3') }}</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Email Contact Box at Bottom -->
                        <div class="relative z-10 mt-12 pt-6 border-t border-white/10 flex items-center gap-4">
                            <span class="w-10 h-10 rounded-full bg-white/15 flex items-center justify-center text-white shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </span>
                            <div class="text-start">
                                <p class="text-xs text-white/60 font-semibold mb-0.5">{{ __('new_design.wholesale.email_label') }}</p>
                                <a href="mailto:b2b@al-qatar.com" class="text-sm font-black text-[#FBF0D8] hover:underline">b2b@al-qatar.com</a>
                            </div>
                        </div>

                    </div>

                    <!-- Main White Form Column -->
                    <div class="lg:col-span-8 p-8 lg:p-12 flex flex-col">
                        
                        <h3 class="text-2xl lg:text-3xl font-black text-[#1A4231] pb-2 border-b border-gray-100 mb-6 text-start">
                            {{ __('new_design.wholesale.form_title') }}
                        </h3>

                        @if(!auth()->check())
                            <!-- GUEST STATE -->
                            <div class="bg-[#F9F8F6] rounded-2xl p-8 text-center flex flex-col items-center justify-center border border-gray-100 my-auto h-full min-h-[300px]">
                                <div class="w-16 h-16 rounded-full bg-[#1A4231]/10 flex items-center justify-center text-[#1A4231] mb-4 mx-auto">
                                    <i class="fas fa-lock text-2xl"></i>
                                </div>
                                <h4 class="text-xl font-bold text-[#1A4231] mb-2">{{ $isRtl ? 'تسجيل الدخول مطلوب' : 'Login Required' }}</h4>
                                <p class="text-gray-600 font-medium mb-8 max-w-md mx-auto">{{ $isRtl ? 'للتعرف على منتجات الجملة وتقديم طلب، يرجى تسجيل الدخول أو إنشاء حساب أولاً.' : 'To get to know wholesale products and submit a request, please log in or register first.' }}</p>
                                <div class="flex flex-wrap items-center justify-center gap-4">
                                    <a href="{{ route('login') }}" class="bg-[#1A4231] text-white px-8 py-3 rounded-full font-bold hover:bg-[#1A4231]/90 transition-colors shadow-sm">{{ $isRtl ? 'تسجيل الدخول' : 'Login' }}</a>
                                    <a href="{{ route('user.sign.up') }}" class="bg-white text-[#1A4231] border border-[#1A4231] px-8 py-3 rounded-full font-bold hover:bg-gray-50 transition-colors shadow-sm">{{ $isRtl ? 'إنشاء حساب' : 'Register' }}</a>
                                </div>
                            </div>
                        @elseif(isset($is_pending) && $is_pending)
                            <!-- PENDING STATE -->
                            <div class="bg-[#FBF0D8]/50 rounded-2xl p-8 text-center flex flex-col items-center justify-center border border-[#C5A880]/30 my-auto h-full min-h-[300px]">
                                <div class="w-16 h-16 rounded-full bg-[#1A4231] flex items-center justify-center text-[#FBF0D8] mb-4 mx-auto shadow-inner">
                                    <i class="fas fa-clock text-2xl"></i>
                                </div>
                                <h4 class="text-xl font-bold text-[#1A4231] mb-2">{{ $isRtl ? 'طلبك قيد المراجعة' : 'Your Request is Under Review' }}</h4>
                                <p class="text-gray-700 font-medium max-w-md mx-auto">{{ $isRtl ? 'شكراً لك! يقوم فريقنا حالياً بمراجعة طلبك. سنتواصل معك قريباً، وبمجرد الموافقة سيتم فتح كتالوج الجملة لك.' : 'Thank you! Our team is currently reviewing your request. We will contact you soon, and once approved, the wholesale catalog will be unlocked for you.' }}</p>
                            </div>
                        @else
                            <!-- FORM STATE -->
                            <form id="wholesale-request-form" action="{{ route('wholesale.orders.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-6 text-start">
                                @csrf

                        <!-- Grid Inputs -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            
                            <!-- input: Company Name -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-bold text-gray-700">{{ __('new_design.wholesale.input_company') }}</label>
                                <input type="text" name="company_name" required placeholder="{{ __('new_design.wholesale.input_company') }}" 
                                       class="w-full bg-[#F9F8F6] border border-gray-200 rounded-2xl py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-[#1A4231] focus:bg-white transition-all">
                            </div>

                            <!-- input: Contact Person -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-bold text-gray-700">{{ __('new_design.wholesale.input_name') }}</label>
                                <input type="text" name="contact_name" required placeholder="{{ __('new_design.wholesale.input_name') }}" 
                                       class="w-full bg-[#F9F8F6] border border-gray-200 rounded-2xl py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-[#1A4231] focus:bg-white transition-all">
                            </div>

                            <!-- input: Contact Phone -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-bold text-gray-700">{{ __('new_design.wholesale.input_phone') }}</label>
                                <input type="tel" name="contact_phone" required placeholder="+966 50 000 0000" 
                                       class="w-full bg-[#F9F8F6] border border-gray-200 rounded-2xl py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-[#1A4231] focus:bg-white transition-all text-start" dir="ltr">
                            </div>

                            <!-- input: Est Qty -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-bold text-gray-700">{{ __('new_design.wholesale.input_qty') }}</label>
                                <input type="text" name="estimated_qty" required placeholder="{{ $isRtl ? 'بين 50 إلى 200 كجم' : 'Between 50 to 200 kg' }}" 
                                       class="w-full bg-[#F9F8F6] border border-gray-200 rounded-2xl py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-[#1A4231] focus:bg-white transition-all">
                            </div>

                        </div>

                        <!-- Checkboxes: Services Required -->
                        <div class="flex flex-col gap-3">
                            <label class="text-sm font-bold text-gray-700">{{ __('new_design.wholesale.services_title') }}</label>
                            
                            <div class="flex flex-wrap items-center gap-6 text-sm font-semibold text-gray-600">
                                
                                <!-- beans -->
                                <label class="flex items-center gap-2.5 cursor-pointer hover:text-[#1A4231]">
                                    <input type="checkbox" name="services[]" value="beans" 
                                           class="w-5 h-5 rounded-md border-gray-300 text-[#1A4231] focus:ring-[#1A4231] cursor-pointer">
                                    <span>{{ __('new_design.wholesale.service_beans') }}</span>
                                </label>

                                <!-- equipment -->
                                <label class="flex items-center gap-2.5 cursor-pointer hover:text-[#1A4231]">
                                    <input type="checkbox" name="services[]" value="equipment" 
                                           class="w-5 h-5 rounded-md border-gray-300 text-[#1A4231] focus:ring-[#1A4231] cursor-pointer">
                                    <span>{{ __('new_design.wholesale.service_equip') }}</span>
                                </label>

                                <!-- training -->
                                <label class="flex items-center gap-2.5 cursor-pointer hover:text-[#1A4231]">
                                    <input type="checkbox" name="services[]" value="training" 
                                           class="w-5 h-5 rounded-md border-gray-300 text-[#1A4231] focus:ring-[#1A4231] cursor-pointer">
                                    <span>{{ __('new_design.wholesale.service_train') }}</span>
                                </label>

                                <!-- maintenance -->
                                <label class="flex items-center gap-2.5 cursor-pointer hover:text-[#1A4231]">
                                    <input type="checkbox" name="services[]" value="maintenance" 
                                           class="w-5 h-5 rounded-md border-gray-300 text-[#1A4231] focus:ring-[#1A4231] cursor-pointer">
                                    <span>{{ __('new_design.wholesale.service_maintenance') }}</span>
                                </label>

                            </div>
                        </div>

                        <!-- File Upload: CR or Signboard -->
                        <div class="flex flex-col gap-2 border border-gray-200 p-4 rounded-2xl bg-white shadow-sm mt-2">
                            <label class="text-sm font-bold text-gray-800">{{ $isRtl ? 'السجل التجاري أو لائحة المحل' : 'Commercial Register or Shop Signboard' }} <span class="text-red-500">*</span></label>
                            <p class="text-xs text-gray-500 mb-2 font-medium">{{ $isRtl ? 'يرجى إرفاق ملف (PDF) أو صورة (JPG, PNG) - الحد الأقصى 10 ميجابايت' : 'Please upload a PDF or Image (JPG, PNG) - Max 10MB' }}</p>
                            <input type="file" name="cr_or_signboard" accept=".pdf,image/jpeg,image/png,image/jpg" required 
                                   class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#1A4231]/10 file:text-[#1A4231] hover:file:bg-[#1A4231]/20 cursor-pointer transition-all">
                        </div>

                        <!-- Notes text area -->
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-bold text-gray-700">{{ __('new_design.wholesale.input_notes') }}</label>
                            <textarea rows="4" name="notes" placeholder="{{ __('new_design.wholesale.input_notes_placeholder') }}" 
                                      class="w-full bg-[#F9F8F6] border border-gray-200 rounded-2xl py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-[#1A4231] focus:bg-white transition-all"></textarea>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" 
                                class="w-full sm:w-auto self-start mt-4 bg-[#1A4231] text-white hover:opacity-90 active:scale-[0.98] px-10 py-4 rounded-full text-base font-bold shadow-lg transition-all flex items-center justify-center gap-3">
                            <span>{{ __('new_design.wholesale.btn_submit') }}</span>
                            <svg class="w-5 h-5 transform {{ $isRtl ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        </button>

                    </form>
                    @endif
                    </div>

            </div>
        </div>
    </section>

    @endif
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#wholesale-request-form').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var submitBtn = form.find('button[type="submit"]');
        var originalBtnHtml = submitBtn.html();
        
        // Show loading state
        submitBtn.prop('disabled', true).html(`
            <span>{{ $isRtl ? 'جاري الإرسال...' : 'Sending...' }}</span>
            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="width: 20px; height: 20px; display: inline-block; animation: spin 1s linear infinite;"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" style="opacity: 0.25;"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" style="opacity: 0.75;"></path></svg>
        `);
        
        var formData = new FormData(form[0]);
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                // Restore button
                submitBtn.prop('disabled', false).html(originalBtnHtml);
                
                if (response.success) {
                    toastr.success(response.message);
                    form[0].reset();
                } else {
                    toastr.error(response.message || "{{ $isRtl ? 'حدث خطأ ما، يرجى المحاولة مرة أخرى.' : 'Something went wrong, please try again.' }}");
                }
            },
            error: function(xhr) {
                // Restore button
                submitBtn.prop('disabled', false).html(originalBtnHtml);
                
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = xhr.responseJSON.errors;
                    Object.keys(errors).forEach(function(key) {
                        toastr.error(errors[key][0]);
                    });
                } else {
                    toastr.error("{{ $isRtl ? 'حدث خطأ ما، يرجى المحاولة مرة أخرى.' : 'Something went wrong, please try again.' }}");
                }
            }
        });
    });
});
</script>
<style>
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
@endpush
