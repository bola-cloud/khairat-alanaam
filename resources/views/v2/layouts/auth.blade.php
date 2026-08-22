<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'en' ? 'ltr' : 'rtl' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'خيرات الأنعام')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('v2_assets/css/style.css') }}">
    
    @php
        $siteFavicon = isset($allsettings['favicon']) ? asset(IMG_FAVICON_PATH . $allsettings['favicon']) : asset('assets/images/favicon.png');
        $siteLogo = isset($allsettings['main_logo']) ? asset(IMG_LOGO_PATH . $allsettings['main_logo']) : asset('assets/images/logo.png');
    @endphp
    <link rel="icon" href="{{ $siteFavicon }}">
</head>

<body>
    <!-- Auth Header (Minimal) -->
    <header style="background:var(--white); padding: 15px 0; border-bottom: 1px solid var(--border-color);">
        <div class="v2-container" style="display: flex; justify-content: space-between; align-items: center;">
            <!-- Actions (Left) -->
            <div style="display: flex; gap: 15px; align-items: center;">
                <div class="header-lang" style="position: relative; display: inline-block;">
                    <div onclick="document.getElementById('lang-dropdown-auth').classList.toggle('show-lang')" style="cursor: pointer; display: flex; align-items: center; gap: 5px; border: 1px solid var(--border-color); padding: 5px 10px; border-radius: 8px;">
                        <i class="fas fa-globe"></i> 
                        {{ app()->getLocale() == 'fr' ? 'العربية' : 'English' }} 
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div id="lang-dropdown-auth" style="display: none; position: absolute; top: 100%; right: 0; background: #fff; border: 1px solid var(--border-color); border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1); z-index: 100; min-width: 120px;">
                        <a href="{{ route('locale.switch', 'fr') }}" style="display: block; padding: 10px 15px; color: var(--text-color); text-decoration: none; border-bottom: 1px solid var(--border-color); {{ app()->getLocale() == 'fr' ? 'background: #f5f5f5;' : '' }}">العربية</a>
                        <a href="{{ route('locale.switch', 'en') }}" style="display: block; padding: 10px 15px; color: var(--text-color); text-decoration: none; {{ app()->getLocale() == 'en' ? 'background: #f5f5f5;' : '' }}">English</a>
                    </div>
                    <script>
                        window.addEventListener('click', function(event) {
                            if (!event.target.closest('.header-lang')) {
                                var dropdowns = document.getElementsByClassName("show-lang");
                                for (var i = 0; i < dropdowns.length; i++) {
                                    dropdowns[i].classList.remove('show-lang');
                                    dropdowns[i].style.display = 'none';
                                }
                            }
                        });
                        document.querySelector('.header-lang > div').addEventListener('click', function(e) {
                            var dropdown = document.getElementById('lang-dropdown-auth');
                            dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
                            dropdown.classList.add('show-lang');
                        });
                    </script>
                </div>
                @if(!Route::is('admin.login'))
                <div class="header-auth">
                    <a href="{{ route('login') }}" class="btn-primary" style="padding: 6px 20px;">{{ __('v2_auth.login_btn') }}</a>
                    <a href="{{ route('user.sign.up') }}" class="btn-outline"
                        style="padding: 6px 20px; background: #f5f5f5; color: var(--text-color); border:none;">{{ __('v2_auth.register_now') }}</a>
                </div>
                @endif
            </div>

            <!-- Logo (Right) -->
            <div class="logo">
                <a href="{{ route('front') }}">
                    <img src="{{ $siteLogo }}" alt="Khairat Al An'aam" style="height: 50px;">
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main style="background: var(--bg-color); min-height: calc(100vh - 81px);">
        @yield('content')
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('v2_assets/js/main.js') }}"></script>
</body>

</html>