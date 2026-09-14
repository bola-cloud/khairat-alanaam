@extends('v2.layouts.app')

@php
    $lang = app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? 'fr' : 'en';
    $cartContent = \Cart::content();
    $cartCount = \Cart::count();
    $cartTotal = \Cart::total(2, '.', '');
    $cartSubtotal = \Cart::subtotal(2, '.', ''); 
    
    // Calculate Subscription Discount
    $subDiscountPercent = 0;
    $maxDiscountAmount = PHP_INT_MAX;
    if (auth()->check()) {
        $activeSubscription = \App\Models\UserSubscription::where('user_id', auth()->id())
            ->where('status', 'active')
            ->whereDate('end_at', '>=', now())
            ->first();
        if ($activeSubscription && $activeSubscription->subscription) {
            $subDiscountPercent = $activeSubscription->subscription->discount_percent ?? 0;
            $maxDiscountAmount = $activeSubscription->subscription->max_discount_amount ?? PHP_INT_MAX;
        }
    }
    
    $subtotalVal = floatval($cartSubtotal);
    $calculatedSubDiscount = ($subDiscountPercent / 100) * $subtotalVal;
    $subscriptionDiscountAmount = min($calculatedSubDiscount, $maxDiscountAmount);

    // Calculate Coupon Discount
    $couponDiscount = floatval(session()->get('CouponAmount', 0));
    
    $totalDiscount = $subscriptionDiscountAmount + $couponDiscount;
    $rawSubtotal = floatval($cartSubtotal);
    $finalTotal = max(0, $rawSubtotal - $totalDiscount);
@endphp

@section('title', __('v2_store.store_title', ['default' => 'سلة التسوق']))

@section('content')
<style>
    .cart-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 30px;
        align-items: start;
    }
    @media (max-width: 991px) {
        .cart-grid {
            grid-template-columns: 1fr;
        }
        .cart-summary {
            order: -1; /* Put summary on top in mobile or let it stay below */
        }
    }
    .qty-btn {
        width: 30px;
        height: 30px;
        border: none;
        background: #f5f5f5;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-weight: bold;
        color: #333;
        transition: 0.2s;
    }
    .qty-btn:hover {
        background: #e0e0e0;
    }
    .remove-btn {
        background: none;
        border: none;
        color: var(--text-light);
        cursor: pointer;
        padding: 5px;
        transition: 0.2s;
    }
    .remove-btn:hover {
        color: var(--primary-color);
    }
</style>

<div class="v2-container" style="padding: 40px 15px;">
    
    <!-- Breadcrumb -->
    <nav style="font-size: 14px; margin-bottom: 30px; color: var(--text-light);">
        <a href="{{ route('front') }}" style="color: var(--text-color); text-decoration: none;">@lang('v2_product.home')</a> 
        <span style="margin: 0 5px;">/</span> 
        <span>@lang('v2_home.cart_title')</span>
    </nav>

    <!-- Header -->
    <div style="margin-bottom: 30px;">
        <h1 style="margin: 0; font-size: 28px; font-weight: 800; color: #333;">@lang('v2_home.cart_title') <span style="font-size: 16px; color: var(--text-light); font-weight: normal; margin-right: 5px;" id="cart-header-count">({{ $cartCount }} @lang('v2_home.products_count'))</span></h1>
    </div>

    @if($cartCount > 0)
    <div class="cart-grid">
        
        <!-- Cart Items List (Right Side) -->
        <div class="cart-items-container">
            @foreach($cartContent as $item)
                @php
                    $productName = $lang == 'fr' ? ($item->options->name_ar ?? $item->name) : $item->name;
                    $itemImage = $item->options->image && $item->options->image != 'default.png' ? asset(ProductImage() . $item->options->image) : asset('assets/images/placeholder.png');
                    $originalPrice = \App\Models\Admin\Product::find($item->id)->Price ?? $item->price;
                @endphp
                <div class="cart-item-row" id="row-{{ $item->rowId }}" style="display: flex; gap: 20px; background: #fff; padding: 20px; border: 1px solid var(--border-color); border-radius: 12px; margin-bottom: 15px; position: relative;">
                    <!-- Image -->
                    <div style="flex-shrink: 0;">
                        <img src="{{ $itemImage }}" alt="{{ $productName }}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;">
                    </div>
                    
                    <!-- Details -->
                    <div style="flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <h3 style="margin: 0 0 10px 0; font-size: 16px; font-weight: 800; color: var(--text-color);">{{ $productName }}</h3>
                            
                            @if($item->options->size || $item->options->size_ar)
                                <div style="display: inline-block; background: #f9f9f9; padding: 4px 10px; border-radius: 20px; font-size: 12px; color: var(--text-light); border: 1px solid #eee; margin-bottom: 5px;">
                                    {{ $lang == 'fr' ? ($item->options->size_ar ?? $item->options->size) : $item->options->size }}
                                </div>
                            @endif
                            @if($item->weight > 0)
                                <div style="display: inline-block; background: #f9f9f9; padding: 4px 10px; border-radius: 20px; font-size: 12px; color: var(--text-light); border: 1px solid #eee; margin-bottom: 5px;">
                                    {{ $item->weight }}
                                </div>
                            @endif
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 15px;">
                            <!-- Price -->
                            <div>
                                @if($originalPrice > $item->price)
                                    <div style="text-decoration: line-through; color: var(--text-light); font-size: 12px; margin-bottom: 2px;">{{ number_format($originalPrice, 2) }}</div>
                                @endif
                                <div style="font-size: 18px; font-weight: 800; color: var(--primary-color);">
                                    <span id="price-{{ $item->rowId }}">{{ number_format($item->price, 2) }}</span> <span style="font-size: 12px;">@lang('v2_layout.currency_omr', ['default' => 'ر.ع'])</span>
                                </div>
                            </div>
                            
                            <!-- Actions -->
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <button type="button" class="remove-btn" onclick="deleteCartItem('{{ $item->rowId }}')" title="حذف">
                                    <i class="far fa-trash-alt" style="font-size: 16px;"></i>
                                </button>
                                
                                <div style="display: flex; align-items: center; gap: 10px; background: #fff; border: 1px solid #eee; border-radius: 8px; padding: 4px;">
                                    <button type="button" class="qty-btn" onclick="updateQty('{{ $item->rowId }}', 'decrease')"><i class="fas fa-minus" style="font-size: 10px;"></i></button>
                                    <span id="qty-{{ $item->rowId }}" style="font-size: 14px; font-weight: 700; min-width: 20px; text-align: center;">{{ $item->qty }}</span>
                                    <button type="button" class="qty-btn" onclick="updateQty('{{ $item->rowId }}', 'increase')"><i class="fas fa-plus" style="font-size: 10px;"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Order Summary (Left Side) -->
        <div class="cart-summary">
            <div style="background: #fff; padding: 25px; border-radius: 12px; border: 1px solid var(--border-color); position: sticky; top: 20px;">
                <h3 style="margin: 0 0 20px 0; font-size: 18px; font-weight: 800; text-align: center;">@lang('v2_home.order_summary')</h3>
                

                   <div style="display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 14px; color: var(--text-color);">
                    <span>@lang('v2_home.subtotal')</span>
                    <span style="font-weight: 600;"><span id="summary-subtotal">{{ number_format($rawSubtotal, 2) }}</span> @lang('v2_layout.currency_omr')</span>
                </div>
                
                @if($subscriptionDiscountAmount > 0)
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px; color: var(--primary-color);">
                    <span>{{ $lang == 'fr' ? 'خصم الاشتراك' : 'Subscription Discount' }}</span>
                    <span style="font-weight: 600;" dir="ltr">- <span id="summary-sub-discount">{{ number_format($subscriptionDiscountAmount, 2) }}</span> @lang('v2_layout.currency_omr')</span>
                </div>
                @endif

                @if($couponDiscount > 0)
                <div style="display: flex; justify-content: space-between; margin-bottom: 20px; font-size: 14px; color: var(--primary-color); border-bottom: 1px solid #eee; padding-bottom: 20px;">
                    <span>{{ $lang == 'fr' ? 'خصم الكوبون' : 'Coupon Discount' }} ({{ session('couponCode') }})</span>
                    <span style="font-weight: 600;" dir="ltr">- <span id="summary-coupon-discount">{{ number_format($couponDiscount, 2) }}</span> @lang('v2_layout.currency_omr')</span>
                </div>
                @endif
                
                <div style="display: flex; justify-content: space-between; margin-bottom: 25px; font-size: 18px; font-weight: 800; color: var(--text-color);">
                    <span>@lang('v2_home.total')</span>
                    <span style="color: var(--primary-color);"><span id="summary-total">{{ number_format($finalTotal, 2) }}</span> @lang('v2_layout.currency_omr')</span>
                </div>
                
                <!-- Coupon Code -->
                <div style="margin-bottom: 25px;">
                    <form action="{{ route('apply.coupon') }}" method="POST">
                        @csrf
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-color); margin-bottom: 8px;">@lang('v2_home.got_coupon')</label>
                        <div style="display: flex; gap: 10px;">
                            <input type="text" name="coupon_code" value="{{ session('couponCode', '') }}" placeholder="@lang('v2_home.enter_coupon')" style="flex-grow: 1; padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px; font-family: inherit; font-size: 14px; outline: none; text-align: left;" dir="ltr">
                            <button type="submit" style="padding: 0 20px; background: var(--primary-color); color: #fff; border: none; border-radius: 8px; font-weight: 700; font-family: inherit; cursor: pointer;">@lang('v2_home.apply')</button>
                        </div>
                    </form>
                </div>
                
                <a href="{{ route('checkout') }}" class="btn-primary" style="display: block; text-align: center; padding: 15px; font-size: 16px; border-radius: 8px; margin-bottom: 10px;">@lang('v2_home.checkout')</a>
                <a href="{{ route('front.store') }}" class="btn-outline" style="display: block; text-align: center; padding: 15px; font-size: 16px; border-radius: 8px; background: #f9f9f9; color: var(--text-color); border: 1px solid #eee; text-decoration: none; font-weight: 700;">@lang('v2_home.continue_shopping')</a>
            </div>
        </div>

    </div>
    @else
    <!-- Empty State -->
    <div style="text-align: center; padding: 80px 20px; background: #fff; border-radius: 12px; border: 1px solid var(--border-color); max-width: 600px; margin: 0 auto;">
        <i class="fas fa-shopping-basket" style="font-size: 80px; color: #f0f0f0; margin-bottom: 25px;"></i>
        <h3 style="font-size: 24px; font-weight: 800; color: #333; margin-bottom: 15px;">@lang('v2_home.cart_empty_title')</h3>
        <p style="color: var(--text-light); margin-bottom: 35px; line-height: 1.6;">@lang('v2_home.cart_empty_desc')</p>
        <a href="{{ route('front.store') }}" class="btn-primary" style="padding: 14px 40px; font-size: 16px; border-radius: 8px;">@lang('v2_home.browse_store')</a>
    </div>
    @endif

    <!-- Related Products -->
    @if(isset($relatedProducts) && $relatedProducts->count() > 0)
    <div style="margin-top: 60px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h2 style="margin: 0; font-size: 22px; font-weight: 800; color: #333;">@lang('v2_home.you_may_also_like')</h2>
            <a href="{{ route('front.store') }}" style="color: var(--primary-color); font-weight: 700; text-decoration: none; font-size: 14px; display: flex; align-items: center; gap: 5px;">
                @lang('v2_home.view_all') <i class="fas fa-chevron-left" style="font-size: 10px;"></i>
            </a>
        </div>
        
        <div class="grid-products" style="display: grid; gap: 20px; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));">
            @foreach($relatedProducts as $relProduct)
                @php
                    $relName = $lang == 'fr' ? ($relProduct->fr_Product_Name ?? $relProduct->en_Product_Name) : $relProduct->en_Product_Name;
                    $relAbout = $lang == 'fr' ? ($relProduct->fr_About ?? $relProduct->en_About) : $relProduct->en_About;
                @endphp
                <div class="product-card" style="background:#fff; border: 1px solid #eee; border-radius:12px; padding:15px; transition: 0.3s; position: relative; display: flex; flex-direction: column; height: 100%;">
                    <!-- Wishlist Icon -->
                    <button type="button" style="position: absolute; top: 25px; left: 25px; width: 35px; height: 35px; background: #fff; border: 1px solid #eee; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 2;">
                        <i class="far fa-heart" style="font-size: 16px; color: #555;"></i>
                    </button>
                    <!-- Stock Status Badge -->
                    @if($relProduct->Quantity <= 0)
                    <span style="position: absolute; top: 185px; right: 25px; background: #f5f5f5; color: #777; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: 600; z-index: 2;">
                        @lang('v2_store.out_of_stock', ['default' => 'نفذ من المخزون'])
                    </span>
                    @endif
                    
                    <a href="{{ route('front.product_details', $relProduct->en_Product_Slug ?: $relProduct->id) }}" style="display: block; position: relative; height: 200px; padding: 20px;">
                        <img src="{{ $relProduct->Primary_Image && $relProduct->Primary_Image != 'default.png' ? asset(ProductImage() . $relProduct->Primary_Image) : asset('assets/images/placeholder.png') }}" alt="{{ $relName }}" style="width:100%; height:100%; object-fit:contain;">
                    </a>
                    
                    <div style="flex-grow: 1; display: flex; flex-direction: column; padding: 0 10px 10px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; font-size: 12px; color: #888;">
                            <span>(4.5) <i class="fas fa-star" style="color: #e32636;"></i>(1.4K)</span>
                        </div>
                        <h4 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 700; color: #333;">
                            <a href="{{ route('front.product_details', $relProduct->en_Product_Slug ?: $relProduct->id) }}" style="color: inherit; text-decoration: none;">{{ $relName }}</a>
                        </h4>
                        <p style="margin: 0 0 15px 0; font-size: 13px; color: #888; line-height: 1.4;">{{ Str::limit($relAbout, 50) }}</p>
                        
                        <div style="margin-top: auto;">
                            <div style="display: flex; justify-content: center; gap: 10px; align-items: center; margin-bottom: 15px;">
                                @php
                                    $relFinalPrice = ($relProduct->Discount_Price > 0 && $relProduct->Discount_Price < $relProduct->Price) ? $relProduct->Discount_Price : $relProduct->Price;
                                    $relHasDiscount = $relFinalPrice < $relProduct->Price;
                                @endphp
                                <div style="font-size: 18px; font-weight: 800; color: #e32636;">
                                    {{ number_format($relFinalPrice, 3) }} ر.ع
                                </div>
                                @if($relHasDiscount)
                                <div style="text-decoration: line-through; color: #999; font-size: 13px;">
                                    {{ number_format($relProduct->Price, 3) }} ر.ع
                                </div>
                                @endif
                            </div>
                            <button type="button" onclick="addToCart('{{ $relProduct->id }}', '{{ $relFinalPrice }}')" class="add-to-cart-btn" style="width: 100%; padding: 12px; background: #e32636; color: #fff; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; transition: 0.3s;">@lang('v2_home.add_to_cart')</button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

<!-- Scripts for AJAX Cart Operations -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function updateDOM(tc, ta, st, rowId, isDelete = false) {
        // Update header count
        document.getElementById('cart-header-count').innerText = '(' + tc + ' منتجات)';
        
        // Update cart icon badge in the global layout if it has a specific ID or class
        // Assuming there is a badge with class 'cart-badge'
        $('.cart-badge').text(tc);

        if(isDelete) {
            $('#row-' + rowId).fadeOut(300, function() { 
                $(this).remove(); 
                if(tc == 0) location.reload(); // Reload to show empty state
            });
        }

        // ta is the total amount (subtotal raw)
        let subtotal = parseFloat(ta);
        
        // Update item specific price (st is subtotal for this row)
        if(!isDelete && st !== undefined) {
            $('#price-' + rowId).text(parseFloat(st).toFixed(2));
        }

        // Re-calculate discounts
        $('#summary-subtotal').text(subtotal.toFixed(2));
        
        let subDiscountText = $('#summary-sub-discount').length ? $('#summary-sub-discount').text() : '0';
        let subDiscount = parseFloat(subDiscountText) || 0;
        
        let couponDiscountText = $('#summary-coupon-discount').length ? $('#summary-coupon-discount').text() : '0';
        let couponDiscount = parseFloat(couponDiscountText) || 0;
        
        // Subscription discount percentage calculation (if backend sent updated total, we could use it, but here's a dynamic approach)
        let subPercent = {{ floatval($subDiscountPercent) }};
        if(subPercent > 0) {
            let maxD = {{ floatval($maxDiscountAmount) }};
            let calc = (subtotal * subPercent) / 100;
            subDiscount = Math.min(calc, maxD);
            if($('#summary-sub-discount').length) {
                $('#summary-sub-discount').text(subDiscount.toFixed(2));
            }
        }
        
        let totalDiscount = subDiscount + couponDiscount;
        let finalTotal = Math.max(0, subtotal - totalDiscount);
        $('#summary-total').text(finalTotal.toFixed(2));
    }

    function updateQty(rowId, action) {
        let url = action === 'increase' ? "{{ route('cart.increase') }}" : "{{ route('cart.decrease') }}";
        
        // Optimistic UI update
        let qtySpan = $('#qty-' + rowId);
        let currentQty = parseInt(qtySpan.text());
        if(action === 'increase') qtySpan.text(currentQty + 1);
        if(action === 'decrease' && currentQty > 1) qtySpan.text(currentQty - 1);
        
        $.ajax({
            url: url,
            type: "GET",
            data: { id: rowId },
            success: function(response) {
                if(response.error) {
                    alert(response.error);
                    qtySpan.text(currentQty); // revert
                    return;
                }
                // response: [0: tc, 1: ta, 2: cd, 3: st, 'total_amount_formatted', 'subtotal_formatted']
                let tc = response[0];
                let ta = response[1];
                let st = response[3];
                updateDOM(tc, ta, st, rowId);
            },
            error: function(xhr) {
                qtySpan.text(currentQty); // revert
                if(xhr.responseJSON && xhr.responseJSON.error) {
                    alert(xhr.responseJSON.error);
                }
            }
        });
    }

    function deleteCartItem(rowId) {
        if(confirm('{{ __("v2_home.confirm_remove_cart") }}')) {
            $.ajax({
                url: "{{ route('cart.delete') }}",
                type: "GET",
                data: { id: rowId },
                success: function(response) {
                    // response: [0: tc, 1: ta, 2: cd, 'total_amount_formatted']
                    let tc = response[0];
                    let ta = response[1];
                    updateDOM(tc, ta, undefined, rowId, true);
                }
            });
        }
    }

    function applyCoupon() {
        // Now handled by native form submission
    }
</script>
@endsection
