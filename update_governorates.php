<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\State;

$translations = [
    'Muscat' => 'مسقط',
    'Dhofar' => 'ظفار',
    'Musandam' => 'مسندم',
    'Al Buraimi' => 'البريمي',
    'Ad Dakhiliyah' => 'الداخلية',
    'Al Batinah North' => 'شمال الباطنة',
    'Al Batinah South' => 'جنوب الباطنة',
    'Ash Sharqiyah North' => 'شمال الشرقية',
    'Ash Sharqiyah South' => 'جنوب الشرقية',
    'Ad Dhahirah' => 'الظاهرة',
    'Al Wusta' => 'الوسطى',
    'Al Sharqiya North' => 'شمال الشرقية'
];

$states = State::all();
$updatedCount = 0;

foreach ($states as $state) {
    if (isset($translations[$state->name_en])) {
        // Because of the accessor logic in State.php, name_ar modifies name_fr in db
        $state->name_ar = $translations[$state->name_en];
        $state->save();
        echo "Updated {$state->name_en} to {$translations[$state->name_en]}\n";
        $updatedCount++;
    }
}

echo "Total updated: $updatedCount\n";
