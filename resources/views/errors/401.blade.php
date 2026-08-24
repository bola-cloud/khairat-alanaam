@extends('v2.layouts.app')
@section('title', app()->getLocale() == 'ar' ? 'خطأ 401' : 'Error 401')

@section('content')
<div class="v2-container" style="padding: 100px 0; text-align: center; min-height: 500px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
    <div style="max-width: 600px;">
        <div style="font-size: 120px; color: var(--primary-color); margin-bottom: 20px;">
            <i class="fas fa-exclamation-circle"></i>
        </div>
        <h1 style="font-size: 60px; font-weight: 900; color: #333; margin-bottom: 10px;">401</h1>
        <h2 style="font-size: 24px; font-weight: 800; color: #333; margin-bottom: 20px;">
            {{ app()->getLocale() == 'ar' ? 'غير مصرح' : 'Unauthorized' }}
        </h2>
        <p style="font-size: 16px; color: #666; margin-bottom: 40px; line-height: 1.6;">
            {{ app()->getLocale() == 'ar' ? 'أنت غير مصرح لك بمشاهدة هذه الصفحة.' : 'You are not authorized to view this page.' }}
        </p>
        <a href="{{ route('front') }}" class="btn-primary" style="font-size: 16px; padding: 12px 30px;">
            <i class="fas fa-home" style="margin-right: 8px; margin-left: 8px;"></i> {{ app()->getLocale() == 'ar' ? 'الرئيسية' : 'Back to Home' }}
        </a>
    </div>
</div>
@endsection