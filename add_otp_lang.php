<?php

$files = ['resources/lang/en.json', 'resources/lang/fr.json'];

$new_keys = [
    'v2_auth.verify_account' => ['en' => 'Verify your account', 'fr' => 'تحقق من حسابك'],
    'v2_auth.otp_sent_to' => ['en' => 'We sent a 6-digit verification code to', 'fr' => 'أرسلنا رمز التحقق المكون من 6 أرقام إلى'],
    'v2_auth.change_phone' => ['en' => 'Change Phone', 'fr' => 'تغيير رقم الهاتف'],
    'v2_auth.didnt_receive' => ['en' => 'Didn\'t receive the code?', 'fr' => 'لم تستلم الرمز؟'],
    'v2_auth.resend_in' => ['en' => 'Resend in', 'fr' => 'أعد الإرسال خلال'],
    'v2_auth.verify_code_btn' => ['en' => 'Verify Code', 'fr' => 'تحقق من الرمز'],
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = json_decode(file_get_contents($file), true) ?: [];
    
    $lang = strpos($file, 'en.json') !== false ? 'en' : 'fr';
    
    foreach ($new_keys as $key => $values) {
        $content[$key] = $values[$lang];
    }
    
    file_put_contents($file, json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

echo "Translation keys updated successfully.\n";
