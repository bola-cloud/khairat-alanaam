<?php
$file = 'resources/lang/ar.json';
$ar = json_decode(file_get_contents($file), true);

$ar['new_design.auth.phone_taken'] = 'رقم الهاتف هذا مسجل بالفعل لدينا.';
$ar['Sign Up Successfully! Please verify your account with the OTP sent to your phone.'] = 'تم إنشاء الحساب بنجاح! يرجى تأكيد حسابك باستخدام رمز التحقق المرسل إلى هاتفك.';
$ar['Please verify your account with the OTP sent to your phone.'] = 'يرجى تأكيد حسابك باستخدام رمز التحقق المرسل إلى هاتفك.';
$ar['Failed to send OTP. Please try again later.'] = 'فشل إرسال رمز التحقق. يرجى المحاولة مرة أخرى لاحقاً.';
$ar['Sign Up Successfully! But failed to send OTP. Please try resending the OTP.'] = 'تم إنشاء الحساب بنجاح! ولكن فشل إرسال رمز التحقق. يرجى المحاولة مرة أخرى لاحقاً.';
$ar['OTP has been resent to your phone.'] = 'تم إعادة إرسال رمز التحقق إلى هاتفك.';

file_put_contents($file, json_encode($ar, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo "Done ar\n";

$fileEn = 'resources/lang/en.json';
$en = json_decode(file_get_contents($fileEn), true);

$en['new_design.auth.phone_taken'] = 'This phone number is already registered.';
$en['Sign Up Successfully! Please verify your account with the OTP sent to your phone.'] = 'Sign Up Successfully! Please verify your account with the OTP sent to your phone.';
$en['Please verify your account with the OTP sent to your phone.'] = 'Please verify your account with the OTP sent to your phone.';
$en['Failed to send OTP. Please try again later.'] = 'Failed to send OTP. Please try again later.';
$en['Sign Up Successfully! But failed to send OTP. Please try resending the OTP.'] = 'Sign Up Successfully! But failed to send OTP. Please try resending the OTP.';
$en['OTP has been resent to your phone.'] = 'OTP has been resent to your phone.';

file_put_contents($fileEn, json_encode($en, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo "Done en\n";
