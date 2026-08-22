@extends('v2.layouts.auth')

@section('title', __('v2_home.verify_your_email'))

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

        <h1 style="font-size: 24px; font-weight: 800; color: #333; margin-bottom: 10px;">@lang('v2_home.verify_your_email')</h1>
        <p style="font-size: 14px; color: #666; margin-bottom: 30px; line-height: 1.6;">
            @lang('v2_home.sent_6_digit_code_to') <strong style="color: #333;">{{ $email }}</strong>
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

        <form action="{{ route('forget.password.otp.post') }}" method="POST" id="otpForm" style="background: #fff; border: 1px solid #eee; border-radius: 12px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
            @csrf
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <a href="{{ route('forget.password.get') }}" style="color: #e32636; font-size: 12px; font-weight: 600; text-decoration: none;">@lang('v2_home.change_email')</a>
                <span style="font-size: 13px; font-weight: 600; color: #555;">{{ $email }}</span>
            </div>

            <!-- Hidden input to store full OTP -->
            <input type="hidden" name="otp" id="fullOtpInput">

            <!-- OTP Inputs -->
            <div style="display: flex; gap: 10px; justify-content: center; margin-bottom: 25px; direction: ltr;">
                @for($i=1; $i<=6; $i++)
                <input type="text" maxlength="1" class="otp-input" required
                    style="width: 45px; height: 50px; text-align: center; font-size: 20px; font-weight: 700; border: 1px solid #ddd; border-radius: 8px; outline: none; transition: 0.3s; color: #333;"
                    onfocus="this.style.borderColor='#e32636'" onblur="this.style.borderColor='#ddd'"
                    onkeyup="moveToNext(this, event)" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
                @endfor
            </div>

            <div style="margin-bottom: 25px; font-size: 13px; color: #666;">
                @lang('v2_home.didnt_receive_code') 
                <span id="countdownText">@lang('v2_home.resend_in') <strong id="countdown" style="color: #e32636;">01:00</strong></span>
                <a href="#" onclick="event.preventDefault(); document.getElementById('resendForm').submit();" id="resendLink" style="display: none; color: #e32636; font-weight: 700; text-decoration: none;">
                    @lang('v2_home.resend_now')
                </a>
            </div>

            <button type="submit" class="btn-primary" style="width: 100%; padding: 14px; font-size: 15px; font-weight: 700; border-radius: 8px; border: none; cursor: pointer; transition: 0.3s;" onclick="combineOtp()">
                @lang('v2_home.verify_code')
            </button>
        </form>
        
        <!-- Hidden Resend Form -->
        <form id="resendForm" action="{{ route('forget.password.post') }}" method="POST" style="display: none;">
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">
        </form>

    </div>

</div>

<script>
    function moveToNext(current, event) {
        if (current.value.length >= 1) {
            let next = current.nextElementSibling;
            if (next && next.classList.contains('otp-input')) {
                next.focus();
            }
        }
        if (event.key === "Backspace") {
            let prev = current.previousElementSibling;
            if (prev && prev.classList.contains('otp-input')) {
                prev.focus();
            }
        }
    }

    function combineOtp() {
        let inputs = document.querySelectorAll('.otp-input');
        let otp = '';
        inputs.forEach(input => {
            otp += input.value;
        });
        document.getElementById('fullOtpInput').value = otp;
    }

    // Timer logic
    let timeLeft = 60;
    let timerId = setInterval(() => {
        timeLeft--;
        let seconds = timeLeft < 10 ? '0' + timeLeft : timeLeft;
        document.getElementById('countdown').innerText = '00:' + seconds;
        
        if (timeLeft <= 0) {
            clearInterval(timerId);
            document.getElementById('countdownText').style.display = 'none';
            document.getElementById('resendLink').style.display = 'inline-block';
        }
    }, 1000);
</script>

@endsection
