<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    @php
        // Fetch Settings from global $allsettings
        $siteLogo = isset($allsettings['main_logo']) ? asset(IMG_LOGO_PATH . $allsettings['main_logo']) : asset('assets/images/logo.png');
        $footerLogo = isset($allsettings['footer_logo']) ? asset(IMG_LOGO_PATH . $allsettings['footer_logo']) : asset('assets/images/logo.png');
        $siteFavicon = isset($allsettings['favicon']) ? asset(IMG_FAVICON_PATH . $allsettings['favicon']) : asset('assets/images/favicon.png');
        
        // Fetch SEO data based on Current Route Name
        $routeName = request()->route() ? request()->route()->getName() : '';
        $seo = \App\Models\SeoSetting::where('slug', $routeName)->first();
        
        $seoTitle = $seo ? $seo->title : (@$allsettings['app_title'] ?? 'خيرات الأنعام');
        $seoDesc = $seo ? $seo->description : 'متجر خيرات الأنعام لللحوم الطازجة والمبردة بأسعار تنافسية.';
        $seoKeys = $seo ? $seo->keywords : 'لحوم, طازجة, عمان, غنم, بقر';
    @endphp

    <title>@yield('title', $seoTitle)</title>
    <meta name="description" content="@yield('meta_description', $seoDesc)">
    <meta name="keywords" content="@yield('meta_keywords', $seoKeys)">
    <link rel="icon" href="{{ asset($siteFavicon) }}">


    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('v2_assets/css/style.css') }}">

    @stack('styles')
    <script>
        function toggleWishlist(id, btn) {
            fetch('{{ route('wishlist.add') }}?product_id=' + id, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if(data.status == 1) {
                    toastr.success(data.message);
                    btn.querySelector('i').classList.remove('far');
                    btn.querySelector('i').classList.add('fas');
                    btn.querySelector('i').style.color = 'var(--primary-color)';
                } else if(data.status == 0 && data.message == 'Login First') {
                    toastr.error('{{ app()->getLocale() == "en" ? "You must login first!" : "يجب عليك تسجيل الدخول أولاً!" }}');
                } else {
                    toastr.info(data.message);
                    btn.querySelector('i').classList.remove('far');
                    btn.querySelector('i').classList.add('fas');
                    btn.querySelector('i').style.color = 'var(--primary-color)';
                }
            })
            .catch(error => {
                toastr.error('حدث خطأ، يرجى المحاولة مرة أخرى');
            });
        }
    </script>
</head>

<body>
    @php
        $promoAr = @$allsettings['top_bar_announcement_ar'] ?: '🔥 عرض خاص: شحن مجاني للطلبات فوق 50 ريال 🔥 أفكار مبيعا: تشكيلة لحوم الضأن الأسترالي المبرد 🔥 عروض ترويجية وتخفيضات كبرى';
        $promoEn = @$allsettings['top_bar_announcement_en'] ?: '🔥 Special Offer: Free Shipping on Orders Over 50 OMR 🔥 Best Sellers: Chilled Australian Lamb Selection 🔥 Mega Promotions & Discounts';
        $promoText = app()->getLocale() == 'ar' ? $promoAr : $promoEn;
    @endphp
    <!-- Top Announcement Bar -->
    <div class="top-bar" style="overflow: hidden; white-space: nowrap; position: relative; display: flex;">
        <div class="ticker-wrapper" style="display: flex; flex-shrink: 0; animation: ticker-{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }} 45s linear infinite;">
            <div style="padding: 0 30px; display: inline-block;">{{ $promoText }}</div>
            <div style="padding: 0 30px; display: inline-block;">{{ $promoText }}</div>
            <div style="padding: 0 30px; display: inline-block;">{{ $promoText }}</div>
            <div style="padding: 0 30px; display: inline-block;">{{ $promoText }}</div>
            <div style="padding: 0 30px; display: inline-block;">{{ $promoText }}</div>
        </div>
        <div class="ticker-wrapper" style="display: flex; flex-shrink: 0; animation: ticker-{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }} 45s linear infinite;">
            <div style="padding: 0 30px; display: inline-block;">{{ $promoText }}</div>
            <div style="padding: 0 30px; display: inline-block;">{{ $promoText }}</div>
            <div style="padding: 0 30px; display: inline-block;">{{ $promoText }}</div>
            <div style="padding: 0 30px; display: inline-block;">{{ $promoText }}</div>
            <div style="padding: 0 30px; display: inline-block;">{{ $promoText }}</div>
        </div>
    </div>

    <style>
        @keyframes ticker-ltr {
            0% { transform: translateX(0); }
            100% { transform: translateX(-100%); }
        }
        @keyframes ticker-rtl {
            0% { transform: translateX(0); }
            100% { transform: translateX(100%); }
        }
        .top-bar:hover .ticker-wrapper {
            animation-play-state: paused;
        }
    </style>

    <!-- Main Header -->
    <header class="v2-header">
        <div class="v2-container header-inner">
                        <!-- Mobile Hamburger Menu -->
            <div class="mobile-hamburger" onclick="toggleMobileNav()">
                <i class="fas fa-bars"></i>
            </div>
            <!-- Logo -->
            <div class="logo">
                <a href="{{ route('front') }}">
                    <img src="{{ asset($siteLogo) }}" alt="Khairat Al An'aam" style="max-height: 50px;">
                </a>
            </div>

            <!-- Delivery Location -->
            <div class="header-location">
                <i class="fas fa-map-marker-alt"></i>
                <div class="location-text">
                    <span class="light">@lang('v2_layout.deliver_to') <i></i></span>
                    <strong>@lang('v2_layout.muscat_oman')</strong>
                </div>
            </div>

                        <!-- Mobile Nav Icons (Heart/Cart) -->
            <div class="mobile-nav-icons">
                <a href="{{ route('front.v2.favorites') }}" class="action-btn" style="position: relative;">
                    <i class="far fa-heart"></i>
                    @php $wishlistCount = Auth::check() ? \App\Models\Front\Wishlist::where('User_Id', auth()->id())->count() : 0; @endphp
                    <span class="fav-badge badge" style="position: absolute; top: -5px; right: -8px; background: var(--primary-color); color: #fff; border-radius: 50%; padding: 2px 5px; font-size: 10px; font-weight: bold; {{ $wishlistCount == 0 ? 'display: none;' : '' }}">{{ $wishlistCount }}</span>
                </a>
                <a href="{{ route('front.cart') }}" class="action-btn has-badge" style="position: relative;">
                    <i class="fas fa-shopping-cart"></i>
                    @php $cartCount = \Cart::count(); @endphp
                    <span class="cart-badge badge" style="position: absolute; top: -5px; right: -8px; background: var(--primary-color); color: #fff; border-radius: 50%; padding: 2px 5px; font-size: 10px; font-weight: bold; {{ $cartCount == 0 ? 'display: none;' : '' }}">{{ $cartCount }}</span>
                </a>
            </div>

            <!-- Search Bar -->
            <div class="header-search">
                <form action="{{ route('front.store') }}" method="GET">
                    <input type="text" name="q" placeholder="@lang('v2_layout.search_placeholder')" value="{{ request('q') }}" id="main-search-input" autocomplete="off">
                    <button type="submit" style="z-index: 10; pointer-events: auto; cursor: pointer; height: 100%; {{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? 'right: 15px; left: auto;' : 'left: 15px; right: auto;' }}"><i class="fas fa-search"></i></button>
                </form>
                <div class="search-dropdown-results" id="search-results-container"></div>
            </div>

            <!-- Language Selector -->
            <div class="header-lang" style="position: relative; display: inline-block;">
                <div onclick="document.getElementById('lang-dropdown').classList.toggle('show-lang')" style="cursor: pointer; display: flex; align-items: center; gap: 5px;">
                    <i class="fas fa-globe"></i> 
                    {{ app()->getLocale() == 'fr' ? 'العربية' : 'English' }} 
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div id="lang-dropdown" style="display: none; position: absolute; top: 100%; right: 0; background: #fff; border: 1px solid var(--border-color); border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1); z-index: 100; min-width: 120px;">
                    <a href="{{ route('locale.switch', 'fr') }}" style="display: block; padding: 10px 15px; color: var(--text-color); text-decoration: none; border-bottom: 1px solid var(--border-color); {{ app()->getLocale() == 'fr' ? 'background: #f5f5f5;' : '' }}">العربية</a>
                    <a href="{{ route('locale.switch', 'en') }}" style="display: block; padding: 10px 15px; color: var(--text-color); text-decoration: none; {{ app()->getLocale() == 'en' ? 'background: #f5f5f5;' : '' }}">English</a>
                </div>
                <script>
                    // Simple inline toggle script
                    window.onclick = function(event) {
                        if (!event.target.closest('.header-lang')) {
                            var dropdowns = document.getElementsByClassName("show-lang");
                            var i;
                            for (i = 0; i < dropdowns.length; i++) {
                                var openDropdown = dropdowns[i];
                                if (openDropdown.style.display === 'block') {
                                    openDropdown.style.display = 'none';
                                }
                            }
                        }
                    }
                    document.querySelector('.header-lang > div').addEventListener('click', function(e) {
                        var dropdown = document.getElementById('lang-dropdown');
                        dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
                    });
                </script>
            </div>

            <!-- Auth Buttons -->
            <div class="header-auth">
                @auth
                    <div class="user-dropdown" style="position: relative; display: inline-block;">
                        <div onclick="const m = document.getElementById('user-menu'); m.style.display = m.style.display === 'none' ? 'block' : 'none'; m.classList.toggle('show-user-menu');" style="cursor: pointer; display: flex; align-items: center; gap: 8px; font-weight: 600; color: var(--text-color);">
                            <i class="far fa-user-circle" style="font-size: 20px; color: var(--primary-color);"></i>
                            <span class="d-none d-md-inline">{{ explode(' ', Auth::user()->name)[0] }}</span>
                            <i class="fas fa-chevron-down" style="font-size: 12px; color: var(--text-light);"></i>
                        </div>
                        <div id="user-menu" class="dropdown-content" style="display: none; position: absolute; top: 100%; right: 0; background: #fff; border: 1px solid var(--border-color); border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1); z-index: 100; min-width: 150px; margin-top: 10px;">
                            <a href="{{ route('user.profile') }}" style="display: block; padding: 10px 15px; color: var(--text-color); text-decoration: none; border-bottom: 1px solid var(--border-color);"><i class="far fa-user" style="margin-inline-end: 5px;"></i> @lang('v2_layout.my_account')</a>
                            <a href="{{ route('user.profile.orders') }}" style="display: block; padding: 10px 15px; color: var(--text-color); text-decoration: none; border-bottom: 1px solid var(--border-color);"><i class="fas fa-box" style="margin-inline-end: 5px;"></i> @lang('v2_layout.my_orders')</a>
                            <a href="{{ route('user.logout') }}" style="display: block; padding: 10px 15px; color: #c00; text-decoration: none;"><i class="fas fa-sign-out-alt" style="margin-inline-end: 5px;"></i> @lang('v2_layout.logout')</a>
                        </div>
                    </div>
                    <script>
                        window.addEventListener('click', function(e) {
                            if (!e.target.closest('.user-dropdown')) {
                                const menu = document.getElementById('user-menu');
                                if (menu && menu.classList.contains('show-user-menu')) {
                                    menu.classList.remove('show-user-menu');
                                    menu.style.display = 'none';
                                }
                            }
                        });
                    </script>
                @else
                    <a href="{{ route('login') }}" class="btn-primary">@lang('v2_layout.login')</a>
                    <a href="{{ route('user.sign.up') }}" class="btn-outline">@lang('v2_layout.register')</a>
                @endauth
            </div>
        </div>

        <!-- Lower Nav Menu -->
        <div class="lower-nav">
            <div class="v2-container nav-inner">
                <ul class="nav-links">
                    <li><a href="{{ route('front') }}" class="{{ request()->routeIs('front') ? 'active' : '' }}">@lang('v2_layout.nav_home')</a></li>
                    <li class="nav-item-dropdown">
                        <a href="{{ route('front.store') }}" class="{{ request()->routeIs('front.store') ? 'active' : '' }}">@lang('v2_layout.nav_categories') <i class="fas fa-chevron-down"></i></a>
                        @php $navCategories = \App\Models\Admin\Category::where('Status', 1)->get(); @endphp
                        @if($navCategories->count() > 0)
                        <ul class="nav-dropdown-menu">
                            @foreach($navCategories as $cat)
                            <li>
                                <a href="{{ route('front.store', ['category[]' => $cat->id]) }}">
                                    {{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? $cat->fr_Category_Name : $cat->en_Category_Name }}
                                </a>
                            </li>
                            @endforeach
                        </ul>
                        @endif
                    </li>
                    <li><a href="{{ route('about.us') }}" class="{{ request()->routeIs('about.us') ? 'active' : '' }}">@lang('v2_layout.nav_about')</a></li>
                    <li><a href="{{ route('contact.us') }}" class="{{ request()->routeIs('contact.us') ? 'active' : '' }}">@lang('v2_layout.nav_contact')</a></li>
                </ul>
                <div class="nav-icons">
                    <a href="{{ route('front.v2.favorites') }}" class="action-btn" style="position: relative;">
                        <i class="far fa-heart"></i>
                        @php
                            $wishlistCount = Auth::check() ? \App\Models\Front\Wishlist::where('User_Id', auth()->id())->count() : 0;
                        @endphp
                        <span class="fav-badge badge" style="position: absolute; top: -5px; right: -8px; background: var(--primary-color); color: #fff; border-radius: 50%; padding: 2px 5px; font-size: 10px; font-weight: bold; {{ $wishlistCount == 0 ? 'display: none;' : '' }}">{{ $wishlistCount }}</span>
                    </a>
                    <a href="{{ route('front.cart') }}" class="action-btn has-badge" style="position: relative;">
                        <i class="fas fa-shopping-cart"></i>
                        @php
                            $cartCount = \Cart::count();
                        @endphp
                        <span class="cart-badge badge" style="position: absolute; top: -5px; right: -8px; background: var(--primary-color); color: #fff; border-radius: 50%; padding: 2px 5px; font-size: 10px; font-weight: bold; {{ $cartCount == 0 ? 'display: none;' : '' }}">{{ $cartCount }}</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

        <!-- Mobile Navigation Drawer -->
    <div class="mobile-nav-overlay" onclick="toggleMobileNav()"></div>
    <nav class="mobile-nav-drawer">
        <div class="mobile-nav-header">
            <img src="{{ asset($siteLogo) }}" alt="Logo" style="max-height: 40px;">
            <button onclick="toggleMobileNav()"><i class="fas fa-times"></i></button>
        </div>
        <div class="mobile-nav-content">
            <ul class="mobile-nav-links">
                <li><a href="{{ route('front') }}">@lang('v2_layout.nav_home')</a></li>
                <li class="mobile-nav-item-dropdown">
                    <a href="javascript:void(0)" onclick="$(this).next('.mobile-nav-dropdown-menu').slideToggle(); $(this).find('i').toggleClass('fa-chevron-down fa-chevron-up');">
                        @lang('v2_layout.nav_categories') <i class="fas fa-chevron-down"></i>
                    </a>
                    @php $navCategories = \App\Models\Admin\Category::where('Status', 1)->get(); @endphp
                    @if($navCategories->count() > 0)
                    <ul class="mobile-nav-dropdown-menu">
                        @foreach($navCategories as $cat)
                        <li>
                            <a href="{{ route('front.store', ['category[]' => $cat->id]) }}">
                                {{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? $cat->fr_Category_Name : $cat->en_Category_Name }}
                            </a>
                        </li>
                        @endforeach
                        <li><a href="{{ route('front.store') }}" style="color: var(--primary-color) !important; font-weight: bold !important;">@lang('v2_layout.nav_categories') (الكل)</a></li>
                    </ul>
                    @endif
                </li>
                <li><a href="{{ route('about.us') }}">@lang('v2_layout.nav_about')</a></li>
                <li><a href="{{ route('contact.us') }}">@lang('v2_layout.nav_contact')</a></li>
            </ul>
            <div style="border-top: 1px solid #eee; margin: 20px 0; padding-top: 20px;">
                <div style="margin-bottom: 15px;">
                    <a href="{{ route('login') }}" class="btn-primary" style="display: block; text-align: center; margin-bottom: 10px; padding: 10px;">@lang('v2_layout.login')</a>
                    <a href="{{ route('user.sign.up') }}" class="btn-outline" style="display: block; text-align: center; padding: 10px;">@lang('v2_layout.register')</a>
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="{{ route('locale.switch', 'fr') }}" style="flex: 1; text-align: center; padding: 10px; background: {{ app()->getLocale() == 'fr' ? '#f0f0f0' : '#fff' }}; border: 1px solid #eee; border-radius: 8px; text-decoration: none; color: #333;">العربية</a>
                    <a href="{{ route('locale.switch', 'en') }}" style="flex: 1; text-align: center; padding: 10px; background: {{ app()->getLocale() == 'en' ? '#f0f0f0' : '#fff' }}; border: 1px solid #eee; border-radius: 8px; text-decoration: none; color: #333;">English</a>
                </div>
            </div>
        </div>
    </nav>
    <script>
        function toggleMobileNav() {
            document.querySelector('.mobile-nav-drawer').classList.toggle('active');
            document.querySelector('.mobile-nav-overlay').classList.toggle('active');
        }
    </script>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <footer style="background-color: #212936; color: #fff; padding-top: 50px; margin-top: 60px;">
        <div class="v2-container">
            <!-- Newsletter Section -->
            <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 40px; margin-bottom: 40px; gap: 20px;">
                <div style="max-width: 500px;">
                    <h2 style="font-size: 24px; font-weight: 700; margin: 0 0 10px; color: #fff;">@lang('v2_layout.newsletter_title')</h2>
                    <p style="margin: 0; color: #a0aebc; font-size: 14px;">@lang('v2_layout.newsletter_desc')</p>
                </div>
                <form id="newsletter-form" style="display: flex; gap: 10px; flex: 1; max-width: 450px;">
                    @csrf
                    <input type="text" name="subscribe" required placeholder="@lang('v2_layout.newsletter_placeholder')" style="flex: 1; padding: 12px 15px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: transparent; color: #fff; outline: none; text-align: {{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? 'right' : 'left' }}; font-family: inherit;">
                    <button style="background: #e32636; color: #fff; border: none; padding: 12px 25px; border-radius: 8px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; font-family: inherit;">
                        <span class="btn-text">@lang('v2_layout.subscribe')</span> <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>

            <!-- Footer Links Section -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 30px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 40px; margin-bottom: 25px;">
                <!-- Column 1 (Logo & Info - spans more space on large screens) -->
                <div style="grid-column: span 2;">
                    <a href="{{ route('front') }}" style="display: inline-block; margin-bottom: 15px;">
                        <img src="{{ asset($footerLogo) }}" alt="Logo" style="height: 50px; filter: brightness(0) invert(1);">
                    </a>
                    <p style="color: #a0aebc; font-size: 13px; line-height: 1.8; margin-bottom: 20px; max-width: 400px;">@lang('v2_layout.footer_desc')</p>
                    <div style="display: flex; gap: 20px; font-size: 13px; color: #a0aebc; flex-wrap: wrap;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-map-marker-alt" style="color: #fff;"></i> @lang('v2_layout.muscat_oman')
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <i class="far fa-envelope" style="color: #fff;"></i> info@khairat-alanaam.com
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;" dir="ltr">
                            <i class="fas fa-phone-alt" style="color: #fff;"></i> +968 1234 5678
                        </div>
                    </div>
                </div>

                <!-- Column 2 -->
                <div>
                    <h3 style="color: #fff; font-size: 16px; font-weight: 700; margin: 0 0 20px;">@lang('v2_layout.about_company')</h3>
                    <ul style="list-style: none; padding: 0; margin: 0; font-size: 14px;">
                        <li style="margin-bottom: 12px;"><a href="{{ route('about.us') }}" style="color: #a0aebc; text-decoration: none; transition: 0.2s;">@lang('v2_layout.about_us')</a></li>
                        <li><a href="{{ route('contact.us') }}" style="color: #a0aebc; text-decoration: none; transition: 0.2s;">@lang('v2_layout.nav_contact')</a></li>
                    </ul>
                </div>

                <!-- Column 3 -->
                <div>
                    <h3 style="color: #fff; font-size: 16px; font-weight: 700; margin: 0 0 20px;">@lang('v2_layout.policies')</h3>
                    <ul style="list-style: none; padding: 0; margin: 0; font-size: 14px;">
                        <li style="margin-bottom: 12px;"><a href="{{ route('privacy.policy') }}" style="color: #a0aebc; text-decoration: none; transition: 0.2s;">@lang('v2_layout.privacy_policy')</a></li>
                        <li style="margin-bottom: 12px;"><a href="{{ route('terms.conditions') }}" style="color: #a0aebc; text-decoration: none; transition: 0.2s;">@lang('v2_layout.terms_service')</a></li>
                        <li style="margin-bottom: 12px;"><a href="#" style="color: #a0aebc; text-decoration: none; transition: 0.2s;">@lang('v2_layout.cookie_policy')</a></li>
                        <li><a href="#" style="color: #a0aebc; text-decoration: none; transition: 0.2s;">@lang('v2_layout.payment_security')</a></li>
                    </ul>
                </div>

                <!-- Column 4 -->
                <div>
                    <h3 style="color: #fff; font-size: 16px; font-weight: 700; margin: 0 0 20px;">@lang('v2_layout.customer_service')</h3>
                    <ul style="list-style: none; padding: 0; margin: 0; font-size: 14px;">
                        <li style="margin-bottom: 12px;"><a href="{{ route('faq') }}" style="color: #a0aebc; text-decoration: none; transition: 0.2s;">@lang('v2_layout.faq')</a></li>
                        <li style="margin-bottom: 12px;"><a href="{{ route('shipping.return') }}" style="color: #a0aebc; text-decoration: none; transition: 0.2s;">@lang('v2_layout.shipping_info')</a></li>
                        <li style="margin-bottom: 12px;"><a href="{{ route('refund.policy') }}" style="color: #a0aebc; text-decoration: none; transition: 0.2s;">@lang('v2_layout.refunds')</a></li>
                        <li><a href="#" style="color: #a0aebc; text-decoration: none; transition: 0.2s;">@lang('v2_layout.track_order')</a></li>
                    </ul>
                </div>
            </div>

            <!-- Copyright & Social Section -->
            <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; padding-bottom: 25px; font-size: 13px; color: #a0aebc; gap: 20px;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <span>@lang('v2_layout.payment_methods')</span>
                    <div style="display: flex; gap: 8px;">
                        <!-- Payment Icons -->
                        <div style="width: 35px; height: 22px; background: #fff; border-radius: 3px; display: flex; align-items: center; justify-content: center;"><i class="fab fa-cc-mastercard" style="color: #eb001b; font-size: 16px;"></i></div>
                        <div style="width: 35px; height: 22px; background: #fff; border-radius: 3px; display: flex; align-items: center; justify-content: center;"><i class="fab fa-cc-visa" style="color: #1a1f71; font-size: 16px;"></i></div>
                        <div style="width: 35px; height: 22px; background: #fff; border-radius: 3px; display: flex; align-items: center; justify-content: center;"><i class="fab fa-cc-paypal" style="color: #003087; font-size: 16px;"></i></div>
                    </div>
                </div>
                <div style="text-align: center;">
                    &copy; {{ date('Y') }} @lang('v2_layout.copyright')
                </div>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <span>@lang('v2_layout.follow_us')</span>
                    <div style="display: flex; gap: 15px; font-size: 16px;">
                        <a href="#" style="color: #fff; transition: 0.2s;"><i class="fab fa-youtube"></i></a>
                        <a href="#" style="color: #fff; transition: 0.2s;"><i class="fab fa-twitter"></i></a>
                        <a href="#" style="color: #fff; transition: 0.2s;"><i class="fab fa-instagram"></i></a>
                        <a href="#" style="color: #fff; transition: 0.2s;"><i class="fab fa-facebook-f"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Global Success Modal -->
    <div id="cartSuccessModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(5px);">
        <div style="background: #fff; width: 90%; max-width: 400px; border-radius: 16px; padding: 30px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.2); position: relative; animation: slideUp 0.3s ease-out;">
            <button onclick="closeCartModal()" style="position: absolute; top: 15px; {{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? 'left' : 'right' }}: 15px; background: none; border: none; font-size: 20px; color: #999; cursor: pointer; transition: 0.2s;"><i class="fas fa-times"></i></button>
            
            <div style="width: 70px; height: 70px; background: #e8f5e9; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                <i class="fas fa-check" style="font-size: 30px; color: #4caf50;"></i>
            </div>
            
            <h3 style="margin: 0 0 10px; font-size: 20px; font-weight: 800; color: #333;">@lang('v2_home.added_to_cart_success')</h3>
            <p style="margin: 0 0 25px; color: var(--text-light); font-size: 15px; line-height: 1.5;">@lang('v2_home.added_to_cart_desc')</p>
            
            <div style="display: flex; gap: 10px; flex-direction: column;">
                <a href="{{ route('front.cart') }}" class="btn-primary" style="padding: 12px; border-radius: 8px; font-weight: 700; text-decoration: none; display: block; width: 100%;">@lang('v2_home.view_cart')</a>
                <button onclick="closeCartModal()" style="padding: 12px; background: #f9f9f9; color: #333; border: 1px solid #eee; border-radius: 8px; font-weight: 700; cursor: pointer; width: 100%; transition: 0.2s; font-family: inherit;">@lang('v2_home.continue_shopping')</button>
            </div>
        </div>
    </div>
    <style>
        @keyframes slideUp { from { transform: translateY(30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    </style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        function openCartModal(cartCount) {
            if(cartCount !== undefined) {
                // Update badge if you have a class for it
                $('.cart-badge').text(cartCount).show(); 
            }
            document.getElementById('cartSuccessModal').style.display = 'flex';
        }
        
        function closeCartModal() {
            document.getElementById('cartSuccessModal').style.display = 'none';
        }
        
        function addToCart(productId, price) {
            $.ajax({
                url: "{{ route('add.to.cart') }}",
                type: "POST",
                data: {
                    product_id: productId,
                    quantity: 1,
                    price: price,
                    _token: "{{ csrf_token() }}"
                },
                success: function(data) {
                    openCartModal(data[0]);
                },
                error: function(xhr) {
                    if(xhr.responseJSON && xhr.responseJSON.error) {
                        alert(xhr.responseJSON.error);
                    } else {
                        alert("{{ __('v2_home.error_try_again') }}");
                    }
                }
            });
        }
    </script>
    <script src="{{ asset('v2_assets/js/main.js') }}"></script>
    @stack('scripts')
    <script>
        $(document).ready(function() {
            let searchTimer;
            const searchInput = $('#main-search-input');
            const searchResults = $('#search-results-container');
            
            // Hide dropdown when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.header-search').length) {
                    searchResults.removeClass('active');
                }
            });

            searchInput.on('focus', function() {
                if(searchInput.val().length >= 2 && searchResults.html().trim() !== '') {
                    searchResults.addClass('active');
                }
            });

            searchInput.on('keyup', function() {
                const query = $(this).val();
                
                clearTimeout(searchTimer);
                
                if (query.length < 2) {
                    searchResults.removeClass('active');
                    return;
                }
                
                searchResults.html('<div class="search-loading"><i class="fas fa-spinner fa-spin"></i></div>').addClass('active');
                
                searchTimer = setTimeout(function() {
                    $.ajax({
                        url: "{{ route('search.suggest') }}",
                        type: "GET",
                        data: { query: query },
                        success: function(response) {
                            searchResults.empty();
                            
                            if (response.length > 0) {
                                response.forEach(function(product) {
                                    const locale = "{{ app()->getLocale() }}";
                                    const productName = (locale === 'ar' || locale === 'fr') ? product.fr_Product_Name : product.en_Product_Name;
                                    const productUrl = "{{ url('product') }}/" + product.en_Product_Slug;
                                    
                                    // Use the image URL provided by the backend, which is already standardized using asset()
                                    let imgUrl = product.Primary_Image ? product.Primary_Image : "{{ asset('new-design/images/special-offer.png') }}";
                                    
                                    const html = `
                                        <a href="${productUrl}" class="search-result-item">
                                            <img src="${imgUrl}" class="search-result-img" alt="${productName}">
                                            <div class="search-result-info">
                                                <h4 class="search-result-title">${productName}</h4>
                                            </div>
                                        </a>
                                    `;
                                    searchResults.append(html);
                                });
                            } else {
                                searchResults.html('<div class="search-no-results">لا توجد نتائج مطابقة</div>');
                            }
                        },
                        error: function() {
                            searchResults.removeClass('active');
                        }
                    });
                }, 400); // debounce
            });

            $('#newsletter-form').on('submit', function(e) {
                e.preventDefault();
                let form = $(this);
                let btn = form.find('button[type="submit"]');
                let icon = btn.find('i');
                let originalIcon = icon.attr('class');
                
                btn.prop('disabled', true);
                icon.attr('class', 'fas fa-spinner fa-spin');
                
                $.ajax({
                    url: "{{ route('subscribe') }}",
                    type: "POST",
                    data: form.serialize(),
                    success: function(response) {
                        toastr.success(response.message || "{{ app()->getLocale() == 'ar' ? 'تم الاشتراك بنجاح!' : 'Subscription successful!' }}");
                        form[0].reset();
                        btn.prop('disabled', false);
                        icon.attr('class', originalIcon);
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false);
                        icon.attr('class', originalIcon);
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            let errors = xhr.responseJSON.errors;
                            for (let key in errors) {
                                toastr.error(errors[key][0]);
                            }
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            toastr.error(xhr.responseJSON.message);
                        } else {
                            toastr.error("{{ app()->getLocale() == 'ar' ? 'حدث خطأ ما' : 'Something went wrong' }}");
                        }
                    }
                });
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @if(session('order_success_modal'))
        <script>
            Swal.fire({
                title: '{!! session("order_success_modal")["line1"] !!}',
                html: '{!! session("order_success_modal")["line2"] !!}<br><br><b>رقم الطلب: {{ session("order_success_modal")["order_number"] }}</b>',
                icon: 'success',
                confirmButtonColor: '#D92624',
                confirmButtonText: '{{ app()->getLocale() == "ar" ? "موافق" : "OK" }}'
            });
        </script>
    @endif
    @if(session('success'))
        <script>
            toastr.success("{!! session('success') !!}");
        </script>
    @endif
    @if(session('error'))
        <script>
            toastr.error("{!! session('error') !!}");
        </script>
    @endif

    @yield('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>

</html>