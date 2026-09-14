<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$cats = \App\Models\Admin\Category::all()->map(function($c) {
    return ['id' => $c->id, 'name_ar' => $c->en_Category_Name, 'name_fr' => $c->fr_Category_Name];
});
echo json_encode($cats);
