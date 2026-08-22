<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
class TestProductSeeder extends Seeder {
    public function run() {
        $user = \App\Models\User::find(1);
        auth()->login($user);
        
        $request = new \App\Http\Requests\ProductRequest();
        $request->replace([
            'product_type' => 1,
            'en_product_name' => 'Test Product Auto',
            'en_product_slug' => 'test-product-auto-' . time(),
            'en_category_name' => 1,
            'en_description' => 'Test description',
            'fr_product_name' => 'Test Product Auto FR',
            'fr_product_slug' => 'test-product-auto-fr-' . time(),
            'fr_description' => 'Test description FR',
            'price' => 100,
            'qty' => 10,
            'size' => [1],
            'size_price' => [120]
        ]);
        
        $controller = new \App\Http\Controllers\Admin\ProductController();
        try {
            $controller->productStore($request);
            echo "Product creation successful!\n";
            // cleanup
            \App\Models\Admin\Product::where('en_Product_Name', 'Test Product Auto')->forceDelete();
            echo "Product cleaned up!\n";
        } catch (\Exception $e) {
            echo 'Error: ' . $e->getMessage() . "\n" . $e->getFile() . ':' . $e->getLine();
        }
    }
}
