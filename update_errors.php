<?php

$template = <<<EOT
@extends('v2.layouts.app')
@section('title', app()->getLocale() == 'ar' ? 'خطأ {{CODE}}' : 'Error {{CODE}}')

@section('content')
<div class="v2-container" style="padding: 100px 0; text-align: center; min-height: 500px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
    <div style="max-width: 600px;">
        <div style="font-size: 120px; color: var(--primary-color); margin-bottom: 20px;">
            <i class="fas fa-exclamation-circle"></i>
        </div>
        <h1 style="font-size: 60px; font-weight: 900; color: #333; margin-bottom: 10px;">{{CODE}}</h1>
        <h2 style="font-size: 24px; font-weight: 800; color: #333; margin-bottom: 20px;">
            {{ app()->getLocale() == 'ar' ? '{{AR_TITLE}}' : '{{EN_TITLE}}' }}
        </h2>
        <p style="font-size: 16px; color: #666; margin-bottom: 40px; line-height: 1.6;">
            {{ app()->getLocale() == 'ar' ? '{{AR_MSG}}' : '{{EN_MSG}}' }}
        </p>
        <a href="{{ route('front') }}" class="btn-primary" style="font-size: 16px; padding: 12px 30px;">
            <i class="fas fa-home" style="margin-right: 8px; margin-left: 8px;"></i> {{ app()->getLocale() == 'ar' ? 'الرئيسية' : 'Back to Home' }}
        </a>
    </div>
</div>
@endsection
EOT;

$errors = [
    '401' => ['Unauthorized', 'You are not authorized to view this page.', 'غير مصرح', 'أنت غير مصرح لك بمشاهدة هذه الصفحة.'],
    '403' => ['Forbidden', 'You do not have permission to access this page.', 'ممنوع', 'ليس لديك صلاحية للوصول إلى هذه الصفحة.'],
    '404' => ['Oops! page not found', 'The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.', 'عفواً! الصفحة غير موجودة', 'الصفحة التي تبحث عنها ربما تكون قد أزيلت، أو تم تغيير اسمها، أو غير متاحة مؤقتاً.'],
    '419' => ['Page Expired', 'The page has expired due to inactivity. Please refresh and try again.', 'انتهت صلاحية الصفحة', 'انتهت صلاحية الجلسة بسبب عدم النشاط. يرجى تحديث الصفحة والمحاولة مرة أخرى.'],
    '429' => ['Too Many Requests', 'You have made too many requests. Please wait a moment and try again.', 'طلبات كثيرة جداً', 'لقد قمت بإرسال طلبات كثيرة جداً. يرجى الانتظار قليلاً والمحاولة مرة أخرى.'],
    '500' => ['Server Error', 'Oops! Something went wrong on our servers. Please try again later.', 'خطأ في الخادم', 'عفواً! حدث خطأ ما في خوادمنا. يرجى المحاولة مرة أخرى لاحقاً.'],
    '503' => ['Service Unavailable', 'The service is temporarily unavailable for maintenance. Please check back later.', 'الخدمة غير متاحة', 'الخدمة غير متاحة مؤقتاً لأغراض الصيانة. يرجى العودة لاحقاً.']
];

foreach($errors as $code => $data) {
    $content = str_replace(
        ['{{CODE}}', '{{EN_TITLE}}', '{{EN_MSG}}', '{{AR_TITLE}}', '{{AR_MSG}}'], 
        [$code, addslashes($data[0]), addslashes($data[1]), addslashes($data[2]), addslashes($data[3])], 
        $template
    );
    file_put_contents('resources/views/errors/' . $code . '.blade.php', $content);
}
echo 'Error pages translated successfully!';
