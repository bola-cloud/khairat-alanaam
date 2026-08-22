@extends('v2.layouts.auth')

@section('title', __('v2_home.new_password'))

@section('content')
@php
    $lang = app()->getLocale() == 'en' ? 'en' : 'ar';
@endphp

<div class="auth-page-wrapper" style="min-height: 80vh; display: flex; align-items: center; justify-content: center; padding: 40px 20px; background-color: #fcfcfc;">
    
    <div class="auth-card" style="width: 100%; max-width: 500px; text-align: center;">
        
        <!-- Logo -->
        <div style="margin-bottom: 25px;">
            <img src="{{ asset('v2/img/logo.png') }}" alt="Logo" style="height: 60px; margin: 0 auto; object-fit: contain;">
        </div>

        <h1 style="font-size: 24px; font-weight: 800; color: #333; margin-bottom: 10px;">@lang('v2_home.new_password')</h1>
        <p style="font-size: 14px; color: #666; margin-bottom: 30px; line-height: 1.6;">
            @lang('v2_home.create_strong_password')
        </p>

        @if(session('error'))
            <div style="background: #fde8e8; border: 1px solid #f8b4b4; color: #c81e1e; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('reset.password.post') }}" method="POST" style="background: #fff; border: 1px solid #eee; border-radius: 12px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); text-align: {{ $lang == 'en' ? 'left' : 'right' }};">
            @csrf
            
            <div style="margin-bottom: 20px;">
                <label for="password" style="display: block; font-size: 13px; font-weight: 700; color: #555; margin-bottom: 8px;">@lang('v2_home.new_password')</label>
                <div style="position: relative;">
                    <input type="password" id="password" name="password" required placeholder="........"
                        style="width: 100%; padding: 14px 16px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; transition: 0.3s; outline: none; box-sizing: border-box;"
                        onfocus="this.style.borderColor='#e32636'" onblur="this.style.borderColor='#ddd'">
                    <i class="far fa-eye-slash" onclick="togglePassword('password', this)" style="position: absolute; top: 50%; transform: translateY(-50%); {{ $lang == 'en' ? 'right: 15px;' : 'left: 15px;' }} cursor: pointer; color: #999;"></i>
                </div>
                @error('password')
                    <p style="color: #c81e1e; font-size: 12px; margin-top: 5px;">{{ $message }}</p>
                @enderror
            </div>

            <div style="margin-bottom: 25px;">
                <label for="password_confirmation" style="display: block; font-size: 13px; font-weight: 700; color: #555; margin-bottom: 8px;">@lang('v2_home.confirm_new_password')</label>
                <div style="position: relative;">
                    <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="........"
                        style="width: 100%; padding: 14px 16px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; transition: 0.3s; outline: none; box-sizing: border-box;"
                        onfocus="this.style.borderColor='#e32636'" onblur="this.style.borderColor='#ddd'">
                    <i class="far fa-eye-slash" onclick="togglePassword('password_confirmation', this)" style="position: absolute; top: 50%; transform: translateY(-50%); {{ $lang == 'en' ? 'right: 15px;' : 'left: 15px;' }} cursor: pointer; color: #999;"></i>
                </div>
                <p style="font-size: 12px; color: #888; margin-top: 8px;">@lang('v2_home.min_8_chars')</p>
            </div>

            <button type="submit" class="btn-primary" style="width: 100%; padding: 14px; font-size: 15px; font-weight: 700; border-radius: 8px; border: none; cursor: pointer; transition: 0.3s;">
                @lang('v2_home.set_new_password')
            </button>
        </form>

    </div>

</div>

<script>
    function togglePassword(inputId, icon) {
        let input = document.getElementById(inputId);
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
        } else {
            input.type = "password";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
        }
    }
</script>

@endsection
