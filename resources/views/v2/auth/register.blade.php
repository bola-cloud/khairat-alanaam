@extends('v2.layouts.auth')

@php
    $siteLogo = isset($allsettings['main_logo']) ? asset(IMG_LOGO_PATH . $allsettings['main_logo']) : asset('assets/images/logo.png');
@endphp

@section('title', 'إنشاء حساب - خيرات الأنعام')

@section('content')
    <section class="auth-section" style="padding: 50px 0;">
        <div class="v2-container">
            <!-- Logo and Heading inside the page itself -->
            <div style="text-align: center; margin-bottom: 30px;">
                <img src="{{ $siteLogo }}" alt="Khairat" style="height: 60px; margin-bottom:15px; object-fit: contain;">
                <h2 style="font-weight: 800; font-size: 24px; color: var(--text-color); margin: 0 0 5px;">{{ __('v2_auth.register_title') }}</h2>
                <p style="color: var(--text-light); margin: 0;">{{ __('v2_auth.enter_details') }}</p>
            </div>

            <div
                style="max-width: 450px; margin: 0 auto; background: var(--white); padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); text-align: start;">

                @if(session('error'))
                    <div style="background: #fee; color: #c00; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div style="background: #fee; color: #c00; padding: 15px; border-radius: 5px; margin-bottom: 20px; font-size: 14px;">
                        <ul style="margin: 0; padding-inline-start: 20px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('user.sign.up.post') }}" method="POST">
                    @csrf
                    <div style="margin-bottom: 20px;">
                        <label
                            style="display: block; margin-bottom: 8px; font-weight: 600; font-size:14px; color: var(--text-light);">{{ __('v2_auth.name') }}</label>
                        <input type="text" name="name" required placeholder="{{ __('v2_auth.enter_name') }}"
                            style="width: 100%; padding: 12px 15px; border: 1px solid var(--border-color); border-radius: 8px; font-family: inherit; font-size: 14px; box-sizing: border-box;" value="{{ old('name') }}">
                        @error('name')
                            <div style="color: #c00; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div style="margin-bottom: 30px;">
                        <label
                            style="display: block; margin-bottom: 8px; font-weight: 600; font-size:14px; color: var(--text-light);">{{ __('v2_auth.phone_number') }}</label>
                        <div style="display: flex; gap: 10px;">
                            <select name="country_code" required
                                style="background: #fff; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; width: 120px; font-family: inherit; font-size: 14px;" dir="ltr">
                                <option value="+968" selected>🇴🇲 +968</option>
                                <option value="+971">🇦🇪 +971</option>
                                <option value="+966">🇸🇦 +966</option>
                                <option value="+965">🇰🇼 +965</option>
                                <option value="+974">🇶🇦 +974</option>
                                <option value="+973">🇧🇭 +973</option>
                                <option value="+20">🇪🇬 +20</option>
                            </select>
                            <input type="tel" name="phone" required placeholder="{{ __('v2_auth.enter_phone') }}"
                                style="flex:1; padding: 12px 15px; border: 1px solid var(--border-color); border-radius: 8px; font-family: inherit; font-size: 14px; text-align:start;"
                                dir="ltr" value="{{ old('phone') }}">
                        </div>
                        @error('phone')
                            <div style="color: #c00; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
                        @enderror
                        @error('full_phone')
                            <div style="color: #c00; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn-primary"
                        style="width: 100%; padding: 15px; font-size: 16px; margin-bottom: 15px;">{{ __('v2_auth.create_account_btn') }}</button>
                </form>

                <a href="{{ route('user.redirect_google') }}"
                    style="display:flex; justify-content:center; align-items:center; gap: 10px; width: 100%; padding: 15px; font-size: 14px; font-weight:600; color: var(--text-color); border: 1px solid var(--border-color); border-radius: 8px; text-decoration: none; box-sizing: border-box;">
                    {{ __('v2_auth.register_google') }} <i class="fab fa-google" style="color: #4285F4; font-size: 18px;"></i>
                </a>

                <div style="text-align: center; margin-top: 25px; font-size: 14px;">
                    {{ __('v2_auth.has_account') }} <a href="{{ route('login') }}"
                        style="color: var(--primary-color); text-decoration: none; font-weight: 600;">{{ __('v2_auth.login_btn') }}</a>
                </div>
            </div>
        </div>
    </section>
@endsection