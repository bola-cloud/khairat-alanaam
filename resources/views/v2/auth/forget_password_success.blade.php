@extends('v2.layouts.auth')

@section('title', __('v2_home.success_exclamation'))

@section('content')

<div class="auth-page-wrapper" style="min-height: 80vh; display: flex; align-items: center; justify-content: center; padding: 40px 20px; background-color: #fcfcfc;">
    
    <div class="auth-card" style="width: 100%; max-width: 500px; text-align: center;">
        
        <div style="background: #fff; border: 1px solid #eee; border-radius: 12px; padding: 40px 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
            
            <!-- Success Icon -->
            <div style="width: 80px; height: 80px; background: #e1fdeb; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px;">
                <i class="fas fa-check" style="font-size: 35px; color: #1e8b41;"></i>
            </div>

            <h1 style="font-size: 24px; font-weight: 800; color: #333; margin-bottom: 15px;">@lang('v2_home.success_exclamation') 🎉</h1>
            <p style="font-size: 14px; color: #666; margin-bottom: 35px; line-height: 1.6;">
                @lang('v2_home.password_reset_success_desc')
            </p>

            <a href="{{ route('login') }}" class="btn-primary" style="display: block; width: 100%; padding: 14px; font-size: 15px; font-weight: 700; border-radius: 8px; border: none; cursor: pointer; transition: 0.3s; text-decoration: none; text-align: center;">
                @lang('v2_home.login_now')
            </a>
            
        </div>

    </div>

</div>

@endsection
