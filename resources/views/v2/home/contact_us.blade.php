@extends('v2.layouts.app')

@php
    $lang = app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? 'fr' : 'en';
@endphp

@section('title', $title ?? __('v2_layout.nav_contact'))

@section('content')
<style>
    .contact-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
        margin-top: 40px;
    }
    .info-boxes {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-top: 20px;
    }
    @media (max-width: 768px) {
        .info-boxes {
            grid-template-columns: 1fr;
        }
    }
    .info-box {
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 30px 20px;
        text-align: center;
        transition: 0.3s;
    }
    .info-box:hover {
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        border-color: #eee;
    }
    .info-icon {
        width: 50px;
        height: 50px;
        background: rgba(227, 38, 54, 0.1);
        color: var(--primary-color);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        font-size: 20px;
    }
    .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 700;
        color: #333;
        margin-bottom: 8px;
    }
    .form-group label span {
        color: var(--primary-color);
    }
    .form-control {
        width: 100%;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-family: inherit;
        font-size: 14px;
        outline: none;
        transition: 0.2s;
    }
    .form-control:focus {
        border-color: var(--primary-color);
    }
    .map-container {
        border-radius: 16px;
        overflow: hidden;
        margin-top: 60px;
        border: 1px solid var(--border-color);
    }
</style>

<div class="v2-container" style="padding: 40px 15px;">
    
    <!-- Breadcrumb -->
    <nav style="font-size: 14px; margin-bottom: 40px; color: var(--text-light); text-align: center;">
        <a href="{{ route('front') }}" style="color: var(--text-color); text-decoration: none;">@lang('v2_product.home')</a> 
        <span style="margin: 0 5px;">/</span> 
        <span>@lang('v2_layout.nav_contact')</span>
    </nav>

    <!-- Success Message -->
    @if(session('success'))
    <div style="background: #e8f5e9; color: #4caf50; padding: 15px 20px; border-radius: 8px; text-align: center; font-weight: 700; margin-bottom: 30px;">
        {{ session('success') }}
    </div>
    @endif
    
    <!-- Form Section -->
    <div style="max-width: 800px; margin: 0 auto; background: #fff; padding: 50px 40px; border-radius: 16px; border: 1px solid var(--border-color);">
        <div style="text-align: center; margin-bottom: 40px;">
            <h1 style="margin: 0 0 10px; font-size: 32px; font-weight: 800; color: #333;">@lang('v2_home.send_message')</h1>
            <p style="margin: 0; color: var(--text-light); font-size: 15px;">@lang('v2_home.send_message_desc')</p>
        </div>
        
        <form action="{{ route('contact.us.send') }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <!-- Full Name -->
                <div class="form-group">
                    <label>@lang('v2_home.full_name') <span>*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="@lang('v2_home.full_name')" required value="{{ old('name') }}">
                    @error('name')<span style="color:red; font-size: 12px;">{{ $message }}</span>@enderror
                </div>
                <!-- Email -->
                <div class="form-group">
                    <label>@lang('v2_home.email') <span>*</span></label>
                    <input type="email" name="email" class="form-control" placeholder="example@company.com" required value="{{ old('email') }}" dir="ltr" style="text-align: {{ $lang == 'fr' ? 'right' : 'left' }};">
                    @error('email')<span style="color:red; font-size: 12px;">{{ $message }}</span>@enderror
                </div>
            </div>
            
            <!-- Phone -->
            <div class="form-group" style="margin-bottom: 20px;">
                <label>@lang('v2_home.phone') <span>*</span></label>
                <input type="text" name="phone" class="form-control" placeholder="+968 9XXX XXXX" required value="{{ old('phone') }}" dir="ltr" style="text-align: {{ $lang == 'fr' ? 'right' : 'left' }};">
                @error('phone')<span style="color:red; font-size: 12px;">{{ $message }}</span>@enderror
            </div>
            
            <!-- Message -->
            <div class="form-group" style="margin-bottom: 30px;">
                <label>@lang('v2_home.message') <span>*</span></label>
                <textarea name="message" class="form-control" placeholder="@lang('v2_home.message')..." rows="5" required>{{ old('message') }}</textarea>
                @error('message')<span style="color:red; font-size: 12px;">{{ $message }}</span>@enderror
            </div>
            
            <button type="submit" class="btn-primary" style="width: 100%; padding: 16px; font-size: 16px; border-radius: 8px; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 10px;">
                @lang('v2_home.submit_message') <i class="far fa-paper-plane"></i>
            </button>
        </form>
    </div>

    <!-- Info Boxes -->
    <div class="info-boxes">
        <!-- Address -->
        <div class="info-box">
            <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
            <h3 style="margin: 0 0 10px; font-size: 18px; font-weight: 800; color: #333;">@lang('v2_home.contact_info')</h3>
            <p style="margin: 0; color: var(--text-light); font-size: 14px; line-height: 1.6;">
                {!! nl2br(e($settings['contact_address_'.$lang] ?? 'مسقط، سلطنة عمان')) !!}
            </p>
        </div>
        
        <!-- Phone -->
        <div class="info-box">
            <div class="info-icon"><i class="fas fa-phone-alt"></i></div>
            <h3 style="margin: 0 0 10px; font-size: 18px; font-weight: 800; color: #333;">@lang('v2_home.phone')</h3>
            <p style="margin: 0; color: var(--text-light); font-size: 14px; line-height: 1.6;" dir="ltr">
                {!! nl2br(e($settings['contact_phone'] ?? '+968 24 123 456')) !!}
            </p>
        </div>
        
        <!-- Email -->
        <div class="info-box">
            <div class="info-icon"><i class="far fa-envelope"></i></div>
            <h3 style="margin: 0 0 10px; font-size: 18px; font-weight: 800; color: #333;">@lang('v2_home.email')</h3>
            <p style="margin: 0; color: var(--text-light); font-size: 14px; line-height: 1.6;" dir="ltr">
                {!! nl2br(e($settings['contact_email'] ?? 'info@khairat-alan3am.com')) !!}
            </p>
        </div>
    </div>

    <!-- Map Section -->
    <div style="text-align: center; margin-top: 80px;">
        <h2 style="margin: 0 0 10px; font-size: 32px; font-weight: 800; color: #333;">@lang('v2_home.our_location')</h2>
        <p style="margin: 0; color: var(--text-light); font-size: 16px;">@lang('v2_home.our_location_desc')</p>
    </div>
    
    <div class="map-container">
        @php
            $mapVal = trim($settings['contact_map_iframe'] ?? '');
        @endphp
        @if(str_starts_with($mapVal, 'http'))
            <iframe src="{{ $mapVal }}" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        @else
            {!! $mapVal !!}
        @endif
    </div>
</div>
@endsection
