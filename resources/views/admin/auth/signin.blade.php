@extends('v2.layouts.auth')

@section('title', 'تسجيل دخول المشرف - خيرات الأنعام')

@section('content')
    <section class="auth-section" style="padding: 50px 0;">
        <div class="v2-container">
            <!-- Logo and Heading inside the page itself -->
            <div style="text-align: center; margin-bottom: 30px;">
                <img src="{{ isset($allsettings['main_logo']) ? asset(IMG_LOGO_PATH . $allsettings['main_logo']) : asset('assets/images/logo.png') }}" alt="Khairat" style="height: 60px; margin-bottom:15px;">
                <h2 style="font-weight: 800; font-size: 24px; color: var(--text-color); margin: 0 0 5px;">{{ __('Admin Sign In') }}</h2>
                <p style="color: var(--text-light); margin: 0;">{{ __('Admin Panel Access') }}</p>
            </div>

            <div style="max-width: 450px; margin: 0 auto; background: var(--white); padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); text-align: right;">

                @if(session('error'))
                    <div style="background: #fee; color: #c00; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                        {{ session('error') }}
                    </div>
                @endif
                @if(session('success'))
                    <div style="background: #e6f9ed; color: #1a4231; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST">
                    @csrf
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size:14px; color: var(--text-light);">{{ __('Email Address') }}</label>
                        <input type="email" name="email" value="{{ env('APP_DEMO') == true ? 'admin@gmail.com' : old('email') }}" required placeholder="{{ __('Email Address') }}"
                            style="width: 100%; padding: 12px 15px; border: 1px solid var(--border-color); border-radius: 8px; font-family: inherit; font-size: 14px; box-sizing: border-box;">
                        @error('email')
                            <span style="color: #c00; font-size: 12px; display: block; margin-top: 5px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div style="margin-bottom: 10px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size:14px; color: var(--text-light);">{{ __('Password') }}</label>
                        <div style="position: relative;">
                            <input type="password" id="password" name="password" value="{{ env('APP_DEMO') == true ? '123456' : '' }}" required placeholder="••••••••"
                                style="width: 100%; padding: 12px 15px; border: 1px solid var(--border-color); border-radius: 8px; font-family: inherit; font-size: 14px; box-sizing: border-box;">
                            <i class="fas fa-eye-slash" onclick="togglePassword()"
                                style="position: absolute; left: 15px; top: 15px; color: var(--text-light); cursor:pointer;"></i>
                        </div>
                        @error('password')
                            <span style="color: #c00; font-size: 12px; display: block; margin-top: 5px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; font-size: 14px;">
                        <label style="display: flex; align-items: center; gap: 5px; color: var(--text-light);">
                            <input type="checkbox" name="remember"> {{ __('Remember me') }}
                        </label>
                    </div>

                    <button type="submit" class="btn-primary"
                        style="width: 100%; padding: 15px; font-size: 16px; margin-bottom: 15px;">{{ __('Sign In') }}</button>

                </form>

            </div>
        </div>
    </section>

    <script>
        function togglePassword(){
            var p = document.getElementById('password');
            if(p.type === 'password'){ 
                p.type = 'text'; 
            } else { 
                p.type = 'password'; 
            }
        }
    </script>
@endsection
