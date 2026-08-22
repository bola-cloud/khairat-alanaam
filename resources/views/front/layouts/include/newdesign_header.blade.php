@php
    $isRtl = app()->getLocale() != 'en';
    $dir = $isRtl ? 'rtl' : 'ltr';
    $searchText = $isRtl ? 'ابحث هنا' : 'Search here';
@endphp

<style>
    .desktop-store-btn {
        display: none !important;
    }
    @media (min-width: 768px) {
        .desktop-store-btn {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
    }
</style>

<!-- Top Header (Categories Showcase Background Image) -->
<div class="relative z-[1001] py-3 lg:py-4 shadow-sm" dir="{{ $dir }}" style="background-image: url('{{ asset('assets/elketar/Section - Categories Showcase.png') }}'); background-size: cover; background-position: center; background-blend-mode: overlay;" x-data="{ mobileMenu: false }">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between gap-4">
            
            <!-- Right/Left: Search Bar (Hidden on Mobile, shown in menu) -->
            <div class="hidden lg:flex w-1/3 justify-start">
                <div class="relative w-full max-w-xs">
                    <input type="text" placeholder="{{ $searchText }}" class="w-full bg-white border border-gray-200 rounded-full py-2 px-10 text-sm focus:outline-none focus:ring-1 focus:ring-[#1A4231]">
                    <svg class="w-4 h-4 absolute {{ $isRtl ? 'right-4' : 'left-4' }} top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>
            
            <!-- Center: Logo -->
            <div class="w-1/2 lg:w-1/3 flex justify-start lg:justify-center">
                <a href="{{ route('front') }}">
                    <img src="{{ isset($allsettings['main_logo']) ? asset(IMG_LOGO_PATH . $allsettings['main_logo']) : asset('assets/elketar/logo.png') }}" alt="Logo" class="h-10 lg:h-14 object-contain">
                </a>
            </div>
            
            <!-- Left/Right: Actions & Hamburger -->
            <div class="w-1/2 lg:w-1/3 flex justify-end items-center gap-2 lg:gap-3">
                <div class="hidden sm:flex gap-2 items-center">
                    <!-- Language Switcher Button -->
                    @if($isRtl)
                        <a href="{{ route('locale.switch', 'en') }}" class="bg-white text-gray-700 border border-gray-300 px-4 py-2 rounded-full text-[12px] lg:text-sm font-bold hover:bg-gray-50 transition-colors whitespace-nowrap">English</a>
                    @else
                        <a href="{{ route('locale.switch', 'fr') }}" class="bg-white text-gray-700 border border-gray-300 px-4 py-2 rounded-full text-[12px] lg:text-sm font-bold hover:bg-gray-50 transition-colors whitespace-nowrap">العربية</a>
                    @endif

                    @if(auth()->check())
                        <!-- Logged In User Dropdown -->
                        <div class="relative" x-data="{ userMenu: false }">
                            <button @click="userMenu = !userMenu" @click.away="userMenu = false" class="bg-[#1A4231] text-white px-4 lg:px-6 py-2 rounded-full text-[12px] lg:text-sm font-bold hover:opacity-90 transition-opacity whitespace-nowrap flex items-center gap-2">
                                @if(auth()->user()->image)
                                    <img src="{{ str_starts_with(auth()->user()->image, 'http') ? auth()->user()->image : asset('uploaded_files/admin_profile/' . auth()->user()->image) }}" class="w-6 h-6 rounded-full object-cover border border-white/20">
                                @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                @endif
                                <span>{{ auth()->user()->name }}</span>
                                <svg class="w-3 h-3 transition-transform" :class="userMenu ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <!-- Dropdown Menu -->
                            <div x-show="userMenu" 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute {{ $isRtl ? 'left-0' : 'right-0' }} mt-2 w-48 bg-white border border-gray-100 rounded-2xl shadow-xl py-2 z-50 {{ $isRtl ? 'text-right' : 'text-left' }}" style="display: none;">
                                <a href="{{ route('user.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-[#1A4231] font-semibold transition-colors flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    <span>{{ $isRtl ? 'حسابي' : 'My Profile' }}</span>
                                </a>
                                <div class="border-t border-gray-100 my-1"></div>
                                <a href="{{ route('user.logout') }}" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 font-semibold transition-colors flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                    <span>{{ $isRtl ? 'تسجيل الخروج' : 'Logout' }}</span>
                                </a>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="bg-[#1A4231] text-white px-4 lg:px-8 py-2 rounded-full text-[12px] lg:text-sm font-bold hover:opacity-90 transition-opacity whitespace-nowrap">{{ __('new_design.menu.login') }}</a>
                    @endif
                    <a href="{{ route('front.store') }}" class="bg-white text-gray-700 border border-gray-300 px-4 lg:px-6 py-2 rounded-full text-[12px] lg:text-sm font-bold hover:bg-gray-50 hover:text-[#1A4231] transition-colors whitespace-nowrap desktop-store-btn">{{ __('new_design.menu.store') }}</a>
                </div>
                
                <!-- Mobile Menu Button -->
                <button @click="mobileMenu = !mobileMenu" class="lg:hidden p-2 text-[#1A4231]">
                    <svg x-show="!mobileMenu" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                    <svg x-show="mobileMenu" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu Overlay -->
        <div x-show="mobileMenu" x-transition class="lg:hidden mt-4 pb-4 border-t border-gray-200">
            <div class="mt-4 flex flex-col gap-4">
                <div class="relative w-full">
                    <input type="text" placeholder="{{ $searchText }}" class="w-full bg-white border border-gray-200 rounded-full py-2.5 px-10 text-sm">
                    <svg class="w-4 h-4 absolute {{ $isRtl ? 'right-4' : 'left-4' }} top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <nav class="flex flex-col gap-1 text-start max-h-[60vh] overflow-y-auto px-2">
                    <!-- Mobile Language Switcher -->
                    <a href="{{ route('locale.switch', $isRtl ? 'en' : 'fr') }}" class="text-[#387C5F] font-black py-4 border-b border-gray-100 flex items-center justify-between">
                        <span>{{ $isRtl ? 'English' : 'العربية' }}</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path></svg>
                    </a>
                    
                    <a href="{{ route('front') }}" class="text-[#1A4231] font-bold py-3 border-b border-gray-100">{{ __('new_design.menu.home') }}</a>
                    <a href="{{ route('front.store') }}" class="text-[#1A4231] font-bold py-3 border-b border-gray-100">{{ __('new_design.menu.store') }}</a>
                    <a href="{{ route('coffee.crops') }}" class="text-gray-600 font-bold py-3 border-b border-gray-100">{{ __('new_design.menu.coffee_crops') }}</a>
                    <a href="{{ route('technical.tools') }}" class="text-gray-600 font-bold py-3 border-b border-gray-100">{{ __('new_design.menu.technical_tools') }}</a>
                    <a href="{{ route('wholesale.orders') }}" class="text-gray-600 font-bold py-3 border-b border-gray-100">{{ __('new_design.menu.wholesale_orders') }}</a>
                    <a href="{{ route('trial.boxes') }}" class="text-gray-600 font-bold py-3 border-b border-gray-100">{{ __('new_design.menu.experience_boxes') }}</a>
                    <a href="{{ route('custom.box') }}" class="text-gray-600 font-bold py-3 border-b border-gray-100">{{ $isRtl ? 'البوكس المخصص' : 'Custom Box' }}</a>
                    <a href="{{ route('experts') }}" class="text-gray-600 font-bold py-3 border-b border-gray-100">{{ __('new_design.menu.experts') }}</a>
                    <a href="{{ route('social.responsibility') }}" class="text-gray-600 font-bold py-3 border-b border-gray-100">{{ __('new_design.menu.social_responsibility') }}</a>
                    <a href="{{ route('monthly.offers') }}" class="text-gray-600 font-bold py-3 border-b border-gray-100">{{ __('new_design.menu.monthly_offers') }}</a>
                    <a href="{{ route('become.partner') }}" class="text-gray-600 font-bold py-3 border-b border-gray-100">{{ __('new_design.menu.become_partner') }}</a>
                    <a href="{{ route('about.us') }}" class="text-gray-600 font-bold py-3 border-b border-gray-100">{{ __('new_design.menu.about_us') }}</a>
                    <a href="{{ route('gift.cards') }}" class="text-gray-600 font-bold py-3 border-b border-gray-100">{{ __('new_design.menu.gifts') }}</a>
                    <a href="{{ route('contact.us') }}" class="text-gray-600 font-bold py-3 border-b border-gray-100">{{ __('new_design.menu.contact_us') }}</a>
                    @if(auth()->check())
                        <div class="border-t border-gray-100 my-2"></div>
                        <div class="px-2 py-3">
                            <p class="text-[12px] font-bold text-gray-400 mb-2">{{ $isRtl ? 'مرحباً،' : 'Welcome,' }} {{ auth()->user()->name }}</p>
                            <div class="flex flex-col gap-2">
                                <a href="{{ route('user.profile') }}" class="text-[#1A4231] font-bold py-2 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    <span>{{ $isRtl ? 'حسابي' : 'My Profile' }}</span>
                                </a>
                                <a href="{{ route('user.logout') }}" class="text-red-600 font-bold py-2 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                    <span>{{ $isRtl ? 'تسجيل الخروج' : 'Logout' }}</span>
                                </a>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-[#1A4231] font-black py-4">{{ __('new_design.menu.login') }}</a>
                    @endif
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Secondary Navigation (Hidden on Mobile) -->
<style>
    .no-scrollbar::-webkit-scrollbar {
        display: none !important;
    }
</style>
<nav class="hidden lg:block py-3.5 sticky top-0 z-[1000] shadow-md w-full" dir="{{ $dir }}" style="background: linear-gradient(to right, #1A4231, #387C5F);">
    <div class="container mx-auto px-4">
        <ul class="flex items-center justify-start lg:justify-center gap-4 lg:gap-6 text-white font-medium overflow-x-auto no-scrollbar" style="font-family: 'Cairo', sans-serif; font-size: 18px; line-height: 27px; scrollbar-width: none; -ms-overflow-style: none; justify-content: safe center;">
            <li><a href="{{ route('front') }}" class="hover:text-white/80 transition-colors whitespace-nowrap">{{ __('new_design.menu.home') }}</a></li>
            <li><a href="{{ route('coffee.crops') }}" class="hover:text-white/80 transition-colors whitespace-nowrap">{{ __('new_design.menu.coffee_crops') }}</a></li>
            <li><a href="{{ route('technical.tools') }}" class="hover:text-white/80 transition-colors whitespace-nowrap">{{ __('new_design.menu.technical_tools') }}</a></li>
            <li><a href="{{ route('wholesale.orders') }}" class="hover:text-white/80 transition-colors whitespace-nowrap">{{ __('new_design.menu.wholesale_orders') }}</a></li>
            <li><a href="{{ route('trial.boxes') }}" class="hover:text-white/80 transition-colors whitespace-nowrap">{{ __('new_design.menu.experience_boxes') }}</a></li>
            <li><a href="{{ route('custom.box') }}" class="hover:text-white/80 transition-colors whitespace-nowrap">{{ $isRtl ? 'البوكس المخصص' : 'Custom Box' }}</a></li>
            <li><a href="{{ route('experts') }}" class="hover:text-white/80 transition-colors whitespace-nowrap">{{ __('new_design.menu.experts') }}</a></li>
            <li><a href="{{ route('social.responsibility') }}" class="hover:text-white/80 transition-colors whitespace-nowrap">{{ __('new_design.menu.social_responsibility') }}</a></li>
            <li><a href="{{ route('monthly.offers') }}" class="hover:text-white/80 transition-colors whitespace-nowrap">{{ __('new_design.menu.monthly_offers') }}</a></li>
            <li><a href="{{ route('become.partner') }}" class="hover:text-white/80 transition-colors whitespace-nowrap">{{ __('new_design.menu.become_partner') }}</a></li>
            <li><a href="{{ route('about.us') }}" class="hover:text-white/80 transition-colors whitespace-nowrap">{{ __('new_design.menu.about_us') }}</a></li>
            <li><a href="{{ route('gift.cards') }}" class="hover:text-white/80 transition-colors whitespace-nowrap">{{ __('new_design.menu.gifts') }}</a></li>
            <li><a href="{{ route('contact.us') }}" class="hover:text-white/80 transition-colors whitespace-nowrap">{{ __('new_design.menu.contact_us') }}</a></li>
        </ul>
    </div>
</nav>
