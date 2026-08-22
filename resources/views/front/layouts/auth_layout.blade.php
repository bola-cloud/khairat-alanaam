<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() != 'en' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'بن القطار | Al-Katar')</title>
    
    <!-- Google Fonts: Cairo for Arabic, Inter for English -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @if(app()->getLocale() != 'en')
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
        <style>body { font-family: 'Cairo', sans-serif; }</style>
    @else
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
        <style>body { font-family: 'Inter', sans-serif; }</style>
    @endif
    
    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="antialiased relative" style="
    background-color: #f4f7f6;
    min-height: 100vh;
">
    <!-- Language Switcher -->
    <div class="absolute top-6 {{ app()->getLocale() != 'en' ? 'left-6' : 'right-6' }} z-50">
        @if(app()->getLocale() != 'en')
            <a href="{{ route('locale.switch', 'en') }}" class="flex items-center gap-2 bg-gray-800/80 hover:bg-gray-900 backdrop-blur-md px-4 py-2 rounded-full text-white font-bold transition-all border border-gray-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path></svg>
                English
            </a>
        @else
            <a href="{{ route('locale.switch', 'fr') }}" class="flex items-center gap-2 bg-gray-800/80 hover:bg-gray-900 backdrop-blur-md px-4 py-2 rounded-full text-white font-bold transition-all border border-gray-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path></svg>
                العربية
            </a>
        @endif
    </div>

    <main class="min-h-screen flex items-center justify-center p-4 lg:p-8 relative z-10">
        <div class="w-full flex items-center justify-center">
            @yield('content')
        </div>
    </main>
</body>
</html>
