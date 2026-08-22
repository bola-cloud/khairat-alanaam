@php
    $isRtl = app()->getLocale() != 'en';
    $dir = $isRtl ? 'rtl' : 'ltr';
    $searchText = $isRtl ? 'ابحث هنا' : 'Search here';
@endphp

<!-- Store Header -->
<header class="relative z-[1001] shadow-sm py-4 lg:py-6" dir="{{ $dir }}" style="background-image: url('{{ asset('assets/elketar/Section - Categories Showcase.png') }}'); background-size: cover; background-position: center;background-blend-mode: overlay;" x-data="{ mobileMenu: false }">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="flex items-center justify-between gap-4">
            
            <!-- Right: Logo (in RTL) -->
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('front.store') }}" class="flex items-center">
                    <img src="{{ isset($allsettings['main_logo']) ? asset(IMG_LOGO_PATH . $allsettings['main_logo']) : asset('assets/elketar/logo.png') }}" alt="Logo" class="h-10 lg:h-14 object-contain">
                </a>
            </div>

            <!-- Center: Navigation Links (Hidden on Mobile) -->
            <nav class="hidden lg:flex items-center gap-8 text-[#1A4231] font-bold text-base" style="font-family: 'Cairo', sans-serif;">
                <a href="{{ route('front.store') }}" class="hover:opacity-85 transition-opacity">{{ $isRtl ? 'الرئيسية' : 'Home' }}</a>
                <a href="{{ route('coffee.crops') }}" class="hover:opacity-85 transition-opacity">{{ $isRtl ? 'المحاصيل' : 'Crops' }}</a>
                <a href="{{ route('technical.tools') }}" class="hover:opacity-85 transition-opacity">{{ $isRtl ? 'معدات التحضير' : 'Brewing Equipment' }}</a>
                <a href="{{ route('trial.boxes') }}" class="hover:opacity-85 transition-opacity">{{ $isRtl ? 'بوكسات التجربة' : 'Experience Boxes' }}</a>
                <a href="{{ route('custom.box') }}" class="hover:opacity-85 transition-opacity">{{ $isRtl ? 'البوكس المخصص' : 'Custom Box' }}</a>
            </nav>

            <!-- Left: Actions & Buttons (in RTL) -->
            <div class="flex items-center gap-3 lg:gap-5 shrink-0">
                
                <!-- Search Icon -->
                <button class="text-[#1A4231] hover:opacity-80 transition-opacity p-1.5 rounded-full hover:bg-white/10 hidden md:block">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>

                <!-- Profile Icon -->
                <a href="{{ auth()->check() ? route('user.profile') : route('login') }}" class="text-[#1A4231] hover:opacity-80 transition-opacity p-1.5 rounded-full hover:bg-white/10 hidden md:block">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </a>

                <!-- Cart Icon with Badge -->
                <a href="{{ route('front.cart') }}" class="relative text-[#1A4231] hover:opacity-80 transition-opacity p-1.5 rounded-full hover:bg-white/10 flex items-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <!-- Cart count badge -->
                    <span class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-[#1A4231] text-white text-[9px] font-bold rounded-full flex items-center justify-center totalCountItem">
                        {{ Cart::count() }}
                    </span>
                </a>

                <!-- Login Button -->
                @auth
                    <a href="{{ route('user.profile') }}" class="hidden sm:inline-block bg-[#1A4231] text-white px-5 lg:px-7 py-2 lg:py-2.5 rounded-full text-sm font-extrabold hover:bg-[#235841] transition-all whitespace-nowrap" style="font-family: 'Cairo', sans-serif;">
                        {{ $isRtl ? 'الملف الشخصي' : 'Profile' }}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="hidden sm:inline-block bg-[#1A4231] text-white px-5 lg:px-7 py-2 lg:py-2.5 rounded-full text-sm font-extrabold hover:bg-[#235841] transition-all whitespace-nowrap" style="font-family: 'Cairo', sans-serif;">
                        {{ $isRtl ? 'تسجيل الدخول' : 'Login' }}
                    </a>
                @endauth

                <!-- Qatar Community Pill Button -->
                <a href="{{ route('front') }}" class="hidden sm:inline-block bg-white text-[#1A4231] border border-[#1A4231] px-5 lg:px-7 py-2 lg:py-2.5 rounded-full text-sm font-extrabold hover:bg-gray-50 transition-all whitespace-nowrap" style="font-family: 'Cairo', sans-serif;">
                    {{ $isRtl ? 'مجتمع القطار' : 'Qatar Community' }}
                </a>

                <!-- Mobile Hamburger Button -->
                <button @click="mobileMenu = !mobileMenu" class="lg:hidden p-1.5 text-[#1A4231] focus:outline-none">
                    <svg x-show="!mobileMenu" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16m-7 6h7"/>
                    </svg>
                    <svg x-show="mobileMenu" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

            </div>

        </div>

        <!-- Mobile Menu Slider -->
        <div x-show="mobileMenu" x-transition class="lg:hidden mt-4 pb-4 border-t border-gray-200/50">
            <div class="mt-4 flex flex-col gap-4 text-start font-bold text-sm px-2">
                <a href="{{ route('front.store') }}" class="text-[#1A4231] py-3 border-b border-gray-200/30 flex items-center justify-between">
                    <span>{{ $isRtl ? 'الرئيسية' : 'Home' }}</span>
                </a>
                <a href="{{ route('coffee.crops') }}" class="text-[#1A4231] py-3 border-b border-gray-200/30 flex items-center justify-between">
                    <span>{{ $isRtl ? 'المحاصيل' : 'Crops' }}</span>
                </a>
                <a href="{{ route('technical.tools') }}" class="text-[#1A4231] py-3 border-b border-gray-200/30 flex items-center justify-between">
                    <span>{{ $isRtl ? 'معدات التحضير' : 'Brewing Equipment' }}</span>
                </a>
                <a href="{{ route('trial.boxes') }}" class="text-[#1A4231] py-3 border-b border-gray-200/30 flex items-center justify-between">
                    <span>{{ $isRtl ? 'بوكسات التجربة' : 'Experience Boxes' }}</span>
                </a>
                <a href="{{ route('custom.box') }}" class="text-[#1A4231] py-3 border-b border-gray-200/30 flex items-center justify-between">
                    <span>{{ $isRtl ? 'البوكس المخصص' : 'Custom Box' }}</span>
                </a>

                <!-- Mobile Actions -->
                <div class="flex flex-col gap-3 mt-4">
                    @auth
                        <a href="{{ route('user.profile') }}" class="w-full text-center bg-[#1A4231] text-white py-3 rounded-full font-bold">
                            {{ $isRtl ? 'الملف الشخصي' : 'Profile' }}
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="w-full text-center bg-[#1A4231] text-white py-3 rounded-full font-bold">
                            {{ $isRtl ? 'تسجيل الدخول' : 'Login' }}
                        </a>
                    @endauth
                    <a href="{{ route('front') }}" class="w-full text-center bg-white text-[#1A4231] border border-[#1A4231] py-3 rounded-full font-bold">
                        {{ $isRtl ? 'مجتمع القطار' : 'Qatar Community' }}
                    </a>
                </div>
            </div>
        </div>

    </div>
</header>
