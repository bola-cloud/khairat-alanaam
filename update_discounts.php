<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$products = \App\Models\Admin\Product::where('Discount', '>', 0)->get();
$updates = [];
foreach($products as $p) {
    $calc = round($p->Price - ($p->Price * $p->Discount / 100), 3);
    if((float)$p->Discount_Price !== (float)$calc) {
        $updates[] = [
            'id' => $p->id,
            'name' => $p->en_Product_Name,
            'price' => $p->Price,
            'discount_percent' => $p->Discount,
            'old_dp' => $p->Discount_Price,
            'new_dp' => $calc
        ];
        $p->update(['Discount_Price' => $calc]);
    }
}
echo json_encode(['count' => count($updates), 'updates' => $updates]);
