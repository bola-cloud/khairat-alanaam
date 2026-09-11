@extends('v2.layouts.app')

@section('title', 'الرئيسية - خيرات الأنعام')
@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
@endpush

@section('content')
    <!-- Hero Section -->
    @if(isset($heroAdvertises) && $heroAdvertises->count() > 0)
        <div class="hero-slider-container" style="position: relative; overflow: hidden; height: 500px;">
            @foreach($heroAdvertises as $index => $ad)
                @php
                    $bgUrl = asset('assets/images/hero-bg.jpg'); // default
                    if(!empty($ad->image)) {
                        if(file_exists(public_path('assets/images/' . $ad->image))) {
                            $bgUrl = asset('assets/images/' . $ad->image);
                        } elseif(file_exists(public_path('uploaded_files/promotion/' . $ad->image))) {
                            $bgUrl = asset('uploaded_files/promotion/' . $ad->image);
                        }
                    }
                @endphp
                <section class="hero-section hero-slide" data-index="{{ $index }}"
                    style="display: {{ $index == 0 ? 'flex' : 'none' }}; background-image: url('{{ $bgUrl }}'); background-size: cover; background-position: center; position: absolute; inset: 0; text-align: {{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? 'right' : 'left' }}; color: #fff; align-items: center; transition: opacity 0.5s ease-in-out; opacity: {{ $index == 0 ? '1' : '0' }}; z-index: {{ $index == 0 ? '1' : '0' }};">
                    <!-- Overlay for dark contrast -->
                    <div style="position: absolute; top:0; left:0; width:100%; height:100%; background: linear-gradient(to {{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? 'right' : 'left' }}, rgba(0,0,0,0.8), transparent);"></div>

                    <div class="v2-container" style="position: relative; z-index: 2; width: 100%;">
                        <!-- Slider Arrows -->
                        @if($heroAdvertises->count() > 1)
                        <a href="javascript:void(0)" class="hero-prev"
                            style="position: absolute; {{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? 'right' : 'left' }}: 10px; top: 50%; transform: translateY(-50%); width: 40px; height: 40px; background: rgba(0,0,0,0.5); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 18px; z-index: 10;"><i
                                class="fas fa-chevron-{{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? 'right' : 'left' }}"></i></a>
                        <a href="javascript:void(0)" class="hero-next"
                            style="position: absolute; {{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? 'left' : 'right' }}: 10px; top: 50%; transform: translateY(-50%); width: 40px; height: 40px; background: rgba(0,0,0,0.5); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 18px; z-index: 10;"><i
                                class="fas fa-chevron-{{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? 'left' : 'right' }}"></i></a>
                        @endif

                        <div class="hero-content-box">
                            <div style="background: rgba(255,255,255,0.2); padding: 5px 15px; border-radius: 20px; display: inline-block; font-size: 14px; margin-bottom: 20px;">
                                @php
                                    $badge = app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? $ad->ar_badge : $ad->en_badge;
                                    if(empty($badge)) $badge = __('v2_home.hero_badge');
                                @endphp
                                {{ $badge }}
                            </div>

                            <h1 class="hero-title" style="font-weight: 800; line-height: 1.2; margin-bottom: 20px;">
                                {{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? $ad->ar_title : $ad->en_title }} <br>
                                <span class="text-primary">{{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? $ad->ar_subtitle : $ad->en_subtitle }}</span>
                            </h1>

                            <p style="font-size: 18px; margin-bottom: 30px; line-height: 1.6;">
                                {{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? $ad->ar_small_description : $ad->en_small_description }}
                            </p>

                            <div style="display: flex; gap: 15px;">
                                <a href="{{ !empty($ad->link) && $ad->link !== '#' ? $ad->link : route('front.store') }}" class="btn-primary" style="padding: 12px 30px; font-size: 16px;">@lang('v2_home.shop_now')</a>
                                <a href="{{ route('about.us') }}" class="btn-outline-white" style="padding: 12px 30px; font-size: 16px;">@lang('v2_home.our_story')</a>
                            </div>
                        </div>
                    </div>
                </section>
            @endforeach
            
            @if($heroAdvertises->count() > 1)
            <!-- Slider Dots -->
            <div style="position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); display: flex; gap: 8px; z-index: 10;">
                @foreach($heroAdvertises as $index => $ad)
                <span class="hero-dot" data-index="{{ $index }}"
                    style="width: {{ $index == 0 ? '30px' : '10px' }}; height: 10px; border-radius: 10px; background: {{ $index == 0 ? 'var(--primary-color)' : 'rgba(255,255,255,0.5)' }}; display: inline-block; cursor:pointer; transition: 0.3s;"></span>
                @endforeach
            </div>
            @endif
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const slides = document.querySelectorAll('.hero-slide');
                const dots = document.querySelectorAll('.hero-dot');
                const nextBtns = document.querySelectorAll('.hero-next');
                const prevBtns = document.querySelectorAll('.hero-prev');
                let currentSlide = 0;
                let slideInterval;

                if (slides.length <= 1) return;

                function showSlide(index) {
                    slides.forEach((slide, i) => {
                        if (i === index) {
                            slide.style.display = 'flex';
                            setTimeout(() => {
                                slide.style.opacity = '1';
                                slide.style.zIndex = '1';
                            }, 50);
                        } else {
                            slide.style.opacity = '0';
                            slide.style.zIndex = '0';
                            setTimeout(() => {
                                if(currentSlide !== i) slide.style.display = 'none';
                            }, 500);
                        }
                    });

                    dots.forEach((dot, i) => {
                        if (i === index) {
                            dot.style.width = '30px';
                            dot.style.background = 'var(--primary-color)';
                        } else {
                            dot.style.width = '10px';
                            dot.style.background = 'rgba(255,255,255,0.5)';
                        }
                    });
                    
                    currentSlide = index;
                }

                function nextSlide() {
                    let next = (currentSlide + 1) % slides.length;
                    showSlide(next);
                }

                function prevSlide() {
                    let prev = (currentSlide - 1 + slides.length) % slides.length;
                    showSlide(prev);
                }

                nextBtns.forEach(btn => btn.addEventListener('click', () => {
                    nextSlide();
                    resetInterval();
                }));
                
                prevBtns.forEach(btn => btn.addEventListener('click', () => {
                    prevSlide();
                    resetInterval();
                }));

                dots.forEach(dot => {
                    dot.addEventListener('click', function() {
                        const index = parseInt(this.getAttribute('data-index'));
                        showSlide(index);
                        resetInterval();
                    });
                });

                function startInterval() {
                    slideInterval = setInterval(nextSlide, 5000);
                }

                function resetInterval() {
                    clearInterval(slideInterval);
                    startInterval();
                }

                startInterval();
            });
        </script>
    @else
        <!-- Fallback Static Hero -->
        <section class="hero-section"
            style="background-image: url('{{ asset('assets/images/hero-bg.jpg') }}'); background-size: cover; background-position: center; position: relative; padding: 100px 0; text-align: {{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? 'right' : 'left' }}; color: #fff; min-height: 500px; display: flex; align-items: center;">
            <div style="position: absolute; top:0; left:0; width:100%; height:100%; background: linear-gradient(to {{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? 'right' : 'left' }}, rgba(0,0,0,0.8), transparent);">
            </div>
            <div class="v2-container" style="position: relative; z-index: 1; width: 100%;">
                <div class="hero-content-box">
                    <div style="background: rgba(255,255,255,0.2); padding: 5px 15px; border-radius: 20px; display: inline-block; font-size: 14px; margin-bottom: 20px;">
                        @lang('v2_home.hero_badge')
                    </div>
                    <h1 class="hero-title" style="font-weight: 800; line-height: 1.2; margin-bottom: 20px;">
                        @lang('v2_home.hero_title') <br>
                        <span class="text-primary">@lang('v2_home.hero_subtitle')</span>
                    </h1>
                    <p style="font-size: 18px; margin-bottom: 30px; line-height: 1.6;">@lang('v2_home.hero_desc')</p>
                    <div style="display: flex; gap: 15px;">
                        <a href="{{ route('front.store') }}" class="btn-primary" style="padding: 12px 30px; font-size: 16px;">@lang('v2_home.shop_now')</a>
                        <a href="{{ route('about.us') }}" class="btn-outline-white" style="padding: 12px 30px; font-size: 16px;">@lang('v2_home.our_story')</a>
                    </div>
                </div>
            </div>
        </section>
    @endif

        <!-- Category Swiper -->
    <section class="home-categories" style="padding: 40px 0; background: var(--bg-color); overflow: hidden;">
        <div class="v2-container" style="position: relative;">
            <div class="swiper category-swiper">
                <div class="swiper-wrapper">
                    @foreach($allCategories as $category)
                    <div class="swiper-slide">
                        <a href="{{ route('front.store', ['category[]' => $category->id]) }}" style="text-align: center; cursor: pointer; transition: 0.3s; text-decoration: none; display: block;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                            <img src="{{ $category->Category_Icon ? asset('uploaded_files/category_image/' . $category->Category_Icon) : asset('assets/images/placeholder.png') }}" alt="{{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? $category->fr_Category_Name : $category->en_Category_Name }}" style="width: 100%; aspect-ratio: 1/1; object-fit: cover; border-radius: 12px; margin-bottom: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                            <h3 style="margin: 0; font-size: 15px; font-weight: 700; color: #333;">{{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? $category->fr_Category_Name : $category->en_Category_Name }}</h3>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
            
            <!-- Custom Swiper Navigation -->
            @if($allCategories->count() > 5)
            <div class="category-button-next" style="position: absolute; {{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? 'left' : 'right' }}: -20px; top: 40%; transform: translateY(-50%); width: 40px; height: 40px; background: #fff; border-radius: 50%; box-shadow: 0 4px 10px rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 10; color: var(--primary-color);"><i class="fas fa-chevron-{{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? 'left' : 'right' }}"></i></div>
            <div class="category-button-prev" style="position: absolute; {{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? 'right' : 'left' }}: -20px; top: 40%; transform: translateY(-50%); width: 40px; height: 40px; background: #fff; border-radius: 50%; box-shadow: 0 4px 10px rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 10; color: var(--primary-color);"><i class="fas fa-chevron-{{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? 'right' : 'left' }}"></i></div>
            @endif
        </div>
    </section>

    <!-- Products Section -->
    <section class="home-products" id="discover-products" style="padding: 60px 0; background: #fff;">
        <div class="v2-container">
            <!-- Header -->
            <div class="products-header" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px;">
                <div>
                    <h2 style="margin: 0 0 15px 0; font-size: 28px; font-weight: 800; color: #333;">@lang('v2_home.discover_products')</h2>
                    @php $currentTab = request('tab', 'best_seller'); @endphp
                    <div class="products-tabs" style="display: flex; gap: 10px;">
                        <a href="{{ route('front', ['tab' => 'best_seller']) }}#discover-products" class="product-tab" style="padding: 8px 20px; background: {{ $currentTab == 'best_seller' ? '#e32636' : '#fff' }}; color: {{ $currentTab == 'best_seller' ? '#fff' : '#e32636' }}; border: 1px solid {{ $currentTab == 'best_seller' ? '#e32636' : '#ffb3b3' }}; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 600;">@lang('v2_home.best_seller')</a>
                        
                        <a href="{{ route('front', ['tab' => 'new_arrival']) }}#discover-products" class="product-tab" style="padding: 8px 20px; background: {{ $currentTab == 'new_arrival' ? '#e32636' : '#fff' }}; color: {{ $currentTab == 'new_arrival' ? '#fff' : '#e32636' }}; border: 1px solid {{ $currentTab == 'new_arrival' ? '#e32636' : '#ffb3b3' }}; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 600;">@lang('v2_home.new_arrival')</a>
                        
                        <a href="{{ route('front', ['tab' => 'trending']) }}#discover-products" class="product-tab" style="padding: 8px 20px; background: {{ $currentTab == 'trending' ? '#e32636' : '#fff' }}; color: {{ $currentTab == 'trending' ? '#fff' : '#e32636' }}; border: 1px solid {{ $currentTab == 'trending' ? '#e32636' : '#ffb3b3' }}; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 600;">@lang('v2_home.trending')</a>
                    </div>
                </div>
                <a href="{{ route('front.store') }}" style="color: #e32636; text-decoration: none; font-size: 15px; font-weight: 700; display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                    @lang('v2_home.view_all') <i class="fas fa-chevron-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}" style="font-size: 12px;"></i>
                </a>
            </div>

            <!-- Grid -->
            <div class="grid-products">
                <!-- Product Card (Static for design matching) -->
                @foreach($products->take(4) as $product)
                <div class="product-card" style="background:#fff; border: 1px solid var(--border-color); border-radius:16px; padding:12px; transition: 0.3s; position: relative; display: flex; flex-direction: column; height: 100%;">
                    
                    <!-- Wishlist Icon -->
                    <button type="button" onclick="event.preventDefault(); event.stopPropagation(); toggleWishlist('{{ $product->id }}', this)" style="position: absolute; top: 20px; left: 20px; width: 32px; height: 32px; background: rgba(255,255,255,0.9); border: none; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 2; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                        <i class="far fa-heart" style="font-size: 15px; color: #555;"></i>
                    </button>

                    <a href="{{ route('front.product_details', $product->en_Product_Slug ?: $product->id) }}" style="display:block; position: relative; margin-bottom: 12px; flex-shrink: 0;">
                        <img src="{{ $product->Primary_Image ? asset(ProductImage() . $product->Primary_Image) : asset('assets/images/placeholder.png') }}" alt="{{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? $product->fr_Product_Name : $product->en_Product_Name }}" style="width:100%; height:160px; object-fit:cover; border-radius:12px;">
                    </a>
                    
                    <div style="flex-grow: 1; display: flex; flex-direction: column;">
                        <!-- Badge & Rating Row -->
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <!-- Stock Status Badge -->
                            @if($product->Quantity <= 0)
                            <span style="background: #ffebee; color: #e32636; padding: 3px 8px; border-radius: 20px; font-size: 10px; font-weight: 700;">
                                @lang('v2_home.out_of_stock')
                            </span>
                            @elseif($product->Quantity > 0 && $product->Quantity <= 5)
                            <span style="background: #fff3e0; color: #e65100; padding: 3px 8px; border-radius: 20px; font-size: 10px; font-weight: 700;">
                                @lang('v2_store.limited')
                            </span>
                            @else
                            <span style="background: #e8f5e9; color: #2e7d32; padding: 3px 8px; border-radius: 20px; font-size: 10px; font-weight: 700;">
                                @lang('v2_store.in_stock')
                            </span>
                            @endif

                            <!-- Rating -->
                            <div style="display: flex; align-items: center; gap: 3px; font-size: 11px; font-weight: 600; color: #555;">
                                <span style="color: #999; font-weight: normal;">({{ number_format($product->reviews_avg_rating ?? 5, 1) }})</span>
                                <i class="fas fa-star" style="color: #e32636; font-size: 10px;"></i>
                                <span>({{ $product->reviews_count ?? '0' }})</span>
                            </div>
                        </div>

                        <!-- Title & Description -->
                        <h3 style="font-size:14px; font-weight: 800; margin:0 0 4px; line-height: 1.4;"><a href="{{ route('front.product_details', $product->en_Product_Slug ?: $product->id) }}" style="color:var(--text-color); text-decoration:none;">{{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? $product->fr_Product_Name : $product->en_Product_Name }}</a></h3>
                        <p style="margin: 0; color: var(--text-light); font-size: 11px; line-height: 1.4;">{{ Str::limit(app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? ($product->fr_About ?? trans('v2_store.no_description')) : ($product->en_About ?? trans('v2_store.no_description')), 40) }}</p>
                    </div>

                    <!-- Fixed height bottom section for Price and Button -->
                    <div style="margin-top: auto; padding-top: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            @if($product->Discount_Price > 0 && $product->Discount_Price < $product->Price)
                                <div style="font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 4px; color: #333;">
                                    {{ $product->Discount_Price }} @lang('v2_layout.currency_omr')
                                </div>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <span style="text-decoration: line-through; color: var(--text-light); font-size: 11px;">{{ $product->Price }}</span>
                                    <span style="color: #4caf50; font-weight: 700; font-size: 11px;">{{ round((($product->Price - $product->Discount_Price) / $product->Price) * 100) }}% <i class="fas fa-arrow-down" style="font-size: 8px;"></i></span>
                                </div>
                            @else
                                <div style="font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 4px; color: #333;">
                                    {{ $product->Price ?? '0.00' }} @lang('v2_layout.currency_omr')
                                </div>
                            @endif
                        </div>

                        <button type="button" onclick="event.preventDefault(); event.stopPropagation(); addToCart('{{ $product->id }}', '{{ $product->Discount_Price > 0 ? $product->Discount_Price : $product->Price }}')" style="width:100%; background-color: var(--primary-color); color: white; border: none; padding: 10px; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; transition: 0.2s; font-family: inherit;">
                            @lang('v2_home.add_to_cart')
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Occasions -->
    <section class="home-occasions" style="padding: 60px 0; background: var(--bg-color); text-align: center;">
        <div class="v2-container">
            <h2 style="margin: 0 0 10px 0; font-size: 28px; font-weight: 800; color: #333;">@lang('v2_home.shop_by_occasion')</h2>
            <p style="margin: 0 0 40px 0; font-size: 15px; color: #666;">@lang('v2_home.occasion_desc')</p>
            
            <div class="swiper occasion-swiper" style="padding: 20px 0;">
                <div class="swiper-wrapper">
                    @foreach($occasions as $occasion)
                    <div class="swiper-slide">
                        <a href="{{ route('front.store', ['category[]' => $occasion->id]) }}" style="text-decoration: none; display: block; text-align: center; cursor: pointer; transition: 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                            <img src="{{ $occasion->Category_Icon ? asset('uploaded_files/category_image/' . $occasion->Category_Icon) : asset('assets/images/placeholder.png') }}" alt="{{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? $occasion->fr_Category_Name : $occasion->en_Category_Name }}" style="width: 100%; height: 220px; object-fit: cover; border-radius: 12px; margin-bottom: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                            <h3 style="margin: 0 0 5px 0; font-size: 18px; font-weight: 700; color: #333;">{{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? $occasion->fr_Category_Name : $occasion->en_Category_Name }}</h3>
                        </a>
                    </div>
                    @endforeach
                </div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </section>

    <!-- Bundles -->
    <section class="home-bundles" style="padding: 20px 0 80px 0; background: linear-gradient(to bottom, var(--bg-color), #fcf0f0); text-align: center;">
        <div class="v2-container">
            <div style="display: inline-block; padding: 6px 15px; border: 1px solid #ffb3b3; color: #e32636; border-radius: 20px; font-size: 13px; font-weight: 700; margin-bottom: 20px; background: rgba(227,38,54,0.05);">
                <i class="fas fa-percentage"></i> @lang('v2_home.limited_offers')
            </div>
            <h2 style="margin: 0 0 10px 0; font-size: 28px; font-weight: 800; color: #333;">@lang('v2_home.bundles_title')</h2>
            <p style="margin: 0 0 50px 0; font-size: 15px; color: #666;">@lang('v2_home.bundles_desc')</p>

            <div class="swiper packages-swiper" style="text-align: {{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? 'right' : 'left' }}; padding: 20px 0;">
                <div class="swiper-wrapper">
                    @foreach($packages as $package)
                    <div class="swiper-slide" style="height: auto;">
                        <div style="background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: 1px solid #eee; height: 100%; display: flex; flex-direction: column;">
                            <img src="{{ $package->Primary_Image ? asset(ProductImage() . $package->Primary_Image) : asset('assets/images/placeholder.png') }}" alt="{{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? $package->fr_Product_Name : $package->en_Product_Name }}" style="width: 100%; height: 220px; object-fit: cover;">
                            <div style="padding: 25px; flex-grow: 1; display: flex; flex-direction: column;">
                                @if($package->Discount > 0)
                                <div style="display: inline-block; padding: 4px 10px; background: #e8f5e9; color: #2ecc71; border-radius: 4px; font-size: 11px; font-weight: 700; margin-bottom: 10px; width: fit-content;">{{ __('Save :amount%', ['amount' => $package->Discount]) }}</div>
                                @endif
                                <h3 style="margin: 0 0 5px 0; font-size: 20px; font-weight: 800; color: #333;">
                                    <a href="{{ route('front.product_details', $package->en_Product_Slug ?: $package->id) }}" style="color: inherit; text-decoration: none;">{{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? $package->fr_Product_Name : $package->en_Product_Name }}</a>
                                </h3>
                                <p style="margin: 0 0 20px 0; font-size: 13px; color: #888;">{{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? $package->fr_About : $package->en_About }}</p>
                                
                                <div style="display: flex; justify-content: {{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? 'flex-end' : 'flex-start' }}; align-items: center; margin-bottom: 20px;">
                                    <div style="font-size: 28px; font-weight: 800; color: #333;">
                                        <i class="fas fa-box" style="font-size:18px; color:#555;"></i> {{ $package->Discount > 0 ? $package->Discount_Price : $package->Price }}
                                    </div>
                                    @if($package->Discount > 0)
                                    <div style="margin-right: 15px; margin-left: 15px; font-size: 14px; color: #999; text-decoration: line-through;">
                                        <i class="fas fa-box" style="font-size:12px;"></i> {{ $package->Price }}
                                    </div>
                                    @endif
                                </div>

                                <div class="package-description" style="font-size: 13px; color: #555; line-height: 2; margin-bottom: 25px; flex-grow: 1;">
                                    {!! app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? $package->fr_Description : $package->en_Description !!}
                                </div>

                                <button type="button" onclick="event.preventDefault(); event.stopPropagation(); addToCart('{{ $package->id }}', '{{ $package->Discount > 0 ? $package->Discount_Price : $package->Price }}')" style="width: 100%; padding: 15px; background: #e32636; color: #fff; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; transition: 0.3s; font-size: 15px; font-family: inherit;">@lang('v2_home.add_to_cart')</button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </section>

    <!-- Premium Cuts -->
    <section class="home-cuts" style="padding: 60px 0; background: #faf4e1; text-align: center;">
        <div class="v2-container">
            <div style="display: inline-block; padding: 4px 15px; background: rgba(227,38,54,0.1); color: #e32636; border-radius: 20px; font-size: 13px; font-weight: 700; margin-bottom: 15px;">@lang('v2_home.butcher_master')</div>
            <h2 style="margin: 0 0 10px 0; font-size: 28px; font-weight: 800; color: #333;">@lang('v2_home.premium_cuts_title')</h2>
            <p style="margin: 0 0 40px 0; font-size: 15px; color: #666;">@lang('v2_home.premium_cuts_desc')</p>

            <div class="grid-cuts">
                @foreach($cuts as $cut)
                <div style="position: relative; border-radius: 12px; overflow: hidden; height: 160px; transition: 0.3s; cursor: pointer;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'" onclick="window.location.href='{{ route('front.store') }}?category[]={{ $cut->id }}'">
                    @if($cut->show_on_home)
                    <span style="position:absolute; top:10px; {{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? 'left' : 'right' }}:10px; background:#e32636; color:#fff; font-size:10px; padding:2px 8px; border-radius:10px; z-index:2;">@lang('v2_home.popular')</span>
                    @endif
                    <img src="{{ $cut->Category_Icon ? asset(CategoryImage() . $cut->Category_Icon) : asset('assets/images/placeholder.png') }}" alt="{{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? $cut->fr_Category_Name : $cut->en_Category_Name }}" style="width:100%; height:100%; object-fit:cover;">
                    <div style="position: absolute; inset:0; background: rgba(0,0,0,0.5); display: flex; flex-direction: column; justify-content: flex-end; padding: 15px; color: #fff; text-align: {{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? 'right' : 'left' }};">
                        <h4 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 700;">{{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? $cut->fr_Category_Name : $cut->en_Category_Name }}</h4>
                        <p style="margin: 0; font-size: 11px; opacity: 0.8;">{{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? $cut->fr_Description : $cut->en_Description }}</p>
                        <span style="font-size: 10px; margin-top: 5px;">{{ $cut->products_count ?? 0 }} @lang('v2_home.products_count')</span>
                    </div>
                </div>
                @endforeach
            </div>
            
            <div style="margin-top: 30px;">
                <a href="{{ route('front.store') . '?' . http_build_query(['category' => $cuts->pluck('id')->toArray()]) }}" style="color: #e32636; text-decoration: none; font-size: 15px; font-weight: 700;">@lang('v2_home.view_all_cuts') <i class="fas fa-chevron-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}" style="font-size:12px; margin-right:5px; margin-left:5px;"></i></a>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="home-testimonials" style="padding: 80px 0; background: #fff; text-align: center;">
        <div class="v2-container">
            <h2 style="margin: 0 0 10px 0; font-size: 28px; font-weight: 800; color: #333;">@lang('v2_home.testimonials_title')</h2>
            <p style="margin: 0 0 40px 0; font-size: 15px; color: #666;">@lang('v2_home.testimonials_desc')</p>

            <div class="grid-testimonials">
                <!-- Review 1 -->
                <div style="border: 1px solid #eee; border-radius: 12px; padding: 30px; text-align: {{ app()->getLocale() == 'ar' ? 'right' : 'left' }};">
                    <div style="color: #f1c40f; font-size: 14px; margin-bottom: 15px;">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p style="color: #555; font-size: 14px; line-height: 1.8; margin-bottom: 25px; min-height: 70px;">"خدمة العملاء ممتازة وسرعة في التوصيل. جودة اللحوم واضحة من لونها ورائحتها. سأعتمد عليكم دائماً."</p>
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: #f5f5f5; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #888;">ع</div>
                        <div>
                            <h5 style="margin: 0; font-size: 14px; font-weight: 700;">عبدالله خ.</h5>
                            <span style="font-size: 12px; color: #999;">@lang('v2_home.verified_buyer')</span>
                        </div>
                    </div>
                </div>
                <!-- Review 2 -->
                <div style="border: 1px solid #eee; border-radius: 12px; padding: 30px; text-align: {{ app()->getLocale() == 'ar' ? 'right' : 'left' }};">
                    <div style="color: #f1c40f; font-size: 14px; margin-bottom: 15px;">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p style="color: #555; font-size: 14px; line-height: 1.8; margin-bottom: 25px; min-height: 70px;">"لحم الضأن كان استثنائياً، التقطيع دقيق ومرتب. استخدمنا الوصفة الموجودة في الموقع وكانت النتيجة رائعة. شكراً لكم!"</p>
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <img src="{{ asset('assets/images/placeholder.png') }}" alt="User" style="width: 40px; height: 40px; border-radius: 50%;">
                        <div>
                            <h5 style="margin: 0; font-size: 14px; font-weight: 700;">سارة م.</h5>
                            <span style="font-size: 12px; color: #999;">@lang('v2_home.verified_buyer')</span>
                        </div>
                    </div>
                </div>
                <!-- Review 3 -->
                <div style="border: 1px solid #eee; border-radius: 12px; padding: 30px; text-align: {{ app()->getLocale() == 'ar' ? 'right' : 'left' }};">
                    <div style="color: #f1c40f; font-size: 14px; margin-bottom: 15px;">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p style="color: #555; font-size: 14px; line-height: 1.8; margin-bottom: 25px; min-height: 70px;">"أفضل تجربة شراء لحوم على الإطلاق. التغليف كان ممتازاً ووصل اللحم بارداً جداً وكأنه خرج من الثلاجة للتو. جودة الواغيو لا يُعلى عليها."</p>
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <img src="{{ asset('assets/images/placeholder.png') }}" alt="User" style="width: 40px; height: 40px; border-radius: 50%;">
                        <div>
                            <h5 style="margin: 0; font-size: 14px; font-weight: 700;">أحمد س.</h5>
                            <span style="font-size: 12px; color: #999;">@lang('v2_home.verified_buyer')</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Farm Section -->
    @if(isset($farmSection) && $farmSection->status)
    @php
        $fsContent = in_array(app()->getLocale(), ['ar', 'fr']) ? $farmSection->content_fr : $farmSection->content_en;
        $fsImg = null;
        if(!empty($farmSection->image)){
            if (file_exists(public_path($farmSection->image))) {
                $fsImg = asset($farmSection->image);
            } elseif (file_exists(public_path(PromotionImage() . $farmSection->image))) {
                $fsImg = asset(PromotionImage() . $farmSection->image);
            }
        }
        $fsImg2 = null;
        if(isset($fsContent['image2']) && !empty($fsContent['image2'])) {
            if (file_exists(public_path(PromotionImage() . $fsContent['image2']))) {
                $fsImg2 = asset(PromotionImage() . $fsContent['image2']);
            }
        }
    @endphp
    <section class="home-farm" style="padding: 60px 0; background: var(--bg-color);">
        <div class="v2-container">
            <div class="grid-farm">
                <!-- Image Side -->
                <div style="position: relative;">
                    <img src="{{ $fsImg ?: asset('assets/images/placeholder.png') }}" alt="{{ $fsContent['title'] ?? 'Farm' }}" style="width: 100%; height: 500px; object-fit: cover; border-radius: 20px;">
                    
                    @php 
                        $insetPos = (app()->getLocale() == 'ar' || app()->getLocale() == 'fr') ? 'right: -30px;' : 'left: -30px;'; 
                    @endphp
                    @if($fsImg2)
                        <img src="{{ $fsImg2 }}" alt="Meat" style="position: absolute; bottom: -30px; {{ $insetPos }} width: 280px; height: 280px; object-fit: cover; border-radius: 20px; border: 12px solid var(--bg-color); box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                    @else
                        <img src="{{ asset('assets/images/placeholder.png') }}" alt="Meat" style="position: absolute; bottom: -30px; {{ $insetPos }} width: 280px; height: 280px; object-fit: cover; border-radius: 20px; border: 12px solid var(--bg-color); box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                    @endif
                </div>

                <!-- Text Side -->
                <div>
                    <h2 style="margin: 0 0 20px 0; font-size: 32px; font-weight: 800; color: #333;">{{ $fsContent['title'] ?? '' }}</h2>
                    <p style="margin: 0 0 40px 0; font-size: 15px; color: #666; line-height: 1.8;">{{ $fsContent['lead'] ?? '' }}</p>

                    <div style="display: flex; flex-direction: column; gap: 30px;">
                        @if(isset($fsContent['items']) && is_array($fsContent['items']))
                            @foreach($fsContent['items'] as $item)
                                @if(!empty($item['title']))
                                <div style="display: flex; gap: 20px;">
                                    <div style="width: 50px; height: 50px; background: #fff5f5; color: #e32636; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                                        <i class="{{ $item['icon'] ?? 'fas fa-check' }}"></i>
                                    </div>
                                    <div>
                                        <h4 style="margin: 0 0 8px 0; font-size: 16px; font-weight: 700; color: #333;">{{ $item['title'] }}</h4>
                                        <p style="margin: 0; font-size: 13px; color: #777; line-height: 1.6;">{{ $item['desc'] ?? '' }}</p>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif

    <!-- Recipes -->
    <section class="home-recipes" style="padding: 100px 0 80px 0; background: #fff; text-align: center;">
        <div class="v2-container">
            <div style="display: inline-block; padding: 4px 15px; border: 1px solid #ffb3b3; color: #e32636; border-radius: 20px; font-size: 13px; font-weight: 700; margin-bottom: 15px;">
                <i class="fas fa-hat-chef"></i> @lang('v2_home.recipes_badge')
            </div>
            <h2 style="margin: 0 0 40px 0; font-size: 28px; font-weight: 800; color: #333;">@lang('v2_home.recipes_title')</h2>

            <div class="grid-recipes" style="text-align: {{ app()->getLocale() == 'ar' ? 'right' : 'left' }};">
                @foreach($recipes ?? [] as $recipe)
                    <div style="border: 1px solid #eee; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
                        <div style="position: relative; height: 220px; cursor: pointer;" class="recipe-trigger" data-recipe="{{ base64_encode(json_encode([
                            'title' => $recipe->localized_title,
                            'description' => $recipe->localized_description,
                            'image' => $recipe->image ? asset('uploaded_files/recipes/' . $recipe->image) : asset('assets/images/placeholder.png'),
                            'difficulty' => $recipe->difficulty == 'easy' ? __('v2_home.diff_easy') : ($recipe->difficulty == 'medium' ? __('v2_home.diff_medium') : __('v2_home.diff_hard')),
                            'time' => $recipe->time_to_cook,
                            'timeText' => __('v2_home.minutes')
                        ])) }}">
                            @if($recipe->image)
                                <img src="{{ asset('uploaded_files/recipes/' . $recipe->image) }}" alt="{{ $recipe->localized_title }}" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <img src="{{ asset('assets/images/placeholder.png') }}" alt="{{ $recipe->localized_title }}" style="width: 100%; height: 100%; object-fit: cover;">
                            @endif
                            <div style="position: absolute; top: 15px; right: 15px; background: rgba(255,255,255,0.9); padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; color: #333; {{ app()->getLocale() == 'en' ? 'right: auto; left: 15px;' : '' }}">
                                @if($recipe->difficulty == 'easy')
                                    @lang('v2_home.diff_easy')
                                @elseif($recipe->difficulty == 'medium')
                                    @lang('v2_home.diff_medium')
                                @else
                                    @lang('v2_home.diff_hard')
                                @endif
                                &bull; <i class="far fa-clock" style="color: #e32636;"></i> {{ $recipe->time_to_cook }} @lang('v2_home.minutes')
                            </div>
                        </div>
                        <div style="padding: 25px;">
                            <h3 style="margin: 0 0 10px 0; font-size: 18px; font-weight: 700; color: #333;">{{ $recipe->localized_title }}</h3>
                            <p style="margin: 0 0 20px 0; font-size: 13px; color: #777; line-height: 1.6;">{{ \Illuminate\Support\Str::limit($recipe->localized_description, 80) }}</p>
                            <a href="javascript:void(0)" class="recipe-trigger" data-recipe="{{ base64_encode(json_encode([
                                'title' => $recipe->localized_title,
                                'description' => $recipe->localized_description,
                                'image' => $recipe->image ? asset('uploaded_files/recipes/' . $recipe->image) : asset('assets/images/placeholder.png'),
                                'difficulty' => $recipe->difficulty == 'easy' ? __('v2_home.diff_easy') : ($recipe->difficulty == 'medium' ? __('v2_home.diff_medium') : __('v2_home.diff_hard')),
                                'time' => $recipe->time_to_cook,
                                'timeText' => __('v2_home.minutes')
                            ])) }}" style="color: #e32636; text-decoration: none; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 5px;">@lang('v2_home.view_recipe') <i class="fas fa-chevron-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}" style="font-size:10px;"></i></a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- FAQs -->
    <section class="home-faq" style="padding: 60px 0 100px 0; background: var(--bg-color); text-align: center;">
        <div class="v2-container" style="max-width: 800px; margin: 0 auto;">
            <h2 style="margin: 0 0 10px 0; font-size: 28px; font-weight: 800; color: #333;">@lang('v2_home.faq_title')</h2>
            <p style="margin: 0 0 40px 0; font-size: 15px; color: #666;">@lang('v2_home.faq_desc')</p>

            <div style="text-align: {{ app()->getLocale() == 'ar' ? 'right' : 'left' }};">
                @foreach($faqs ?? [] as $index => $faq)
                <div class="faq-item" style="background: #fff; border: 1px solid #eee; border-radius: 8px; margin-bottom: 15px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.02);">
                    <div class="faq-header" style="padding: 20px; display: flex; justify-content: space-between; align-items: center; cursor: pointer; font-weight: 700; color: #333; font-size: 16px;">
                        <span>{{ app()->getLocale() == 'ar' ? $faq->question : ($faq->question_fr ?: $faq->question) }}</span>
                        <i class="fas fa-chevron-down faq-icon" style="color: #999; font-size: 12px; transition: transform 0.3s ease;"></i>
                    </div>
                    <div class="faq-body" style="display: none; padding: 0 20px 20px 20px; font-size: 14px; color: #666; line-height: 1.6; border-top: 1px solid #f9f9f9; padding-top: 15px;">
                        {{ app()->getLocale() == 'ar' ? $faq->answer : ($faq->answer_fr ?: $faq->answer) }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Features Section -->
    @php
        $featData = null;
        if (isset($featuresSection) && $featuresSection->status) {
            $featData = in_array(app()->getLocale(), ['ar', 'fr']) ? $featuresSection->content_fr : $featuresSection->content_en;
        }
    @endphp
    <section class="features-section" style="padding: 100px 0 50px 0; background-color: #fff;">
        <div class="v2-container">
            <div class="grid-features">
                @if($featData && isset($featData['items']))
                    @foreach(array_slice($featData['items'], 0, 4) as $index => $item)
                        @php
                            $icons = ['fas fa-truck-fast', 'fas fa-shield-alt', 'fas fa-ribbon', 'far fa-clock'];
                        @endphp
                        <div style="background: #fff; padding: 40px 20px; text-align: center; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.05);">
                            <div style="width: 60px; height: 60px; background: #fff5f5; color: #e32636; font-size: 24px; display: inline-flex; align-items: center; justify-content: center; border-radius: 12px; margin-bottom: 20px;">
                                <i class="{{ $icons[$index] ?? 'fas fa-check' }}"></i>
                            </div>
                            <h4 style="margin: 0 0 8px; font-size: 16px; font-weight: 700; color: #333;">{{ $item['title'] ?? '' }}</h4>
                            <p style="margin: 0; color: #888; font-size: 13px;">{{ $item['desc'] ?? '' }}</p>
                        </div>
                    @endforeach
                @else
                    <!-- Fallback if dynamic features not set -->
                    <!-- Feature 1 -->
                    <div style="background: #fff; padding: 40px 20px; text-align: center; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.05);">
                        <div style="width: 60px; height: 60px; background: #fff5f5; color: #e32636; font-size: 24px; display: inline-flex; align-items: center; justify-content: center; border-radius: 12px; margin-bottom: 20px;">
                            <i class="fas fa-truck-fast"></i>
                        </div>
                        <h4 style="margin: 0 0 8px; font-size: 16px; font-weight: 700; color: #333;">@lang('v2_home.feature_1_title')</h4>
                        <p style="margin: 0; color: #888; font-size: 13px;">@lang('v2_home.feature_1_desc')</p>
                    </div>
                    <!-- Feature 2 -->
                    <div style="background: #fff; padding: 40px 20px; text-align: center; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.05);">
                        <div style="width: 60px; height: 60px; background: #fff5f5; color: #e32636; font-size: 24px; display: inline-flex; align-items: center; justify-content: center; border-radius: 12px; margin-bottom: 20px;">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4 style="margin: 0 0 8px; font-size: 16px; font-weight: 700; color: #333;">@lang('v2_home.feature_2_title')</h4>
                        <p style="margin: 0; color: #888; font-size: 13px;">@lang('v2_home.feature_2_desc')</p>
                    </div>
                    <!-- Feature 3 -->
                    <div style="background: #fff; padding: 40px 20px; text-align: center; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.05);">
                        <div style="width: 60px; height: 60px; background: #fff5f5; color: #e32636; font-size: 24px; display: inline-flex; align-items: center; justify-content: center; border-radius: 12px; margin-bottom: 20px;">
                            <i class="fas fa-ribbon"></i>
                        </div>
                        <h4 style="margin: 0 0 8px; font-size: 16px; font-weight: 700; color: #333;">@lang('v2_home.feature_3_title')</h4>
                        <p style="margin: 0; color: #888; font-size: 13px;">@lang('v2_home.feature_3_desc')</p>
                    </div>
                    <!-- Feature 4 -->
                    <div style="background: #fff; padding: 40px 20px; text-align: center; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.05);">
                        <div style="width: 60px; height: 60px; background: #fff5f5; color: #e32636; font-size: 24px; display: inline-flex; align-items: center; justify-content: center; border-radius: 12px; margin-bottom: 20px;">
                            <i class="far fa-clock"></i>
                        </div>
                        <h4 style="margin: 0 0 8px; font-size: 16px; font-weight: 700; color: #333;">@lang('v2_home.feature_4_title')</h4>
                        <p style="margin: 0; color: #888; font-size: 13px;">@lang('v2_home.feature_4_desc')</p>
                    </div>
                @endif
            </div>
        </div>
    </section>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const faqHeaders = document.querySelectorAll('.faq-header');
            
            faqHeaders.forEach(header => {
                header.addEventListener('click', function() {
                    const body = this.nextElementSibling;
                    const icon = this.querySelector('.faq-icon');
                    const isOpen = body.style.display === 'block';
                    
                    // Close all FAQs
                    document.querySelectorAll('.faq-body').forEach(b => b.style.display = 'none');
                    document.querySelectorAll('.faq-icon').forEach(i => {
                        i.style.transform = 'rotate(0deg)';
                        i.style.color = '#999';
                        i.classList.remove('fa-chevron-up');
                        i.classList.add('fa-chevron-down');
                    });
                    
                    // Toggle current FAQ
                    if (!isOpen) {
                        body.style.display = 'block';
                        icon.style.transform = 'rotate(180deg)';
                        icon.style.color = '#e32636';
                        icon.classList.remove('fa-chevron-down');
                        icon.classList.add('fa-chevron-up');
                    }
                });
            });
        });
    </script>
    
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <style>
        .package-description ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .package-description ul li {
            position: relative;
            padding-left: 25px;
            margin-bottom: 8px;
        }
        html[dir="rtl"] .package-description ul li {
            padding-left: 0;
            padding-right: 25px;
        }
        .package-description ul li::before {
            content: '\f00c';
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            color: #e32636;
        }
        html[dir="rtl"] .package-description ul li::before {
            left: auto;
            right: 0;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if(document.querySelector('.category-swiper')) {
                new Swiper('.category-swiper', {
                    slidesPerView: 2,
                    spaceBetween: 15,
                    navigation: {
                        nextEl: '.category-button-next',
                        prevEl: '.category-button-prev',
                    },
                    breakpoints: {
                        576: { slidesPerView: 3, spaceBetween: 20 },
                        768: { slidesPerView: 4, spaceBetween: 20 },
                        1024: { slidesPerView: 5, spaceBetween: 25 },
                    }
                });
            }
            if(document.querySelector('.occasion-swiper')) {
                new Swiper('.occasion-swiper', {
                    slidesPerView: 1.2,
                    spaceBetween: 15,
                    pagination: {
                        el: '.occasion-swiper .swiper-pagination',
                        clickable: true,
                    },
                    breakpoints: {
                        576: { slidesPerView: 2, spaceBetween: 20 },
                        768: { slidesPerView: 3, spaceBetween: 20 },
                        1024: { slidesPerView: 3, spaceBetween: 25 },
                    }
                });
            }
            if(document.querySelector('.packages-swiper')) {
                new Swiper('.packages-swiper', {
                    slidesPerView: 1.2,
                    spaceBetween: 15,
                    pagination: {
                        el: '.packages-swiper .swiper-pagination',
                        clickable: true,
                    },
                    breakpoints: {
                        576: { slidesPerView: 2, spaceBetween: 20 },
                        768: { slidesPerView: 2, spaceBetween: 20 },
                        1024: { slidesPerView: 3, spaceBetween: 25 },
                    }
                });
            }
        });
    </script>

    <!-- Recipe Modal -->
    <div id="recipeModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 9999; justify-content: center; align-items: center; padding: 20px;">
        <div style="background: #fff; border-radius: 20px; width: 100%; max-width: 800px; max-height: 90vh; overflow-y: auto; position: relative; display: flex; flex-direction: column;">
            
            <!-- Close Button -->
            <button onclick="closeRecipeModal()" style="position: absolute; top: 15px; left: 15px; background: rgba(0,0,0,0.5); color: #fff; border: none; width: 35px; height: 35px; border-radius: 50%; font-size: 16px; cursor: pointer; z-index: 10; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-times"></i>
            </button>

            <!-- Image Header -->
            <div style="position: relative; height: 350px; border-radius: 20px 20px 0 0; overflow: hidden;">
                <img id="modalRecipeImage" src="" alt="Recipe" style="width: 100%; height: 100%; object-fit: cover;">
                <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0) 100%);"></div>
                
                <div style="position: absolute; bottom: 30px; left: 0; right: 0; text-align: center; color: #fff; padding: 0 20px;">
                    <div style="display: inline-block; background: rgba(255,255,255,0.9); padding: 5px 15px; border-radius: 20px; font-size: 13px; font-weight: 700; color: #333; margin-bottom: 15px;">
                        <span id="modalRecipeDifficulty"></span> &bull; <i class="far fa-clock" style="color: #e32636;"></i> <span id="modalRecipeTime"></span> <span id="modalRecipeTimeText"></span>
                    </div>
                    <h2 id="modalRecipeTitle" style="margin: 0; font-size: 32px; font-weight: 800; color: #fff; text-shadow: 0 2px 5px rgba(0,0,0,0.5);"></h2>
                </div>
            </div>

            <!-- Content Area -->
            <div style="padding: 40px;">
                <div style="text-align: center; margin-bottom: 25px;">
                    <h3 style="font-size: 20px; font-weight: 800; color: #333; margin: 0 0 10px 0;">@lang('v2_home.description' ?? 'الوصف')</h3>
                    <div style="width: 50px; height: 3px; background: #e32636; margin: 0 auto; border-radius: 2px;"></div>
                </div>
                <div id="modalRecipeDesc" style="font-size: 15px; color: #555; line-height: 1.8; text-align: center; white-space: pre-line;"></div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const triggers = document.querySelectorAll('.recipe-trigger');
            triggers.forEach(function(trigger) {
                trigger.addEventListener('click', function(e) {
                    e.preventDefault();
                    const rawData = this.getAttribute('data-recipe');
                    if(rawData) {
                        try {
                            const data = JSON.parse(atob(rawData));
                            openRecipeModal(data);
                        } catch(err) {
                            console.error('Error parsing recipe data', err);
                        }
                    }
                });
            });
        });

        function openRecipeModal(data) {
            document.getElementById('modalRecipeImage').src = data.image;
            document.getElementById('modalRecipeTitle').innerText = data.title;
            document.getElementById('modalRecipeDifficulty').innerText = data.difficulty;
            document.getElementById('modalRecipeTime').innerText = data.time;
            document.getElementById('modalRecipeTimeText').innerText = data.timeText;
            document.getElementById('modalRecipeDesc').innerText = data.description;
            
            document.getElementById('recipeModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeRecipeModal() {
            document.getElementById('recipeModal').style.display = 'none';
            document.body.style.overflow = '';
        }

        // Close on outside click
        document.getElementById('recipeModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRecipeModal();
            }
        });
    </script>
    @endpush
@endsection