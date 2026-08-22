@extends('v2.layouts.auth')

@section('title', __('v2_home.forget_password_title'))

@section('content')
@php
    $lang = app()->getLocale() == 'en' ? 'en' : 'ar';
@endphp

<div class="auth-page-wrapper" style="min-height: 80vh; display: flex; align-items: center; justify-content: center; padding: 40px 20px; background-color: #fcfcfc;">
    
    <div class="auth-card" style="width: 100%; max-width: 500px; text-align: center;">
        
        <!-- Logo for Mobile or Centered -->
        <div style="margin-bottom: 25px;">
            <img src="{{ asset('v2/img/logo.png') }}" alt="Logo" style="height: 60px; margin: 0 auto; object-fit: contain;">
        </div>

        <h1 style="font-size: 24px; font-weight: 800; color: #333; margin-bottom: 10px;">@lang('v2_home.forget_password_title')</h1>
        <p style="font-size: 14px; color: #666; margin-bottom: 30px; line-height: 1.6;">
            @lang('v2_home.forget_password_email_desc')
        </p>

        @if(session('error'))
            <div style="background: #fde8e8; border: 1px solid #f8b4b4; color: #c81e1e; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
                {{ session('error') }}
            </div>
        @endif
        @if(session('success'))
            <div style="background: #e1fdeb; border: 1px solid #94f5b5; color: #1e8b41; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('forget.password.post') }}" method="POST" style="background: #fff; border: 1px solid #eee; border-radius: 12px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
            @csrf
            
            <div style="text-align: {{ $lang == 'en' ? 'left' : 'right' }}; margin-bottom: 20px;">
                <label for="email" style="display: block; font-size: 13px; font-weight: 700; color: #555; margin-bottom: 8px;">@lang('v2_home.email')</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="@lang('v2_home.enter_email')" required
                    style="width: 100%; padding: 14px 16px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; transition: 0.3s; outline: none; box-sizing: border-box;"
                    onfocus="this.style.borderColor='#e32636'" onblur="this.style.borderColor='#ddd'">
                @error('email')
                    <p style="color: #c81e1e; font-size: 12px; margin-top: 5px;">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn-primary" style="width: 100%; padding: 14px; font-size: 15px; font-weight: 700; border-radius: 8px; border: none; cursor: pointer; transition: 0.3s;">
                @lang('v2_home.send_verification_code')
            </button>
        </form>

        <p style="margin-top: 25px; font-size: 14px; color: #666;">
            @lang('v2_home.dont_have_account') 
            <a href="{{ route('user.sign.up') }}" style="color: #e32636; font-weight: 700; text-decoration: none;">@lang('v2_home.register_now')</a>
        </p>

    </div>

</div>

@endsection
