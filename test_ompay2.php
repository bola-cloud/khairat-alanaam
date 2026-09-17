<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$response = \Illuminate\Support\Facades\Http::withHeaders([
    'OMPAY-API-Key' => 'f63676c6e1d553f23278bbf6aaa009b47494fb176eeba18f032bc2d1379c4865', 
    'OMPAY-API-Secret' => 'f1b45d147475f7121297af34826957510bdab43fdec7a1a21a9bb70ba0911935', 
    'Content-Type' => 'application/json'
])->post('https://sandbox.truepay.ompay.om/api/v1/transactions/bank-hosted', [
    'amount' => 10.0, 
    'currency' => 'OMR', 
    'return_url' => 'https://example.com/return', 
    'reference_number' => 'TEST1234'
]);

echo $response->status() . "\n" . $response->body() . "\n";
