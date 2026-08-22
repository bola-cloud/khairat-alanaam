<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Admin\Color;
use App\Models\Admin\Product;
use App\Models\Admin\Size;
use App\Models\SeoSetting;
use App\Models\User;
use App\Models\WeightProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
// use Cart;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        if ($request->ajax()) {
            $product = Product::with('colors', 'sizes')
                ->where('id', $request->product_id)
                ->first();

            if (!$product) {
                return response()->json(['error' => 'Product not found'], 404);
            }

            // Check Virtual Stock Validation FIRST
            $availableStock = $product->virtual_stock;
            $currentCartQty = 0;
            foreach (Cart::content() as $cItem) {
                if ($cItem->id == $product->id) {
                    $currentCartQty += $cItem->qty;
                }
            }

            if (($currentCartQty + $request->quantity) > $availableStock) {
                $msg = $availableStock <= 0 ? __('Out of stock') : __('Requested quantity not available. Max stock: ') . $availableStock;
                return response()->json(['error' => $msg], 422);
            }

            // Experience Box limit check: Max 1 experience box per customer
            $trialBoxesCatId = DB::table('categories')
                ->where('en_Category_Slug', 'trial-boxes')
                ->orWhere('fr_Category_Slug', 'trial-boxes')
                ->value('id');
            if ($product->Category_Id == $trialBoxesCatId) {
                $trialBoxesInCartCount = 0;
                foreach (Cart::content() as $cItem) {
                    $cartItemProd = Product::find($cItem->id);
                    if ($cartItemProd && $cartItemProd->Category_Id == $trialBoxesCatId) {
                        $trialBoxesInCartCount += $cItem->qty;
                    }
                }
                if (($trialBoxesInCartCount + $request->quantity) > 1) {
                    return response()->json(['error' => __('Only 1 experience box is allowed per customer.')], 422);
                }
            }

            // Check if exact same item (with same options) already in cart
            foreach (Cart::content() as $cart) {
                if ($cart->id == $product->id && ($request->selectedSize == ($cart->options->selectedSize ?? null)) && ($request->weight_id == ($cart->options->selectedWeightId ?? null))) {
                    $qty = $cart->qty + $request->quantity;
                    Cart::update($cart->rowId, $qty);

                    $cd = Cart::content();
                    $ta = 0;
                    foreach ($cd as $item) {
                        $ta = $ta + $item->price * $item->qty;
                    }
                    $tc = Cart::count();
                    return response()->json([$tc, $ta, $cd]);
                }
            }

            $color_id = DB::table('color_product')->where('Product_Id', $request->product_id)->where('Color_Id', $request->color_id)->count();
            $size_id = DB::table('size_product')->where('Product_Id', $request->product_id)->where('Size_Id', $request->size_id)->count();
            if (isset($request->additions)) {
                $additions = DB::table('additions')->where('product_id', $request->product_id)->whereIn('id', $request->additions)->get();
            }

            $color_name = Color::where('id', $request->color_id)->first();
            $size_name = Size::where('id', $request->size_id)->first();

            $selected_size = DB::table('size_product')->where('Product_Id', $request->product_id)->where('Size_Id', $request->size_id)->first();
            $selected_weight = WeightProduct::where('product_id', $request->product_id)->where('id', $request->weight_id)->first();

            $cart = Cart::add([
                'id' => $request->product_id,
                'name' => $product->en_Product_Name,
                'qty' => $request->quantity,
                'price' => $request->price,
                'weight' => $selected_size->weight ?? 0,
                'options' =>
                    [
                        'name_ar' => $product->fr_Product_Name,
                        'additions' => $additions ?? [],
                        'size' => ($size_id > 0 && $size_name) ? $size_name->Size : null,
                        'size_ar' => ($size_id > 0 && $size_name) ? $size_name->Size_ar : null,
                        'color' => ($color_id > 0 && $color_name) ? $color_name->ColorCode : null,
                        'image' => $product->Primary_Image,
                        'weight' => $selected_weight ?? null,
                        'selectedSize' => $request->selectedSize ?? $request->size_id ?? null,
                        'selectedWeightId' => $request->weight_id ?? null,
                        'slug' => $product->en_Product_Slug,
                        'discount_price' => $request->price,
                        'item_tag' => $product->ItemTag,
                        'discount_parcent' => $product->Discount,
                        'voucher' => $product->Voucher,
                    ]
            ]);

            if ($cart) {
                $cd = Cart::content();
                $ta = 0;
                foreach ($cd as $item) {
                    $ta = $ta + $item->price * $item->qty;
                }
                $tc = Cart::count();
                return response()->json([$tc, $ta, $cd]);
            }
        }
    }




    public function cartContent()
    {
        return redirect()->route('front.cart');
    }
    public function cartDelete(Request $request)
    {
        // return response()->json($request->all());
        Session::forget('CouponAmount');
        Session::forget('couponCode');

        $id = $request->id;
        if ($id) {
            Cart::remove($id);
        }
        $cd = Cart::content();
        $ta = 0;
        foreach ($cd as $item) {
            $ta = $ta + $item->price * $item->qty;
        }
        $tc = Cart::count();
        return response()->json([
            $tc,
            $ta,
            $cd,
            'total_amount_formatted' => currencyConverter($ta)
        ]);
    }

    public function cartDecrease(Request $request)
    {
        $id = $request->id;
        $cd = Cart::content();
        $ta = 0;
        foreach (Cart::content() as $cart) {
            if ($cart->rowId == $id) {
                $qty = $request->quantity == 1 ? 1 : $cart->qty - 1;
                $singleValue = Cart::update($cart->rowId, $qty);
                $st = $singleValue->price * $singleValue->qty;

                foreach ($cd as $item) {
                    $ta = $ta + $item->price * $item->qty;
                }
                $tc = Cart::count();

                return response()->json([
                    $tc,
                    $ta,
                    $cd,
                    $st,
                    'total_amount_formatted' => currencyConverter($ta),
                    'subtotal_formatted' => currencyConverter($st)
                ]);
            }
        }
    }

    public function cartIncrease(Request $request)
    {
        $id = $request->id;
        $cd = Cart::content();
        $ta = 0;
        foreach (Cart::content() as $cart) {
            if ($cart->rowId == $id) {
                // Check Stock Validation
                $product = Product::find($cart->id);
                
                if ($product) {
                    $trialBoxesCatId = DB::table('categories')
                        ->where('en_Category_Slug', 'trial-boxes')
                        ->orWhere('fr_Category_Slug', 'trial-boxes')
                        ->value('id');
                    if ($product->Category_Id == $trialBoxesCatId) {
                        return response()->json(['error' => __('Only 1 experience box is allowed per customer.')], 422);
                    }
                }

                $availableStock = $product ? $product->virtual_stock : 999;

                if (($cart->qty + 1) > $availableStock) {
                    return response()->json(['error' => __('Max stock reached')], 422);
                }

                $qty = $cart->qty + 1;
                $singleValue = Cart::update($cart->rowId, $qty);
                $st = $singleValue->price * $singleValue->qty;

                foreach ($cd as $item) {
                    $ta = $ta + $item->price * $item->qty;
                }
                $tc = Cart::count();

                return response()->json([
                    $tc,
                    $ta,
                    $cd,
                    $st,
                    'total_amount_formatted' => currencyConverter($ta),
                    'subtotal_formatted' => currencyConverter($st)
                ]);
            }
        }
    }

    public function currencyPrice(Request $request)
    {
        if ($request->ajax()) {
            return currencyConverter($request->price);
        }
    }

    public function currencySymbol()
    {
        return currencySymbol()[currency()];
    }

    public function addCustomBoxToCart(Request $request)
    {
        $request->validate([
            'template' => 'required|string',
            'capacity' => 'required|integer|in:4,6',
            'products' => 'required|array',
            'print_name' => 'nullable|string|max:100',
            'gift_message' => 'nullable|string|max:500',
        ]);

        $dbTemplate = \App\Models\CustomBoxTemplate::where('name_en', $request->template)
            ->orWhere('name_ar', $request->template)
            ->first();
        
        $basePrice = $dbTemplate ? (float) $dbTemplate->price : 2.000;
        $totalPrice = $basePrice;
        $totalQty = 0;
        $firstImage = 'trail-box.png'; // default image

        // Validate items and compute price
        foreach ($request->products as $productId => $qty) {
            $qty = intval($qty);
            if ($qty <= 0) continue;
            
            $product = Product::find($productId);
            if (!$product || $product->Status != 1) {
                return response()->json(['error' => __('Selected product is invalid or unavailable')], 422);
            }

            // Verify virtual stock
            if ($qty > $product->virtual_stock) {
                return response()->json(['error' => __('Insufficient stock for ') . (app()->getLocale() == 'en' ? $product->en_Product_Name : $product->fr_Product_Name)], 422);
            }

            $totalPrice += $product->Price * $qty;
            $totalQty += $qty;
            
            $itemName = app()->getLocale() == 'en' ? $product->en_Product_Name : $product->fr_Product_Name;
            $itemsDetails[] = "{$qty}x {$itemName}";

            if ($firstImage == 'trail-box.png' && $product->Primary_Image) {
                $firstImage = $product->Primary_Image;
            }
        }

        if ($totalQty > $request->capacity) {
            return response()->json(['error' => __('Selected items exceed the box capacity of :capacity', ['capacity' => $request->capacity])], 422);
        }

        if ($totalQty <= 0) {
            return response()->json(['error' => __('Please select at least one item to fill the box')], 422);
        }

        $boxName = app()->getLocale() == 'en' 
            ? "Custom Coffee Box ({$request->template})" 
            : "بوكس قهوة مخصص ({$request->template})";

        $customDetailsStr = implode(', ', $itemsDetails);

        $cartItem = Cart::add([
            'id' => 'custom_box_' . uniqid(),
            'name' => $boxName,
            'qty' => 1,
            'price' => $totalPrice,
            'weight' => 0, // 0 for base shipping weight rules or generic estimation
            'options' => [
                'name_ar' => "بوكس قهوة مخصص ({$request->template})",
                'image' => $firstImage,
                'is_custom_box' => true,
                'template' => $request->template,
                'capacity' => $request->capacity,
                'print_name' => $request->print_name,
                'gift_message' => $request->gift_message,
                'custom_box_details' => $customDetailsStr,
                'slug' => 'custom-box',
                'discount_price' => $totalPrice,
            ]
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'cart_count' => Cart::count(),
                'cart_total' => Cart::total(),
                'message' => __('Custom Box added to cart successfully')
            ]);
        }

        return redirect()->route('cart.content')->with('success', __('Custom Box added to cart successfully'));
    }
}
