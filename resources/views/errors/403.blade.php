@extends('v2.layouts.app')
@section('title', __('Error 403'))

@section('content')
<div class="v2-container" style="padding: 100px 0; text-align: center; min-height: 500px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
    <div style="max-width: 600px;">
        <div style="font-size: 120px; color: var(--primary-color); margin-bottom: 20px;">
            <i class="fas fa-exclamation-circle"></i>
        </div>
        <h1 style="font-size: 60px; font-weight: 900; color: #333; margin-bottom: 10px;">403</h1>
        <h2 style="font-size: 24px; font-weight: 800; color: #333; margin-bottom: 20px;">{{ __('Forbidden') }}</h2>
        <p style="font-size: 16px; color: #666; margin-bottom: 40px; line-height: 1.6;">
            {{ __('You do not have permission to access this page.') }}
        </p>
        <a href="{{ route('front') }}" class="btn-primary" style="font-size: 16px; padding: 12px 30px;">
            <i class="fas fa-home" style="margin-right: 8px; margin-left: 8px;"></i> {{ __('v2_layout.home' ?? 'Back to Home') }}
        </a>
    </div>
</div>
@endsection