<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$url = config('services.muscatapps.url');
$username = config('services.muscatapps.username');
$password = config('services.muscatapps.password');

echo "Testing GenOTP Length...\n";
$payload = [
    'Phoneno' => '96898829882',
    'Username' => $username,
    'Password' => $password,
    'MsgTemplate' => '{OTP} is your verification code for Khairat Alanaam',
    'Length' => 6,
    'OTPLength' => 6,
    'OtpLength' => 6,
    'Size' => 6,
];
$res = Illuminate\Support\Facades\Http::post("$url/api/GenOTP", $payload);
echo $res->body() . "\n";
