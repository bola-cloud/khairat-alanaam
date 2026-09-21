@extends('v2.layouts.auth')

@php
    $siteLogo = isset($allsettings['main_logo']) ? asset(IMG_LOGO_PATH . $allsettings['main_logo']) : asset('assets/images/logo.png');
@endphp

@section('title', __('v2_auth.verify_account'))

@section('content')
    <section class="auth-section" style="padding: 50px 0;">
        <div class="v2-container">
            <!-- Logo and Heading -->
            <div style="text-align: center; margin-bottom: 30px;">
                <img src="{{ $siteLogo }}" alt="Khairat" style="height: 60px; margin-bottom:15px; object-fit: contain;">
                <h2 style="font-weight: 800; font-size: 24px; color: var(--text-color); margin: 0 0 5px;">{{ __('v2_auth.verify_account') }}</h2>
                <p style="color: var(--text-light); margin: 0; font-size: 14px;">
                    {{ __('v2_auth.otp_sent_to') }}
                </p>
            </div>

            <div style="max-width: 450px; margin: 0 auto; background: var(--white); padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); text-align: start;">

                @if(session('error'))
                    <div style="background: #fee; color: #c00; padding: 10px; border-radius: 5px; margin-bottom: 20px; font-size: 14px;">
                        {{ session('error') }}
                    </div>
                @endif
                @if(session('success'))
                    <div style="background: #eef; color: #080; padding: 10px; border-radius: 5px; margin-bottom: 20px; font-size: 14px;">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ $postRoute }}" method="POST" id="otp-form">
                    @csrf
                    @if(isset($phone_number))
                        <input type="hidden" name="phone_number" value="{{ $phone_number }}">
                        <input type="hidden" name="country_code" value="{{ $country_code }}">
                        <input type="hidden" name="name" value="{{ $name }}">
                    @endif

                    <div style="display: flex; justify-content: space-between; align-items: center; border: 1px solid var(--border-color); border-radius: 8px; padding: 12px 15px; margin-bottom: 25px;">
                        <a href="{{ route('login') }}" style="color: var(--primary-color); font-size: 13px; font-weight: 600; text-decoration: none;">
                            {{ __('v2_auth.change_phone') }}
                        </a>
                        <span style="font-weight: 600; font-size: 14px; color: var(--text-color);" dir="ltr">{{ $targetPhone }}</span>
                    </div>

                    <!-- 6 inputs for OTP -->
                    <div dir="ltr" style="display: flex; gap: 10px; justify-content: space-between; margin-bottom: 25px;">
                        @for($i=1; $i<=5; $i++)
                            <input type="text" maxlength="1" class="otp-input" style="width: 100%; height: 50px; border: 1px solid var(--border-color); border-radius: 8px; text-align: center; font-size: 20px; font-weight: 700; color: var(--text-color); outline: none;">
                        @endfor
                    </div>
                    
                    <input type="hidden" name="otp" id="final_otp" required>
                    
                    @error('otp')
                        <p style="color: red; font-size: 12px; margin-top: -15px; margin-bottom: 15px;">{{ $message }}</p>
                    @enderror

                    <div style="text-align: center; margin-bottom: 25px; font-size: 13px; color: var(--text-light); font-weight: 600;">
                        {{ __('v2_auth.didnt_receive') }} 
                        <span style="color: var(--primary-color);">{{ __('v2_auth.resend_in') }} <span id="timer">01:00</span></span>
                    </div>

                    <button type="submit" class="btn-primary" style="width: 100%; padding: 15px; font-size: 16px;">{{ __('v2_auth.verify_code_btn') }}</button>
                </form>

            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.otp-input');
            const finalOtp = document.getElementById('final_otp');
            const form = document.getElementById('otp-form');

            inputs.forEach((input, index) => {
                input.addEventListener('input', (e) => {
                    // Update hidden field
                    updateFinalOtp();
                    
                    if (e.target.value && index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    }
                });

                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Backspace' && !e.target.value && index > 0) {
                        inputs[index - 1].focus();
                    }
                });
                
                input.addEventListener('paste', (e) => {
                    e.preventDefault();
                    const pastedData = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 5);
                    if (pastedData) {
                        for (let i = 0; i < pastedData.length; i++) {
                            if (inputs[i]) {
                                inputs[i].value = pastedData[i];
                            }
                        }
                        const focusIndex = Math.min(pastedData.length, 5);
                        inputs[focusIndex].focus();
                        updateFinalOtp();
                    }
                });
            });

            function updateFinalOtp() {
                let otpVal = '';
                inputs.forEach(input => {
                    otpVal += input.value;
                });
                finalOtp.value = otpVal;
            }

            // Simple timer
            let timeLeft = 60;
            const timerEl = document.getElementById('timer');
            const timerInterval = setInterval(() => {
                timeLeft--;
                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    timerEl.parentElement.innerHTML = '<a href="{{ route("user.resend.otp") }}" style="color: var(--primary-color); text-decoration: none; font-weight: bold;">{{ app()->getLocale() == "fr" ? "إعادة إرسال الرمز" : "Resend OTP" }}</a>'; 
                } else {
                    const m = Math.floor(timeLeft / 60);
                    const s = timeLeft % 60;
                    timerEl.innerText = '0' + m + ':' + (s < 10 ? '0' : '') + s;
                }
            }, 1000);
        });
    </script>
@endsection
