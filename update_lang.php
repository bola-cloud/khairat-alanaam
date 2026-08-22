<?php
$en = json_decode(file_get_contents('resources/lang/en.json'), true) ?? [];
$fr = json_decode(file_get_contents('resources/lang/fr.json'), true) ?? [];
$keys = [
    'v2_auth.login_title' => ['en' => 'Login', 'fr' => 'تسجيل الدخول'],
    'v2_auth.welcome_back' => ['en' => 'Welcome back!', 'fr' => 'مرحباً بعودتك!'],
    'v2_auth.phone_number' => ['en' => 'Phone Number', 'fr' => 'رقم الهاتف'],
    'v2_auth.enter_phone' => ['en' => 'Enter phone number', 'fr' => 'أدخل رقم الهاتف'],
    'v2_auth.remember_me' => ['en' => 'Remember me', 'fr' => 'تذكرني'],
    'v2_auth.login_btn' => ['en' => 'Login', 'fr' => 'دخول'],
    'v2_auth.login_google' => ['en' => 'Login with Google', 'fr' => 'سجل دخولك باستخدام جوجل'],
    'v2_auth.no_account' => ['en' => 'Don\'t have an account?', 'fr' => 'ليس لديك حساب؟'],
    'v2_auth.register_now' => ['en' => 'Register Now', 'fr' => 'سجل الآن'],
    'v2_auth.register_title' => ['en' => 'Create your account now', 'fr' => 'قم بإنشاء حسابك الآن'],
    'v2_auth.enter_details' => ['en' => 'Please enter your details.', 'fr' => 'يرجى إدخال بياناتك.'],
    'v2_auth.name' => ['en' => 'Full Name', 'fr' => 'الاسم الكامل'],
    'v2_auth.enter_name' => ['en' => 'Enter your full name', 'fr' => 'أدخل اسمك الكامل'],
    'v2_auth.agree_terms' => ['en' => 'I agree to the', 'fr' => 'أوافق على'],
    'v2_auth.terms_and_privacy' => ['en' => 'Terms & Privacy Policy', 'fr' => 'الشروط والأحكام وسياسة الخصوصية'],
    'v2_auth.create_account_btn' => ['en' => 'Create Account', 'fr' => 'إنشاء حساب'],
    'v2_auth.register_google' => ['en' => 'Sign up with Google', 'fr' => 'أنشئ حسابك باستخدام جوجل'],
    'v2_auth.has_account' => ['en' => 'Already have an account?', 'fr' => 'لديك حساب بالفعل؟']
];
foreach ($keys as $k => $v) {
    $en[$k] = $v['en'];
    $fr[$k] = $v['fr'];
}
file_put_contents('resources/lang/en.json', json_encode($en, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
file_put_contents('resources/lang/fr.json', json_encode($fr, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo "Done";
